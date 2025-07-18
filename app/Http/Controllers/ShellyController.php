<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use App\Models\Device;

class ShellyController extends Controller
{
    /** POST /device/scan  {ip?} */
    public function scan(Request $request)
    {
        $ip = $request->input('ip', '192.168.33.1');

        try {
            $json = Http::timeout(8)->get("http://{$ip}/rpc/WiFi.Scan", [
                'index'      => 0,
                'timeout_ms' => 5000,
            ])->json();

            return collect($json['results'] ?? $json['aps'] ?? [])
                   ->pluck('ssid')->filter()->values();
        } catch (\Throwable $e) {
            logger()->warning('Shelly scan failed: '.$e->getMessage());
            return response()->json(['error' => 'connect_failed'], 502);
        }
    }

    /** POST /device/config  {ip, ssid, password?} */
    public function config(Request $request)
    {
        $request->validate([
            'ip'       => 'required|ip',
            'ssid'     => 'required|string',
            'password' => 'nullable|string',
        ]);

        $ip = $request->ip;

        /* 1 ▸ Sys.GetStatus → mac-uid + model */
        $sys   = Http::timeout(5)->get("http://{$ip}/rpc/Sys.GetStatus")->json();
        $uid   = $sys['mac']   ?? null;          // MAC = унікальний uid
        $model = $sys['model'] ?? 'Plus Plug S';

        /* 2 ▸ записуємо / оновлюємо пристрій ТІЛЬКИ для поточного user_id */
        if ($uid) {
            Device::updateOrCreate(
                ['uid' => $uid, 'user_id' => Auth::id()],    // ← ключі пошуку
                [
                    'name'   => 'Розетка',
                    'ip'     => $ip,
                    'brand'  => 'Shelly',
                    'model'  => $model,
                    'category' => 'sockets',
                ]
            );
        }

        /* 3 ▸ налаштовуємо Wi-Fi STA */
        Http::timeout(5)->post("http://{$ip}/rpc/WiFi.SetConfig", [
            'config' => [
                'sta' => [
                    'ssid'   => $request->ssid,
                    'pass'   => $request->password ?? '',
                    'enable' => true,
                ],
                'ap' => ['enable' => false],
            ],
            'save' => true,
        ])->throw();

        /* 4 ▸ «one-shot» оновлення IP через 10 с */
        if ($uid) {
            Bus::dispatch(function () use ($uid) {
                sleep(10);
                $host = "http://shelly-{$uid}.local";
                try {
                    Http::timeout(4)->get("$host/rpc/Sys.GetStatus");
                    Device::where(['uid' => $uid, 'user_id' => Auth::id()])
                          ->update(['ip' => parse_url($host, PHP_URL_HOST)]);
                } catch (\Throwable $e) {
                    logger()->notice("Update-IP fail for $uid: ".$e->getMessage());
                }
            });
        }

        return response()->json(['success' => true]);
    }
}
