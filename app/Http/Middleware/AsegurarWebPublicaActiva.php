<?php

namespace App\Http\Middleware;

use App\Models\Modulo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AsegurarWebPublicaActiva
{
    /**
     * Bloquea la web publica cuando el modulo no esta contratado/activo.
     *
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Modulo::activo('web_publica'), 404);

        return $next($request);
    }
}
