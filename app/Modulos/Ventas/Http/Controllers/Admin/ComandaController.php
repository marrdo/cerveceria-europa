<?php

namespace App\Modulos\Ventas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Inventario\Models\UbicacionInventario;
use App\Modulos\Ventas\Actions\CrearComandaAction;
use App\Modulos\Ventas\Actions\RegistrarPagoComandaAction;
use App\Modulos\Ventas\Actions\ServirLineaComandaAction;
use App\Modulos\Ventas\Enums\EstadoComanda;
use App\Modulos\Ventas\Enums\EstadoLineaComanda;
use App\Modulos\Ventas\Enums\MetodoPagoComanda;
use App\Modulos\Ventas\Http\Requests\GuardarComandaRequest;
use App\Modulos\Ventas\Http\Requests\RegistrarPagoComandaRequest;
use App\Modulos\Ventas\Models\Comanda;
use App\Modulos\Ventas\Models\LineaComanda;
use App\Modulos\WebPublica\Models\ContenidoWeb;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComandaController extends Controller
{
    /**
     * Lista comandas con filtros operativos para barra/sala.
     */
    public function index(Request $request): View
    {
        $filtros = [
            'busqueda' => trim((string) $request->query('busqueda', '')),
            'estado' => (string) $request->query('estado', ''),
        ];

        $comandas = Comanda::query()
            ->with(['creador', 'ubicacionInventario'])
            ->withCount('lineas')
            ->when($filtros['busqueda'] !== '', function ($query) use ($filtros): void {
                $query->where(function ($consulta) use ($filtros): void {
                    $consulta->where('numero', 'like', '%'.$filtros['busqueda'].'%')
                        ->orWhere('mesa', 'like', '%'.$filtros['busqueda'].'%')
                        ->orWhere('cliente_nombre', 'like', '%'.$filtros['busqueda'].'%');
                });
            })
            ->when($filtros['estado'] !== '', fn ($query) => $query->where('estado', $filtros['estado']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('modulos.ventas.comandas.index', [
            'comandas' => $comandas,
            'estados' => EstadoComanda::cases(),
            'filtros' => $filtros,
        ]);
    }

    /**
     * Muestra la pantalla de toma de comanda desde la carta.
     */
    public function create(): View
    {
        $contenidos = ContenidoWeb::query()
            ->publicado()
            ->with(['categoriaCarta.padre', 'tarifas', 'producto.stock'])
            ->orderBy('tipo')
            ->orderBy('orden')
            ->orderBy('titulo')
            ->get();

        return view('modulos.ventas.comandas.create', [
            'ubicaciones' => UbicacionInventario::query()->where('activo', true)->orderBy('nombre')->get(),
            'contenidos' => $contenidos,
            'seccionesCarta' => $this->agruparCartaParaComanda($contenidos),
        ]);
    }

    /**
     * Agrupa la carta en seccion padre y categoria hija para toma rapida en sala.
     *
     * @param \Illuminate\Support\Collection<int, ContenidoWeb> $contenidos
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function agruparCartaParaComanda($contenidos)
    {
        return $contenidos
            ->groupBy(function (ContenidoWeb $contenido): string {
                $categoria = $contenido->categoriaCarta;
                $padre = $categoria?->padre;

                return $padre?->id ?? $categoria?->id ?? 'sin-categoria';
            })
            ->map(function ($contenidosSeccion): array {
                /** @var ContenidoWeb $primero */
                $primero = $contenidosSeccion->first();
                $categoria = $primero->categoriaCarta;
                $padre = $categoria?->padre;
                $seccion = $padre ?? $categoria;

                $categorias = $contenidosSeccion
                    ->groupBy(function (ContenidoWeb $contenido) use ($seccion): string {
                        $categoria = $contenido->categoriaCarta;

                        if (! $categoria) {
                            return 'sin-categoria';
                        }

                        return $categoria->id === $seccion?->id ? 'general' : $categoria->id;
                    })
                    ->map(function ($contenidosCategoria): array {
                        /** @var ContenidoWeb $primeroCategoria */
                        $primeroCategoria = $contenidosCategoria->first();
                        $categoria = $primeroCategoria->categoriaCarta;
                        $nombre = $categoria && $categoria->padre
                            ? $categoria->nombre
                            : ($categoria ? 'General' : 'Sin categoria');

                        return [
                            'nombre' => $nombre,
                            'orden' => $categoria?->orden ?? 9999,
                            'contenidos' => $contenidosCategoria->sortBy([
                                ['orden', 'asc'],
                                ['titulo', 'asc'],
                            ])->values(),
                        ];
                    })
                    ->sortBy([
                        ['orden', 'asc'],
                        ['nombre', 'asc'],
                    ])
                    ->values();

                return [
                    'nombre' => $seccion?->nombre ?? 'Sin categoria',
                    'descripcion' => $seccion?->descripcion,
                    'orden' => $seccion?->orden ?? 9999,
                    'categorias' => $categorias,
                    'total' => $contenidosSeccion->count(),
                ];
            })
            ->sortBy([
                ['orden', 'asc'],
                ['nombre', 'asc'],
            ])
            ->values();
    }

    /**
     * Guarda una comanda nueva en estado abierta.
     */
    public function store(GuardarComandaRequest $request, CrearComandaAction $crearComanda): RedirectResponse
    {
        $comanda = $crearComanda->execute($request->datosComanda(), (string) $request->user()?->id);

        return redirect()->route('admin.ventas.comandas.show', $comanda)
            ->with('status', 'Comanda creada correctamente.');
    }

    /**
     * Muestra el detalle operativo de una comanda.
     */
    public function show(Comanda $comanda): View
    {
        return view('modulos.ventas.comandas.show', [
            'comanda' => $comanda->load(['lineas.producto.unidad', 'lineas.movimientoInventario', 'pagos.cobrador', 'ubicacionInventario', 'creador']),
            'metodosPago' => MetodoPagoComanda::cases(),
        ]);
    }

    /**
     * Marca una linea como servida y descuenta stock si procede.
     */
    public function servirLinea(Request $request, Comanda $comanda, LineaComanda $linea, ServirLineaComandaAction $servirLinea): RedirectResponse
    {
        abort_unless($linea->comanda_id === $comanda->id, 404);

        $servirLinea->execute($linea, (string) $request->user()?->id);

        return redirect()->route('admin.ventas.comandas.show', $comanda)
            ->with('status', 'Linea servida y stock actualizado si correspondia.');
    }

    /**
     * Sirve todas las lineas pendientes de la comanda.
     */
    public function servir(Request $request, Comanda $comanda, ServirLineaComandaAction $servirLinea): RedirectResponse
    {
        $comanda->load('lineas');

        foreach ($comanda->lineas as $linea) {
            if ($linea->estado !== EstadoLineaComanda::Servida && $linea->estado !== EstadoLineaComanda::Cancelada) {
                $servirLinea->execute($linea, (string) $request->user()?->id);
            }
        }

        return redirect()->route('admin.ventas.comandas.show', $comanda)
            ->with('status', 'Comanda servida y stock descontado.');
    }

    /**
     * Cancela una comanda siempre que no tenga lineas servidas.
     */
    public function cancelar(Request $request, Comanda $comanda): RedirectResponse
    {
        if ($comanda->lineas()->where('estado', EstadoLineaComanda::Servida->value)->exists()) {
            return redirect()->route('admin.ventas.comandas.show', $comanda)
                ->withErrors(['estado' => 'No puedes cancelar una comanda que ya tiene lineas servidas.']);
        }

        $comanda->lineas()->update(['estado' => EstadoLineaComanda::Cancelada->value]);
        $comanda->update([
            'estado' => EstadoComanda::Cancelada,
            'cerrada_at' => now(),
            'actualizado_por' => $request->user()?->id,
        ]);

        return redirect()->route('admin.ventas.comandas.show', $comanda)
            ->with('status', 'Comanda cancelada correctamente.');
    }

    /**
     * Registra un pago de la comanda.
     */
    public function cobrar(RegistrarPagoComandaRequest $request, Comanda $comanda, RegistrarPagoComandaAction $registrarPago): RedirectResponse
    {
        $registrarPago->execute($comanda, $request->datosPago(), (string) $request->user()?->id);

        return redirect()->route('admin.ventas.comandas.show', $comanda)
            ->with('status', 'Pago registrado correctamente.');
    }
}
