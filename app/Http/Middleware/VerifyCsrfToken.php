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
        // Endpoint de diagnóstico: recibe errores JS del browser. Sin CSRF para
        // no perder reportes cuando la sesión expiró o el error ocurre antes de
        // que cargue el resto de la página. Protegido con throttle en la ruta.
        'frontend-log',
    ];
}
