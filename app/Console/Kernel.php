<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
   
    protected function commands(): void
    {
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('sockets:poll')->everyFiveMinutes();
    }

protected $commands = [
    \App\Console\Commands\RefreshShellyIp::class,
];


}
