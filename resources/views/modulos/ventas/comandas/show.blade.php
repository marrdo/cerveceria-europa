<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="'Comanda '.$comanda->numero" :description="$comanda->mesa ? 'Mesa '.$comanda->mesa : 'Sin mesa asignada'">
            <x-slot name="actions">
                @if ($comanda->puedeEditar())
                    <form method="POST" action="{{ route('admin.ventas.comandas.servir', $comanda) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="admin-btn-primary">Servir todo</button>
                    </form>
                @endif
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

    <div class="grid gap-4 lg:grid-cols-[1fr_320px]">
        <section class="admin-card overflow-hidden">
            <div class="border-b border-border p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-foreground">Lineas</h2>
                        <p class="mt-1 text-sm text-muted-foreground">El stock se descuenta al servir cada linea.</p>
                    </div>
                    <x-admin.status-badge :variant="$comanda->estado->variante()">{{ $comanda->estado->etiqueta() }}</x-admin.status-badge>
                </div>
            </div>

            <div class="divide-y divide-border">
                @foreach ($comanda->lineas as $linea)
                    <div class="grid gap-3 p-4 md:grid-cols-[1fr_130px_120px] md:items-center">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-semibold text-foreground">{{ $linea->nombre }}</h3>
                                <x-admin.status-badge :variant="$linea->estado->variante()">{{ $linea->estado->etiqueta() }}</x-admin.status-badge>
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ number_format((float) $linea->cantidad, 3, ',', '.') }} x {{ number_format((float) $linea->precio_unitario, 2, ',', '.') }} EUR
                                @if ($linea->producto)
                                    · Stock: {{ $linea->producto->nombre }}
                                @else
                                    · Sin producto de inventario
                                @endif
                            </p>
                        </div>

                        <div class="text-right text-sm font-semibold text-foreground">{{ number_format((float) $linea->total, 2, ',', '.') }} EUR</div>

                        <div class="text-right">
                            @if (! $linea->estaServida() && $linea->estado !== \App\Modulos\Ventas\Enums\EstadoLineaComanda::Cancelada)
                                <form method="POST" action="{{ route('admin.ventas.comandas.lineas.servir', [$comanda, $linea]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="admin-btn-outline">Servir</button>
                                </form>
                            @elseif ($linea->movimientoInventario)
                                <span class="text-xs text-muted-foreground">Stock descontado</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <aside class="space-y-4">
            <section class="admin-card p-4">
                <h2 class="text-base font-semibold text-foreground">Resumen</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Ubicacion</dt>
                        <dd class="text-right text-foreground">{{ $comanda->ubicacionInventario?->nombre ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Camarero</dt>
                        <dd class="text-right text-foreground">{{ $comanda->creador?->nombre ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Subtotal</dt>
                        <dd class="text-right text-foreground">{{ number_format((float) $comanda->subtotal, 2, ',', '.') }} EUR</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-border pt-3">
                        <dt class="font-semibold text-foreground">Total</dt>
                        <dd class="text-right font-semibold text-foreground">{{ number_format((float) $comanda->total, 2, ',', '.') }} EUR</dd>
                    </div>
                </dl>
            </section>

            @if ($comanda->puedeEditar())
                <form method="POST" action="{{ route('admin.ventas.comandas.cancelar', $comanda) }}" class="admin-card p-4">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="admin-btn-outline w-full">Cancelar comanda</button>
                </form>
            @endif
        </aside>
    </div>
</x-app-layout>
