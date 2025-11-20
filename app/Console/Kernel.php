<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Планувальник 
     */
    protected function schedule(Schedule $schedule): void
    {
        if ((bool) env('ENABLE_LARAVEL_SCHEDULER', false)) {
            $schedule->command('shelly:poll')
                ->everyMinute()
                ->withoutOverlapping()
                ->runInBackground();
        }
    }

    /**
     * Реєстрація консольних команд.
     */
    protected function commands(): void
    {
        // Підвантажуємо класи команд PollShelly 
        $this->load(__DIR__ . '/Commands');

        $consoleRoutes = base_path('routes/console.php');
        if (is_file($consoleRoutes)) {
            require $consoleRoutes;
        }
    }
}
