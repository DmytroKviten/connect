<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceIngestController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\Api\ProvisionController;
use App\Http\Controllers\MonitoringController;

/* ───────── ПУБЛІЧНІ  ───────── */

/** телеметрія (ingest) */
Route::post('/ingest', [DeviceIngestController::class, 'storeMetricsByMac'])
    ->name('api.ingest')
    ->middleware('throttle:240,1'); // до 240 запитів/хв з одного IP

/** спарювання через SetupToken */
Route::post('/device/ping', [DeviceController::class, 'ping'])
    ->name('api.device.ping')
    ->middleware('throttle:60,1');

/** Скани Wi-Fi з AP пристрою  */
Route::post('/device/scan', [DeviceController::class, 'scanWifi'])
    ->name('api.device.scan')
    ->middleware('throttle:30,1');


/* ───────── АВТОРИЗАЦІЯ (JSON) ───────── */

Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login',    [AuthController::class, 'login'])->name('api.login');


/* ───────── ПРИВАТНІ (під Sanctum) ───────── */

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    // Користувач
    Route::get('/user',    [AuthController::class, 'user'])->name('user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Токен для первинного спарювання
    Route::post('/setup-token', [DeviceController::class, 'createSetupToken'])->name('setup-token');

    // Provisioning-flows
    Route::post('/provision/claim',               [ProvisionController::class, 'claim'])->name('provision.claim');
    Route::post('/devices/register-manual',       [ProvisionController::class, 'registerManual'])->name('devices.register-manual');
    Route::post('/devices/{device}/install-push', [ProvisionController::class, 'installPush'])
        ->whereNumber('device')->name('devices.install-push');

    // Налаштування Wi-Fi пристрою (приват)
    Route::post('/device/config', [DeviceController::class, 'configWifi'])->name('device.config');

    // Пристрої
    Route::get('/devices',                        [DeviceController::class, 'apiIndex'])->name('devices.index');
    Route::get('/devices/{device}',               [DeviceController::class, 'apiShow'])->whereNumber('device')->name('devices.show');
    Route::get('/devices/{device}/info',          [DeviceController::class, 'info'])->whereNumber('device')->name('device.info');
    Route::post('/devices/{device}/switch',       [DeviceController::class, 'toggleSwitch'])->whereNumber('device')->name('device.switch');
    Route::post('/devices/{device}/reboot',       [DeviceController::class, 'reboot'])->whereNumber('device')->name('device.reboot');
    Route::post('/devices/{device}/factory-reset',[DeviceController::class, 'factoryReset'])->whereNumber('device')->name('device.factory-reset');

    // Метрики (JSON для UI)
    Route::get('/devices/{device}/metrics',        [DeviceIngestController::class, 'index'])->whereNumber('device')->name('device.metrics.index');
    Route::get('/devices/{device}/metrics/latest', [DeviceIngestController::class, 'latest'])->whereNumber('device')->name('device.metrics.latest');
    Route::post('/devices/{device}/metrics',       [DeviceIngestController::class, 'storeMetrics'])->whereNumber('device')->name('device.metrics.store');

    // АНАЛІТИКА: середній графік по всіх пристроях користувача
    Route::get('/readings/aggregate', [DeviceIngestController::class, 'aggregate'])->name('readings.aggregate');
});

/* ───────── Перевірка приватної зони ───────── */
Route::get('/ping-auth', function (Request $r) {
    return response()->json([
        'ok'   => true,
        'user' => $r->user()?->only('id','email','name'),
    ]);
})->middleware('auth:sanctum')->name('api.ping-auth');

Route::post('/devices/demo-add', [App\Http\Controllers\DeviceController::class, 'demoAdd'])->middleware('auth:sanctum');

Route::delete('/devices/{device}', [App\Http\Controllers\DeviceController::class, 'destroy'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/monitoring/summary', [MonitoringController::class, 'summary'])
    ->name('api.monitoring.summary');

    Route::get('/monitoring/predictions', [App\Http\Controllers\Api\ModePredictionController::class, 'index'])
        ->name('api.monitoring.predictions');
    Route::post('/monitoring/classify', [App\Http\Controllers\Api\ModePredictionController::class, 'store'])
        ->name('api.monitoring.classify');
