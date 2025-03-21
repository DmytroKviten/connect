<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Головна сторінка
Route::get('/', function () {
    return view('home'); // Зараз створимо цей шаблон
})->name('home');