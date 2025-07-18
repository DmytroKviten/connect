<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Support\Facades\Auth;

class SocketController extends Controller
{
    public function show(Device $device)
    {
        abort_unless(
            $device->user_id === Auth::id() && $device->category === 'sockets',
            403
        );

        // останні 20 показників — для графіка
        $readings = $device->readings()
                   ->latest('taken_at')
                   ->take(20)
                   ->get([
                       'power_w  as power',
                       'voltage_v as voltage',
                       'energy_wh as energy',
                       'taken_at'
                   ])
                   ->reverse();

        return view('sockets.show', compact('device', 'readings'));
    }
}
