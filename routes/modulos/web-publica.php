<?php

use App\Modulos\WebPublica\Http\Controllers\Admin\CategoriaBlogController;
use App\Modulos\WebPublica\Http\Controllers\Admin\CategoriaCartaController;
use App\Modulos\WebPublica\Http\Controllers\Admin\ContenidoWebController;
use App\Modulos\WebPublica\Http\Controllers\Admin\PostBlogController;
use App\Modulos\WebPublica\Http\Controllers\Admin\SeccionWebController;
use App\Modulos\WebPublica\Http\Controllers\Publico\BlogPublicoController;
use App\Modulos\WebPublica\Http\Controllers\Publico\CartaPublicaController;
use App\Modulos\WebPublica\Http\Controllers\Publico\FueraCartaPublicaController;
use App\Modulos\WebPublica\Http\Controllers\Publico\SeoPublicoController;
use App\Modulos\WebPublica\Http\Controllers\Publico\WebPublicaController;
use Illuminate\Support\Facades\Route;

Route::middleware('modulo.publico:web_publica')->group(function (): void {
    Route::get('/site.webmanifest', [SeoPublicoController::class, 'manifest'])->name('web.manifest');
    Route::get('/robots.txt', [SeoPublicoController::class, 'robots'])->name('web.robots');
    Route::get('/sitemap.xml', [SeoPublicoController::class, 'sitemap'])->name('web.sitemap');
    Route::get('/', fn () => app(WebPublicaController::class)->inicio())->name('web.inicio');
    Route::get('/carta', [CartaPublicaController::class, 'index'])->name('web.carta');
    Route::get('/cervezas', [CartaPublicaController::class, 'cervezas'])->name('web.cervezas');
    Route::get('/fuera-de-carta', [FueraCartaPublicaController::class, 'index'])->name('web.fuera-carta');
    Route::get('/recomendaciones', [WebPublicaController::class, 'recomendaciones'])->name('web.recomendaciones');
    Route::get('/contacto', [WebPublicaController::class, 'contacto'])->name('web.contacto');

    Route::middleware('modulo.publico:blog')->group(function (): void {
        Route::get('/blog', [BlogPublicoController::class, 'index'])->name('web.blog');
        Route::get('/blog/categoria/{categoria:slug}', [BlogPublicoController::class, 'categoria'])->name('web.blog.categoria');
        Route::get('/blog/{post:slug}', [BlogPublicoController::class, 'show'])->name('web.blog.show');
    });
});

Route::middleware(['auth', 'modulo:web_publica'])
    ->prefix('admin/web-publica')
    ->name('admin.web-publica.')
    ->group(function (): void {
        Route::patch('contenidos/{contenido}/toggle/{campo}', [ContenidoWebController::class, 'toggle'])->name('contenidos.toggle');
        Route::resource('contenidos', ContenidoWebController::class)->except(['show'])->parameters(['contenidos' => 'contenido']);
        Route::resource('secciones', SeccionWebController::class)->only(['index', 'edit', 'update'])->parameters(['secciones' => 'seccion']);
        Route::resource('carta-categorias', CategoriaCartaController::class)->except(['show'])->parameters(['carta-categorias' => 'categoria']);

        Route::middleware('modulo:blog')->group(function (): void {
            Route::resource('blog-categorias', CategoriaBlogController::class)->except(['show'])->parameters(['blog-categorias' => 'categoria']);
            Route::patch('blog/{post}/toggle/{campo}', [PostBlogController::class, 'toggle'])->name('blog.toggle');
            Route::resource('blog', PostBlogController::class)->except(['show'])->parameters(['blog' => 'post']);
        });
    });
