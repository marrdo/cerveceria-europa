<?php

namespace App\Modulos\WebPublica\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\WebPublica\Http\Requests\GuardarCategoriaBlogRequest;
use App\Modulos\WebPublica\Models\CategoriaBlog;
use App\Models\Modulo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoriaBlogController extends Controller
{
    public function index(): View
    {
        $this->asegurarBlogActivo();

        return view('modulos.web-publica.blog-categorias.index', [
            'categorias' => CategoriaBlog::query()->orderBy('orden')->orderBy('nombre')->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->asegurarBlogActivo();

        return view('modulos.web-publica.blog-categorias.form', [
            'categoria' => new CategoriaBlog(['activo' => true]),
        ]);
    }

    public function store(GuardarCategoriaBlogRequest $request): RedirectResponse
    {
        $this->asegurarBlogActivo();
        $datos = $request->datosCategoria();
        $datos['slug'] = $this->slugUnico($datos['nombre']);

        CategoriaBlog::query()->create($datos);

        return redirect()->route('admin.web-publica.blog-categorias.index')
            ->with('status', 'Categoria creada correctamente.');
    }

    public function edit(CategoriaBlog $categoria): View
    {
        $this->asegurarBlogActivo();

        return view('modulos.web-publica.blog-categorias.form', [
            'categoria' => $categoria,
        ]);
    }

    public function update(GuardarCategoriaBlogRequest $request, CategoriaBlog $categoria): RedirectResponse
    {
        $this->asegurarBlogActivo();
        $categoria->update($request->datosCategoria());

        return redirect()->route('admin.web-publica.blog-categorias.index')
            ->with('status', 'Categoria actualizada correctamente.');
    }

    public function destroy(CategoriaBlog $categoria): RedirectResponse
    {
        $this->asegurarBlogActivo();
        $categoria->delete();

        return redirect()->route('admin.web-publica.blog-categorias.index')
            ->with('status', 'Categoria eliminada correctamente.');
    }

    private function asegurarBlogActivo(): void
    {
        abort_unless(Modulo::activo('blog'), 404);
    }

    private function slugUnico(string $nombre): string
    {
        $base = Str::slug($nombre) ?: Str::uuid()->toString();
        $slug = $base;
        $contador = 2;

        while (CategoriaBlog::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$contador;
            $contador++;
        }

        return $slug;
    }
}
