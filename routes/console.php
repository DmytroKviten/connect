<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Carbon;
use App\Models\Device;
use App\Models\Reading;

Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Тестові показники
 */
Artisan::command('readings:fake {device_id} {--n=60} {--step=60}', function ($deviceId) {
    $n    = max(1, (int) $this->option('n'));
    $step = max(5, (int) $this->option('step'));

    $device = Device::find($deviceId);
    if (!$device) {
        $this->error("Device #{$deviceId} not found");
        return 1;
    }

    $t = Carbon::now()->subSeconds($n * $step);

    for ($i = 0; $i < $n; $i++) {
        $t = $t->copy()->addSeconds($step);

        $power   = 20 + mt_rand(-5, 25);      // ~15..45 Вт
        $voltage = 228 + mt_rand(-4, 4);      // ~224..232 В
        $energy  = mt_rand(0, 5000);

        Reading::updateOrCreate(
            ['device_id' => $device->id, 'taken_at' => $t],
            ['power_w' => $power, 'voltage_v' => $voltage, 'energy_wh' => $energy]
        );
    }

    $this->info("Inserted/updated {$n} readings for device #{$device->id}");
    return 0;
})->purpose('Generate fake readings for testing');
