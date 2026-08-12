<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Sistema\Modulos\GestorModulos;
use App\ViewData\DashboardDemoViewData;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Muestra el punto de entrada común del panel y su recorrido demostrable.
 */
final class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        DashboardDemoViewData $demoViewData,
        GestorModulos $modulos,
    ): View {
        $productos = Producto::query()->with('stock')->get();

        return view('dashboard', [
            'totalProductos' => $productos->count(),
            'productosBajoStock' => $productos
                ->filter(fn (Producto $producto): bool => $producto->estadoStock()->value === 'bajo')
                ->count(),
            'movimientosRecientes' => MovimientoInventario::query()
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'modulosConfigurables' => $modulos->resumenAdministracion(),
            'recorridoDemo' => $demoViewData->construir($request->user()),
        ]);
    }
}
