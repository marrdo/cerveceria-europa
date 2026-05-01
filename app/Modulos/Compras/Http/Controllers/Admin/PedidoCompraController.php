<?php

namespace App\Modulos\Compras\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Compras\Enums\EstadoPedidoCompra;
use App\Modulos\Compras\Http\Requests\GuardarPedidoCompraRequest;
use App\Modulos\Compras\Models\PedidoCompra;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Inventario\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PedidoCompraController extends Controller
{
    /**
     * Lista pedidos de compra con filtros basicos.
     */
    public function index(Request $request): View
    {
        $filtros = [
            'busqueda' => trim((string) $request->query('busqueda', '')),
            'proveedor_id' => (string) $request->query('proveedor_id', ''),
            'estado' => (string) $request->query('estado', ''),
        ];

        $pedidos = PedidoCompra::query()
            ->with(['proveedor', 'creador'])
            ->when($filtros['busqueda'] !== '', function ($query) use ($filtros): void {
                $query->where('numero', 'like', '%'.$filtros['busqueda'].'%');
            })
            ->when($filtros['proveedor_id'] !== '', fn ($query) => $query->where('proveedor_id', $filtros['proveedor_id']))
            ->when($filtros['estado'] !== '', fn ($query) => $query->where('estado', $filtros['estado']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('modulos.compras.pedidos.index', [
            'pedidos' => $pedidos,
            'proveedores' => Proveedor::query()->where('activo', true)->orderBy('nombre')->get(),
            'estados' => EstadoPedidoCompra::cases(),
            'filtros' => $filtros,
        ]);
    }

    /**
     * Muestra el formulario de alta.
     */
    public function create(): View
    {
        return view('modulos.compras.pedidos.create', $this->opcionesFormulario());
    }

    /**
     * Guarda un pedido nuevo en borrador.
     */
    public function store(GuardarPedidoCompraRequest $request): RedirectResponse
    {
        $pedido = DB::transaction(function () use ($request): PedidoCompra {
            $pedido = PedidoCompra::query()->create(array_merge($request->datosPedido(), [
                'numero' => $this->generarNumeroPedido(),
                'estado' => EstadoPedidoCompra::Borrador,
                'creado_por' => $request->user()?->id,
                'actualizado_por' => $request->user()?->id,
            ]));

            $this->guardarLineas($pedido, $request->lineasLimpias());
            $this->recalcularTotales($pedido);
            $this->registrarEvento($pedido, 'creado', 'Pedido creado en borrador.', $request->user()?->id);

            return $pedido;
        });

        return redirect()->route('admin.compras.pedidos.show', $pedido)
            ->with('status', 'Pedido de compra creado correctamente.');
    }

    /**
     * Muestra el detalle del pedido.
     */
    public function show(PedidoCompra $pedido): View
    {
        return view('modulos.compras.pedidos.show', [
            'pedido' => $pedido->load(['proveedor', 'lineas.producto.unidad', 'lineas.recepciones', 'recepciones.lineas.producto.unidad', 'recepciones.lineas.ubicacion', 'recepciones.receptor', 'eventos.usuario', 'creador']),
            'estadosCambioManual' => EstadoPedidoCompra::estadosCambioManual(),
        ]);
    }

    /**
     * Muestra el formulario de edicion.
     */
    public function edit(PedidoCompra $pedido): View|RedirectResponse
    {
        if (! $pedido->puedeEditar()) {
            return redirect()->route('admin.compras.pedidos.show', $pedido)
                ->with('status', 'Solo puedes editar pedidos en estado borrador.');
        }

        return view('modulos.compras.pedidos.edit', array_merge($this->opcionesFormulario(), [
            'pedido' => $pedido->load('lineas'),
        ]));
    }

    /**
     * Actualiza un pedido mientras sigue en borrador.
     */
    public function update(GuardarPedidoCompraRequest $request, PedidoCompra $pedido): RedirectResponse
    {
        if (! $pedido->puedeEditar()) {
            return redirect()->route('admin.compras.pedidos.show', $pedido)
                ->with('status', 'Solo puedes editar pedidos en estado borrador.');
        }

        DB::transaction(function () use ($request, $pedido): void {
            $pedido->update(array_merge($request->datosPedido(), [
                'actualizado_por' => $request->user()?->id,
            ]));

            $pedido->lineas()->delete();
            $this->guardarLineas($pedido, $request->lineasLimpias());
            $this->recalcularTotales($pedido);
            $this->registrarEvento($pedido, 'actualizado', 'Pedido actualizado en borrador.', $request->user()?->id);
        });

        return redirect()->route('admin.compras.pedidos.show', $pedido)
            ->with('status', 'Pedido de compra actualizado correctamente.');
    }

    /**
     * Cambia el estado operativo del pedido y registra evento.
     */
    public function cambiarEstado(Request $request, PedidoCompra $pedido): RedirectResponse
    {
        $datos = $request->validate([
            'estado' => ['required', 'string'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ], [
            'estado.required' => 'El campo estado es obligatorio.',
        ]);

        $estadoNuevo = EstadoPedidoCompra::tryFrom($datos['estado']);

        if (! $estadoNuevo || ! in_array($estadoNuevo, EstadoPedidoCompra::estadosCambioManual(), true)) {
            return back()->withErrors(['estado' => 'El estado seleccionado no es valido.']);
        }

        $estadoAnterior = $pedido->estado;
        $pedido->update([
            'estado' => $estadoNuevo,
            'actualizado_por' => $request->user()?->id,
        ]);

        $this->registrarEvento(
            $pedido,
            'cambio_estado',
            $datos['descripcion'] ?: 'Cambio de estado a '.$estadoNuevo->etiqueta().'.',
            $request->user()?->id,
            $estadoAnterior,
            $estadoNuevo,
        );

        return redirect()->route('admin.compras.pedidos.show', $pedido)
            ->with('status', 'Estado del pedido actualizado correctamente.');
    }

    /**
     * Opciones comunes del formulario.
     *
     * @return array<string, mixed>
     */
    private function opcionesFormulario(): array
    {
        return [
            'proveedores' => Proveedor::query()->where('activo', true)->orderBy('nombre')->get(),
            'productos' => Producto::query()->where('activo', true)->with('unidad')->orderBy('nombre')->get(),
        ];
    }

    private function generarNumeroPedido(): string
    {
        $prefijo = 'PC-'.now()->format('Ymd').'-';
        $secuencia = PedidoCompra::query()->where('numero', 'like', $prefijo.'%')->count() + 1;

        return $prefijo.str_pad((string) $secuencia, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param array<int, array<string, mixed>> $lineas
     */
    private function guardarLineas(PedidoCompra $pedido, array $lineas): void
    {
        foreach ($lineas as $orden => $linea) {
            $producto = Producto::query()->findOrFail($linea['producto_id']);
            $subtotal = round($linea['cantidad'] * $linea['coste_unitario'], 2);
            $impuestos = round($subtotal * ($linea['iva_porcentaje'] / 100), 2);

            $pedido->lineas()->create([
                'producto_id' => $producto->id,
                'descripcion' => $linea['descripcion'] !== '' ? $linea['descripcion'] : $producto->nombre,
                'cantidad' => $linea['cantidad'],
                'coste_unitario' => $linea['coste_unitario'],
                'iva_porcentaje' => $linea['iva_porcentaje'],
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => round($subtotal + $impuestos, 2),
                'orden' => $orden,
            ]);
        }
    }

    private function recalcularTotales(PedidoCompra $pedido): void
    {
        $lineas = $pedido->lineas()->get();

        $pedido->update([
            'subtotal' => round((float) $lineas->sum('subtotal'), 2),
            'impuestos' => round((float) $lineas->sum('impuestos'), 2),
            'total' => round((float) $lineas->sum('total'), 2),
        ]);
    }

    private function registrarEvento(
        PedidoCompra $pedido,
        string $tipo,
        string $descripcion,
        ?string $usuarioId,
        ?EstadoPedidoCompra $estadoAnterior = null,
        ?EstadoPedidoCompra $estadoNuevo = null,
    ): void {
        $pedido->eventos()->create([
            'tipo' => $tipo,
            'estado_anterior' => $estadoAnterior?->value,
            'estado_nuevo' => $estadoNuevo?->value,
            'descripcion' => $descripcion,
            'usuario_id' => $usuarioId,
        ]);
    }
}
