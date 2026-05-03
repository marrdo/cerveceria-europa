<?php

namespace App\Modulos\Compras\Http\Requests;

use App\Modulos\Compras\Enums\TipoIncidenciaRecepcionCompra;
use App\Modulos\Compras\Models\PedidoCompra;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GuardarIncidenciaRecepcionCompraRequest extends FormRequest
{
    /**
     * Autoriza incidencias a usuarios autenticados.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Reglas de validacion de la incidencia.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', Rule::in(TipoIncidenciaRecepcionCompra::valores())],
            'recepcion_compra_id' => ['nullable', 'uuid', 'exists:recepciones_compra,id'],
            'linea_pedido_compra_id' => ['nullable', 'uuid', 'exists:lineas_pedido_compra,id'],
            'cantidad_afectada' => ['nullable', 'numeric', 'min:0.001'],
            'descripcion' => ['required', 'string', 'max:1000'],
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
            'tipo.required' => 'El campo tipo de incidencia es obligatorio.',
            'tipo.in' => 'El tipo de incidencia seleccionado no es valido.',
            'recepcion_compra_id.exists' => 'La recepcion seleccionada no existe.',
            'linea_pedido_compra_id.exists' => 'La linea de pedido seleccionada no existe.',
            'cantidad_afectada.numeric' => 'El campo cantidad afectada debe ser un numero.',
            'cantidad_afectada.min' => 'El campo cantidad afectada debe ser mayor que cero.',
            'descripcion.required' => 'El campo descripcion de la incidencia es obligatorio.',
            'descripcion.max' => 'El campo descripcion de la incidencia no puede superar 1000 caracteres.',
        ];
    }

    /**
     * Validaciones cruzadas para asegurar que la incidencia pertenece al pedido.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $pedido = $this->pedido();

                $recepcionId = (string) $this->input('recepcion_compra_id', '');
                if ($recepcionId !== '' && ! $pedido->recepciones->contains('id', $recepcionId)) {
                    $validator->errors()->add('recepcion_compra_id', 'La recepcion seleccionada no pertenece a este pedido.');
                }

                $lineaPedidoId = (string) $this->input('linea_pedido_compra_id', '');
                if ($lineaPedidoId !== '' && ! $pedido->lineas->contains('id', $lineaPedidoId)) {
                    $validator->errors()->add('linea_pedido_compra_id', 'La linea seleccionada no pertenece a este pedido.');
                }
            },
        ];
    }

    /**
     * Datos listos para persistir.
     *
     * @return array<string, mixed>
     */
    public function datosIncidencia(): array
    {
        return [
            'tipo' => $this->input('tipo'),
            'recepcion_compra_id' => $this->input('recepcion_compra_id') ?: null,
            'linea_pedido_compra_id' => $this->input('linea_pedido_compra_id') ?: null,
            'cantidad_afectada' => $this->filled('cantidad_afectada') ? round((float) $this->input('cantidad_afectada'), 3) : null,
            'descripcion' => trim((string) $this->input('descripcion')),
        ];
    }

    private function pedido(): PedidoCompra
    {
        /** @var PedidoCompra $pedido */
        $pedido = $this->route('pedido');

        return $pedido->loadMissing(['recepciones', 'lineas']);
    }
}
