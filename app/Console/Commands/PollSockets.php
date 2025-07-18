<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Device;

class PollSockets extends Command
{
    protected $signature = 'sockets:poll';
    protected $description = 'Read power / voltage / energy from all sockets';

    public function handle(): void
    {
        Device::where('category', 'sockets')
              ->chunkById(50, function ($devices) {

            foreach ($devices as $d) {
                try {
                    $m = Http::timeout(4)
                             ->get("http://{$d->ip}/rpc/Meter.GetStatus", ['id' => 0])
                             ->json();

                    $d->readings()->create([
                        'power_w'   => $m['power']   ?? 0,
                        'voltage_v' => $m['voltage'] ?? 0,
                        'energy_wh' => $m['energy']  ?? 0,
                        'taken_at'  => now(),
                    ]);

                } catch (\Throwable $e) {
                    logger()->warning("poll {$d->ip} fail: ".$e->getMessage());
                }
            }
        });

        $this->info('Sockets polled '.now());
    }
}
