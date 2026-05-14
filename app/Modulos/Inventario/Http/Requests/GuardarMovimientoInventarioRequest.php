<?php

namespace App\Modulos\Inventario\Http\Requests;

use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
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
            'codigo_lote' => ['nullable', 'string', 'max:100'],
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
            'caduca_el.date' => 'El campo fecha de caducidad debe ser una fecha valida.',
        ];
    }

    /**
     * Validaciones cruzadas del formulario de movimiento.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->input('tipo') === TipoMovimientoInventario::Transferencia->value) {
                    $origenId = $this->input('ubicacion_origen_id');
                    $destinoId = $this->input('ubicacion_destino_id');

                    if ($origenId !== null && $destinoId !== null && $origenId === $destinoId) {
                        $validator->errors()->add(
                            'ubicacion_destino_id',
                            'El campo ubicacion de destino debe ser distinto de la ubicacion de origen.',
                        );
                    }
                }

                $producto = $this->productoRuta();

                if (
                    $producto?->controla_caducidad
                    && $this->input('tipo') === TipoMovimientoInventario::Entrada->value
                    && blank($this->input('caduca_el'))
                ) {
                    $validator->errors()->add(
                        'caduca_el',
                        'El campo fecha de caducidad es obligatorio para entradas de productos con caducidad.',
                    );
                }
            },
        ];
    }

    /**
     * Resuelve el producto de la ruta cuando llega por SKU visible o por UUID heredado.
     */
    private function productoRuta(): ?Producto
    {
        $producto = $this->route('producto');

        if ($producto instanceof Producto) {
            return $producto;
        }

        if (! is_string($producto) || $producto === '') {
            return null;
        }

        return Producto::query()
            ->where(function ($query) use ($producto): void {
                $query
                    ->where('sku', $producto)
                    ->orWhere('id', $producto);
            })
            ->first();
    }
}
