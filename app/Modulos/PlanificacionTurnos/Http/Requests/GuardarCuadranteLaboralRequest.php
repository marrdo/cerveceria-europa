<?php

namespace App\Modulos\PlanificacionTurnos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Valida la creación de una semana de planificación.
 */
class GuardarCuadranteLaboralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarPlanificacionTurnos() === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'semana_inicio' => [
                'required',
                'date',
                Rule::unique('cuadrantes_laborales', 'semana_inicio'),
            ],
            'notas' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'semana_inicio.required' => 'Selecciona una semana.',
            'semana_inicio.unique' => 'Ya existe un cuadrante para esa semana.',
            'notas.max' => 'Las notas no pueden superar los 2000 caracteres.',
        ];
    }

    /**
     * @return array{semana_inicio: string, notas: ?string}
     */
    public function datosCuadrante(): array
    {
        return [
            'semana_inicio' => (string) $this->input('semana_inicio'),
            'notas' => filled($this->input('notas')) ? trim((string) $this->input('notas')) : null,
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('semana_inicio')) {
            $this->merge([
                'semana_inicio' => Carbon::parse((string) $this->input('semana_inicio'))
                    ->startOfWeek()
                    ->toDateString(),
            ]);
        }
    }
}
