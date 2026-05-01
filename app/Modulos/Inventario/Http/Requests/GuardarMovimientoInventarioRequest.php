<?php

namespace App\Modulos\Inventario\Http\Requests;

use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
}
