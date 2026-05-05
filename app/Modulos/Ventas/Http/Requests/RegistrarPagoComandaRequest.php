<?php

namespace App\Modulos\Ventas\Http\Requests;

use App\Modulos\Ventas\Enums\MetodoPagoComanda;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RegistrarPagoComandaRequest extends FormRequest
{
    /**
     * Autoriza el cobro a usuarios con acceso al modulo de ventas.
     */
    public function authorize(): bool
    {
        return $this->user()?->puedeAccederModulo('ventas') === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'metodo' => ['required', 'string', Rule::in(MetodoPagoComanda::valores())],
            'importe' => ['nullable', 'numeric', 'min:0.01', 'max:99999.99'],
            'recibido' => ['nullable', 'numeric', 'min:0.01', 'max:99999.99'],
            'referencia' => ['nullable', 'string', 'max:191'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'metodo.required' => 'Selecciona un metodo de pago.',
            'metodo.in' => 'El metodo de pago no es valido.',
            'importe.numeric' => 'El importe debe ser un numero valido.',
            'importe.min' => 'El importe debe ser mayor que cero.',
            'recibido.numeric' => 'El importe recibido debe ser un numero valido.',
            'recibido.min' => 'El importe recibido debe ser mayor que cero.',
        ];
    }

    /**
     * Validaciones cruzadas especificas de efectivo.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('metodo') !== MetodoPagoComanda::Efectivo->value) {
                return;
            }

            $importe = (float) ($this->input('importe') ?: 0);
            $recibido = (float) ($this->input('recibido') ?: 0);

            if ($recibido > 0 && $importe > 0 && $recibido + 0.005 < $importe) {
                $validator->errors()->add('recibido', 'El efectivo recibido no puede ser menor que el importe cobrado.');
            }
        });
    }

    /**
     * Datos normalizados para registrar el pago.
     *
     * @return array<string, mixed>
     */
    public function datosPago(): array
    {
        return [
            'metodo' => MetodoPagoComanda::from((string) $this->input('metodo')),
            'importe' => $this->filled('importe') ? round((float) $this->input('importe'), 2) : null,
            'recibido' => $this->filled('recibido') ? round((float) $this->input('recibido'), 2) : null,
            'referencia' => trim((string) $this->input('referencia', '')) ?: null,
            'notas' => trim((string) $this->input('notas', '')) ?: null,
        ];
    }
}
