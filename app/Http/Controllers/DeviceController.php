<?php

namespace App\Http\Controllers;

use App\Models\Device;

class DeviceController extends Controller
{
    /** Список усіх підключених розеток */
    public function index()
    {
        // тягнемо пристрої + останній вимір
        $devices = auth()->user()
    ->devices()
    ->with('latestReading')
    ->latest()
    ->get();


        // повертаємо саме той Blade, який уже лежить у /views/demo
    return view('demo.index', compact('devices'));
    }
}
