<?php

namespace App\Modulos\Compras\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Compras\Http\Requests\GuardarIncidenciaRecepcionCompraRequest;
use App\Modulos\Compras\Models\PedidoCompra;
use Illuminate\Http\RedirectResponse;

class IncidenciaRecepcionCompraController extends Controller
{
    /**
     * Registra una incidencia operativa sobre un pedido o recepcion.
     */
    public function store(GuardarIncidenciaRecepcionCompraRequest $request, PedidoCompra $pedido): RedirectResponse
    {
        $incidencia = $pedido->incidencias()->create(array_merge($request->datosIncidencia(), [
            'registrada_por' => $request->user()?->id,
        ]));

        $pedido->eventos()->create([
            'tipo' => 'incidencia_recepcion',
            'estado_anterior' => $pedido->estado->value,
            'estado_nuevo' => $pedido->estado->value,
            'descripcion' => 'Incidencia registrada: '.$incidencia->tipo->etiqueta().'.',
            'usuario_id' => $request->user()?->id,
        ]);

        return redirect()->route('admin.compras.pedidos.show', $pedido)
            ->with('status', 'Incidencia registrada correctamente.');
    }
}
