<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🔥 якщо ми всередині docker-контейнера — вмикаємо debug, щоб не було білого екрану
        if ($this->runningInDocker()) {
            config([
                'app.env'   => 'local',
                'app.debug' => true,
            ]);
        }
    }

    private function runningInDocker(): bool
    {
        if (file_exists('/.dockerenv')) {
            return true;
        }

        if (is_readable('/proc/1/cgroup')) {
            $cgroup = file_get_contents('/proc/1/cgroup');
            if (strpos($cgroup, 'docker') !== false || strpos($cgroup, 'containerd') !== false) {
                return true;
            }
        }

        return false;
    }
}
