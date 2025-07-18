<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShellyController;   
use App\Models\Device;                      

/*───────────────────────  ПУБЛІЧНІ СТОРІНКИ  ───────────────────────*/

Route::view('/',             'home.home'   )->name('home');
Route::view('/demo',         'demo.demo'   )->name('demo');
Route::view('/demo/master',  'demo.index'  )->name('demo.master');
Route::view('/demo/setup',   'demo.setup'  )->name('demo.setup');
Route::view('/product',      'home.product')->name('product');

/*───────────────────────  АУТЕНТИФІКАЦІЯ  ──────────────────────────*/

Route::get ('/register', [RegisterController::class, 'show' ])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

Route::get ('/login',    [LoginController::class,    'show' ])->name('login');
Route::post('/login',    [LoginController::class,    'login']);

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

/*───────────────────────  ПРИСТРОЇ КОРИСТУВАЧА  ────────────────────*/

/* список усіх пристроїв поточного користувача (Blade / SPA) */
Route::get('/devices', [DeviceController::class, 'index'])
      ->middleware('auth')
      ->name('devices.index');

/* Створюємо окремий «device»-префікс для AJAX-запитів Shelly */
Route::middleware('auth')->prefix('device')->group(function () {

    /* POST /device/scan   – повертає список SSID */
    Route::post('scan',   [ShellyController::class, 'scan'])
          ->name('device.scan');

    /* POST /device/config – налаштовує розетку й записує у БД */
    Route::post('config', [ShellyController::class, 'config'])
          ->name('device.config');
});

/* (опційно) якщо саму сторінку /setup хочете закрити гість-редиректом */
Route::view('/setup', 'demo.setup')
      ->middleware('auth')        // ← після view()
      ->name('setup');

      Route::middleware('auth')->get('/sockets/{device}', 
    [\App\Http\Controllers\SocketController::class, 'show'])
    ->name('sockets.show');


    Route::middleware('auth')->get('/device/{device}/latest', function (Device $device) {
    abort_unless($device->user_id === Auth::id(), 403);
    $r = $device->latestReading;
    return [
        'power'   => $r->power   ?? '—',
        'voltage' => $r->voltage ?? '—',
        'energy'  => $r->energy  ?? '—',
    ];
});


Route::middleware('auth')->get(
    '/devices/{device}',
    [\App\Http\Controllers\SocketController::class, 'show']
)->name('devices.show');