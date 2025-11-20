<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();

        $minutes = (int) $request->integer('minutes', 60);
        $minutes = max(5, min(43200, $minutes));

        $from = now()->subMinutes($minutes);

        $latestPredictionPerDevice = DB::table('mode_predictions')
            ->selectRaw('device_id, MAX(predicted_at) as last_pred')
            ->where('user_id', $user->id)
            ->groupBy('device_id');

        $latestPredictions = DB::table('mode_predictions as mp')
            ->select('mp.device_id', 'mp.mode', 'mp.confidence', 'mp.predicted_at')
            ->joinSub($latestPredictionPerDevice, 'lp', function ($join) {
                $join->on('mp.device_id', '=', 'lp.device_id')
                    ->on('mp.predicted_at', '=', 'lp.last_pred');
            })
            ->where('mp.user_id', $user->id);

        $timeline = DB::table('readings')
            ->join('devices', 'readings.device_id', '=', 'devices.id')
            ->where('devices.user_id', $user->id)
            ->where('readings.taken_at', '>=', $from)
            ->selectRaw("
                DATE_FORMAT(readings.taken_at, '%Y-%m-%d %H:%i:00') as ts,
                AVG(readings.power_w)  as avg_power_w,
                AVG(readings.voltage_v) as avg_voltage_v,
                SUM(readings.energy_wh) as sum_energy_wh
            ")
            ->groupBy('ts')
            ->orderBy('ts')
            ->get();

        $byDevice = DB::table('readings')
            ->join('devices', 'readings.device_id', '=', 'devices.id')
            ->leftJoinSub($latestPredictions, 'mp', function ($join) {
                $join->on('mp.device_id', '=', 'devices.id');
            })
            ->where('devices.user_id', $user->id)
            ->where('readings.taken_at', '>=', $from)
            ->groupBy('devices.id', 'devices.name', 'mp.mode', 'mp.confidence', 'mp.predicted_at')
            ->selectRaw("
                devices.id,
                devices.name,
                AVG(readings.power_w)  as avg_power_w,
                MAX(readings.power_w)  as peak_power_w,
                MAX(readings.taken_at) as last_seen_at,
                mp.mode         as mode,
                mp.confidence   as mode_confidence,
                mp.predicted_at as mode_predicted_at
            ")
            ->orderBy('devices.name')
            ->get();

        return response()->json([
            'from'      => $from,
            'to'        => now(),
            'minutes'   => $minutes,
            'timeline'  => $timeline,
            'by_device' => $byDevice,
        ]);
    }
}
