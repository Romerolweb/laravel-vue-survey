<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'https://calculadorahidricagestores.com/*',
        'http://calculadorahidricagestores.com/*',
        'https://35.171.140.220/*',
        'http://35.171.140.220/*',
    ];
}
