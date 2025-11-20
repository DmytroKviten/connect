<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Якщо користувач неавторизований, то для API ми НЕ редіректимо,
     * а віддаємо 401 JSON. Бо в нас немає web-роута "login".
     */
    protected function redirectTo($request): ?string
    {
        // для API – повертаємо null, Laravel сам віддасть 401
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        // якщо колись зʼявиться веб-логін – тут можна буде повернути route('login')
        return null;
    }
}
