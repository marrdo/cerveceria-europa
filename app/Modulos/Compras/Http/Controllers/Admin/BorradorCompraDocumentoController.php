<?php

namespace App\Modulos\Compras\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Compras\Actions\CrearPedidoCompraBorradorAction;
use App\Modulos\Compras\Enums\EstadoDocumentoCompra;
use App\Modulos\Compras\Http\Requests\GuardarBorradorCompraDocumentoRequest;
use App\Modulos\Compras\Models\BorradorCompraDocumento;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BorradorCompraDocumentoController extends Controller
{
    /**
     * Muestra la pantalla de revision manual del borrador.
     */
    public function edit(BorradorCompraDocumento $borrador): View
    {
        return view('modulos.compras.documentos.borradores.edit', [
            'borrador' => $borrador->load(['documento.proveedor', 'documento.lecturas', 'pedido']),
            'proveedores' => Proveedor::query()->where('activo', true)->orderBy('nombre')->get(),
            'productos' => Producto::query()->where('activo', true)->with('unidad')->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Guarda datos revisados sin crear pedido ni tocar inventario.
     */
    public function update(GuardarBorradorCompraDocumentoRequest $request, BorradorCompraDocumento $borrador): RedirectResponse
    {
        $borrador->update([
            'estado' => 'pendiente_revision',
            'datos_borrador' => $request->datosBorrador(),
            'notas_revision' => $request->notasRevision(),
            'revisado_por' => $request->user()?->id,
            'revisado_at' => now(),
        ]);

        $borrador->documento()->update([
            'proveedor_id' => $request->datosBorrador()['proveedor_id'],
            'estado' => EstadoDocumentoCompra::EnRevision,
        ]);

        return redirect()->route('admin.compras.documentos.show', $borrador->documento)
            ->with('status', 'Borrador guardado correctamente. No se ha creado ningun pedido ni movimiento de stock.');
    }

    /**
     * Convierte el borrador revisado en pedido de compra en borrador.
     */
    public function generarPedido(
        GuardarBorradorCompraDocumentoRequest $request,
        BorradorCompraDocumento $borrador,
        CrearPedidoCompraBorradorAction $crearPedido,
    ): RedirectResponse {
        if ($borrador->pedido_compra_id) {
            return redirect()->route('admin.compras.pedidos.show', $borrador->pedido_compra_id)
                ->with('status', 'Este borrador ya tiene un pedido asociado.');
        }

        $datosBorrador = $request->datosBorrador();

        if (blank($datosBorrador['proveedor_id'])) {
            return back()->withErrors(['proveedor_id' => 'Debes seleccionar proveedor antes de generar el pedido.']);
        }

        if (count($request->lineasLimpias()) === 0) {
            return back()->withErrors(['lineas' => 'Debes revisar al menos una linea completa antes de generar el pedido.']);
        }

        $pedido = DB::transaction(function () use ($request, $borrador, $crearPedido, $datosBorrador) {
            $pedido = $crearPedido->execute([
                'proveedor_id' => $datosBorrador['proveedor_id'],
                'fecha_pedido' => $datosBorrador['fecha_documento'] ?: now()->toDateString(),
                'fecha_prevista' => null,
                'notas' => 'Pedido generado desde documento '.$borrador->documento->nombre_original.'.',
            ], $request->lineasLimpias(), $request->user()?->id);

            $borrador->update([
                'pedido_compra_id' => $pedido->id,
                'estado' => 'convertido_pedido',
                'datos_borrador' => $datosBorrador,
                'notas_revision' => $request->notasRevision(),
                'revisado_por' => $request->user()?->id,
                'revisado_at' => now(),
            ]);

            $borrador->documento()->update([
                'proveedor_id' => $datosBorrador['proveedor_id'],
                'estado' => EstadoDocumentoCompra::Procesado,
            ]);

            $pedido->eventos()->create([
                'tipo' => 'documento_compra',
                'estado_anterior' => $pedido->estado->value,
                'estado_nuevo' => $pedido->estado->value,
                'descripcion' => 'Pedido generado desde documento de compra revisado.',
                'usuario_id' => $request->user()?->id,
            ]);

            return $pedido;
        });

        return redirect()->route('admin.compras.pedidos.show', $pedido)
            ->with('status', 'Pedido borrador generado desde documento revisado.');
    }
}
