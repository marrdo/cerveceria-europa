<?php

namespace App\Modulos\Compras\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Compras\Actions\CrearPedidoCompraBorradorAction;
use App\Modulos\Compras\Http\Requests\GenerarPedidoDesdePropuestaRequest;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Services\DashboardInventarioMetricas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PropuestaCompraController extends Controller
{
    /**
     * Muestra propuestas de reposicion agrupadas por proveedor.
     */
    public function index(DashboardInventarioMetricas $metricas): View
    {
        $propuestas = $metricas->reposicionUrgente(30, 200, 7);

        $productosSinProveedor = $propuestas
            ->filter(fn (array $propuesta): bool => blank($propuesta['producto']->proveedor_id))
            ->map(fn (array $propuesta): array => $this->normalizarPropuesta($propuesta))
            ->values();

        $grupos = $propuestas
            ->filter(fn (array $propuesta): bool => filled($propuesta['producto']->proveedor_id))
            ->groupBy(fn (array $propuesta): string => (string) $propuesta['producto']->proveedor_id)
            ->map(fn (Collection $propuestasProveedor): array => [
                'proveedor' => $propuestasProveedor->first()['producto']->proveedor,
                'productos' => $propuestasProveedor
                    ->map(fn (array $propuesta): array => $this->normalizarPropuesta($propuesta))
                    ->values(),
            ])
            ->sortBy(fn (array $grupo): string => $grupo['proveedor']?->nombre ?? '');

        return view('modulos.compras.propuestas.index', [
            'grupos' => $grupos,
            'productosSinProveedor' => $productosSinProveedor,
        ]);
    }

    /**
     * Genera un pedido borrador desde una propuesta de reposicion.
     */
    public function store(
        GenerarPedidoDesdePropuestaRequest $request,
        CrearPedidoCompraBorradorAction $crearPedido,
    ): RedirectResponse {
        $pedido = $crearPedido->execute([
            'proveedor_id' => $request->proveedorId(),
            'fecha_pedido' => now()->toDateString(),
            'fecha_prevista' => null,
            'notas' => 'Pedido generado desde propuesta de compra por stock bajo.',
        ], $request->lineasLimpias(), $request->user()?->id);

        $pedido->eventos()->create([
            'tipo' => 'propuesta_compra',
            'estado_anterior' => $pedido->estado->value,
            'estado_nuevo' => $pedido->estado->value,
            'descripcion' => 'Pedido generado desde propuesta de compra.',
            'usuario_id' => $request->user()?->id,
        ]);

        return redirect()->route('admin.compras.pedidos.show', $pedido)
            ->with('status', 'Pedido borrador generado desde propuesta de compra.');
    }

    /**
     * Normaliza los datos de reposicion para la vista de propuestas.
     *
     * @param array{
     *     producto: Producto,
     *     stock_actual: float,
     *     salidas_periodo: float,
     *     consumo_medio_diario: float,
     *     dias_restantes: float|null,
     *     motivo: string,
     *     urgencia: int
     * } $propuesta
     *
     * @return array{
     *     producto: Producto,
     *     stock_actual: float,
     *     salidas_periodo: float,
     *     consumo_medio_diario: float,
     *     dias_restantes: float|null,
     *     motivo: string,
     *     cantidad_sugerida: float
     * }
     */
    private function normalizarPropuesta(array $propuesta): array
    {
        return [
            'producto' => $propuesta['producto'],
            'stock_actual' => $propuesta['stock_actual'],
            'salidas_periodo' => $propuesta['salidas_periodo'],
            'consumo_medio_diario' => $propuesta['consumo_medio_diario'],
            'dias_restantes' => $propuesta['dias_restantes'],
            'motivo' => $propuesta['motivo'],
            'cantidad_sugerida' => $this->calcularCantidadSugerida($propuesta),
        ];
    }

    /**
     * Calcula reposicion hasta doble de alerta o hasta 14 dias de cobertura.
     *
     * @param array{
     *     producto: Producto,
     *     stock_actual: float,
     *     consumo_medio_diario: float
     * } $propuesta
     */
    private function calcularCantidadSugerida(array $propuesta): float
    {
        $producto = $propuesta['producto'];
        $stockActual = (float) $propuesta['stock_actual'];
        $alerta = (float) $producto->cantidad_alerta_stock;
        $objetivoPorAlerta = $alerta > 0 ? $alerta * 2 : 1;
        $objetivoPorConsumo = ((float) $propuesta['consumo_medio_diario']) > 0
            ? ((float) $propuesta['consumo_medio_diario']) * 14
            : 0;
        $objetivo = max($objetivoPorAlerta, $objetivoPorConsumo, 1);

        return max(1, round($objetivo - $stockActual, 3));
    }
}
