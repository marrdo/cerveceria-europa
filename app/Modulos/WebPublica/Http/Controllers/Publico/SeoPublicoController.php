<?php

namespace App\Modulos\WebPublica\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Modulo;
use App\Modulos\Configuracion\Models\ConfiguracionNegocio;
use App\Modulos\WebPublica\Models\PostBlog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Expone los documentos técnicos de descubrimiento de la web pública.
 */
class SeoPublicoController extends Controller
{
    public function manifest(): JsonResponse
    {
        $negocio = ConfiguracionNegocio::actual();
        $favicon = $negocio->urlRecurso($negocio->favicon_path, asset('favicon.svg'));

        return response()->json([
            'name' => $negocio->nombre_comercial,
            'short_name' => str($negocio->nombre_comercial)->limit(24, '')->toString(),
            'start_url' => route('web.inicio', absolute: false),
            'display' => 'standalone',
            'background_color' => $negocio->color_fondo,
            'theme_color' => $negocio->color_primario,
            'icons' => [['src' => $favicon, 'sizes' => 'any', 'purpose' => 'any']],
        ])->header('Content-Type', 'application/manifest+json; charset=UTF-8');
    }

    public function robots(): Response
    {
        $negocio = ConfiguracionNegocio::actual();
        $contenido = $negocio->seo_indexar
            ? "User-agent: *\nAllow: /\nSitemap: ".route('web.sitemap')."\n"
            : "User-agent: *\nDisallow: /\n";

        return response($contenido, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        $rutas = collect([
            route('web.inicio'),
            route('web.carta'),
            route('web.cervezas'),
            route('web.fuera-carta'),
            route('web.recomendaciones'),
            route('web.contacto'),
        ]);

        if (Modulo::activo('blog')) {
            $rutas->push(route('web.blog'));
            $rutas->push(...PostBlog::query()->publicado()->get()->map(
                fn (PostBlog $post): string => route('web.blog.show', $post),
            ));
        }

        return response()->view('web-publica.seo.sitemap', ['rutas' => $rutas->unique()], 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
