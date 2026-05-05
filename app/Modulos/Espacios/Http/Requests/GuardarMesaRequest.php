<?php

namespace App\Modulos\Espacios\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarMesaRequest extends FormRequest
{
    /**
     * Autoriza la gestion de mesas a perfiles operativos con permiso.
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
            'zona_id' => ['required', 'uuid', Rule::exists('zonas', 'id')->whereNull('deleted_at')],
            'nombre' => ['required', 'string', 'max:191'],
            'capacidad' => ['nullable', 'integer', 'min:1', 'max:999'],
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
            'zona_id' => $this->input('zona_id'),
            'nombre' => trim((string) $this->input('nombre')),
            'capacidad' => $this->input('capacidad') !== null && $this->input('capacidad') !== '' ? (int) $this->input('capacidad') : null,
            'orden' => (int) $this->input('orden', 0),
            'notas' => trim((string) $this->input('notas', '')) ?: null,
            'activa' => $this->boolean('activa', true),
        ];
    }
}
