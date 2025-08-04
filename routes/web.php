<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShellyController;
use App\Http\Controllers\SocketController;
use App\Models\Device;

/* ───────────── ПУБЛІЧНІ СТОРІНКИ ───────────── */

Route::view('/',             'home.home'   )->name('home');
Route::view('/demo',         'demo.demo'   )->name('demo');
Route::view('/demo/master',  'demo.index'  )->name('demo.master');
Route::view('/demo/setup',   'demo.setup'  )->name('demo.setup');
Route::view('/product',      'home.product')->name('product');

/* ───────────── АУТЕНТИФІКАЦІЯ ───────────── */

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

/* ───────────── ПРИСТРОЇ КОРИСТУВАЧА ───────────── */

// Список пристроїв
Route::middleware('auth')->get('/devices', [DeviceController::class, 'index'])->name('devices.index');

// Деталі пристрою (сторінка)
Route::middleware('auth')->get('/devices/{device}', [SocketController::class, 'show'])->name('devices.show');
Route::middleware('auth')->get('/sockets/{device}', [SocketController::class, 'show'])->name('sockets.show');

// AJAX-дані про пристрій (для live показників)
Route::middleware('auth')->get('/device/{device}/latest', function (Device $device) {
    abort_unless($device->user_id === Auth::id(), 403);
    $r = $device->latestReading;
    return [
        'power'   => $r->power   ?? '—',
        'voltage' => $r->voltage ?? '—',
        'energy'  => $r->energy  ?? '—',
    ];
})->name('device.latest');

/* ───── Група для device‑операцій (AJAX + керування Shelly) ───── */
Route::middleware('auth')->prefix('device')->group(function () {
    // Shelly Wi-Fi та авто-дискавері
    Route::post('scan',          [ShellyController::class, 'scan'])->name('device.scan');
    Route::post('config',        [ShellyController::class, 'config'])->name('device.config');
    Route::post('auto-discover', [DeviceController::class, 'autoDiscover'])->name('device.auto_discover');

    // LIVE, STATE, INFO
    Route::get('{device}/live',  [DeviceController::class, 'live'])->name('device.live');
    Route::get('{device}/state', [DeviceController::class, 'state'])->name('device.state');
    Route::get('{device}/info',  [DeviceController::class, 'info'])->name('device.info');

    // Керування пристроєм
    Route::post('{device}/switch',        [DeviceController::class, 'switch'])->name('device.switch');
    Route::post('{device}/reboot',        [DeviceController::class, 'reboot'])->name('device.reboot');
    Route::post('{device}/factory-reset', [DeviceController::class, 'factoryReset'])->name('device.factoryReset');
    // Якщо потрібно — ці ще додай, якщо в контролері є:
    // Route::post('{device}/reset',   [DeviceController::class, 'reset'])->name('device.reset');
    // Route::post('{device}/refresh', [DeviceController::class, 'refresh'])->name('device.refresh');
});

/* (опційно) якщо саму сторінку /setup хочете закрити гість-редиректом */
Route::view('/setup', 'demo.setup')
    ->middleware('auth')
    ->name('setup');

// Діагностика
Route::get('/check-ip', fn() => request()->ip());

Route::middleware('auth')->get('/device/{device}/chart-data', [DeviceController::class, 'chartData']);
