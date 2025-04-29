<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ShellyApController;

Route::post('/shelly/scan',   [ShellyApController::class, 'scan']);
Route::post('/shelly/config', [ShellyApController::class, 'config']);