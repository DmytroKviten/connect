<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Куди редиректити неавторизовані браузерні запити.
     */
    protected function redirectTo($request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
