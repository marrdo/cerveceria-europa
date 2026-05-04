<?php

namespace App\Modulos\WebPublica\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarPostBlogRequest extends FormRequest
{
    /**
     * Autoriza la gestion de posts a usuarios autenticados.
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
        return [
            'titulo' => ['required', 'string', 'max:191'],
            'categorias' => ['nullable', 'array'],
            'categorias.*' => ['uuid', 'exists:categorias_blog,id'],
            'resumen' => ['nullable', 'string', 'max:500'],
            'contenido' => ['required', 'string', 'max:20000'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'autor' => ['nullable', 'string', 'max:191'],
            'publicado' => ['nullable', 'boolean'],
            'destacado' => ['nullable', 'boolean'],
            'publicado_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titulo.required' => 'El campo titulo es obligatorio.',
            'titulo.max' => 'El titulo no puede superar 191 caracteres.',
            'categorias.*.exists' => 'Una de las categorias seleccionadas no existe.',
            'resumen.max' => 'El resumen no puede superar 500 caracteres.',
            'contenido.required' => 'El campo contenido es obligatorio.',
            'contenido.max' => 'El contenido no puede superar 20000 caracteres.',
            'imagen.image' => 'La imagen debe ser un archivo de imagen valido.',
            'imagen.mimes' => 'La imagen debe ser JPG, PNG o WEBP.',
            'imagen.max' => 'La imagen no puede superar 4 MB.',
            'autor.max' => 'El autor no puede superar 191 caracteres.',
            'publicado_at.date' => 'La fecha de publicacion no es valida.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function datosPost(): array
    {
        return [
            'titulo' => trim((string) $this->input('titulo')),
            'resumen' => trim((string) $this->input('resumen', '')) ?: null,
            'contenido' => trim((string) $this->input('contenido')),
            'autor' => trim((string) $this->input('autor', '')) ?: null,
            'publicado' => $this->boolean('publicado'),
            'destacado' => $this->boolean('destacado'),
            'publicado_at' => $this->input('publicado_at') ?: now(),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function categoriasSeleccionadas(): array
    {
        return array_values((array) $this->input('categorias', []));
    }
}
