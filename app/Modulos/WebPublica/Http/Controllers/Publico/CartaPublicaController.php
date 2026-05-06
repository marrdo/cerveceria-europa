<?php

namespace App\Modulos\WebPublica\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Modulos\WebPublica\Enums\TipoContenidoWeb;
use App\Modulos\WebPublica\Models\CategoriaCarta;
use App\Modulos\WebPublica\Models\ContenidoWeb;
use Illuminate\View\View;

class CartaPublicaController extends Controller
{
    /**
     * Tipos visibles dentro de la carta publica general.
     *
     * @return array<int, TipoContenidoWeb>
     */
    private function tiposCarta(): array
    {
        return [TipoContenidoWeb::Plato, TipoContenidoWeb::Cerveza, TipoContenidoWeb::Bebida];
    }

    /**
     * Carta completa: cocina, cervezas y bebidas publicadas.
     */
    public function index(): View
    {
        $tiposCarta = $this->tiposCarta();

        $categoriasPadre = CategoriaCarta::query()
            ->whereNull('categoria_padre_id')
            ->where('activo', true)
            ->with([
                'contenidos' => fn ($query) => $query->with(['producto.stock', 'tarifas'])->publicado()->whereIn('tipo', $tiposCarta),
                'hijas' => fn ($query) => $query->where('activo', true)->with([
                    'contenidos' => fn ($contenidos) => $contenidos->with(['producto.stock', 'tarifas'])->publicado()->whereIn('tipo', $tiposCarta),
                ]),
            ])
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return view('web-publica.carta', [
            'categoriasPadre' => $categoriasPadre,
            'sinCategoria' => $this->contenidos()
                ->whereIn('tipo', $tiposCarta)
                ->whereNull('categoria_carta_id')
                ->get(),
        ]);
    }

    /**
     * Seccion especifica de cervezas.
     */
    public function cervezas(): View
    {
        return view('web-publica.listado', [
            'titulo' => 'Cervezas',
            'descripcion' => 'Importacion, artesanas y seleccion rotativa para descubrir estilos nuevos cada semana.',
            'contenidos' => $this->contenidos()->where('tipo', TipoContenidoWeb::Cerveza)->paginate(12),
        ]);
    }

    /**
     * Query base para carta.
     */
    private function contenidos()
    {
        return ContenidoWeb::query()
            ->with(['producto.stock', 'tarifas'])
            ->publicado()
            ->orderBy('orden')
            ->latest('created_at');
    }
}
