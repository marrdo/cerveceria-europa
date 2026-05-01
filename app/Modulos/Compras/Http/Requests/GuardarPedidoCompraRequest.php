<?php

namespace App\Modulos\Compras\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GuardarPedidoCompraRequest extends FormRequest
{
    /**
     * Autoriza la gestion de pedidos a usuarios autenticados.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Reglas de validacion del pedido y sus lineas.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'proveedor_id' => ['required', 'uuid', 'exists:proveedores,id'],
            'fecha_pedido' => ['nullable', 'date'],
            'fecha_prevista' => ['nullable', 'date', 'after_or_equal:fecha_pedido'],
            'notas' => ['nullable', 'string'],
            'lineas' => ['required', 'array'],
            'lineas.*.producto_id' => ['nullable', 'uuid', 'exists:productos,id'],
            'lineas.*.descripcion' => ['nullable', 'string', 'max:191'],
            'lineas.*.cantidad' => ['nullable', 'numeric', 'min:0.001'],
            'lineas.*.coste_unitario' => ['nullable', 'numeric', 'min:0'],
            'lineas.*.iva_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * Mensajes de validacion en espanol con campo claro.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'proveedor_id.required' => 'El campo proveedor es obligatorio.',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.',
            'fecha_pedido.date' => 'El campo fecha de pedido debe ser una fecha valida.',
            'fecha_prevista.date' => 'El campo fecha prevista debe ser una fecha valida.',
            'fecha_prevista.after_or_equal' => 'El campo fecha prevista debe ser igual o posterior a la fecha de pedido.',
            'lineas.required' => 'Debes anadir al menos una linea al pedido.',
            'lineas.*.producto_id.exists' => 'Uno de los productos seleccionados no existe.',
            'lineas.*.cantidad.min' => 'La cantidad de cada linea debe ser mayor que cero.',
            'lineas.*.coste_unitario.min' => 'El coste unitario no puede ser negativo.',
            'lineas.*.iva_porcentaje.max' => 'El IVA no puede ser superior al 100%.',
        ];
    }

    /**
     * Validacion cruzada para exigir lineas completas.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach ((array) $this->input('lineas', []) as $indice => $linea) {
                    $tieneAlguno = filled($linea['producto_id'] ?? null)
                        || filled($linea['descripcion'] ?? null)
                        || filled($linea['cantidad'] ?? null)
                        || filled($linea['coste_unitario'] ?? null);

                    $estaCompleta = filled($linea['producto_id'] ?? null)
                        && (float) ($linea['cantidad'] ?? 0) > 0;

                    if ($tieneAlguno && ! $estaCompleta) {
                        $validator->errors()->add(
                            "lineas.{$indice}.producto_id",
                            'Cada linea usada debe tener producto y cantidad mayor que cero.',
                        );
                    }
                }

                if (count($this->lineasLimpias()) === 0) {
                    $validator->errors()->add('lineas', 'Debes anadir al menos una linea completa con producto, cantidad y coste.');
                }
            },
        ];
    }

    /**
     * Datos principales normalizados.
     *
     * @return array<string, mixed>
     */
    public function datosPedido(): array
    {
        return [
            'proveedor_id' => $this->input('proveedor_id'),
            'fecha_pedido' => $this->input('fecha_pedido') ?: null,
            'fecha_prevista' => $this->input('fecha_prevista') ?: null,
            'notas' => $this->input('notas') ?: null,
        ];
    }

    /**
     * Lineas completas listas para guardar.
     *
     * @return array<int, array<string, mixed>>
     */
    public function lineasLimpias(): array
    {
        $lineas = [];

        foreach ((array) $this->input('lineas', []) as $linea) {
            $productoId = (string) ($linea['producto_id'] ?? '');
            $cantidad = (float) ($linea['cantidad'] ?? 0);
            $costeUnitario = (float) ($linea['coste_unitario'] ?? 0);

            if ($productoId === '' && $cantidad <= 0 && $costeUnitario <= 0) {
                continue;
            }

            if ($productoId === '' || $cantidad <= 0) {
                continue;
            }

            $lineas[] = [
                'producto_id' => $productoId,
                'descripcion' => trim((string) ($linea['descripcion'] ?? '')),
                'cantidad' => round($cantidad, 3),
                'coste_unitario' => round($costeUnitario, 2),
                'iva_porcentaje' => round((float) ($linea['iva_porcentaje'] ?? 21), 2),
            ];
        }

        return $lineas;
    }
}
