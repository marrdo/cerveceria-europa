<?php

namespace App\Http\Middleware;

use App\Modulos\Sistema\Modulos\GestorModulos;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Oculta con 404 cualquier superficie pública cuyo módulo no esté operativo.
 */
final readonly class AsegurarModuloPublicoActivo
{
    public function __construct(private GestorModulos $modulos) {}

    /** @param  Closure(Request): Response  $next */
    public function handle(Request $request, Closure $next, string $modulo): Response
    {
        abort_unless($this->modulos->estaOperativo($modulo), 404);

        return $next($request);
    }
}
