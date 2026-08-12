<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Añade una base conservadora de seguridad HTTP a todas las respuestas web.
 *
 * No se fija aquí una CSP estricta porque la web pública permite recursos de
 * marca y fuentes externas configurables. Debe diseñarse con nonces antes de
 * activarla para no romper Vite, Alpine ni las previsualizaciones del panel.
 */
final class CabecerasSeguridad
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        if ($request->user() !== null) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
