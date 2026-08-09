<?php

namespace App\Modulos\PlanificacionTurnos\Http\Requests;

use App\Modulos\PlanificacionTurnos\Models\CoberturaMinimaLaboral;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GuardarCoberturaMinimaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarPlanificacionTurnos() === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'area_trabajo_id' => ['required', 'uuid', Rule::exists('areas_trabajo', 'id')->where('activo', true)],
            'dia_semana' => ['required', 'integer', 'between:1,7'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i'],
            'minimo_personas' => ['required', 'integer', 'between:1,50'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->filled(['hora_inicio', 'hora_fin'])) {
                return;
            }

            if (Carbon::parse((string) $this->input('hora_fin'))->lte(Carbon::parse((string) $this->input('hora_inicio')))) {
                $validator->errors()->add('hora_fin', 'La cobertura debe terminar después de la hora inicial.');
            }

            $duplicada = CoberturaMinimaLaboral::query()
                ->where('area_trabajo_id', $this->input('area_trabajo_id'))
                ->where('dia_semana', $this->integer('dia_semana'))
                ->where('hora_inicio', $this->input('hora_inicio'))
                ->where('hora_fin', $this->input('hora_fin'))
                ->exists();

            if ($duplicada) {
                $validator->errors()->add('hora_inicio', 'Ya existe una regla para esa área, día y franja.');
            }
        }];
    }

    /** @return array<string, mixed> */
    public function datosCobertura(): array
    {
        return [
            'area_trabajo_id' => (string) $this->input('area_trabajo_id'),
            'dia_semana' => $this->integer('dia_semana'),
            'hora_inicio' => (string) $this->input('hora_inicio'),
            'hora_fin' => (string) $this->input('hora_fin'),
            'minimo_personas' => $this->integer('minimo_personas'),
            'activo' => true,
        ];
    }
}
