<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| WEB routes (SPA-first)
| - Вся навігація рендериться фронтом (view('app'))
| - API живе під /api/* (див. routes/api.php)
|--------------------------------------------------------------------------
*/

// Головна (SPA)
Route::view('/', 'app')->name('spa.home');

// Сторінка логіну для SPA (фронт сам рендерить логін-форму)
Route::view('/login', 'app')->name('spa.login');
Route::view('/register', 'app')->name('spa.register');

// Якщо ти все ж використовуєш веб-сесії десь у Blade — залишаю logout.
// Для SPA з Bearer-токеном цей маршрут зазвичай не потрібен.
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Службове
Route::get('/check-ip', fn () => request()->ip());

// SPA fallback — все, що НЕ /api/*, віддаємо фронту
Route::get('/{any}', fn () => view('app'))
    ->where('any', '^(?!api).*$')
    ->name('spa.fallback');

    