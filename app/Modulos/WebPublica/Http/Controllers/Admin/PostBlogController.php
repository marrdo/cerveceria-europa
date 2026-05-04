<?php

namespace App\Modulos\WebPublica\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\WebPublica\Http\Requests\GuardarPostBlogRequest;
use App\Modulos\WebPublica\Models\CategoriaBlog;
use App\Models\Modulo;
use App\Modulos\WebPublica\Models\PostBlog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostBlogController extends Controller
{
    /**
     * Listado de posts del modulo blog.
     */
    public function index(): View
    {
        $this->asegurarBlogActivo();

        return view('modulos.web-publica.blog.index', [
            'posts' => PostBlog::query()->with('categorias')->latest('publicado_at')->paginate(15),
        ]);
    }

    /**
     * Formulario de alta.
     */
    public function create(): View
    {
        $this->asegurarBlogActivo();

        return view('modulos.web-publica.blog.form', [
            'post' => new PostBlog(['publicado' => true, 'publicado_at' => now()]),
            'categorias' => $this->categoriasDisponibles(),
        ]);
    }

    /**
     * Guarda un post nuevo.
     */
    public function store(GuardarPostBlogRequest $request): RedirectResponse
    {
        $this->asegurarBlogActivo();

        $datos = $request->datosPost();
        $datos['slug'] = $this->slugUnico($datos['titulo']);
        $datos['imagen'] = $this->guardarImagen($request);

        $post = PostBlog::query()->create($datos);
        $post->categorias()->sync($request->categoriasSeleccionadas());

        return redirect()->route('admin.web-publica.blog.index')
            ->with('status', 'Post publicado correctamente.');
    }

    /**
     * Formulario de edicion.
     */
    public function edit(PostBlog $post): View
    {
        $this->asegurarBlogActivo();

        return view('modulos.web-publica.blog.form', [
            'post' => $post->load('categorias'),
            'categorias' => $this->categoriasDisponibles(),
        ]);
    }

    /**
     * Actualiza un post existente.
     */
    public function update(GuardarPostBlogRequest $request, PostBlog $post): RedirectResponse
    {
        $this->asegurarBlogActivo();

        $datos = $request->datosPost();

        if ($request->hasFile('imagen')) {
            $this->eliminarImagen($post);
            $datos['imagen'] = $this->guardarImagen($request);
        }

        $post->update($datos);
        $post->categorias()->sync($request->categoriasSeleccionadas());

        return redirect()->route('admin.web-publica.blog.index')
            ->with('status', 'Post actualizado correctamente.');
    }

    /**
     * Elimina un post.
     */
    public function destroy(PostBlog $post): RedirectResponse
    {
        $this->asegurarBlogActivo();
        $this->eliminarImagen($post);
        $post->delete();

        return redirect()->route('admin.web-publica.blog.index')
            ->with('status', 'Post eliminado correctamente.');
    }

    /**
     * Cambia publicado o destacado.
     */
    public function toggle(PostBlog $post, string $campo): RedirectResponse
    {
        $this->asegurarBlogActivo();
        abort_unless(in_array($campo, ['publicado', 'destacado'], true), 404);

        $post->update([
            $campo => ! $post->{$campo},
        ]);

        return back()->with('status', 'Estado actualizado correctamente.');
    }

    private function asegurarBlogActivo(): void
    {
        abort_unless(Modulo::activo('blog'), 404);
    }

    private function guardarImagen(GuardarPostBlogRequest $request): ?string
    {
        if (! $request->hasFile('imagen')) {
            return null;
        }

        return $request->file('imagen')->store('web-publica/blog', 'public');
    }

    private function eliminarImagen(PostBlog $post): void
    {
        if ($post->imagen && ! str_starts_with($post->imagen, 'http')) {
            Storage::disk('public')->delete($post->imagen);
        }
    }

    private function slugUnico(string $titulo): string
    {
        $base = Str::slug($titulo) ?: Str::uuid()->toString();
        $slug = $base;
        $contador = 2;

        while (PostBlog::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$contador;
            $contador++;
        }

        return $slug;
    }

    private function categoriasDisponibles()
    {
        return CategoriaBlog::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();
    }
}
