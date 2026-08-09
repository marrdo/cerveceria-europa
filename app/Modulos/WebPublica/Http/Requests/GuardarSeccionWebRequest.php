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
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'eliminar_imagen' => ['nullable', 'boolean'],
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'cta_principal' => ['nullable', 'string', 'max:50'],
            'cta_secundaria' => ['nullable', 'string', 'max:50'],
            'reservas' => ['nullable', 'string', 'max:500'],
            'valor_1_titulo' => ['nullable', 'string', 'max:80'],
            'valor_1_descripcion' => ['nullable', 'string', 'max:300'],
            'valor_2_titulo' => ['nullable', 'string', 'max:80'],
            'valor_2_descripcion' => ['nullable', 'string', 'max:300'],
            'valor_3_titulo' => ['nullable', 'string', 'max:80'],
            'valor_3_descripcion' => ['nullable', 'string', 'max:300'],
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
            'imagen.image' => 'El archivo debe ser una imagen válida.',
            'imagen.mimes' => 'La imagen debe estar en JPG, PNG o WebP.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function datosSeccion(): array
    {
        $camposDatos = [
            'eyebrow',
            'cta_principal',
            'cta_secundaria',
            'reservas',
            'valor_1_titulo',
            'valor_1_descripcion',
            'valor_2_titulo',
            'valor_2_descripcion',
            'valor_3_titulo',
            'valor_3_descripcion',
        ];

        return [
            'titulo' => trim((string) $this->input('titulo', '')) ?: null,
            'subtitulo' => trim((string) $this->input('subtitulo', '')) ?: null,
            'contenido' => trim((string) $this->input('contenido', '')) ?: null,
            'datos' => collect($camposDatos)
                ->mapWithKeys(fn (string $campo): array => [
                    $campo => trim((string) $this->input($campo, '')) ?: null,
                ])
                ->filter()
                ->all(),
            'activo' => $this->boolean('activo'),
        ];
    }
}
