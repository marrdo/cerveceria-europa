<?php

namespace App\Modulos\Compras\Http\Requests;

use App\Modulos\Inventario\Models\Producto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GenerarPedidoDesdePropuestaRequest extends FormRequest
{
    /**
     * Autoriza la generacion de pedidos desde propuestas.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Reglas de validacion de la propuesta seleccionada.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'proveedor_id' => ['required', 'uuid', 'exists:proveedores,id'],
            'productos' => ['required', 'array'],
            'productos.*.producto_id' => ['required', 'uuid', 'exists:productos,id'],
            'productos.*.cantidad' => ['required', 'numeric', 'min:0.001'],
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
            'proveedor_id.required' => 'El campo proveedor es obligatorio.',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.',
            'productos.required' => 'Debes seleccionar al menos un producto para generar el pedido.',
            'productos.*.producto_id.required' => 'Cada linea propuesta debe tener producto.',
            'productos.*.producto_id.exists' => 'Uno de los productos seleccionados no existe.',
            'productos.*.cantidad.required' => 'Cada linea propuesta debe tener cantidad.',
            'productos.*.cantidad.numeric' => 'La cantidad propuesta debe ser un numero.',
            'productos.*.cantidad.min' => 'La cantidad propuesta debe ser mayor que cero.',
        ];
    }

    /**
     * Validaciones cruzadas de proveedor y lineas completas.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (count($this->lineasLimpias()) === 0) {
                    $validator->errors()->add('productos', 'Debes seleccionar al menos un producto con cantidad mayor que cero.');
                    return;
                }

                $proveedorId = (string) $this->input('proveedor_id');

                foreach ($this->lineasLimpias() as $indice => $linea) {
                    $producto = Producto::query()->find($linea['producto_id']);

                    if (! $producto || $producto->proveedor_id !== $proveedorId) {
                        $validator->errors()->add("productos.{$indice}.producto_id", 'El producto seleccionado no pertenece al proveedor de la propuesta.');
                    }
                }
            },
        ];
    }

    /**
     * Lineas normalizadas para crear el pedido.
     *
     * @return array<int, array<string, mixed>>
     */
    public function lineasLimpias(): array
    {
        $lineas = [];

        foreach ((array) $this->input('productos', []) as $linea) {
            $productoId = (string) ($linea['producto_id'] ?? '');
            $cantidad = round((float) ($linea['cantidad'] ?? 0), 3);

            if ($productoId === '' || $cantidad <= 0) {
                continue;
            }

            $producto = Producto::query()->find($productoId);

            if (! $producto) {
                continue;
            }

            $lineas[] = [
                'producto_id' => $producto->id,
                'descripcion' => $producto->nombre,
                'cantidad' => $cantidad,
                'coste_unitario' => round((float) ($producto->precio_coste ?? 0), 2),
                'iva_porcentaje' => 21,
            ];
        }

        return $lineas;
    }

    public function proveedorId(): string
    {
        return (string) $this->input('proveedor_id');
    }
}
