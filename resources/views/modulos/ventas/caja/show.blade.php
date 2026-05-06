<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="'Caja '.$caja->numero" :description="$caja->recinto?->nombre_comercial ?? 'Caja general'">
            <x-slot name="actions">
                <a href="{{ route('admin.ventas.caja.index') }}" class="admin-btn-outline">Volver</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.ventas.partials.nav')

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-destructive/25 bg-destructive/10 p-4 text-sm text-destructive">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-4 lg:grid-cols-[1fr_360px]">
        <div class="space-y-4">
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="admin-card p-4">
                    <p class="text-xs font-semibold uppercase text-muted-foreground">Ventas</p>
                    <p class="mt-2 text-2xl font-bold tabular-nums text-foreground">{{ number_format((float) $resumen['total'], 2, ',', '.') }} EUR</p>
                </div>
                <div class="admin-card p-4">
                    <p class="text-xs font-semibold uppercase text-muted-foreground">Efectivo esperado</p>
                    <p class="mt-2 text-2xl font-bold tabular-nums text-foreground">{{ number_format((float) $resumen['efectivo_esperado'], 2, ',', '.') }} EUR</p>
                </div>
                <div class="admin-card p-4">
                    <p class="text-xs font-semibold uppercase text-muted-foreground">Pagos</p>
                    <p class="mt-2 text-2xl font-bold tabular-nums text-foreground">{{ $resumen['pagos_count'] }}</p>
                </div>
                <div class="admin-card p-4">
                    <p class="text-xs font-semibold uppercase text-muted-foreground">Estado</p>
                    <div class="mt-3"><x-admin.status-badge :variant="$caja->estado->variante()">{{ $caja->estado->etiqueta() }}</x-admin.status-badge></div>
                </div>
            </section>

            <section class="admin-card p-4">
                <h2 class="text-base font-semibold text-foreground">Ventas por metodo</h2>
                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                    <div class="rounded-md border border-border p-3">
                        <p class="text-xs text-muted-foreground">Efectivo</p>
                        <p class="mt-1 text-lg font-semibold tabular-nums text-foreground">{{ number_format((float) $resumen['efectivo'], 2, ',', '.') }} EUR</p>
                    </div>
                    <div class="rounded-md border border-border p-3">
                        <p class="text-xs text-muted-foreground">Tarjeta</p>
                        <p class="mt-1 text-lg font-semibold tabular-nums text-foreground">{{ number_format((float) $resumen['tarjeta'], 2, ',', '.') }} EUR</p>
                    </div>
                    <div class="rounded-md border border-border p-3">
                        <p class="text-xs text-muted-foreground">Bizum</p>
                        <p class="mt-1 text-lg font-semibold tabular-nums text-foreground">{{ number_format((float) $resumen['bizum'], 2, ',', '.') }} EUR</p>
                    </div>
                    <div class="rounded-md border border-border p-3">
                        <p class="text-xs text-muted-foreground">Invitacion</p>
                        <p class="mt-1 text-lg font-semibold tabular-nums text-foreground">{{ number_format((float) $resumen['invitacion'], 2, ',', '.') }} EUR</p>
                    </div>
                    <div class="rounded-md border border-border p-3">
                        <p class="text-xs text-muted-foreground">Otro</p>
                        <p class="mt-1 text-lg font-semibold tabular-nums text-foreground">{{ number_format((float) $resumen['otro'], 2, ',', '.') }} EUR</p>
                    </div>
                </div>
            </section>

            <section class="overflow-x-auto rounded-lg border border-border bg-card">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50">
                            <th class="px-4 py-3 text-left font-medium text-foreground">Comanda</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Metodo</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Cobrador</th>
                            <th class="px-4 py-3 text-left font-medium text-foreground">Fecha</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Importe</th>
                            <th class="px-4 py-3 text-right font-medium text-foreground">Cambio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pagos as $pago)
                            <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                <td class="px-4 py-3 font-medium text-foreground">
                                    @if ($pago->comanda)
                                        <a href="{{ route('admin.ventas.comandas.show', $pago->comanda) }}" class="text-primary hover:underline">{{ $pago->comanda->numero }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $pago->metodo->etiqueta() }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $pago->cobrador?->nombre ?? '-' }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $pago->cobrado_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-right text-foreground">{{ number_format((float) $pago->importe, 2, ',', '.') }} EUR</td>
                                <td class="px-4 py-3 text-right text-muted-foreground">{{ number_format((float) $pago->cambio, 2, ',', '.') }} EUR</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-muted-foreground">No hay pagos vinculados a esta caja.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </div>

        <aside class="space-y-4">
            <section class="admin-card p-4">
                <h2 class="text-base font-semibold text-foreground">Resumen de caja</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Saldo inicial</dt>
                        <dd class="text-right text-foreground">{{ number_format((float) $caja->saldo_inicial, 2, ',', '.') }} EUR</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Efectivo cobrado</dt>
                        <dd class="text-right text-foreground">{{ number_format((float) $resumen['efectivo'], 2, ',', '.') }} EUR</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Cambio entregado</dt>
                        <dd class="text-right text-foreground">{{ number_format((float) $resumen['cambio'], 2, ',', '.') }} EUR</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-border pt-3">
                        <dt class="font-semibold text-foreground">Efectivo esperado</dt>
                        <dd class="text-right font-semibold text-foreground">{{ number_format((float) $resumen['efectivo_esperado'], 2, ',', '.') }} EUR</dd>
                    </div>
                    @if ($caja->estado === \App\Modulos\Ventas\Enums\EstadoTurnoCaja::Cerrada)
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Efectivo contado</dt>
                            <dd class="text-right text-foreground">{{ number_format((float) $caja->efectivo_contado, 2, ',', '.') }} EUR</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Descuadre</dt>
                            <dd class="text-right {{ abs((float) $caja->descuadre) > 0.005 ? 'font-semibold text-destructive' : 'text-foreground' }}">{{ number_format((float) $caja->descuadre, 2, ',', '.') }} EUR</dd>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Abierta por</dt>
                        <dd class="text-right text-foreground">{{ $caja->abiertoPor?->nombre ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Apertura</dt>
                        <dd class="text-right text-foreground">{{ $caja->abierta_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if ($caja->cerrada_at)
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Cerrada por</dt>
                            <dd class="text-right text-foreground">{{ $caja->cerradoPor?->nombre ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Cierre</dt>
                            <dd class="text-right text-foreground">{{ $caja->cerrada_at?->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            @if ($caja->estaAbierta())
                <section class="admin-card p-4">
                    <h2 class="text-base font-semibold text-foreground">Cerrar caja</h2>
                    <form method="POST" action="{{ route('admin.ventas.caja.cerrar', $caja) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')
                        <div>
                            <x-input-label for="efectivo_contado" value="Efectivo contado" />
                            <input id="efectivo_contado" name="efectivo_contado" type="number" step="0.01" min="0" class="admin-input mt-1 block h-10 w-full" value="{{ old('efectivo_contado', number_format((float) $resumen['efectivo_esperado'], 2, '.', '')) }}" required>
                        </div>
                        <div>
                            <x-input-label for="notas_cierre" value="Notas de cierre" />
                            <textarea id="notas_cierre" name="notas_cierre" rows="3" class="admin-input mt-1 block w-full">{{ old('notas_cierre') }}</textarea>
                        </div>
                        <button type="submit" class="admin-btn-primary w-full">Cerrar caja</button>
                    </form>
                </section>
            @endif
        </aside>
    </div>
</x-app-layout>
