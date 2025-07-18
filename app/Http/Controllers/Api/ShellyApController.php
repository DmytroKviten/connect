<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Device;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Auth;

class ShellyApController extends Controller
{
    /** POST /api/shelly/scan */
    public function scan(Request $r)
    {
        $ip = $r->input('ip', '192.168.33.1');

        try {
            $json = Http::timeout(8)
                ->get("http://{$ip}/rpc/WiFi.Scan", [
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

    /** POST /api/shelly/config  {ip, ssid, password} */
    public function config(Request $r)
    {
        $r->validate([
            'ip'       => 'required|ip',
            'ssid'     => 'required|string',
            'password' => 'nullable|string',
        ]);

       logger()->info('💡 USER ID при підключенні:', ['id' => Auth::id()]);
       logger()->info('User ID = ' . auth()->id());


        $ip = $r->input('ip');

        /* 1 ▸ читаємо Sys.GetStatus і запам’ятовуємо uid/model */
        $uid = $model = null;
        try {
            $sys   = Http::timeout(5)->get("http://{$ip}/rpc/Sys.GetStatus")->json();
            $uid   = $sys['mac']   ?? null;            // Shelly повертає MAC у полі mac
            $model = $sys['model'] ?? 'Plus Plug S';

            if ($uid) {
                Device::updateOrCreate(
                    ['uid' => $uid],
                    [
                        'uid'   => $uid,
                        'name'  => 'Розетка',
                        'ip'    => $ip,
                        'brand' => 'Shelly',
                        'model' => $model,
                        'user_id' => Auth::id(),

                    ]
                );
            }
        } catch (\Throwable $e) {
            logger()->warning('Shelly Sys.GetStatus fail: '.$e->getMessage());
        }

        /* 2 ▸ надсилаємо WiFi.SetConfig; тайм-аут очікуваний */
        try {
            Http::timeout(5)->post("http://{$ip}/rpc/WiFi.SetConfig", [
                'config' => [
                    'sta' => [
                        'ssid'   => $r->input('ssid'),
                        'pass'   => $r->input('password', ''),
                        'enable' => true,
                    ],
                    'ap' => ['enable' => false],
                ],
                'save' => true,
            ])->throw();
        } catch (\Throwable $e) {
            logger()->notice('SetConfig timeout (OK): '.$e->getMessage());
        }

        /* 3 ▸ запускаємо “one-shot” оновлення IP через 10 с */
        if ($uid) {
            Bus::dispatch(function () use ($uid) {
                sleep(10);                                 // чекаємо перезапуску
                $host = "http://shelly-{$uid}.local";
                try {
                    Http::timeout(4)->get("$host/rpc/Sys.GetStatus");
                    Device::where('uid', $uid)
                          ->update(['ip' => parse_url($host, PHP_URL_HOST)]);
                    logger()->info("IP updated for $uid");
                } catch (\Throwable $e) {
                    logger()->notice("Update-IP fail for $uid: ".$e->getMessage());
                }
            });
        }

        /* 4 ▸ відповідаємо фронту успіхом */
        return response()->json(['success' => true]);
    }
}

