<?php

namespace App\Modulos\WebPublica\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarCategoriaBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:191'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'activo' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar 191 caracteres.',
            'descripcion.max' => 'La descripcion no puede superar 1000 caracteres.',
            'orden.integer' => 'El orden debe ser un numero entero.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function datosCategoria(): array
    {
        return [
            'nombre' => trim((string) $this->input('nombre')),
            'descripcion' => trim((string) $this->input('descripcion', '')) ?: null,
            'activo' => $this->boolean('activo'),
            'orden' => (int) $this->input('orden', 0),
        ];
    }
}
