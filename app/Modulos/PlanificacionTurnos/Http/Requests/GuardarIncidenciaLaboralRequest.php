<?php

namespace App\Modulos\PlanificacionTurnos\Http\Requests;

use App\Enums\RolUsuario;
use App\Modulos\PlanificacionTurnos\Enums\TipoIncidenciaLaboral;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Valida descansos, vacaciones, bajas, ausencias y festivos laborales.
 */
class GuardarIncidenciaLaboralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarPlanificacionTurnos() === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::enum(TipoIncidenciaLaboral::class)],
            'usuario_id' => [
                'nullable',
                'uuid',
                Rule::exists('usuarios', 'id')->where(fn ($query) => $query
                    ->whereNull('deleted_at')
                    ->where('es_protegido', false)
                    ->where('rol', '!=', RolUsuario::Superadmin->value)),
            ],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'notas' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'tipo.required' => 'Selecciona el tipo de incidencia.',
            'tipo.enum' => 'El tipo de incidencia no es válido.',
            'usuario_id.exists' => 'El empleado seleccionado no está disponible.',
            'fecha_inicio.required' => 'Indica la fecha de inicio.',
            'fecha_fin.required' => 'Indica la fecha de finalización.',
            'fecha_fin.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
            'notas.max' => 'Las notas no pueden superar los 500 caracteres.',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $tipo = TipoIncidenciaLaboral::tryFrom((string) $this->input('tipo'));

            if ($tipo?->esGlobal() && $this->filled('usuario_id')) {
                $validator->errors()->add('usuario_id', 'Un festivo se aplica al calendario general y no lleva empleado.');
            }

            if ($tipo !== null && ! $tipo->esGlobal() && ! $this->filled('usuario_id')) {
                $validator->errors()->add('usuario_id', 'Selecciona el empleado afectado.');
            }

            if ($this->filled(['fecha_inicio', 'fecha_fin'])) {
                $inicio = Carbon::parse((string) $this->input('fecha_inicio'));
                $fin = Carbon::parse((string) $this->input('fecha_fin'));

                if ($inicio->diffInDays($fin) > 366) {
                    $validator->errors()->add('fecha_fin', 'Una incidencia no puede superar 366 días.');
                }
            }
        }];
    }

    /** @return array<string, mixed> */
    public function datosIncidencia(): array
    {
        $tipo = TipoIncidenciaLaboral::from((string) $this->input('tipo'));

        return [
            'tipo' => $tipo,
            'usuario_id' => $tipo->esGlobal() ? null : (string) $this->input('usuario_id'),
            'fecha_inicio' => (string) $this->input('fecha_inicio'),
            'fecha_fin' => (string) $this->input('fecha_fin'),
            'notas' => filled($this->input('notas')) ? trim((string) $this->input('notas')) : null,
            'creado_por_id' => $this->user()?->id,
        ];
    }
}
