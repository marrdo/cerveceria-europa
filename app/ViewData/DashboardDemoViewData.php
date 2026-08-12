<?php

namespace App\ViewData;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use App\Modulos\Compras\Enums\EstadoPedidoCompra;
use App\Modulos\Compras\Models\PedidoCompra;
use App\Modulos\Espacios\Models\Mesa;
use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\PlanificacionTurnos\Models\CuadranteLaboral;
use App\Modulos\Ventas\Enums\EstadoComanda;
use App\Modulos\Ventas\Enums\EstadoTurnoCaja;
use App\Modulos\Ventas\Models\Comanda;
use App\Modulos\Ventas\Models\TurnoCaja;
use App\Modulos\WebPublica\Models\ContenidoWeb;

/**
 * Compone el estado real y los enlaces del recorrido comercial de la demo.
 */
final class DashboardDemoViewData
{
    /**
     * @return array{
     *     resumen: array<string, int|string>,
     *     pasos: array<int, array{titulo: string, descripcion: string, estado: string, detalle: string, url: string|null, accion: string}>
     * }
     */
    public function construir(Usuario $usuario): array
    {
        $cuadrante = CuadranteLaboral::query()
            ->withCount('jornadas')
            ->orderByDesc('jornadas_count')
            ->first();
        $pedidoPendiente = PedidoCompra::query()
            ->where('numero', 'like', 'DEMO-PC-%')
            ->whereIn('estado', [EstadoPedidoCompra::Pedido, EstadoPedidoCompra::RecibidoParcial])
            ->orderBy('numero')
            ->first();
        $caja = TurnoCaja::query()
            ->where('numero', 'like', 'DEMO-CAJA-%')
            ->where('estado', EstadoTurnoCaja::Abierta)
            ->first();
        $comandas = Comanda::query()->where('numero', 'like', 'DEMO-COM-%')->get();
        $comandasActivas = $comandas->whereIn('estado', [
            EstadoComanda::Abierta,
            EstadoComanda::EnPreparacion,
            EstadoComanda::Servida,
        ])->count();
        $comandasPagadas = $comandas->where('estado', EstadoComanda::Pagada)->count();
        $mesas = Mesa::query()
            ->whereHas('zona', fn ($query) => $query->where('codigo', 'like', 'DEMO-%'))
            ->count();
        $equipo = Usuario::query()
            ->where('rol', '!=', RolUsuario::Superadmin->value)
            ->where('es_protegido', false)
            ->count();
        $movimientosConectados = MovimientoInventario::query()
            ->where('referencia', 'like', 'DEMO-COM-%')
            ->orWhere('referencia', 'like', 'DEMO-RC-%')
            ->count();
        $contenidosPublicados = ContenidoWeb::query()->publicado()->count();

        return [
            'resumen' => [
                'comandas_activas' => $comandasActivas,
                'ventas_cobradas' => $comandasPagadas,
                'mesas' => $mesas,
                'equipo' => $equipo,
            ],
            'pasos' => [
                $this->paso(
                    'Carta pública',
                    'Empieza como cliente y revisa la carta que alimenta las comandas del panel.',
                    $contenidosPublicados > 0,
                    $contenidosPublicados.' referencias publicadas',
                    route('web.carta'),
                    'Abrir carta',
                ),
                $this->paso(
                    'Sala y mesas',
                    'Comprueba el local ficticio, sus zonas y las mesas usadas por el servicio.',
                    $mesas > 0,
                    $mesas.' mesas en 2 zonas',
                    $usuario->puedeAccederModulo('espacios') ? route('admin.espacios.mesas.index') : null,
                    'Ver mesas',
                ),
                $this->paso(
                    'Equipo y cuadrante',
                    'Revisa quién trabaja, sus incidencias y la semana preparada para publicación.',
                    ($cuadrante?->jornadas_count ?? 0) > 0,
                    $equipo.' personas · '.($cuadrante?->jornadas_count ?? 0).' tramos',
                    $usuario->puedeGestionarPlanificacionTurnos() && $cuadrante
                        ? route('admin.planificacion-turnos.cuadrantes.show', $cuadrante)
                        : null,
                    'Abrir cuadrante',
                ),
                $this->paso(
                    'Servicio en curso',
                    'Hay comandas abiertas, en preparación, servidas y pagadas para continuar cada caso.',
                    $comandas->count() >= 4,
                    $comandasActivas.' activas · '.$comandasPagadas.' pagada',
                    $usuario->puedeAccederModulo('ventas') ? route('admin.ventas.comandas.index') : null,
                    'Ver comandas',
                ),
                $this->paso(
                    'Caja y cobro',
                    'La venta pagada está enlazada a una caja abierta y la comanda servida puede cobrarse.',
                    $caja !== null,
                    $caja ? 'Caja abierta desde '.$caja->abierta_at?->format('H:i') : 'Caja sin abrir',
                    $usuario->puedeGestionarCaja() && $caja ? route('admin.ventas.caja.show', $caja) : null,
                    'Revisar caja',
                ),
                $this->paso(
                    'Inventario trazable',
                    'Las líneas servidas descuentan stock y la recepción parcial genera una entrada real.',
                    $movimientosConectados >= 4,
                    $movimientosConectados.' movimientos conectados',
                    $usuario->puedeAccederModulo('inventario') ? route('admin.inventario.index') : null,
                    'Abrir inventario',
                ),
                $this->paso(
                    'Compra y reposición',
                    'Continúa un pedido pendiente o revisa otro recibido parcialmente con mercancía por llegar.',
                    $pedidoPendiente !== null,
                    $pedidoPendiente?->estado->etiqueta() ?? 'Sin pedido preparado',
                    $usuario->puedeAccederModulo('compras') && $pedidoPendiente
                        ? route('admin.compras.pedidos.show', $pedidoPendiente)
                        : null,
                    'Abrir pedido',
                ),
                $this->paso(
                    'Informes del negocio',
                    'Cierra el recorrido comprobando ventas, tickets y márgenes construidos con esos datos.',
                    $comandasPagadas > 0,
                    $comandasPagadas.' venta disponible en informes',
                    $usuario->puedeConsultarInformesVentas() ? route('admin.ventas.informes.index') : null,
                    'Ver informes',
                ),
            ],
        ];
    }

    /**
     * @return array{titulo: string, descripcion: string, estado: string, detalle: string, url: string|null, accion: string}
     */
    private function paso(
        string $titulo,
        string $descripcion,
        bool $preparado,
        string $detalle,
        ?string $url,
        string $accion,
    ): array {
        return [
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'estado' => $preparado ? 'preparado' : 'pendiente',
            'detalle' => $detalle,
            'url' => $url,
            'accion' => $accion,
        ];
    }
}
