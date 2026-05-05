<?php

namespace App\Modulos\Ventas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarComandaOperativaRequest extends FormRequest
{
    /**
     * Autoriza la edicion operativa de comandas a usuarios con acceso a ventas.
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
            'lineas' => ['nullable', 'array'],
            'lineas.*.cantidad' => ['nullable', 'numeric', 'min:0', 'max:999.999'],
            'lineas.*.notas' => ['nullable', 'string', 'max:500'],
            'lineas.*.cancelar' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function datosOperativos(): array
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
                ->mapWithKeys(fn (array $linea, string $lineaId): array => [
                    $lineaId => [
                        'cantidad' => (float) ($linea['cantidad'] ?? 0),
                        'notas' => trim((string) ($linea['notas'] ?? '')) ?: null,
                        'cancelar' => (bool) ($linea['cancelar'] ?? false),
                    ],
                ])
                ->all(),
        ];
    }
}
