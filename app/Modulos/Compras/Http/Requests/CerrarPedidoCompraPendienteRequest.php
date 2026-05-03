<?php

namespace App\Modulos\Compras\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CerrarPedidoCompraPendienteRequest extends FormRequest
{
    /**
     * Autoriza el cierre a usuarios autenticados.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Reglas para cerrar un pedido parcialmente recibido.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'motivo_cierre' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * Mensajes de validacion en espanol.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'motivo_cierre.required' => 'El campo motivo de cierre es obligatorio.',
            'motivo_cierre.max' => 'El campo motivo de cierre no puede superar 1000 caracteres.',
        ];
    }

    public function motivoCierre(): string
    {
        return trim((string) $this->input('motivo_cierre'));
    }
}
