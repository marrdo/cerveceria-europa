<?php

namespace App\Providers;

use App\Modulos\Configuracion\Models\ConfiguracionNegocio;
use App\Modulos\WebPublica\ViewData\SeoPublicoViewData;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ViewFacade::composer(
            ['layouts.*', 'web-publica.*'],
            static function (View $view): void {
                $negocio = ConfiguracionNegocio::actual();
                $view->with('negocio', $negocio);

                if ($view->name() === 'layouts.publico') {
                    $view->with('seoEstructurado', app(SeoPublicoViewData::class)->construir($negocio));
                }
            },
        );
    }
}
