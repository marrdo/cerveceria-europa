<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Informes de ventas" description="Analisis comercial del periodo seleccionado." />
    </x-slot>

    @include('modulos.ventas.partials.nav')

    <form method="GET" action="{{ route('admin.ventas.informes.index') }}" class="admin-card mb-4 grid gap-4 p-4 md:grid-cols-[180px_180px_auto] md:items-end">
        <div>
            <x-input-label for="fecha_desde" value="Desde" />
            <x-text-input id="fecha_desde" name="fecha_desde" type="date" class="mt-1 block h-10 w-full" :value="$filtros['fecha_desde']" />
        </div>
        <div>
            <x-input-label for="fecha_hasta" value="Hasta" />
            <x-text-input id="fecha_hasta" name="fecha_hasta" type="date" class="mt-1 block h-10 w-full" :value="$filtros['fecha_hasta']" />
        </div>
        <div class="flex gap-2">
            <button type="submit" class="admin-btn-primary">Filtrar</button>
            <a href="{{ route('admin.ventas.informes.index') }}" class="admin-btn-outline">Mes actual</a>
        </div>
    </form>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="admin-card p-4">
            <p class="text-xs font-semibold uppercase text-muted-foreground">Ventas cobradas</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-foreground">{{ number_format((float) $kpis['total_ventas'], 2, ',', '.') }} EUR</p>
        </div>
        <div class="admin-card p-4">
            <p class="text-xs font-semibold uppercase text-muted-foreground">Comandas pagadas</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-foreground">{{ $kpis['comandas_pagadas'] }}</p>
        </div>
        <div class="admin-card p-4">
            <p class="text-xs font-semibold uppercase text-muted-foreground">Ticket medio</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-foreground">{{ number_format((float) $kpis['ticket_medio'], 2, ',', '.') }} EUR</p>
        </div>
        <div class="admin-card p-4">
            <p class="text-xs font-semibold uppercase text-muted-foreground">Canceladas</p>
            <p class="mt-2 text-2xl font-bold tabular-nums {{ (int) $kpis['comandas_canceladas'] > 0 ? 'text-destructive' : 'text-foreground' }}">{{ $kpis['comandas_canceladas'] }}</p>
        </div>
    </section>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        <section class="admin-card overflow-hidden">
            <div class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Ventas por dia</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium text-foreground">Fecha</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Pagos</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ventasPorDia as $dia)
                            <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                <td class="px-4 py-3 text-foreground">{{ \Illuminate\Support\Carbon::parse($dia->fecha)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right text-muted-foreground">{{ $dia->pagos }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-foreground">{{ number_format((float) $dia->total, 2, ',', '.') }} EUR</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-muted-foreground">Sin ventas cobradas en el periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Ventas por metodo</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium text-foreground">Metodo</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Pagos</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ventasPorMetodo as $metodo)
                            @php
                                $metodoEnum = $metodo->metodo instanceof \App\Modulos\Ventas\Enums\MetodoPagoComanda
                                    ? $metodo->metodo
                                    : \App\Modulos\Ventas\Enums\MetodoPagoComanda::tryFrom((string) $metodo->metodo);
                            @endphp
                            <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                <td class="px-4 py-3 text-foreground">{{ $metodoEnum?->etiqueta() ?? $metodo->metodo }}</td>
                                <td class="px-4 py-3 text-right text-muted-foreground">{{ $metodo->pagos }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-foreground">{{ number_format((float) $metodo->total, 2, ',', '.') }} EUR</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-muted-foreground">Sin pagos en el periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Productos mas vendidos</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium text-foreground">Producto</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Cantidad</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productosMasVendidos as $producto)
                            <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                <td class="px-4 py-3 text-foreground">{{ $producto->nombre }}</td>
                                <td class="px-4 py-3 text-right text-muted-foreground">{{ number_format((float) $producto->cantidad, 3, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-foreground">{{ number_format((float) $producto->total, 2, ',', '.') }} EUR</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-muted-foreground">Sin productos vendidos en el periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Ventas por categoria</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium text-foreground">Categoria</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Cantidad</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ventasPorCategoria as $categoria)
                            <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                <td class="px-4 py-3 text-foreground">{{ $categoria->categoria }}</td>
                                <td class="px-4 py-3 text-right text-muted-foreground">{{ number_format((float) $categoria->cantidad, 3, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-foreground">{{ number_format((float) $categoria->total, 2, ',', '.') }} EUR</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-muted-foreground">Sin categorias vendidas en el periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Ventas por camarero</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium text-foreground">Usuario</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Pagos</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ventasPorCamarero as $fila)
                            <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                <td class="px-4 py-3 text-foreground">{{ $fila->usuario }}</td>
                                <td class="px-4 py-3 text-right text-muted-foreground">{{ $fila->pagos }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-foreground">{{ number_format((float) $fila->total, 2, ',', '.') }} EUR</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-muted-foreground">Sin pagos por usuario en el periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Comandas canceladas</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium text-foreground">Numero</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Mesa</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Usuario</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($comandasCanceladas as $comanda)
                            <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                <td class="px-4 py-3 font-medium text-foreground">{{ $comanda->numero }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $comanda->mesaEspacio?->nombre ?? $comanda->mesa ?? '-' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $comanda->creador?->nombre ?? '-' }}</td>
                                <td class="px-4 py-3 text-right text-foreground">{{ number_format((float) $comanda->total, 2, ',', '.') }} EUR</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-muted-foreground">Sin comandas canceladas en el periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
