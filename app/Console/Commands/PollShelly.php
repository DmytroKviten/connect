<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Http;
use App\Models\Device;
use App\Models\Reading;

class PollShelly extends Command
{
    protected $signature = 'shelly:poll';
    protected $description = 'Опитує всі пристрої Shelly та пише дані у readings';

    public function handle()
{
    foreach (Device::all() as $dev) {
        // список можливих хостів
        $hosts = array_filter([
            $dev->ip ? "http://{$dev->ip}" : null,
            "http://shelly-{$dev->uid}.local",
        ]);

        $data = null;
        foreach ($hosts as $host) {
            try {
                $data = Http::timeout(4)
                    ->get("$host/rpc/Switch.GetStatus", ['id' => 0])
                    ->json();

                // якщо вдалося – оновлюємо збережений IP (міг змінитись)
                $dev->update(['ip' => parse_url($host, PHP_URL_HOST)]);
                break;                      // вириваємось з foreach
            } catch (\Throwable $e) {
                logger()->notice("poll fail ".$dev->uid." via $host: ".$e->getMessage());
            }
        }

        if (!$data) {
            $this->warn("FAIL ".$dev->name);
            continue;
        }

        // запис у readings
        Reading::create([
            'device_id' => $dev->id,
            'power_w'   => $data['apower']          ?? 0,
            'voltage_v' => $data['voltage']         ?? 0,
            'energy_wh' => $data['aenergy']['total']?? 0,
            'taken_at'  => now(),
        ]);

        $this->info("OK  ".$dev->name);
    }
}
}
