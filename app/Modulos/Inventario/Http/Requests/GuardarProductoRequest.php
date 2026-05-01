<?php

namespace App\Modulos\Inventario\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarProductoRequest extends FormRequest
{
    /**
     * Autoriza la gestion de productos a usuarios autenticados.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Reglas de validacion del formulario de producto.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $productoId = $this->route('producto')?->id;

        return [
            'categoria_producto_id' => ['required', 'uuid', 'exists:categorias_productos,id'],
            'proveedor_id' => ['nullable', 'uuid', 'exists:proveedores,id'],
            'unidad_inventario_id' => ['required', 'uuid', 'exists:unidades_inventario,id'],
            'nombre' => ['required', 'string', 'max:191'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('productos', 'sku')->ignore($productoId)],
            'codigo_barras' => ['nullable', 'string', 'max:100'],
            'referencia_proveedor' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'precio_coste' => ['nullable', 'numeric', 'min:0'],
            'cantidad_alerta_stock' => ['required', 'numeric', 'min:0'],
            'controla_stock' => ['nullable', 'boolean'],
            'controla_caducidad' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Datos normalizados para escritura.
     *
     * @return array<string, mixed>
     */
    public function datos(): array
    {
        return array_merge($this->validated(), [
            'controla_stock' => $this->boolean('controla_stock'),
            'controla_caducidad' => $this->boolean('controla_caducidad'),
            'activo' => $this->boolean('activo', true),
        ]);
    }
}
