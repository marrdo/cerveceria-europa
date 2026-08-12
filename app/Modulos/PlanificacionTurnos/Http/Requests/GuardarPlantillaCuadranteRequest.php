<?php

namespace App\Modulos\PlanificacionTurnos\Http\Requests;

use App\Modulos\PlanificacionTurnos\Models\PlantillaCuadranteLaboral;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarPlantillaCuadranteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->puedeGestionarPlanificacionTurnos() === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120', Rule::unique(PlantillaCuadranteLaboral::class, 'nombre')],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, mixed> */
    public function datosPlantilla(): array
    {
        return [
            'nombre' => trim((string) $this->input('nombre')),
            'descripcion' => filled($this->input('descripcion')) ? trim((string) $this->input('descripcion')) : null,
            'creado_por_id' => $this->user()?->id,
        ];
    }
}
