<?php

namespace App\Modulos\WebPublica\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Modulos\WebPublica\Models\CategoriaBlog;
use App\Models\Modulo;
use App\Modulos\WebPublica\Models\PostBlog;
use Illuminate\View\View;

class BlogPublicoController extends Controller
{
    /**
     * Listado publico del blog.
     */
    public function index(): View
    {
        $this->asegurarBlogActivo();

        return view('web-publica.blog.index', [
            'posts' => PostBlog::query()
                ->with('categorias')
                ->publicado()
                ->latest('publicado_at')
                ->paginate(9),
            'categorias' => CategoriaBlog::query()->where('activo', true)->orderBy('orden')->orderBy('nombre')->get(),
            'categoriaActual' => null,
        ]);
    }

    /**
     * Listado publico filtrado por categoria.
     */
    public function categoria(CategoriaBlog $categoria): View
    {
        $this->asegurarBlogActivo();
        abort_unless($categoria->activo, 404);

        return view('web-publica.blog.index', [
            'posts' => PostBlog::query()
                ->with('categorias')
                ->publicado()
                ->whereHas('categorias', fn ($categorias) => $categorias->whereKey($categoria->id))
                ->latest('publicado_at')
                ->paginate(9),
            'categorias' => CategoriaBlog::query()->where('activo', true)->orderBy('orden')->orderBy('nombre')->get(),
            'categoriaActual' => $categoria,
        ]);
    }

    /**
     * Detalle publico de un post.
     */
    public function show(PostBlog $post): View
    {
        $this->asegurarBlogActivo();
        abort_unless($post->publicado && ($post->publicado_at === null || $post->publicado_at->lte(now())), 404);

        return view('web-publica.blog.show', [
            'post' => $post->load('categorias'),
        ]);
    }

    private function asegurarBlogActivo(): void
    {
        abort_unless(Modulo::activo('blog'), 404);
    }
}
