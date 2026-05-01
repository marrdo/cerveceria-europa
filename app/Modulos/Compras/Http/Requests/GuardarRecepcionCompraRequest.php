<?php

namespace App\Modulos\Compras\Http\Requests;

use App\Modulos\Compras\Models\PedidoCompra;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GuardarRecepcionCompraRequest extends FormRequest
{
    /**
     * Autoriza recepciones a usuarios autenticados.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Reglas de validacion de la recepcion.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fecha_recepcion' => ['required', 'date'],
            'notas' => ['nullable', 'string'],
            'lineas' => ['required', 'array'],
            'lineas.*.linea_pedido_compra_id' => ['nullable', 'uuid', 'exists:lineas_pedido_compra,id'],
            'lineas.*.ubicacion_inventario_id' => ['nullable', 'uuid', 'exists:ubicaciones_inventario,id'],
            'lineas.*.cantidad' => ['nullable', 'numeric', 'min:0.001'],
            'lineas.*.codigo_lote' => ['nullable', 'string', 'max:100'],
            'lineas.*.caduca_el' => ['nullable', 'date'],
            'lineas.*.notas' => ['nullable', 'string'],
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
            'fecha_recepcion.required' => 'El campo fecha de recepcion es obligatorio.',
            'fecha_recepcion.date' => 'El campo fecha de recepcion debe ser una fecha valida.',
            'lineas.required' => 'Debes anadir al menos una linea recibida.',
            'lineas.*.linea_pedido_compra_id.exists' => 'Una de las lineas del pedido no existe.',
            'lineas.*.ubicacion_inventario_id.exists' => 'Una de las ubicaciones seleccionadas no existe.',
            'lineas.*.cantidad.min' => 'La cantidad recibida debe ser mayor que cero.',
            'lineas.*.caduca_el.date' => 'La fecha de caducidad debe ser una fecha valida.',
        ];
    }

    /**
     * Validaciones cruzadas contra el pedido recibido.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $pedido = $this->pedido();
                $lineasLimpias = $this->lineasLimpias();

                if (count($lineasLimpias) === 0) {
                    $validator->errors()->add('lineas', 'Debes anadir al menos una linea recibida completa.');
                }

                foreach ($lineasLimpias as $indice => $linea) {
                    $lineaPedido = $pedido->lineas->firstWhere('id', $linea['linea_pedido_compra_id']);

                    if (! $lineaPedido) {
                        $validator->errors()->add("lineas.{$indice}.linea_pedido_compra_id", 'La linea no pertenece a este pedido.');
                        continue;
                    }

                    if ($lineaPedido->producto?->controla_caducidad && blank($linea['caduca_el'])) {
                        $validator->errors()->add("lineas.{$indice}.caduca_el", 'La fecha de caducidad es obligatoria para productos con caducidad.');
                    }
                }

                foreach ($this->cantidadesPorLinea() as $lineaPedidoId => $cantidadRecibida) {
                    $lineaPedido = $pedido->lineas->firstWhere('id', $lineaPedidoId);

                    if (! $lineaPedido) {
                        continue;
                    }

                    if ($cantidadRecibida - $lineaPedido->cantidadPendiente() > 0.0005) {
                        $validator->errors()->add('lineas', "La cantidad recibida de {$lineaPedido->descripcion} supera la cantidad pendiente.");
                    }
                }
            },
        ];
    }

    /**
     * Datos principales de la recepcion.
     *
     * @return array<string, mixed>
     */
    public function datosRecepcion(): array
    {
        return [
            'fecha_recepcion' => $this->input('fecha_recepcion'),
            'notas' => $this->input('notas') ?: null,
        ];
    }

    /**
     * Lineas completas listas para registrar.
     *
     * @return array<int, array<string, mixed>>
     */
    public function lineasLimpias(): array
    {
        $lineas = [];

        foreach ((array) $this->input('lineas', []) as $linea) {
            $lineaPedidoId = (string) ($linea['linea_pedido_compra_id'] ?? '');
            $ubicacionId = (string) ($linea['ubicacion_inventario_id'] ?? '');
            $cantidad = (float) ($linea['cantidad'] ?? 0);

            if ($lineaPedidoId === '' && $ubicacionId === '' && $cantidad <= 0) {
                continue;
            }

            if ($lineaPedidoId === '' || $ubicacionId === '' || $cantidad <= 0) {
                continue;
            }

            $lineas[] = [
                'linea_pedido_compra_id' => $lineaPedidoId,
                'ubicacion_inventario_id' => $ubicacionId,
                'cantidad' => round($cantidad, 3),
                'codigo_lote' => trim((string) ($linea['codigo_lote'] ?? '')) ?: null,
                'caduca_el' => $linea['caduca_el'] ?? null,
                'notas' => trim((string) ($linea['notas'] ?? '')) ?: null,
            ];
        }

        return $lineas;
    }

    /**
     * @return array<string, float>
     */
    private function cantidadesPorLinea(): array
    {
        $cantidades = [];

        foreach ($this->lineasLimpias() as $linea) {
            $lineaPedidoId = $linea['linea_pedido_compra_id'];
            $cantidades[$lineaPedidoId] = round(($cantidades[$lineaPedidoId] ?? 0) + $linea['cantidad'], 3);
        }

        return $cantidades;
    }

    private function pedido(): PedidoCompra
    {
        /** @var PedidoCompra $pedido */
        $pedido = $this->route('pedido');

        return $pedido->loadMissing(['lineas.recepciones', 'lineas.producto']);
    }
}
