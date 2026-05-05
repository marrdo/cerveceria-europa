<?php

namespace App\Modulos\Ventas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AgregarLineasComandaRequest extends FormRequest
{
    /**
     * Autoriza la ampliacion de comandas a usuarios con acceso a ventas.
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
            'lineas' => ['required', 'array'],
            'lineas.*.contenido_web_id' => ['required', 'uuid', Rule::exists('contenidos_web', 'id')->whereNull('deleted_at')],
            'lineas.*.tarifa_contenido_web_id' => ['nullable', 'uuid', 'exists:tarifas_contenido_web,id'],
            'lineas.*.cantidad' => ['nullable', 'numeric', 'min:0', 'max:999.999'],
            'lineas.*.notas' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Valida que haya al menos una linea con cantidad positiva.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $tieneLineas = collect($this->input('lineas', []))
                ->contains(fn (array $linea): bool => (float) ($linea['cantidad'] ?? 0) > 0);

            if (! $tieneLineas) {
                $validator->errors()->add('lineas', 'Anade al menos un producto nuevo con cantidad mayor que cero.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function datosLineas(): array
    {
        return [
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
