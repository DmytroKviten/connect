<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ShellyApController extends Controller
{
    /** POST /api/shelly/scan  { ip: "192.168.33.1" } */
    public function scan(Request $r)
{
    $ip  = $r->input('ip', '192.168.33.1');      // IP AP-режиму Plus-версії
    $cli = new \GuzzleHttp\Client(['timeout' => 8]);

    try {
        // ① одразу GET, бо Plug S уже повертає results
        $resp = $cli->get("http://{$ip}/rpc/WiFi.Scan", [
            'query' => ['index' => 0, 'timeout_ms' => 5000],
        ]);
        $json = json_decode($resp->getBody(), true);

        // ② масив мереж може бути у results або aps
        $list = collect($json['results'] ?? $json['aps'] ?? [])
                ->pluck('ssid')->filter()->values();

        return response()->json($list);
    } catch (\Throwable $e) {
        logger()->warning('Shelly scan failed: '.$e->getMessage());
        return response()->json(['error'=>'connect_failed'], 502);
    }
}

    /** POST /api/shelly/config  { ip, ssid, password } */
    public function config(Request $r)
{
    $r->validate([
        'ip'       => 'required|ip',
        'ssid'     => 'required|string',
        'password' => 'nullable|string',
    ]);

    $cli = new \GuzzleHttp\Client([
        'base_uri' => "http://{$r->ip}",
        'timeout'  => 8,
    ]);

    $payload = [
        'config' => [
            'sta' => [
                'ssid'   => $r->ssid,
                'pass'   => $r->password ?? '',
                'enable' => true,
            ],
        ],
        'save' => true,
    ];

    try {
        $cli->post('/rpc/WiFi.SetConfig', ['json' => $payload]);
        return response()->json(['success' => true]);
    } catch (\Throwable $e) {
        logger()->warning('Shelly SetConfig fail: '.$e->getMessage());
        return response()->json(['error' => 'set_config_failed',
                                 'msg'   => $e->getMessage()], 502);
    }
}

}
