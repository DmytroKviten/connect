<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Реєстрація Artisan‑команд.
     */
    protected function commands(): void
    {
        // тут можуть бути load(__DIR__.'/Commands') …
    }

    /**
     * Планувальник завдань.
     */
    protected function schedule(Schedule $schedule): void
    {
        // наша команда – кожні 5 хв
        $schedule->command('sockets:poll')->everyFiveMinutes();
    }
}
