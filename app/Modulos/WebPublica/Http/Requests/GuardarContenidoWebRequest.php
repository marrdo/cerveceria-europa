<?php

namespace App\Modulos\WebPublica\Http\Requests;

use App\Modulos\WebPublica\Enums\TipoContenidoWeb;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GuardarContenidoWebRequest extends FormRequest
{
    /**
     * Autoriza la gestion de contenido publico a usuarios autenticados.
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
            'tipo' => ['required', 'string', Rule::in(TipoContenidoWeb::valores())],
            'producto_id' => ['nullable', 'uuid', 'exists:productos,id'],
            'categoria_carta_id' => ['nullable', 'uuid', Rule::exists('categorias_carta', 'id')->whereNull('deleted_at')],
            'titulo' => ['required', 'string', 'max:191'],
            'descripcion_corta' => ['nullable', 'string', 'max:500'],
            'contenido' => ['nullable', 'string', 'max:5000'],
            'precio' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'tarifas' => ['nullable', 'array', 'max:12'],
            'tarifas.*.nombre' => ['nullable', 'string', 'max:80'],
            'tarifas.*.precio' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'alergenos' => ['nullable', 'string', 'max:1000'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'imagen_actual' => ['nullable', 'string', 'max:500'],
            'destacado' => ['nullable', 'boolean'],
            'fuera_carta' => ['nullable', 'boolean'],
            'publicado' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'publicado_desde' => ['nullable', 'date'],
            'publicado_hasta' => ['nullable', 'date', 'after_or_equal:publicado_desde'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tipo.required' => 'El campo tipo es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es valido.',
            'producto_id.exists' => 'El producto seleccionado no existe.',
            'categoria_carta_id.exists' => 'La categoria de carta seleccionada no existe.',
            'titulo.required' => 'El campo titulo es obligatorio.',
            'titulo.max' => 'El titulo no puede superar 191 caracteres.',
            'descripcion_corta.max' => 'La descripcion corta no puede superar 500 caracteres.',
            'contenido.max' => 'El contenido no puede superar 5000 caracteres.',
            'precio.numeric' => 'El precio debe ser un numero valido.',
            'precio.min' => 'El precio no puede ser negativo.',
            'tarifas.array' => 'Las tarifas deben enviarse en un formato valido.',
            'tarifas.max' => 'No se pueden indicar mas de 12 tarifas.',
            'tarifas.*.nombre.max' => 'El nombre de cada tarifa no puede superar 80 caracteres.',
            'tarifas.*.precio.numeric' => 'El precio de cada tarifa debe ser un numero valido.',
            'tarifas.*.precio.min' => 'El precio de cada tarifa no puede ser negativo.',
            'alergenos.max' => 'Los alergenos no pueden superar 1000 caracteres.',
            'imagen.image' => 'La imagen debe ser un archivo de imagen valido.',
            'imagen.mimes' => 'La imagen debe ser JPG, PNG o WEBP.',
            'imagen.max' => 'La imagen no puede superar 4 MB.',
            'orden.integer' => 'El orden debe ser un numero entero.',
            'publicado_hasta.after_or_equal' => 'La fecha fin debe ser posterior o igual a la fecha inicio.',
        ];
    }

    /**
     * Datos normalizados para guardar.
     *
     * @return array<string, mixed>
     */
    public function datosContenido(): array
    {
        return [
            'tipo' => $this->input('tipo'),
            'producto_id' => $this->input('producto_id') ?: null,
            'categoria_carta_id' => $this->input('categoria_carta_id') ?: null,
            'titulo' => trim((string) $this->input('titulo')),
            'descripcion_corta' => trim((string) $this->input('descripcion_corta', '')) ?: null,
            'contenido' => trim((string) $this->input('contenido', '')) ?: null,
            'precio' => $this->filled('precio') ? $this->input('precio') : null,
            'alergenos' => $this->alergenos(),
            'destacado' => $this->boolean('destacado'),
            'fuera_carta' => $this->boolean('fuera_carta'),
            'publicado' => $this->boolean('publicado'),
            'orden' => (int) $this->input('orden', 0),
            'publicado_desde' => $this->input('publicado_desde') ?: null,
            'publicado_hasta' => $this->input('publicado_hasta') ?: null,
        ];
    }

    /**
     * Validaciones cruzadas para tarifas de carta.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->input('tarifas', []) as $indice => $tarifa) {
                $nombre = trim((string) ($tarifa['nombre'] ?? ''));
                $precio = trim((string) ($tarifa['precio'] ?? ''));

                if ($nombre !== '' && $precio === '') {
                    $validator->errors()->add("tarifas.$indice.precio", 'El precio de la tarifa es obligatorio cuando indicas un nombre.');
                }
            }
        });
    }

    /**
     * Tarifas normalizadas para guardar.
     *
     * @return array<int, array{nombre: string|null, precio: mixed, orden: int}>
     */
    public function datosTarifas(): array
    {
        return collect($this->input('tarifas', []))
            ->map(function (array $tarifa, int $indice): ?array {
                $precio = trim((string) ($tarifa['precio'] ?? ''));

                if ($precio === '') {
                    return null;
                }

                $nombre = trim((string) ($tarifa['nombre'] ?? ''));

                return [
                    'nombre' => $nombre !== '' ? $nombre : null,
                    'precio' => $precio,
                    'orden' => $indice,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>|null
     */
    private function alergenos(): ?array
    {
        $valor = trim((string) $this->input('alergenos', ''));

        if ($valor === '') {
            return null;
        }

        return collect(explode(',', $valor))
            ->map(fn (string $alergeno): string => trim($alergeno))
            ->filter()
            ->values()
            ->all();
    }
}
