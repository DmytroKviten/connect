<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DeviceController extends Controller
{
    // Список усіх підключених розеток
    public function index()
    {
        $user = Auth::user();
        $devices = $user->devices()->with('latestReading')->latest()->get();
        return view('demo.index', compact('devices'));
    }

    // LIVE‑показники прямо з розетки
    public function live(Device $device)
    {
        try {
            $resp = Http::timeout(3)->get("http://{$device->ip}/rpc/Switch.GetStatus", ['id' => 0]);
            $data = $resp->json();
            return response()->json([
                'power'   => $data['apower'] ?? null,
                'voltage' => $data['voltage'] ?? null,
                'energy'  => $data['aenergy']['total'] ?? null,
                'state'   => $data['output'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Shelly offline'], 500);
        }
    }

    // Отримати поточний стан вимикача
    public function state(Device $device)
    {
        try {
            $resp = Http::timeout(2)->get("http://{$device->ip}/rpc/Switch.GetStatus", ['id' => 0]);
            $data = $resp->json();
            return response()->json([
                'output' => $data['output'] ?? false,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['output' => false], 500);
        }
    }

    // Керування (вмикання/вимикання)
    public function switch(Device $device, Request $request)
    {
        // Очікуємо "on": true/false
        if (! $request->has('on')) {
            return response()->json(['error' => 'Param on=true|false required'], 422);
        }
        $on = (bool)$request->input('on');
        try {
            $resp = Http::timeout(3)->post("http://{$device->ip}/rpc/Switch.Set", [
                'id' => 0,
                'on' => $on,
            ]);
            if ($resp->ok()) {
                return response()->json(['output' => $on]);
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Shelly offline'], 500);
        }
        return response()->json(['error' => 'Failed to switch'], 500);
    }

    // Перезапуск пристрою
    public function reboot(Device $device)
    {
        try {
            $resp = Http::timeout(3)->post("http://{$device->ip}/rpc/Shelly.Reboot");
            if ($resp->ok()) {
                return response()->json(['success' => true]);
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Shelly offline'], 500);
        }
        return response()->json(['error' => 'Failed to reboot'], 500);
    }

    // Скидання до заводських
    public function factoryReset(Device $device)
    {
        try {
            $resp = Http::timeout(3)->post("http://{$device->ip}/rpc/Shelly.FactoryReset");
            if ($resp->ok()) {
                return response()->json(['success' => true]);
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Shelly offline'], 500);
        }
        return response()->json(['error' => 'Failed to reset'], 500);
    }

    // Отримати інформацію про пристрій
    public function info(Device $device)
    {
        try {
            $resp = Http::timeout(3)->get("http://{$device->ip}/rpc/Sys.GetStatus");
            return $resp->json();
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Shelly offline'], 500);
        }
    }

public function chartData(Device $device)
{
    $readings = $device->readings()
        ->orderByDesc('taken_at')
        ->limit(20)
        ->get(['taken_at', 'power_w'])
        ->reverse();

    return response()->json([
        'labels' => $readings->pluck('taken_at')->map(fn($d) => \Carbon\Carbon::parse($d)->format('H:i:s')),
        'power'  => $readings->pluck('power_w'),
    ]);
}



}
