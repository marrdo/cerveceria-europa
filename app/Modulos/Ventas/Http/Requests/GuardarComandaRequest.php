<?php

namespace App\Modulos\Ventas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GuardarComandaRequest extends FormRequest
{
    /**
     * Autoriza la creacion de comandas a usuarios autenticados con acceso al modulo.
     */
    public function authorize(): bool
    {
        return $this->user()?->puedeAccederModulo('ventas') === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mesa' => ['nullable', 'string', 'max:50'],
            'cliente_nombre' => ['nullable', 'string', 'max:191'],
            'recinto_id' => ['nullable', 'uuid', Rule::exists('recintos', 'id')->whereNull('deleted_at')],
            'zona_id' => ['nullable', 'uuid', Rule::exists('zonas', 'id')->whereNull('deleted_at')],
            'mesa_id' => ['nullable', 'uuid', Rule::exists('mesas', 'id')->whereNull('deleted_at')],
            'ubicacion_inventario_id' => ['nullable', 'uuid', Rule::exists('ubicaciones_inventario', 'id')->whereNull('deleted_at')],
            'notas' => ['nullable', 'string', 'max:1000'],
            'lineas' => ['required', 'array'],
            'lineas.*.contenido_web_id' => ['required', 'uuid', Rule::exists('contenidos_web', 'id')->whereNull('deleted_at')],
            'lineas.*.tarifa_contenido_web_id' => ['nullable', 'uuid', 'exists:tarifas_contenido_web,id'],
            'lineas.*.cantidad' => ['nullable', 'numeric', 'min:0', 'max:999.999'],
            'lineas.*.notas' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lineas.required' => 'Debes enviar al menos una linea de comanda.',
            'lineas.*.contenido_web_id.exists' => 'Uno de los productos de carta seleccionados no existe.',
            'lineas.*.cantidad.numeric' => 'La cantidad debe ser un numero valido.',
            'lineas.*.cantidad.min' => 'La cantidad no puede ser negativa.',
        ];
    }

    /**
     * Valida que haya al menos una cantidad positiva.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $tieneLineas = collect($this->input('lineas', []))
                ->contains(fn (array $linea): bool => (float) ($linea['cantidad'] ?? 0) > 0);

            if (! $tieneLineas) {
                $validator->errors()->add('lineas', 'Anade al menos un producto con cantidad mayor que cero.');
            }
        });
    }

    /**
     * Datos normalizados de la comanda.
     *
     * @return array<string, mixed>
     */
    public function datosComanda(): array
    {
        return [
            'mesa' => trim((string) $this->input('mesa', '')) ?: null,
            'cliente_nombre' => trim((string) $this->input('cliente_nombre', '')) ?: null,
            'recinto_id' => $this->input('recinto_id') ?: null,
            'zona_id' => $this->input('zona_id') ?: null,
            'mesa_id' => $this->input('mesa_id') ?: null,
            'ubicacion_inventario_id' => $this->input('ubicacion_inventario_id') ?: null,
            'notas' => trim((string) $this->input('notas', '')) ?: null,
            'lineas' => collect($this->input('lineas', []))
                ->map(fn (array $linea): array => [
                    'contenido_web_id' => $linea['contenido_web_id'],
                    'tarifa_contenido_web_id' => $linea['tarifa_contenido_web_id'] ?? null,
                    'cantidad' => (float) ($linea['cantidad'] ?? 0),
                    'notas' => trim((string) ($linea['notas'] ?? '')) ?: null,
                ])
                ->all(),
        ];
    }
}
