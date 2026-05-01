<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AsegurarAccesoModulo
{
    /**
     * Bloquea el acceso a modulos sin permiso operativo.
     *
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next, string $modulo): Response
    {
        if (! $request->user()?->puedeAccederModulo($modulo)) {
            abort(403, 'No tienes permiso para acceder a este modulo.');
        }

        return $next($request);
    }
}
