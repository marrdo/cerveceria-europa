<?php

namespace App\Modulos\WebPublica\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Modulos\WebPublica\Models\ContenidoWeb;
use Illuminate\View\View;

class FueraCartaPublicaController extends Controller
{
    /**
     * Todos los fuera de carta publicados.
     */
    public function index(): View
    {
        return view('web-publica.listado', [
            'titulo' => 'Fuera de carta',
            'descripcion' => 'Platos, cervezas y recomendaciones disponibles de forma limitada.',
            'contenidos' => ContenidoWeb::query()
                ->with('producto.stock')
                ->publicado()
                ->where('fuera_carta', true)
                ->orderBy('orden')
                ->latest('created_at')
                ->paginate(12),
        ]);
    }
}
