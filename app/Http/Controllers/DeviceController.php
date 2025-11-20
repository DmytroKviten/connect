<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SetupToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class DeviceController extends Controller
{
    /* ==========================================================
     |  WEB (SPA оболонка)
     ========================================================== */

    public function index()
    {
        return view('app');
    }

    public function show(Device $device)
    {
        return view('app');
    }

    /* ==========================================================
     |  API (auth:sanctum)
     ========================================================== */

    /**
     * GET /api/devices
     * Повертає список пристроїв користувача
     */
    public function apiIndex(Request $request)
    {
        $user = $request->user();

        $devices = Device::with('latestReading')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['devices' => $devices]);
    }

    /**
     * GET /api/devices/{device}
     * Деталі пристрою
     */
    public function apiShow(Request $request, Device $device)
    {
        abort_unless($device->user_id === $request->user()->id, Response::HTTP_FORBIDDEN);

        return response()->json([
            'id'           => $device->id,
            'name'         => $device->name,
            'model'        => $device->model ?? 'Unknown',
            'ip'           => $device->ip,
            'mac'          => $device->mac,
            'last_seen_at' => $device->last_seen_at,
        ]);
    }

    /**
     * POST /api/setup-token
     * Створює короткоживучий токен для первинного сетапу
     */
    public function createSetupToken(Request $request)
    {
        $user = $request->user();
        $ttl  = max(60, min(3600, (int) $request->integer('ttl', 900))); // 15 хв за замовчуванням

        do {
            $plain = bin2hex(random_bytes(20));
        } while (SetupToken::where('token', $plain)->exists());

        SetupToken::create([
            'user_id'    => $user?->id,
            'token'      => $plain,
            'expires_at' => now()->addSeconds($ttl),
        ]);

        return response()->json([
            'token'        => $plain,
            'expires_in'   => $ttl,
            'callback_url' => route('api.ingest'),
            'ping_url'     => route('api.device.ping'),
        ]);
    }

    /**
     * GET /api/devices/{device}/live
     * Поточні показники Shelly
     */
    public function live(Request $request, Device $device)
    {
        abort_unless($device->user_id === $request->user()->id, Response::HTTP_FORBIDDEN);

        $resp = $this->shellyRequest($device->ip, 'GET', '/rpc/Switch.GetStatus', ['id' => 0]);
        if (!$resp) {
            return response()->json(['error' => 'Shelly offline'], 502);
        }

        return response()->json([
            'power'   => $resp['apower'] ?? ($resp['switch:0']['apower'] ?? null),
            'voltage' => $resp['voltage'] ?? ($resp['switch:0']['voltage'] ?? null),
            'energy'  => $resp['aenergy']['total'] ?? ($resp['switch:0']['aenergy']['total'] ?? null),
            'state'   => $this->extractOutput($resp),
        ]);
    }

    /**
     * GET /api/devices/{device}/state
     * Поточний стан (on/off)
     */
    public function state(Request $request, Device $device)
    {
        abort_unless($device->user_id === $request->user()->id, Response::HTTP_FORBIDDEN);

        $resp = $this->shellyRequest($device->ip, 'GET', '/rpc/Switch.GetStatus', ['id' => 0]);
        return response()->json(['output' => $this->extractOutput($resp) ?? false]);
    }

    /**
     * POST /api/devices/{device}/toggle
     * Увімкнути / Вимкнути пристрій
     */
    public function toggleSwitch(Request $request, Device $device)
    {
        abort_unless($device->user_id === $request->user()->id, Response::HTTP_FORBIDDEN);

        $data = $request->validate(['on' => 'required|boolean']);
        $on   = (bool) $data['on'];

        $resp = $this->shellyRequest($device->ip, 'POST', '/rpc/Switch.Set', [
            'id' => 0,
            'on' => $on,
        ]);

        if (!$resp) {
            return response()->json(['error' => 'Shelly offline'], 502);
        }

        return response()->json(['output' => $on]);
    }

    public function reboot(Request $request, Device $device)
    {
        abort_unless($device->user_id === $request->user()->id, Response::HTTP_FORBIDDEN);
        $resp = $this->shellyRequest($device->ip, 'POST', '/rpc/Shelly.Reboot');
        return $resp ? response()->json(['success' => true]) : response()->json(['error' => 'Shelly offline'], 502);
    }

    public function factoryReset(Request $request, Device $device)
    {
        abort_unless($device->user_id === $request->user()->id, Response::HTTP_FORBIDDEN);
        $resp = $this->shellyRequest($device->ip, 'POST', '/rpc/Shelly.FactoryReset');
        return $resp ? response()->json(['success' => true]) : response()->json(['error' => 'Shelly offline'], 502);
    }

    public function info(Request $request, Device $device)
    {
        abort_unless($device->user_id === $request->user()->id, Response::HTTP_FORBIDDEN);
        $resp = $this->shellyRequest($device->ip, 'GET', '/rpc/Sys.GetStatus');
        return $resp ? response()->json($resp) : response()->json(['error' => 'Shelly offline'], 502);
    }

    /**
     * GET /api/devices/{device}/chart
     * Дані для графіка потужності
     */
    public function chartData(Request $request, Device $device)
    {
        abort_unless($device->user_id === $request->user()->id, Response::HTTP_FORBIDDEN);

        $readings = $device->readings()
            ->orderByDesc('taken_at')
            ->limit(20)
            ->get(['taken_at', 'power_w'])
            ->reverse();

        return response()->json([
            'labels' => $readings->pluck('taken_at')->map(fn($d) => Carbon::parse($d)->format('H:i:s'))->values(),
            'power'  => $readings->pluck('power_w')->values(),
        ]);
    }

    /* ==========================================================
     |  ПУБЛІЧНІ ДЛЯ ПЕРВИННОГО ПІДКЛЮЧЕННЯ
     ========================================================== */

    public function ping(Request $request)
    {
        $validated = $request->validate([
            'token'      => 'required|string|min:16|max:128',
            'mac'        => 'nullable|string|max:32',
            'ip'         => 'nullable|ip',
            'ip_address' => 'nullable|ip',
            'addr'       => 'nullable|ip',
        ]);

        $setup = SetupToken::where('token', $validated['token'])
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$setup) {
            return response()->json(['ok' => false, 'error' => 'invalid_or_expired_token'], 422);
        }

        $macInput = $validated['mac'] ?? null;
        $ip = $validated['ip'] ?? $validated['ip_address'] ?? $validated['addr'] ?? $request->ip();

        if ($macInput) {
            $macColon = $this->normalizeMac($macInput);
            $uid = strtoupper(str_replace(':', '', $macColon));

            $device = Device::where('user_id', $setup->user_id)
                ->where(fn($q) => $q->where('uid', $uid)
                    ->orWhere('mac', $macColon)
                    ->orWhere('mac', str_replace(':', '', $macColon)))
                ->first();

            if (!$device) {
                $device = Device::create([
                    'user_id' => $setup->user_id,
                    'uid'     => $uid,
                    'mac'     => $macColon,
                    'name'    => 'Розетка',
                    'brand'   => 'Shelly',
                    'model'   => 'Unknown',
                ]);
            } else {
                $device->uid = $uid;
                if (!in_array($device->mac, [$macColon, $uid], true)) {
                    $device->mac = $macColon;
                }
            }

            if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                $device->ip = $ip;
            }

            $device->last_seen_at = now();
            $device->save();

            if (!$setup->device_mac) {
                $setup->device_mac = $macColon;
                $setup->used_at = now();
                $setup->save();
            }
        }

        return response()->json([
            'ok'          => true,
            'ingest_url'  => route('api.ingest'),
            'expires_at'  => $setup->expires_at,
            'claimed_mac' => $setup->device_mac,
        ]);
    }

    public function scanWifi(Request $request)
    {
        $ip = $request->input('ip', '192.168.33.1');

        try {
            $json = Http::timeout(8)->get("http://{$ip}/rpc/WiFi.Scan", [
                'index'      => 0,
                'timeout_ms' => 5000,
            ])->json();

            return collect($json['results'] ?? $json['aps'] ?? [])
                ->pluck('ssid')
                ->filter()
                ->values();
        } catch (\Throwable $e) {
            Log::warning('Shelly scan failed: ' . $e->getMessage());
            return response()->json(['error' => 'connect_failed'], 502);
        }
    }

    public function configWifi(Request $request)
    {
        $request->validate([
            'ip'       => 'required|ip',
            'ssid'     => 'required|string',
            'password' => 'nullable|string',
        ]);

        $user = $request->user();
        $ip   = $request->input('ip');
        $ssid = (string)$request->string('ssid');
        $pass = (string)$request->input('password', '');

        try {
            $sys = $this->httpTo($ip)->get("http://{$ip}/rpc/Sys.GetStatus")->json();
            $uid   = $sys['mac']   ?? null;
            $model = $sys['model'] ?? 'Unknown';

            Http::timeout(6)->post("http://{$ip}/rpc/WiFi.SetConfig", [
                'config' => [
                    'sta' => ['ssid' => $ssid, 'pass' => $pass, 'enable' => true],
                    'ap'  => ['enable' => true],
                ],
                'save' => true,
            ])->throw();

            Http::timeout(4)->post("http://{$ip}/rpc/WiFi.Connect")->throw();

            $newIp = null; $lastReason = null; $state = null;
            for ($i = 0; $i < 12; $i++) {
                usleep(2000000);
                $st = Http::timeout(3)->get("http://{$ip}/rpc/WiFi.GetStatus")->json();
                $sta = $st['sta'] ?? $st;
                $state = $sta['status'] ?? $st['status'] ?? null;
                $newIp = $sta['ip'] ?? $st['sta_ip'] ?? null;
                $lastReason = $sta['reason'] ?? null;
                if (in_array(strtolower((string)$state), ['got ip', 'connected'], true) && $newIp) break;
                if (in_array(strtolower((string)$state), ['disconnected', 'invalid'], true) && $lastReason) break;
            }

            if (!$newIp) {
                return response()->json(['success' => false, 'error' => 'wifi_connect_failed', 'state' => $state], 422);
            }

            if ($uid) {
                $macNorm = $this->normalizeMac($uid);
                $device = Device::firstOrCreate(
                    ['user_id' => $user->id, 'mac' => $macNorm],
                    ['uid' => strtoupper(str_replace(':', '', $macNorm)), 'name' => 'Розетка', 'brand' => 'Shelly', 'model' => $model]
                );
                $device->ip = $newIp;
                $device->last_seen_at = now();
                $device->save();
            }

            return response()->json(['success' => true, 'ip' => $newIp, 'state' => $state]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'unexpected', 'detail' => $e->getMessage()], 500);
        }
    }

    /* ==========================================================
     |  ДОДАТКОВІ (демо та видалення)
     ========================================================== */

    /** POST /api/devices/demo-add */
    public function demoAdd(Request $request)
    {
        $user = $request->user();
        $type = $request->input('type');

        $map = [
            'tv'     => ['Телевізор', 'entertainment', 'TV Samsung', '192.168.1.201'],
            'fridge' => ['Холодильник', 'kitchen', 'Bosch A++', '192.168.1.202'],
            'boiler' => ['Котел', 'heating', 'Bosch Heat', '192.168.1.203'],
            'router' => ['Wi-Fi роутер', 'network', 'TP-Link', '192.168.1.204'],
        ];

        if (!isset($map[$type])) {
            return response()->json(['error' => 'unknown_type'], 422);
        }

        [$name, $category, $model, $ip] = $map[$type];

        $device = Device::create([
            'user_id'      => $user->id,
            'uid'          => strtoupper(bin2hex(random_bytes(6))),
            'mac'          => strtoupper(implode(':', str_split(bin2hex(random_bytes(6)), 2))),
            'ip'           => $ip,
            'name'         => $name,
            'category'     => $category,
            'brand'        => 'Demo',
            'model'        => $model,
            'last_seen_at' => now(),
        ]);

        return response()->json(['ok' => true, 'device' => $device]);
    }

    /** DELETE /api/devices/{device} */
    public function destroy(Device $device, Request $request)
    {
        if ($device->user_id !== $request->user()->id) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        $device->delete();
        return response()->json(['ok' => true]);
    }

    /* ==========================================================
     |  ВНУТРІШНІ ХЕЛПЕРИ
     ========================================================== */

    private function shellyRequest(?string $ip, string $method, string $uri, array $params = null): ?array
    {
        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) return null;
        if ($this->isRunningInDocker() && $this->isPrivateIp($ip)) return null;

        try {
            $http = $this->httpTo($ip);
            $url = "http://{$ip}{$uri}";
            $res = strtoupper($method) === 'GET'
                ? $http->get($url, $params ?? [])
                : $http->post($url, $params ?? []);
            return $res->ok() ? $res->json() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function httpTo(string $ip)
    {
        return Http::timeout(1.5)->connectTimeout(1.0);
    }

    private function extractOutput(array $payload): ?bool
    {
        foreach (['output', 'state', 'is_on'] as $k) {
            if (array_key_exists($k, $payload)) return (bool) $payload[$k];
        }
        if (isset($payload['switch']['output'])) return (bool) $payload['switch']['output'];
        if (isset($payload['switch:0']['output'])) return (bool) $payload['switch:0']['output'];
        return null;
    }

    private function normalizeMac(string $mac): string
    {
        $hex = strtoupper(preg_replace('/[^A-F0-9]/i', '', $mac));
        return strlen($hex) === 12 ? implode(':', str_split($hex, 2)) : strtoupper($mac);
    }

    private function isRunningInDocker(): bool
    {
        return file_exists('/.dockerenv')
            || (is_readable('/proc/1/cgroup') && str_contains(file_get_contents('/proc/1/cgroup'), 'docker'));
    }

    private function isPrivateIp(string $ip): bool
    {
        return str_starts_with($ip, '10.')
            || preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)
            || str_starts_with($ip, '192.168.');
    }
}
