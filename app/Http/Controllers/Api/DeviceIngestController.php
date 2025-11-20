<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Reading;
use App\Support\PowerModeClassifier;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class DeviceIngestController extends Controller
{
    /**
     * ПУБЛІЧНО: /api/ingest
     * Дані від розетки або з фронта (симуляція) -> пишемо в БД.
     *
     * ТЕПЕР підтримуємо ДВА варіанти:
     *  - mac: "A1B2C3..."
     *  - device_id: 123
     *
     * Якщо передані обидва — пріоритет у device_id.
     * Важливо: НЕ створюємо новий девайс — тільки в уже зареєстрований.
     */
    public function storeMetricsByMac(Request $r)
    {
        $payload = $this->normalize($r->all());

        $v = Validator::make($payload, [
            'mac'        => ['nullable','string','max:64'],
            'device_id'  => ['nullable','integer','exists:devices,id'],
            'ip'         => ['nullable','ip'],
            'power_w'    => ['nullable','numeric'],
            'voltage_v'  => ['nullable','numeric'],
            'energy_wh'  => ['nullable','numeric','min:0'],
            'taken_at'   => ['nullable'],
            'name'       => ['sometimes','string','max:255'],
            'brand'      => ['sometimes','string','max:255'],
            'model'      => ['sometimes','string','max:255'],
            'category'   => ['sometimes','string','max:255'],
        ]);

        if ($v->fails()) {
            return response()->json(['ok' => false, 'errors' => $v->errors()], 422);
        }

        $data = $v->validated();

        if (empty($data['mac']) && empty($data['device_id'])) {
            return response()->json(['ok' => false, 'error' => 'mac_or_device_id_required'], 422);
        }

        $data['taken_at'] = $this->parseTimestamp($data['taken_at'] ?? null) ?? now();

        if (!empty($data['device_id'])) {
            $device = Device::find($data['device_id']);
        } else {
            $device = Device::where('mac', $data['mac'])->first();
        }

        if (!$device) {
            return response()->json(['ok' => false, 'error' => 'device_not_registered'], 404);
        }

        try {
            $reading = $this->persistReading($device, $data);

            return response()->json(['ok' => true, 'reading_id' => $reading->id ?? null], 200);
        } catch (\Throwable $e) {
            Log::error('ingest failed: '.$e->getMessage(), [
                'mac' => $data['mac'] ?? null,
                'device_id' => $data['device_id'] ?? null,
            ]);
            return response()->json(['ok' => false, 'error' => 'server_error'], 500);
        }
    }

    /**
     * ПРИВАТНО: список метрик пристрою (з фільтрами)
     * GET /api/devices/{device}/metrics
     */
    public function index(Request $r, Device $device)
    {
        abort_unless($device->user_id === Auth::id(), 403);

        $limit = max(1, min((int)$r->query('limit', 100), 1000));

        $q = Reading::where('device_id', $device->id)
            ->orderByDesc('taken_at');

        if ($r->filled('from')) {
            $from = $this->parseTimestamp($r->query('from'));
            if ($from) {
                $q->where('taken_at', '>=', $from);
            }
        }

        if ($r->filled('to')) {
            $to = $this->parseTimestamp($r->query('to'));
            if ($to) {
                $q->where('taken_at', '<=', $to);
            }
        }

        $readings = $q->limit($limit)->get([
            'device_id',
            'power_w',
            'voltage_v',
            'energy_wh',
            'mode',
            'taken_at'
        ]);

        return response()->json([
            'ok'        => true,
            'device_id' => $device->id,
            'count'     => $readings->count(),
            'items'     => $readings->map(fn($x) => [
                'power_w'   => $x->power_w   !== null ? (float)$x->power_w   : null,
                'voltage_v' => $x->voltage_v !== null ? (float)$x->voltage_v : null,
                'energy_wh' => $x->energy_wh !== null ? (int)$x->energy_wh   : null,
                'taken_at'  => optional($x->taken_at)->toIso8601String(),
                'mode'      => $x->mode ?? 'unknown',
            ]),
        ]);
    }

    /**
     * ПРИВАТНО: остання метрика
     * GET /api/devices/{device}/metrics/latest
     */
    public function latest(Device $device)
    {
        abort_unless($device->user_id === Auth::id(), 403);

        $r = Reading::where('device_id', $device->id)
            ->orderByDesc('taken_at')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'ok'        => true,
            'device_id' => $device->id,
            'reading'   => $r ? [
                'power_w'   => $r->power_w   !== null ? (float)$r->power_w   : null,
                'voltage_v' => $r->voltage_v !== null ? (float)$r->voltage_v : null,
                'energy_wh' => $r->energy_wh !== null ? (int)$r->energy_wh   : null,
                'taken_at'  => optional($r->taken_at)->toIso8601String(),
                'mode'      => $r->mode ?? 'unknown',
            ] : null,
        ]);
    }

    /**
     * ПРИВАТНО: записати метрику для конкретного пристрою користувача
     * POST /api/devices/{device}/metrics
     */
    public function storeMetrics(Request $r, Device $device)
    {
        abort_unless($device->user_id === Auth::id(), Response::HTTP_FORBIDDEN);

        $payload = $this->normalize($r->all());

        $v = Validator::make($payload, [
            'power_w'    => ['nullable','numeric'],
            'voltage_v'  => ['nullable','numeric'],
            'energy_wh'  => ['nullable','numeric','min:0'],
            'taken_at'   => ['nullable'],
            'ip'         => ['nullable','ip'],
        ]);
        if ($v->fails()) {
            return response()->json(['ok' => false, 'errors' => $v->errors()], 422);
        }
        $data = $v->validated();
        $data['taken_at'] = $this->parseTimestamp($data['taken_at'] ?? null) ?? now();

        try {
            $reading = $this->persistReading($device, $data);

            return response()->json(['ok' => true, 'reading_id' => $reading->id ?? null], 200);
        } catch (\Throwable $e) {
            Log::error('ingest/private failed: '.$e->getMessage());
            return response()->json(['ok' => false, 'error' => 'server_error'], 500);
        }
    }

    /**
     * НОВЕ: агрегат по ВСІХ пристроях користувача
     * GET /api/readings/aggregate?minutes=60
     *
     * Віддає масив точок виду:
     *  [
     *    { ts: "2025-11-02 18:10:00", avg_power_w: 123.4, devices: 3 },
     *    ...
     *  ]
     */
    public function aggregate(Request $r)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['ok' => false, 'error' => 'unauthorized'], 401);
        }

        $minutes = (int) $r->query('minutes', 60);
        if ($minutes < 1) $minutes = 1;
        if ($minutes > 1440) $minutes = 1440; // не більше доби

        $from = now()->subMinutes($minutes);

        // беремо тільки пристрої юзера
        $deviceIds = Device::where('user_id', $userId)->pluck('id');
        if ($deviceIds->isEmpty()) {
            return response()->json([
                'ok'    => true,
                'items' => [],
            ]);
        }

        $rows = DB::table('readings')
            ->selectRaw('DATE_FORMAT(taken_at, "%Y-%m-%d %H:%i:00") as ts')
            ->selectRaw('AVG(power_w) as avg_power_w')
            ->selectRaw('COUNT(DISTINCT device_id) as devices')
            ->whereIn('device_id', $deviceIds)
            ->where('taken_at', '>=', $from)
            ->groupBy('ts')
            ->orderBy('ts')
            ->get();

        return response()->json([
            'ok'     => true,
            'from'   => $from->toIso8601String(),
            'to'     => now()->toIso8601String(),
            'items'  => $rows,
        ]);
    }

    /**
     * Нормалізація ключів + MAC (A-F0-9, без розділювачів) — як у ping()
     */
    private function persistReading(Device $device, array $data): Reading
    {
        return DB::transaction(function () use ($device, $data) {
            if (!empty($data['ip']) && $device->ip !== $data['ip']) {
                $device->ip = $data['ip'];
            }
            $device->last_seen_at = now();
            $device->save();

            $existing = Reading::where('device_id', $device->id)
                ->where('taken_at', $data['taken_at'])
                ->first();

            if ($existing) {
                return $existing;
            }

            $payload = [
                'device_id' => $device->id,
                'taken_at'  => $data['taken_at'],
                'power_w'   => Arr::get($data, 'power_w')   !== null ? (float)$data['power_w']   : null,
                'voltage_v' => Arr::get($data, 'voltage_v') !== null ? (float)$data['voltage_v'] : null,
                'energy_wh' => Arr::get($data, 'energy_wh') !== null ? (int) round($data['energy_wh']) : null,
            ];
            $payload['mode'] = PowerModeClassifier::classify($payload['power_w']);

            return Reading::create($payload);
        });
    }

    private function normalize(array $p): array
    {
        // витягнемо можливий вкладений total із aenergy.total
        if (isset($p['aenergy']) && is_array($p['aenergy']) && array_key_exists('total', $p['aenergy'])) {
            $p['aenergy_total'] = $p['aenergy']['total'];
        }

        $map = [
            'mac'        => ['mac','m'],
            'device_id'  => ['device_id','device','id'],
            'ip'         => ['ip','addr'],
            'power_w'    => ['power_w','power','p','apower'],
            'voltage_v'  => ['voltage_v','voltage','u'],
            'energy_wh'  => ['energy_wh','energy','wh','aenergy_total'],
            'taken_at'   => ['taken_at','ts','timestamp'],
            'name'       => ['name'],
            'brand'      => ['brand'],
            'model'      => ['model'],
            'category'   => ['category'],
        ];

        $out = [];
        foreach ($map as $to => $alts) {
            foreach ($alts as $k) {
                if (array_key_exists($k, $p) && $p[$k] !== null && $p[$k] !== '') {
                    $out[$to] = $p[$k];
                    break;
                }
            }
        }

        // MAC → тільки A-F0-9, upper, БЕЗ двокрапок/дефісів
        if (!empty($out['mac'])) {
            $mac = strtoupper($out['mac']);
            $mac = preg_replace('/[^A-F0-9]/', '', $mac);
            $out['mac'] = $mac;
        }

        return $out + $p;
    }

    /**
     * Парсимо часові мітки: ISO8601 | epoch sec | epoch ms.
     */
    private function parseTimestamp($v): ?Carbon
    {
        if ($v === null || $v === '') return null;

        // число → epoch sec/ms
        if (is_numeric($v)) {
            $iv = (int)$v;
            // якщо це мілісекунди (>= 10^12 для нинішніх часів) — переводимо в сек.
            if ($iv > 20000000000) { // ~2033 у мс
                $iv = (int) floor($iv / 1000);
            }
            return Carbon::createFromTimestampUTC($iv)->setTimezone(config('app.timezone'));
        }

        // рядок → ISO8601
        try {
            return Carbon::parse($v);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
