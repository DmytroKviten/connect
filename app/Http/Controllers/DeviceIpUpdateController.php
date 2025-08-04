<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;

class DeviceIpUpdateController extends Controller
{
    public function handle(Request $request)
    {
        $mac = $request->input('mac'); // Наприклад: "AA:BB:CC:DD:EE:FF"
        $ip  = $request->ip(); // автоматично визначає IP запиту

        if (!$mac) {
            return response()->json(['error' => 'MAC is required'], 400);
        }

        // Оновлення або створення запису
        $device = Device::updateOrCreate(
            ['mac_address' => $mac],
            ['ip_address' => $ip]
        );

        return response()->json([
            'message' => 'IP оновлено',
            'device'  => $device,
        ]);
    }
}
