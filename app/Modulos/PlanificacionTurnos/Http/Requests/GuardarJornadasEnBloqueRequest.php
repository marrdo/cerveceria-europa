<?php

namespace App\Modulos\PlanificacionTurnos\Http\Requests;

use App\Enums\RolUsuario;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GuardarJornadasEnBloqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarPlanificacionTurnos() === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'usuario_ids' => ['required', 'array', 'min:1', 'max:50'],
            'usuario_ids.*' => [
                'uuid',
                'distinct',
                Rule::exists('usuarios', 'id')->where(fn ($query) => $query
                    ->whereNull('deleted_at')->where('es_protegido', false)->where('rol', '!=', RolUsuario::Superadmin->value)),
            ],
            'fechas' => ['required', 'array', 'min:1', 'max:7'],
            'fechas.*' => ['date', 'distinct'],
            'area_trabajo_id' => ['required', 'uuid', Rule::exists('areas_trabajo', 'id')->where('activo', true)],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i'],
            'minutos_descanso' => ['required', 'integer', 'min:0', 'max:720'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $cuadrante = $this->route('cuadrante');

            if (! $cuadrante instanceof CuadranteLaboral) {
                return;
            }

            foreach ($this->input('fechas', []) as $fecha) {
                $dia = Carbon::parse((string) $fecha);

                if ($dia->lt($cuadrante->semana_inicio) || $dia->gt($cuadrante->semanaFin())) {
                    $validator->errors()->add('fechas', 'Todos los días deben pertenecer al cuadrante.');
                    break;
                }
            }
        }];
    }

    /** @return array<string, mixed> */
    public function datosComunes(): array
    {
        return [
            'area_trabajo_id' => (string) $this->input('area_trabajo_id'),
            'hora_inicio' => (string) $this->input('hora_inicio'),
            'hora_fin' => (string) $this->input('hora_fin'),
            'termina_dia_siguiente' => false,
            'minutos_descanso' => $this->integer('minutos_descanso'),
            'notas' => null,
        ];
    }
}
