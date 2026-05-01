<?php

namespace App\Modulos\Inventario\Http\Requests;

use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GuardarMovimientoInventarioRequest extends FormRequest
{
    /**
     * Autoriza el registro de movimientos a usuarios autenticados.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Reglas de validacion del movimiento.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::enum(TipoMovimientoInventario::class)],
            'ubicacion_inventario_id' => ['required_unless:tipo,transferencia', 'nullable', 'uuid', 'exists:ubicaciones_inventario,id'],
            'ubicacion_origen_id' => ['required_if:tipo,transferencia', 'nullable', 'uuid', 'exists:ubicaciones_inventario,id'],
            'ubicacion_destino_id' => ['required_if:tipo,transferencia', 'nullable', 'uuid', 'exists:ubicaciones_inventario,id'],
            'proveedor_id' => ['nullable', 'uuid', 'exists:proveedores,id'],
            'cantidad' => ['required', 'numeric', 'min:0.001'],
            'coste_unitario' => ['nullable', 'numeric', 'min:0'],
            'motivo' => ['required', 'string', 'max:191'],
            'referencia' => ['nullable', 'string', 'max:191'],
            'caduca_el' => ['nullable', 'date'],
            'notas' => ['nullable', 'string'],
        ];
    }

    /**
     * Mensajes especificos para que el usuario entienda que campo debe corregir.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ubicacion_inventario_id.required_unless' => 'El campo ubicacion es obligatorio para entradas, salidas y ajustes.',
            'ubicacion_origen_id.required_if' => 'El campo ubicacion de origen es obligatorio para transferencias.',
            'ubicacion_destino_id.required_if' => 'El campo ubicacion de destino es obligatorio para transferencias.',
        ];
    }

    /**
     * Validaciones cruzadas del formulario de movimiento.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->input('tipo') !== TipoMovimientoInventario::Transferencia->value) {
                    return;
                }

                $origenId = $this->input('ubicacion_origen_id');
                $destinoId = $this->input('ubicacion_destino_id');

                if ($origenId !== null && $destinoId !== null && $origenId === $destinoId) {
                    $validator->errors()->add(
                        'ubicacion_destino_id',
                        'El campo ubicacion de destino debe ser distinto de la ubicacion de origen.',
                    );
                }
            },
        ];
    }
}
