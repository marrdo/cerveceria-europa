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
                @elseif ($comanda->puedeCobrar())
                    <a href="#cobro" class="admin-btn-primary">Cobrar</a>
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

    <div class="grid gap-4 lg:grid-cols-[1fr_360px]">
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
                                    - Stock: {{ $linea->producto->nombre }}
                                @else
                                    - Sin producto de inventario
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
                        <dt class="text-muted-foreground">Stock descontado de</dt>
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
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Pagado</dt>
                        <dd class="text-right text-foreground">{{ number_format($comanda->totalPagado(), 2, ',', '.') }} EUR</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Pendiente</dt>
                        <dd class="text-right text-foreground">{{ number_format($comanda->pendientePago(), 2, ',', '.') }} EUR</dd>
                    </div>
                </dl>
            </section>

            @if ($comanda->puedeCobrar())
                <section id="cobro" class="admin-card p-4">
                    <h2 class="text-base font-semibold text-foreground">Cobrar</h2>
                    <form
                        method="POST"
                        action="{{ route('admin.ventas.comandas.pagos.store', $comanda) }}"
                        class="mt-4 space-y-3"
                        x-data="{
                            importe: '{{ old('importe', number_format($comanda->pendientePago(), 2, '.', '')) }}',
                            recibido: '{{ old('recibido') }}',
                            cambio() {
                                const importe = Number(this.importe || 0);
                                const recibido = Number(this.recibido || 0);

                                return Math.max(0, recibido - importe);
                            },
                            dinero(valor) {
                                return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(valor);
                            }
                        }"
                    >
                        @csrf
                        <div>
                            <x-input-label for="metodo" value="Metodo" />
                            <select id="metodo" name="metodo" class="admin-input mt-1 block h-10 w-full" required>
                                @foreach ($metodosPago as $metodo)
                                    <option value="{{ $metodo->value }}" @selected(old('metodo') === $metodo->value)>{{ $metodo->etiqueta() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="importe" value="Importe a cobrar" />
                            <input id="importe" name="importe" type="number" step="0.01" min="0.01" max="{{ $comanda->pendientePago() }}" class="admin-input mt-1 block h-10 w-full" x-model="importe">
                        </div>
                        <div>
                            <x-input-label for="recibido" value="Recibido en efectivo" />
                            <input id="recibido" name="recibido" type="number" step="0.01" min="0.01" class="admin-input mt-1 block h-10 w-full" x-model="recibido">
                            <div x-show="cambio() > 0" x-cloak class="mt-3 rounded-md border border-primary/30 bg-primary/10 p-4 text-center">
                                <p class="text-xs font-semibold uppercase text-muted-foreground">Cambio a devolver</p>
                                <p class="mt-1 text-3xl font-bold tabular-nums text-foreground" x-text="dinero(cambio())"></p>
                            </div>
                        </div>
                        <div>
                            <x-input-label for="referencia" value="Referencia de pago" />
                            <x-text-input id="referencia" name="referencia" class="mt-1 block h-10 w-full" :value="old('referencia')" maxlength="191" />
                            <p class="mt-1 text-xs text-muted-foreground">Opcional. Codigo del TPV, Bizum, ticket externo o identificador del pago.</p>
                        </div>
                        <button type="submit" class="admin-btn-primary w-full">Registrar pago</button>
                    </form>
                </section>
            @endif

            @if ($comanda->pagos->isNotEmpty())
                <section class="admin-card p-4">
                    <h2 class="text-base font-semibold text-foreground">Pagos</h2>
                    <div class="mt-4 space-y-3">
                        @foreach ($comanda->pagos as $pago)
                            <div class="rounded-md border border-border p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <span class="text-sm font-semibold text-foreground">{{ $pago->metodo->etiqueta() }}</span>
                                        <p class="mt-1 text-xs text-muted-foreground">{{ $pago->cobrado_at?->format('d/m/Y H:i') }} - {{ $pago->cobrador?->nombre ?? 'Sin usuario' }}</p>
                                    </div>
                                    <span class="text-lg font-bold tabular-nums text-foreground">{{ number_format((float) $pago->importe, 2, ',', '.') }} EUR</span>
                                </div>
                                @if ((float) $pago->cambio > 0)
                                    <div class="mt-3 rounded-md border border-primary/30 bg-primary/10 p-3 text-center">
                                        <p class="text-xs font-semibold uppercase text-muted-foreground">Cambio entregado</p>
                                        <p class="mt-1 text-3xl font-bold tabular-nums text-foreground">{{ number_format((float) $pago->cambio, 2, ',', '.') }} EUR</p>
                                    </div>
                                @endif
                                @if ($pago->referencia)
                                    <p class="mt-2 text-xs text-muted-foreground">Referencia: {{ $pago->referencia }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

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
