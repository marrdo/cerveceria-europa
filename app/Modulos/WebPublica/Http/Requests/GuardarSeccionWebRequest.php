<?php

namespace App\Modulos\WebPublica\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarSeccionWebRequest extends FormRequest
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
            'titulo' => ['nullable', 'string', 'max:191'],
            'subtitulo' => ['nullable', 'string', 'max:500'],
            'contenido' => ['nullable', 'string', 'max:5000'],
            'reservas' => ['nullable', 'string', 'max:500'],
            'activo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titulo.max' => 'El titulo no puede superar 191 caracteres.',
            'subtitulo.max' => 'El subtitulo no puede superar 500 caracteres.',
            'contenido.max' => 'El contenido no puede superar 5000 caracteres.',
            'reservas.max' => 'Las reservas no pueden superar 500 caracteres.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function datosSeccion(): array
    {
        return [
            'titulo' => trim((string) $this->input('titulo', '')) ?: null,
            'subtitulo' => trim((string) $this->input('subtitulo', '')) ?: null,
            'contenido' => trim((string) $this->input('contenido', '')) ?: null,
            'datos' => [
                'reservas' => trim((string) $this->input('reservas', '')) ?: null,
            ],
            'activo' => $this->boolean('activo'),
        ];
    }
}
