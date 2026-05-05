<?php

namespace App\Modulos\Espacios\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarZonaRequest extends FormRequest
{
    /**
     * Autoriza la gestion de zonas a perfiles operativos con permiso.
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
            'recinto_id' => ['required', 'uuid', Rule::exists('recintos', 'id')->whereNull('deleted_at')],
            'nombre' => ['required', 'string', 'max:191'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'notas' => ['nullable', 'string'],
            'activa' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function datos(): array
    {
        return [
            'recinto_id' => $this->input('recinto_id'),
            'nombre' => trim((string) $this->input('nombre')),
            'codigo' => trim((string) $this->input('codigo', '')) ?: null,
            'orden' => (int) $this->input('orden', 0),
            'notas' => trim((string) $this->input('notas', '')) ?: null,
            'activa' => $this->boolean('activa', true),
        ];
    }
}
