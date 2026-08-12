<?php

namespace App\Modulos\Configuracion\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Configuracion\Http\Requests\ActualizarConfiguracionNegocioRequest;
use App\Modulos\Configuracion\Models\ConfiguracionNegocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        $configuracion = ConfiguracionNegocio::query()
            ->where('clave', ConfiguracionNegocio::CLAVE_PRINCIPAL)
            ->first();
        $datos = $request->datosConfiguracion();

        $this->procesarRecurso($request, $configuracion, $datos, 'logo', 'logo_path');
        $this->procesarRecurso($request, $configuracion, $datos, 'favicon', 'favicon_path');
        $this->procesarRecurso($request, $configuracion, $datos, 'imagen_social', 'imagen_social_path');

        ConfiguracionNegocio::query()->updateOrCreate(
            ['clave' => ConfiguracionNegocio::CLAVE_PRINCIPAL],
            $datos,
        );

        return redirect()->route('admin.configuracion.negocio.edit')
            ->with('status', 'Configuracion del negocio actualizada correctamente.');
    }

    /**
     * Sustituye o elimina un recurso sin dejar archivos huérfanos.
     *
     * @param  array<string, mixed>  $datos
     */
    private function procesarRecurso(
        ActualizarConfiguracionNegocioRequest $request,
        ?ConfiguracionNegocio $configuracion,
        array &$datos,
        string $campo,
        string $atributo,
    ): void {
        $rutaAnterior = $configuracion?->{$atributo};

        if ($request->boolean('eliminar_'.$campo)) {
            $this->eliminarRecurso($rutaAnterior);
            $datos[$atributo] = null;

            return;
        }

        $archivo = $request->file($campo);

        if (! $archivo instanceof UploadedFile) {
            unset($datos[$atributo]);

            return;
        }

        $this->eliminarRecurso($rutaAnterior);
        $datos[$atributo] = $archivo->store('negocio/identidad', 'public');
    }

    private function eliminarRecurso(?string $ruta): void
    {
        if (filled($ruta) && ! str_starts_with($ruta, 'http://') && ! str_starts_with($ruta, 'https://')) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
