<?php

namespace App\Modulos\Ventas\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AbrirTurnoCajaRequest extends FormRequest
{
    /**
     * Solo perfiles de gestion pueden abrir caja.
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
            'recinto_id' => ['nullable', 'uuid', Rule::exists('recintos', 'id')->whereNull('deleted_at')],
            'saldo_inicial' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'notas_apertura' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Datos normalizados para abrir caja.
     *
     * @return array<string, mixed>
     */
    public function datos(): array
    {
        return [
            'recinto_id' => $this->input('recinto_id') ?: null,
            'saldo_inicial' => round((float) $this->input('saldo_inicial', 0), 2),
            'notas_apertura' => trim((string) $this->input('notas_apertura', '')) ?: null,
        ];
    }
}
