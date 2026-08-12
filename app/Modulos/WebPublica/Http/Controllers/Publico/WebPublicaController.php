<?php

namespace App\Modulos\WebPublica\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Modulos\WebPublica\Enums\TipoContenidoWeb;
use App\Modulos\WebPublica\Models\CategoriaCarta;
use App\Modulos\WebPublica\Models\ContenidoWeb;
use App\Modulos\WebPublica\Models\SeccionWeb;
use Illuminate\View\View;

class WebPublicaController extends Controller
{
    /**
     * Portada publica del negocio configurado.
     */
    public function inicio(): View
    {
        $destacados = $this->contenidos()->where('destacado', true)->take(6)->get();
        $fueraCarta = $this->contenidos()->where('fuera_carta', true)->take(6)->get();

        return view('web-publica.inicio', [
            'destacados' => $destacados,
            'fueraCarta' => $fueraCarta,
            'cervezas' => $this->contenidos()->where('tipo', TipoContenidoWeb::Cerveza)->take(4)->get(),
            'recomendaciones' => $this->contenidos()
                ->whereIn('tipo', [TipoContenidoWeb::RecomendacionChef, TipoContenidoWeb::RecomendacionCerveza])
                ->take(4)
                ->get(),
            'secciones' => collect(['hero', 'sugerencias', 'destacados', 'valores'])
                ->mapWithKeys(fn (string $clave): array => [
                    $clave => SeccionWeb::porClave('inicio_'.$clave),
                ]),
            'metricas' => [
                ['n' => $this->contenidos()->count(), 'l' => 'Referencias publicadas'],
                ['n' => CategoriaCarta::query()->where('activo', true)->count(), 'l' => 'Secciones de carta'],
                ['n' => $fueraCarta->count(), 'l' => 'Sugerencias activas'],
            ],
        ]);
    }

    /**
     * Recomendaciones activas del bar.
     */
    public function recomendaciones(): View
    {
        return view('web-publica.listado', [
            'titulo' => 'Recomendaciones',
            'descripcion' => 'Fuera de carta, maridajes y sugerencias del equipo para esta semana.',
            'contenidos' => $this->contenidos()
                ->whereIn('tipo', [TipoContenidoWeb::RecomendacionChef, TipoContenidoWeb::RecomendacionCerveza])
                ->paginate(12),
        ]);
    }

    /**
     * Contacto y ubicacion.
     */
    public function contacto(): View
    {
        return view('web-publica.contacto', [
            'seccion' => SeccionWeb::porClave('contacto'),
        ]);
    }

    /**
     * Query base ordenada para contenido publico.
     */
    private function contenidos()
    {
        return ContenidoWeb::query()
            ->with(['producto.stock', 'categoriaCarta'])
            ->publicado()
            ->orderBy('orden')
            ->latest('created_at');
    }
}
