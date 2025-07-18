<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * Cookies, які НЕ шифруються.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
