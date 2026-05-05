<?php

namespace App\Modulos\Espacios\Http\Requests;

use App\Support\Validacion\ReglasValidacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class GuardarRecintoRequest extends FormRequest
{
    /**
     * Autoriza la gestion de espacios a perfiles operativos con permiso.
     */
    public function authorize(): bool
    {
        return $this->user()?->puedeAccederModulo('espacios') === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre_comercial' => ['required', 'string', 'max:191'],
            'nombre_fiscal' => ['nullable', 'string', 'max:191'],
            'direccion' => ['nullable', 'string', 'max:191'],
            'localidad' => ['nullable', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'codigo_postal' => ['nullable', 'string', 'max:20'],
            'pais' => ['nullable', 'string', 'max:100'],
            'telefono' => ReglasValidacion::telefonoEspanol(),
            'email' => ReglasValidacion::email(),
            'notas' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Datos normalizados del recinto.
     *
     * @return array<string, mixed>
     */
    public function datos(): array
    {
        return [
            'nombre_comercial' => trim((string) $this->input('nombre_comercial')),
            'nombre_fiscal' => trim((string) $this->input('nombre_fiscal', '')) ?: null,
            'direccion' => trim((string) $this->input('direccion', '')) ?: null,
            'localidad' => trim((string) $this->input('localidad', '')) ?: null,
            'provincia' => trim((string) $this->input('provincia', '')) ?: null,
            'codigo_postal' => trim((string) $this->input('codigo_postal', '')) ?: null,
            'pais' => trim((string) $this->input('pais', '')) ?: 'Espana',
            'telefono' => trim((string) $this->input('telefono', '')) ?: null,
            'email' => Str::lower(trim((string) $this->input('email', ''))) ?: null,
            'notas' => trim((string) $this->input('notas', '')) ?: null,
            'activo' => $this->boolean('activo', true),
        ];
    }
}
