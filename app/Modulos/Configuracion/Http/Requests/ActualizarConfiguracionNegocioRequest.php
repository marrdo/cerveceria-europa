<?php

namespace App\Modulos\Configuracion\Http\Requests;

use App\Support\Validacion\ReglasValidacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida y normaliza la configuracion general del negocio.
 */
class ActualizarConfiguracionNegocioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeConfigurarNegocio() === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre_comercial' => ['required', 'string', 'max:191'],
            'razon_social' => ['nullable', 'string', 'max:191'],
            'nif' => ReglasValidacion::documentoIdentidadEspanol(),
            'eslogan' => ['nullable', 'string', 'max:255'],
            'descripcion_corta' => ['nullable', 'string', 'max:500'],
            'telefono' => ReglasValidacion::telefonoEspanol(),
            'email' => ReglasValidacion::email(),
            'direccion' => ['nullable', 'string', 'max:255'],
            'localidad' => ['nullable', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'codigo_postal' => ['nullable', 'string', 'regex:/^[0-9]{5}$/'],
            'pais' => ['required', 'string', 'max:100'],
            'horario' => ['nullable', 'string', 'max:2000'],
            'web_url' => ['nullable', 'url:http,https', 'max:2048'],
            'instagram_url' => ['nullable', 'url:http,https', 'max:2048'],
            'google_maps_url' => ['nullable', 'url:http,https', 'max:2048'],
            'reservas_url' => ['nullable', 'url:http,https', 'max:2048'],
            'zona_horaria' => ['required', Rule::in(timezone_identifiers_list())],
            'moneda' => ['required', Rule::in(['EUR'])],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'codigo_postal.regex' => 'El codigo postal debe tener cinco cifras.',
            '*.url' => 'Introduce una URL completa que empiece por http:// o https://.',
            'zona_horaria.in' => 'La zona horaria seleccionada no es valida.',
        ];
    }

    /**
     * Datos limpios listos para persistir.
     *
     * @return array<string, string|null>
     */
    public function datosConfiguracion(): array
    {
        $datos = $this->validated();

        return collect($datos)
            ->map(static fn (mixed $valor): mixed => is_string($valor) ? trim($valor) : $valor)
            ->map(static fn (mixed $valor): mixed => $valor === '' ? null : $valor)
            ->all();
    }
}
