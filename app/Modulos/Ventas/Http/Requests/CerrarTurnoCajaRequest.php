<?php

namespace App\Modulos\Ventas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CerrarTurnoCajaRequest extends FormRequest
{
    /**
     * Solo perfiles de gestion pueden cerrar caja.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->puedeGestionarCaja();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'efectivo_contado' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'notas_cierre' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Datos normalizados para cerrar caja.
     *
     * @return array<string, mixed>
     */
    public function datos(): array
    {
        return [
            'efectivo_contado' => round((float) $this->input('efectivo_contado', 0), 2),
            'notas_cierre' => trim((string) $this->input('notas_cierre', '')) ?: null,
        ];
    }
}
