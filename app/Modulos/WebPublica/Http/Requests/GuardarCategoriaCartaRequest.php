<?php

namespace App\Modulos\WebPublica\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarCategoriaCartaRequest extends FormRequest
{
    /**
     * Autoriza la gestion de categorias de carta a usuarios autenticados del modulo.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $categoria = $this->route('categoria');

        return [
            'categoria_padre_id' => [
                'nullable',
                'uuid',
                Rule::exists('categorias_carta', 'id')->whereNull('deleted_at'),
                Rule::notIn([$categoria?->id]),
            ],
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
            'categoria_padre_id.exists' => 'La categoria padre seleccionada no existe.',
            'categoria_padre_id.not_in' => 'Una categoria no puede depender de si misma.',
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar 191 caracteres.',
            'descripcion.max' => 'La descripcion no puede superar 1000 caracteres.',
            'orden.integer' => 'El orden debe ser un numero entero.',
            'orden.min' => 'El orden no puede ser negativo.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function datosCategoria(): array
    {
        return [
            'categoria_padre_id' => $this->input('categoria_padre_id') ?: null,
            'nombre' => trim((string) $this->input('nombre')),
            'descripcion' => trim((string) $this->input('descripcion', '')) ?: null,
            'activo' => $this->boolean('activo'),
            'orden' => (int) $this->input('orden', 0),
        ];
    }
}
