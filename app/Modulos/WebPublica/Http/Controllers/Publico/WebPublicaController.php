<?php

namespace App\Modulos\WebPublica\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Modulos\WebPublica\Enums\TipoContenidoWeb;
use App\Modulos\WebPublica\Models\ContenidoWeb;
use App\Modulos\WebPublica\Models\SeccionWeb;
use Illuminate\View\View;

class WebPublicaController extends Controller
{
    /**
     * Portada publica de Cerveceria Europa.
     */
    public function inicio(): View
    {
        return view('web-publica.inicio', [
            'destacados' => $this->contenidos()->where('destacado', true)->take(6)->get(),
            'fueraCarta' => $this->contenidos()->where('fuera_carta', true)->take(6)->get(),
            'cervezas' => $this->contenidos()->where('tipo', TipoContenidoWeb::Cerveza)->take(4)->get(),
            'recomendaciones' => $this->contenidos()
                ->whereIn('tipo', [TipoContenidoWeb::RecomendacionChef, TipoContenidoWeb::RecomendacionCerveza])
                ->take(4)
                ->get(),
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
            ->with('producto.stock')
            ->publicado()
            ->orderBy('orden')
            ->latest('created_at');
    }
}
