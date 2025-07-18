<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * URI, звільнені від перевірки CSRF.
     *
     * @var array<int, string>
     */
    protected $except = [
        // 'api/*',  // ← за потреби можна вимкнути CSRF для всіх API
    ];
}
