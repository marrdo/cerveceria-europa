<?php

namespace App\Modulos\PlanificacionTurnos\Http\Requests;

use App\Enums\RolUsuario;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Valida un tramo de trabajo dentro de un cuadrante semanal.
 */
class GuardarJornadaLaboralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarPlanificacionTurnos() === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'usuario_id' => [
                'required',
                'uuid',
                Rule::exists('usuarios', 'id')->where(fn ($query) => $query
                    ->whereNull('deleted_at')
                    ->where('es_protegido', false)
                    ->where('rol', '!=', RolUsuario::Superadmin->value)),
            ],
            'area_trabajo_id' => [
                'required',
                'uuid',
                Rule::exists('areas_trabajo', 'id')->where('activo', true),
            ],
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i'],
            'termina_dia_siguiente' => ['nullable', 'boolean'],
            'minutos_descanso' => ['required', 'integer', 'min:0', 'max:720'],
            'notas' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'usuario_id.required' => 'Selecciona un empleado.',
            'usuario_id.exists' => 'El empleado seleccionado no esta disponible.',
            'area_trabajo_id.required' => 'Selecciona un área de trabajo.',
            'area_trabajo_id.exists' => 'El área seleccionada no está activa.',
            'fecha.required' => 'Selecciona el día de trabajo.',
            'hora_inicio.required' => 'Indica la hora de inicio.',
            'hora_fin.required' => 'Indica la hora de fin.',
            'minutos_descanso.max' => 'El descanso no puede superar 12 horas.',
            'notas.max' => 'Las notas no pueden superar los 500 caracteres.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function datosJornada(): array
    {
        return [
            'usuario_id' => (string) $this->input('usuario_id'),
            'area_trabajo_id' => (string) $this->input('area_trabajo_id'),
            'fecha' => (string) $this->input('fecha'),
            'hora_inicio' => (string) $this->input('hora_inicio'),
            'hora_fin' => (string) $this->input('hora_fin'),
            'termina_dia_siguiente' => $this->boolean('termina_dia_siguiente'),
            'minutos_descanso' => (int) $this->input('minutos_descanso', 0),
            'notas' => filled($this->input('notas')) ? trim((string) $this->input('notas')) : null,
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $cuadrante = $this->route('cuadrante');

            if (! $cuadrante instanceof CuadranteLaboral || ! $this->filled('fecha')) {
                return;
            }

            $fecha = Carbon::parse((string) $this->input('fecha'))->startOfDay();

            if ($fecha->lt($cuadrante->semana_inicio) || $fecha->gt($cuadrante->semanaFin())) {
                $validator->errors()->add('fecha', 'La fecha debe pertenecer a la semana del cuadrante.');
            }
        }];
    }
}
