<?php

namespace App\Modulos\Configuracion\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Configuracion\Http\Requests\ActualizarConfiguracionNegocioRequest;
use App\Modulos\Configuracion\Models\ConfiguracionNegocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestiona el registro unico de configuracion del negocio.
 */
class ConfiguracionNegocioController extends Controller
{
    /**
     * Muestra la configuracion editable al propietario.
     */
    public function edit(Request $request): View
    {
        abort_unless($request->user()?->puedeConfigurarNegocio(), 403);

        return view('modulos.configuracion.negocio', [
            'configuracion' => ConfiguracionNegocio::actual(),
        ]);
    }

    /**
     * Actualiza la identidad y los datos comunes de la instalacion.
     */
    public function update(ActualizarConfiguracionNegocioRequest $request): RedirectResponse
    {
        ConfiguracionNegocio::query()->updateOrCreate(
            ['clave' => ConfiguracionNegocio::CLAVE_PRINCIPAL],
            $request->datosConfiguracion(),
        );

        return redirect()->route('admin.configuracion.negocio.edit')
            ->with('status', 'Configuracion del negocio actualizada correctamente.');
    }
}
