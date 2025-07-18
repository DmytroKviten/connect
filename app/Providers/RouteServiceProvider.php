<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /** Where to redirect users after login (необов’язково). */
    public const HOME = '/';

    public function boot(): void
    {
        // API маршрути
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));

        // Web маршрути
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }
}
