<?php

namespace App\Modulos\Compras\Http\Requests;

use App\Modulos\Compras\Models\PedidoCompra;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GuardarDevolucionProveedorRequest extends FormRequest
{
    /**
     * Autoriza devoluciones a usuarios autenticados.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Reglas de validacion de la devolucion.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fecha_devolucion' => ['required', 'date'],
            'motivo' => ['required', 'string', 'max:191'],
            'notas' => ['nullable', 'string'],
            'linea_pedido_compra_id' => ['required', 'uuid', 'exists:lineas_pedido_compra,id'],
            'ubicacion_inventario_id' => ['required', 'uuid', 'exists:ubicaciones_inventario,id'],
            'cantidad' => ['required', 'numeric', 'min:0.001'],
            'notas_linea' => ['nullable', 'string'],
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
            'fecha_devolucion.required' => 'El campo fecha de devolucion es obligatorio.',
            'fecha_devolucion.date' => 'El campo fecha de devolucion debe ser una fecha valida.',
            'motivo.required' => 'El campo motivo es obligatorio.',
            'motivo.max' => 'El campo motivo no puede superar 191 caracteres.',
            'linea_pedido_compra_id.required' => 'El campo linea devuelta es obligatorio.',
            'linea_pedido_compra_id.exists' => 'La linea devuelta seleccionada no existe.',
            'ubicacion_inventario_id.required' => 'El campo ubicacion es obligatorio.',
            'ubicacion_inventario_id.exists' => 'La ubicacion seleccionada no existe.',
            'cantidad.required' => 'El campo cantidad es obligatorio.',
            'cantidad.numeric' => 'El campo cantidad debe ser un numero.',
            'cantidad.min' => 'El campo cantidad debe ser mayor que cero.',
        ];
    }

    /**
     * Validaciones cruzadas contra el pedido.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $pedido = $this->pedido();
                $lineaPedidoId = (string) $this->input('linea_pedido_compra_id');
                $lineaPedido = $pedido->lineas->firstWhere('id', $lineaPedidoId);

                if (! $lineaPedido) {
                    $validator->errors()->add('linea_pedido_compra_id', 'La linea devuelta no pertenece a este pedido.');
                    return;
                }

                $cantidad = round((float) $this->input('cantidad'), 3);
                $disponible = $lineaPedido->cantidadDisponibleDevolucion();

                if ($cantidad - $disponible > 0.0005) {
                    $validator->errors()->add('cantidad', "La cantidad devuelta de {$lineaPedido->descripcion} supera la cantidad recibida pendiente de devolver.");
                }
            },
        ];
    }

    /**
     * Datos principales de la devolucion.
     *
     * @return array<string, mixed>
     */
    public function datosDevolucion(): array
    {
        return [
            'fecha_devolucion' => $this->input('fecha_devolucion'),
            'motivo' => trim((string) $this->input('motivo')),
            'notas' => trim((string) $this->input('notas', '')) ?: null,
        ];
    }

    /**
     * Datos de la linea devuelta.
     *
     * @return array<string, mixed>
     */
    public function datosLinea(): array
    {
        return [
            'linea_pedido_compra_id' => (string) $this->input('linea_pedido_compra_id'),
            'ubicacion_inventario_id' => (string) $this->input('ubicacion_inventario_id'),
            'cantidad' => round((float) $this->input('cantidad'), 3),
            'notas' => trim((string) $this->input('notas_linea', '')) ?: null,
        ];
    }

    private function pedido(): PedidoCompra
    {
        /** @var PedidoCompra $pedido */
        $pedido = $this->route('pedido');

        return $pedido->loadMissing(['lineas.recepciones', 'lineas.devoluciones']);
    }
}
