<?php

namespace App\Modulos\Ventas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modulos\Espacios\Models\Recinto;
use App\Modulos\Ventas\Actions\AbrirTurnoCajaAction;
use App\Modulos\Ventas\Actions\CerrarTurnoCajaAction;
use App\Modulos\Ventas\Enums\EstadoTurnoCaja;
use App\Modulos\Ventas\Enums\MetodoPagoComanda;
use App\Modulos\Ventas\Http\Requests\AbrirTurnoCajaRequest;
use App\Modulos\Ventas\Http\Requests\CerrarTurnoCajaRequest;
use App\Modulos\Ventas\Models\PagoComanda;
use App\Modulos\Ventas\Models\TurnoCaja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TurnoCajaController extends Controller
{
    /**
     * Lista turnos de caja y muestra el formulario de apertura.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()?->puedeGestionarCaja(), 403);

        $turnos = TurnoCaja::query()
            ->with(['recinto', 'abiertoPor', 'cerradoPor'])
            ->latest('abierta_at')
            ->paginate(15);

        return view('modulos.ventas.caja.index', [
            'turnos' => $turnos,
            'turnoAbierto' => TurnoCaja::query()
                ->with('recinto')
                ->where('estado', EstadoTurnoCaja::Abierta)
                ->latest('abierta_at')
                ->first(),
            'recintos' => Recinto::query()->where('activo', true)->orderBy('nombre_comercial')->get(),
        ]);
    }

    /**
     * Abre un nuevo turno de caja.
     */
    public function store(AbrirTurnoCajaRequest $request, AbrirTurnoCajaAction $abrirTurno): RedirectResponse
    {
        $turno = $abrirTurno->execute($request->datos(), (string) $request->user()?->id);

        return redirect()->route('admin.ventas.caja.show', $turno)
            ->with('status', 'Caja abierta correctamente.');
    }

    /**
     * Muestra el detalle y resumen del turno.
     */
    public function show(Request $request, TurnoCaja $caja): View
    {
        abort_unless($request->user()?->puedeGestionarCaja(), 403);

        $caja->load(['recinto', 'abiertoPor', 'cerradoPor']);
        $pagos = PagoComanda::query()
            ->with(['comanda', 'cobrador'])
            ->where('caja_turno_id', $caja->id)
            ->latest('cobrado_at')
            ->get();

        return view('modulos.ventas.caja.show', [
            'caja' => $caja,
            'pagos' => $pagos,
            'resumen' => $this->resumenActual($caja, $pagos),
        ]);
    }

    /**
     * Cierra el turno de caja y deja importes congelados.
     */
    public function cerrar(CerrarTurnoCajaRequest $request, TurnoCaja $caja, CerrarTurnoCajaAction $cerrarTurno): RedirectResponse
    {
        $cerrarTurno->execute($caja, $request->datos(), (string) $request->user()?->id);

        return redirect()->route('admin.ventas.caja.show', $caja)
            ->with('status', 'Caja cerrada correctamente.');
    }

    /**
     * Calcula el resumen vivo de caja mientras esta abierta.
     *
     * @param \Illuminate\Support\Collection<int, PagoComanda> $pagos
     * @return array<string, float|int>
     */
    private function resumenActual(TurnoCaja $caja, $pagos): array
    {
        if ($caja->estado === EstadoTurnoCaja::Cerrada) {
            return [
                'total' => (float) $caja->total_ventas,
                'efectivo' => (float) $caja->total_efectivo,
                'tarjeta' => (float) $caja->total_tarjeta,
                'bizum' => (float) $caja->total_bizum,
                'invitacion' => (float) $caja->total_invitacion,
                'otro' => (float) $caja->total_otro,
                'cambio' => (float) $caja->total_cambio,
                'efectivo_esperado' => (float) $caja->efectivo_esperado,
                'pagos_count' => (int) $caja->pagos_count,
            ];
        }

        $efectivo = $this->sumarMetodo($pagos, MetodoPagoComanda::Efectivo);
        $cambio = round((float) $pagos->sum('cambio'), 2);

        return [
            'total' => round((float) $pagos->sum('importe'), 2),
            'efectivo' => $efectivo,
            'tarjeta' => $this->sumarMetodo($pagos, MetodoPagoComanda::Tarjeta),
            'bizum' => $this->sumarMetodo($pagos, MetodoPagoComanda::Bizum),
            'invitacion' => $this->sumarMetodo($pagos, MetodoPagoComanda::Invitacion),
            'otro' => $this->sumarMetodo($pagos, MetodoPagoComanda::Otro),
            'cambio' => $cambio,
            'efectivo_esperado' => round((float) $caja->saldo_inicial + $efectivo - $cambio, 2),
            'pagos_count' => $pagos->count(),
        ];
    }

    /**
     * Suma pagos por metodo.
     */
    private function sumarMetodo($pagos, MetodoPagoComanda $metodo): float
    {
        return round((float) $pagos
            ->filter(fn (PagoComanda $pago): bool => $pago->metodo === $metodo)
            ->sum('importe'), 2);
    }
}
