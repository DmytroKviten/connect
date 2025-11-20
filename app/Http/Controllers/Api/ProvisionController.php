<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SetupToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProvisionController extends Controller
{
    /**
     * Крок 2 майстра (режим AP):
     *  - Читаємо Sys.GetStatus (mac/model)
     *  - Прописуємо Wi-Fi
     *  - Ставимо auto-claim: provisioning_url або одноразовий скрипт
     *
     * Body: { ap_ip, ssid, password?, token }
     */
    public function claim(Request $r)
    {
        $r->validate([
            'ap_ip'    => 'required|ip',
            'ssid'     => 'required|string',
            'password' => 'nullable|string',
            'token'    => 'required|string|max:128',
        ]);

        $user = $r->user();
        $ap   = (string) $r->input('ap_ip');
        $ssid = (string) $r->input('ssid');
        $pass = (string) $r->input('password', '');
        $tk   = (string) $r->input('token');

        // Перевіряємо токен і власника
        $st = SetupToken::where('token', $tk)->first();
        if (!$st || !$st->isValid() || $st->user_id !== $user->id) {
            return response()->json(['ok' => false, 'error' => 'invalid_or_expired_token'], 401);
        }

        // 1) Дізнаємося MAC / модель з AP
        $uid = $mac = $model = null;
        try {
            $sys   = Http::timeout(5)->get("http://{$ap}/rpc/Sys.GetStatus")->json();
            $uid   = $sys['mac']   ?? null;
            $mac   = $uid ? strtoupper(preg_replace('/[^A-F0-9]/', '', $uid)) : null;
            $model = $sys['model'] ?? 'Plus Plug S';

            // Плейсхолдер у списку
            if ($mac) {
                Device::firstOrCreate(
                    ['user_id' => $user->id, 'mac' => $mac],
                    ['uid' => $mac, 'name' => 'Пристрій', 'brand' => 'Shelly', 'model' => $model]
                );
            }
        } catch (\Throwable $e) {
            Log::warning("AP Sys.GetStatus fail: ".$e->getMessage());
            return response()->json(['ok' => false, 'error' => 'ap_unreachable'], 502);
        }

        // 2) Прописуємо Wi-Fi домашньої мережі
        try {
            Http::timeout(5)->post("http://{$ap}/rpc/WiFi.SetConfig", [
                'config' => [
                    'sta' => ['ssid' => $ssid, 'pass' => $pass, 'enable' => true],
                    'ap'  => ['enable' => false],
                ],
                'save' => true,
            ])->throw();
        } catch (\Throwable $e) {
            Log::notice("WiFi.SetConfig issue: ".$e->getMessage());
            // продовжимо — нижче все одно спробуємо поставити auto-claim
        }

        // 3) Намагаємось виставити авто-реєстрацію
        //    (a) provisioning_url → /api/device/ping?token=...
        //    Якщо не вдасться, (b) поставимо одноразовий скрипт, який сам зробить POST на ping після підйому Wi-Fi
        $baseHost = rtrim(config('app.url'), '/'); // МАЄ бути не localhost!
        $cbQuery  = $baseHost . route('api.device.ping', [], false) . '?token=' . urlencode($tk);

        $autoClaimOk = false;
        try {
            Http::timeout(5)->post("http://{$ap}/rpc/Device.SetConfig", [
                'provisioning_url' => $cbQuery,
            ])->throw();
            $autoClaimOk = true;
        } catch (\Throwable $e) {
            Log::notice("Device.SetConfig(provisioning_url) failed: ".$e->getMessage());
        }

        if (!$autoClaimOk) {
            // Резервний варіант: одноразовий скрипт ClaimOnce
            try {
                $script = $this->makeOneShotClaimScript($baseHost, $tk, $mac);
                $id = null;

                // Створити вимкнений скрипт з ім'ям ClaimOnce (або взяти існуючий)
                $list = Http::timeout(4)->get("http://{$ap}/rpc/Script.List")->json();
                foreach (($list['scripts'] ?? []) as $s) {
                    if (($s['name'] ?? '') === 'ClaimOnce') { $id = $s['id']; break; }
                }
                if ($id === null) {
                    $resp = Http::timeout(4)->post("http://{$ap}/rpc/Script.Create", [
                        'name'   => 'ClaimOnce',
                        'enable' => false,
                    ])->json();
                    $id = $resp['id'] ?? 0;
                } else {
                    try { Http::timeout(3)->post("http://{$ap}/rpc/Script.Stop", ['id' => $id]); } catch (\Throwable $e) {}
                }

                // Заливаємо код і запускаємо
                Http::timeout(4)->post("http://{$ap}/rpc/Script.PutCode", ['id' => $id, 'code' => $script])->throw();
                Http::timeout(4)->post("http://{$ap}/rpc/Script.Start",   ['id' => $id])->throw();

                $autoClaimOk = true;
            } catch (\Throwable $e) {
                Log::warning("Fallback ClaimOnce script failed: ".$e->getMessage());
            }
        }

        // (не обов'язково) м'який ребут — деякі моделі швидше перебудовують мережу
        try {
            Http::timeout(3)->post("http://{$ap}/rpc/Shelly.Reboot");
        } catch (\Throwable $e) {}

        return response()->json([
            'ok'         => true,
            'auto_claim' => $autoClaimOk,
            'hint'       => $autoClaimOk
                ? 'Після підключення до Wi-Fi пристрій сам відмітиться у списку.'
                : 'Автопінг не налаштовано. За потреби встанови push-скрипт (кнопка "Встановити push").',
        ]);
    }

    /**
     * Ручна реєстрація: користувач дає IP у домашній мережі — читаємо MAC і створюємо девайс.
     * Body: { ip }
     */
    public function registerManual(Request $r)
    {
        $r->validate(['ip' => 'required|ip']);
        $user = $r->user();
        $ip   = (string) $r->input('ip');

        try {
            $sys   = Http::timeout(4)->get("http://{$ip}/rpc/Sys.GetStatus")->json();
            $uid   = $sys['mac']   ?? null;
            $model = $sys['model'] ?? 'Plus Plug S';
            if (!$uid) return response()->json(['ok' => false, 'error' => 'no_mac_from_device'], 422);

            $mac = strtoupper(preg_replace('/[^A-F0-9]/', '', $uid));
            $device = Device::updateOrCreate(
                ['user_id' => $user->id, 'mac' => $mac],
                ['uid' => $mac, 'ip' => $ip, 'name' => 'Пристрій', 'brand' => 'Shelly', 'model' => $model, 'last_seen_at' => now()]
            );

            return response()->json(['ok' => true, 'device_id' => $device->id]);
        } catch (\Throwable $e) {
            Log::warning("registerManual fail: ".$e->getMessage());
            return response()->json(['ok' => false, 'error' => 'connect_failed'], 502);
        }
    }

    /**
     * Ставимо на пристрій push-скрипт (кожні 5 с POST у /api/ingest).
     * ВАЖЛИВО: APP_URL має бути доступний з мережі пристрою (не localhost!).
     */
    public function installPush(Request $r, Device $device)
    {
        abort_unless($device->user_id === Auth::id(), 403);
        if (!$device->ip) return response()->json(['ok' => false, 'error' => 'no_ip_on_device'], 400);

        $base = rtrim((string) config('app.url'), '/');
        if (preg_match('~^https?://(localhost|127\.0\.0\.1)~i', $base)) {
            return response()->json(['ok' => false, 'error' => 'app_url_unreachable_for_device', 'hint' => 'Встанови APP_URL на LAN/VPS адресу, яку бачить розетка.'], 400);
        }

        $url  = $base . '/api/ingest';
        $code = $this->makeShellyPushScript($url);

        try {
            // Пошук існуючого скрипта
            $list = Http::timeout(4)->get("http://{$device->ip}/rpc/Script.List")->json();
            $id = null;
            foreach (($list['scripts'] ?? []) as $s) {
                if (($s['name'] ?? '') === 'PushToServer') { $id = $s['id']; break; }
            }

            if ($id === null) {
                $created = Http::timeout(4)->post("http://{$device->ip}/rpc/Script.Create", [
                    'name'   => 'PushToServer',
                    'enable' => true,
                ])->json();
                $id = $created['id'] ?? 0;
            } else {
                try { Http::timeout(3)->post("http://{$device->ip}/rpc/Script.Stop", ['id' => $id]); } catch (\Throwable $e) {}
            }

            Http::timeout(4)->post("http://{$device->ip}/rpc/Script.PutCode", [
                'id'   => $id,
                'code' => $code,
            ])->throw();

            Http::timeout(4)->post("http://{$device->ip}/rpc/Script.Start", ['id' => $id])->throw();

            return response()->json(['ok' => true, 'script_id' => $id, 'url' => $url]);
        } catch (\Throwable $e) {
            Log::warning("installPush fail: ".$e->getMessage());
            return response()->json(['ok' => false, 'error' => 'script_failed'], 500);
        }
    }

    /**
     * Періодичний push скрипт (кожні 5 секунд).
     */
    private function makeShellyPushScript(string $url): string
    {
        return <<<JS
let URL = "{$url}";
function push() {
  Shelly.call("Switch.GetStatus", {id:0}, function(st, e1) {
    if (e1) return;
    Shelly.call("Sys.GetStatus", {}, function(sys, e2) {
      if (e2) return;
      let body = JSON.stringify({
        mac: sys.mac,
        ip: (sys.wifi && sys.wifi.sta_ip) ? sys.wifi.sta_ip : null,
        power_w:   (typeof st.apower === "number") ? st.apower : null,
        voltage_v: (typeof st.voltage === "number") ? st.voltage : null,
        energy_wh: (st.aenergy && typeof st.aenergy.total === "number") ? Math.round(st.aenergy.total) : null,
        taken_at:  (new Date()).toISOString()
      });
      Shelly.call("HTTP.Request", {
        method: "POST",
        url: URL,
        headers: {"Content-Type":"application/json"},
        body: body
      }, function(){});
    });
  });
}
Timer.set(5000, true, push);
JS;
    }

    /**
     * Одноразовий скрипт для авто-claim (fallback, якщо provisioning_url не вдається).
     */
    private function makeOneShotClaimScript(string $baseUrl, string $token, ?string $mac): string
    {
        $pingUrl = $baseUrl . route('api.device.ping', [], false);
        $tokenJs = json_encode($token);
        $macJs   = json_encode($mac ?? '');

        return <<<JS
let URL = "{$pingUrl}";
let TOKEN = {$tokenJs};
let PRESET_MAC = {$macJs};

// через 8 секунд після старту робимо один POST на /api/device/ping
Timer.set(8000, false, function() {
  Shelly.call("Sys.GetStatus", {}, function(sys, e) {
    if (e) return;
    let body = {
      token: TOKEN,
      mac: PRESET_MAC || sys.mac,
      ip: (sys.wifi && sys.wifi.sta_ip) ? sys.wifi.sta_ip : null,
      name: sys.model || "Shelly"
    };
    Shelly.call("HTTP.Request", {
      method: "POST",
      url: URL,
      headers: {"Content-Type":"application/json"},
      body: JSON.stringify(body)
    }, function(){});
  });
});
JS;
    }
}
