<?php

namespace App\Modulos\WebPublica\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\WebPublica\Http\Requests\GuardarCategoriaCartaRequest;
use App\Modulos\WebPublica\Models\CategoriaCarta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoriaCartaController extends Controller
{
    /**
     * Listado de categorias editables de la carta publica.
     */
    public function index(): View
    {
        return view('modulos.web-publica.carta-categorias.index', [
            'categorias' => CategoriaCarta::query()
                ->with('padre')
                ->orderByRaw('categoria_padre_id is not null')
                ->orderBy('orden')
                ->orderBy('nombre')
                ->paginate(20),
        ]);
    }

    /**
     * Formulario de alta.
     */
    public function create(): View
    {
        return view('modulos.web-publica.carta-categorias.form', [
            'categoria' => new CategoriaCarta(['activo' => true]),
            'categoriasPadre' => $this->categoriasPadre(),
        ]);
    }

    /**
     * Guarda una categoria nueva.
     */
    public function store(GuardarCategoriaCartaRequest $request): RedirectResponse
    {
        $datos = $request->datosCategoria();
        $datos['slug'] = $this->slugUnico($datos['nombre']);

        CategoriaCarta::query()->create($datos);

        return redirect()->route('admin.web-publica.carta-categorias.index')
            ->with('status', 'Categoria de carta creada correctamente.');
    }

    /**
     * Formulario de edicion.
     */
    public function edit(CategoriaCarta $categoria): View
    {
        return view('modulos.web-publica.carta-categorias.form', [
            'categoria' => $categoria,
            'categoriasPadre' => $this->categoriasPadre($categoria),
        ]);
    }

    /**
     * Actualiza una categoria existente.
     */
    public function update(GuardarCategoriaCartaRequest $request, CategoriaCarta $categoria): RedirectResponse
    {
        $categoria->update($request->datosCategoria());

        return redirect()->route('admin.web-publica.carta-categorias.index')
            ->with('status', 'Categoria de carta actualizada correctamente.');
    }

    /**
     * Oculta una categoria sin borrar sus contenidos.
     */
    public function destroy(CategoriaCarta $categoria): RedirectResponse
    {
        $categoria->delete();

        return redirect()->route('admin.web-publica.carta-categorias.index')
            ->with('status', 'Categoria de carta eliminada correctamente.');
    }

    /**
     * Categorias disponibles como padre.
     */
    private function categoriasPadre(?CategoriaCarta $categoriaActual = null)
    {
        return CategoriaCarta::query()
            ->with('padre')
            ->when($categoriaActual, fn ($query) => $query->whereKeyNot($categoriaActual->id))
            ->orderByRaw('categoria_padre_id is not null')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Genera slug unico para rutas publicas.
     */
    private function slugUnico(string $nombre): string
    {
        $base = Str::slug($nombre) ?: Str::uuid()->toString();
        $slug = $base;
        $contador = 2;

        while (CategoriaCarta::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$contador;
            $contador++;
        }

        return $slug;
    }
}
