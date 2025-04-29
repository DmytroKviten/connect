<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DeviceScanner;
use Illuminate\Http\JsonResponse;

class DeviceController extends Controller
{
    public function scan(DeviceScanner $scanner): JsonResponse
    {
        return response()->json($scanner->discover());
    }
}