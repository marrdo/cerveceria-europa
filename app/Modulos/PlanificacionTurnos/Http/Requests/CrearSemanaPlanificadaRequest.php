<?php

namespace App\Modulos\PlanificacionTurnos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

/**
 * Valida la semana de destino de una copia o plantilla.
 */
class CrearSemanaPlanificadaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarPlanificacionTurnos() === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['semana_inicio' => ['required', 'date']];
    }

    public function semanaInicio(): Carbon
    {
        return Carbon::parse((string) $this->input('semana_inicio'))->startOfWeek();
    }
}
