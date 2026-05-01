<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Pedido {{ $pedido->numero }}" :description="$pedido->proveedor?->nombre">
            <x-slot name="actions">
                @if ($pedido->puedeRecibir())
                    <a href="{{ route('admin.compras.pedidos.recepciones.create', $pedido) }}" class="admin-btn-primary">Registrar recepcion</a>
                @endif
                @if ($pedido->puedeEditar())
                    <a href="{{ route('admin.compras.pedidos.edit', $pedido) }}" class="admin-btn-outline">Editar</a>
                @endif
                <a href="{{ route('admin.compras.pedidos.index') }}" class="admin-btn-outline">Volver</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.compras.partials.nav')

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section class="admin-card overflow-hidden">
                <div class="border-b border-border p-4">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-base font-semibold text-foreground">Lineas</h2>
                        <x-admin.status-badge :variant="$pedido->estado->variante()">{{ $pedido->estado->etiqueta() }}</x-admin.status-badge>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium text-foreground">Producto</th>
                                <th class="px-4 py-3 text-left font-medium text-foreground">Cantidad</th>
                                <th class="px-4 py-3 text-left font-medium text-foreground">Recibido</th>
                                <th class="px-4 py-3 text-right font-medium text-foreground">Coste sin IVA</th>
                                <th class="px-4 py-3 text-right font-medium text-foreground">IVA</th>
                                <th class="px-4 py-3 text-right font-medium text-foreground">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pedido->lineas as $linea)
                                <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-foreground">{{ $linea->descripcion }}</div>
                                        <div class="text-xs text-muted-foreground">{{ $linea->producto?->sku ?? 'Sin SKU' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ $linea->producto?->formatearCantidad($linea->cantidad) ?? $linea->cantidad }} {{ $linea->producto?->codigoUnidad() }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ $linea->producto?->formatearCantidad($linea->cantidadRecibida()) ?? $linea->cantidadRecibida() }} {{ $linea->producto?->codigoUnidad() }}</td>
                                    <td class="px-4 py-3 text-right text-foreground">{{ number_format((float) $linea->coste_unitario, 2, ',', '.') }} EUR</td>
                                    <td class="px-4 py-3 text-right text-muted-foreground">{{ number_format((float) $linea->iva_porcentaje, 2, ',', '.') }}%</td>
                                    <td class="px-4 py-3 text-right font-medium text-foreground">{{ number_format((float) $linea->total, 2, ',', '.') }} EUR</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="admin-card overflow-hidden">
                <div class="border-b border-border p-4">
                    <h2 class="text-base font-semibold text-foreground">Recepciones</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium text-foreground">Numero</th>
                                <th class="px-4 py-3 text-left font-medium text-foreground">Fecha</th>
                                <th class="px-4 py-3 text-left font-medium text-foreground">Lineas</th>
                                <th class="px-4 py-3 text-left font-medium text-foreground">Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pedido->recepciones as $recepcion)
                                <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                    <td class="px-4 py-3 font-medium text-foreground">{{ $recepcion->numero }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ $recepcion->fecha_recepcion->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        @foreach ($recepcion->lineas as $lineaRecepcion)
                                            <div>{{ $lineaRecepcion->producto?->nombre }} - {{ $lineaRecepcion->producto?->formatearCantidad($lineaRecepcion->cantidad) ?? $lineaRecepcion->cantidad }} {{ $lineaRecepcion->producto?->codigoUnidad() }} en {{ $lineaRecepcion->ubicacion?->nombre }}</div>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ $recepcion->receptor?->nombre ?? 'Sin usuario' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-muted-foreground">Todavia no hay recepciones registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="admin-card p-4 lg:p-6">
                <h2 class="mb-4 text-base font-semibold text-foreground">Eventos</h2>
                <div class="divide-y divide-border">
                    @foreach ($pedido->eventos as $evento)
                        <div class="py-3 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium text-foreground">{{ $evento->descripcion }}</span>
                                <span class="text-xs text-muted-foreground">{{ $evento->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="mt-1 text-muted-foreground">{{ $evento->usuario?->nombre ?? 'Sin usuario' }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="admin-card p-4 lg:p-6">
                <h2 class="mb-4 text-base font-semibold text-foreground">Resumen</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><span class="text-muted-foreground">Subtotal</span><span class="text-foreground">{{ number_format((float) $pedido->subtotal, 2, ',', '.') }} EUR</span></div>
                    <div class="flex justify-between gap-4"><span class="text-muted-foreground">IVA</span><span class="text-foreground">{{ number_format((float) $pedido->impuestos, 2, ',', '.') }} EUR</span></div>
                    <div class="flex justify-between gap-4 border-t border-border pt-3 font-semibold"><span class="text-foreground">Total</span><span class="text-foreground">{{ number_format((float) $pedido->total, 2, ',', '.') }} EUR</span></div>
                    <div class="flex justify-between gap-4"><span class="text-muted-foreground">Creado por</span><span class="text-foreground">{{ $pedido->creador?->nombre ?? 'Sin usuario' }}</span></div>
                </div>
            </section>

            <form method="POST" action="{{ route('admin.compras.pedidos.estado', $pedido) }}" class="admin-card space-y-4 p-4 lg:p-6">
                @csrf
                @method('PATCH')
                <h2 class="text-base font-semibold text-foreground">Cambiar estado</h2>
                <p class="text-xs text-muted-foreground">Usa este bloque para marcar el pedido como enviado al proveedor, cerrado o cancelado. La mercancia recibida se registra siempre desde el boton Registrar recepcion.</p>
                <div>
                    <x-input-label for="estado" value="Estado" />
                    <select id="estado" name="estado" class="admin-input mt-1 block h-10 w-full">
                        @foreach ($estadosCambioManual as $estado)
                            <option value="{{ $estado->value }}" @selected($pedido->estado === $estado)>{{ $estado->etiqueta() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('estado')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="descripcion" value="Nota del cambio" />
                    <textarea id="descripcion" name="descripcion" rows="3" class="admin-input mt-1 block w-full"></textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>
                <x-primary-button class="w-full justify-center">Actualizar estado</x-primary-button>
            </form>
        </aside>
    </div>
</x-app-layout>
