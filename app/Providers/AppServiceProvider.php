<?php

namespace App\Providers;

use App\Modulos\Configuracion\Models\ConfiguracionNegocio;
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
                $view->with('negocio', ConfiguracionNegocio::actual());
            },
        );
    }
}
