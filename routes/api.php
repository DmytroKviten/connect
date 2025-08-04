<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;

Route::post('/device/ping', [DeviceController::class, 'ping']);
