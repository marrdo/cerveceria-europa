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

            <section class="admin-card overflow-hidden">
                <div class="border-b border-border p-4">
                    <h2 class="text-base font-semibold text-foreground">Incidencias</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Diferencias detectadas al recibir mercancia: faltas, roturas, productos equivocados o mal estado.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium text-foreground">Tipo</th>
                                <th class="px-4 py-3 text-left font-medium text-foreground">Linea</th>
                                <th class="px-4 py-3 text-left font-medium text-foreground">Cantidad</th>
                                <th class="px-4 py-3 text-left font-medium text-foreground">Descripcion</th>
                                <th class="px-4 py-3 text-left font-medium text-foreground">Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pedido->incidencias as $incidencia)
                                <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                    <td class="px-4 py-3 font-medium text-foreground">{{ $incidencia->tipo->etiqueta() }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ $incidencia->lineaPedido?->descripcion ?? 'Pedido general' }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        @if ($incidencia->cantidad_afectada)
                                            {{ $incidencia->lineaPedido?->producto?->formatearCantidad($incidencia->cantidad_afectada) ?? $incidencia->cantidad_afectada }} {{ $incidencia->lineaPedido?->producto?->codigoUnidad() }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ $incidencia->descripcion }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ $incidencia->registrador?->nombre ?? 'Sin usuario' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-muted-foreground">Todavia no hay incidencias registradas.</td>
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

            <form method="POST" action="{{ route('admin.compras.pedidos.incidencias.store', $pedido) }}" class="admin-card space-y-4 p-4 lg:p-6">
                @csrf
                <h2 class="text-base font-semibold text-foreground">Registrar incidencia</h2>
                <p class="text-xs text-muted-foreground">Usalo cuando el pedido llegue con menos mercancia, producto equivocado, roturas o cualquier diferencia con el albaran.</p>
                <div>
                    <x-input-label for="tipo" value="Tipo" />
                    <select id="tipo" name="tipo" class="admin-input mt-1 block h-10 w-full" required>
                        <option value="">Selecciona tipo</option>
                        @foreach ($tiposIncidencia as $tipoIncidencia)
                            <option value="{{ $tipoIncidencia->value }}" @selected(old('tipo') === $tipoIncidencia->value)>{{ $tipoIncidencia->etiqueta() }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-muted-foreground">Clasifica el problema para poder revisarlo despues con el proveedor.</p>
                    <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="linea_pedido_compra_id" value="Linea afectada" />
                    <select id="linea_pedido_compra_id" name="linea_pedido_compra_id" class="admin-input mt-1 block h-10 w-full">
                        <option value="">Pedido general</option>
                        @foreach ($pedido->lineas as $linea)
                            <option value="{{ $linea->id }}" @selected(old('linea_pedido_compra_id') === $linea->id)>{{ $linea->descripcion }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-muted-foreground">Opcional. Selecciona el producto concreto si la incidencia afecta a una linea.</p>
                    <x-input-error :messages="$errors->get('linea_pedido_compra_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="recepcion_compra_id" value="Recepcion relacionada" />
                    <select id="recepcion_compra_id" name="recepcion_compra_id" class="admin-input mt-1 block h-10 w-full">
                        <option value="">Sin recepcion concreta</option>
                        @foreach ($pedido->recepciones as $recepcion)
                            <option value="{{ $recepcion->id }}" @selected(old('recepcion_compra_id') === $recepcion->id)>{{ $recepcion->numero }} - {{ $recepcion->fecha_recepcion->format('d/m/Y') }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-muted-foreground">Opcional. Vincula la incidencia a una recepcion ya registrada.</p>
                    <x-input-error :messages="$errors->get('recepcion_compra_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="cantidad_afectada" value="Cantidad afectada" />
                    <x-text-input id="cantidad_afectada" name="cantidad_afectada" type="number" step="0.001" min="0.001" class="mt-1 block h-10 w-full" :value="old('cantidad_afectada')" />
                    <p class="mt-1 text-xs text-muted-foreground">Opcional. Indica cuantas unidades, cajas, litros o kg estan afectados.</p>
                    <x-input-error :messages="$errors->get('cantidad_afectada')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="descripcion_incidencia" value="Descripcion" />
                    <textarea id="descripcion_incidencia" name="descripcion" rows="4" class="admin-input mt-1 block w-full" required>{{ old('descripcion') }}</textarea>
                    <p class="mt-1 text-xs text-muted-foreground">Explica que ha pasado y que se ha acordado con el proveedor si ya se sabe.</p>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>
                <x-primary-button class="w-full justify-center">Guardar incidencia</x-primary-button>
            </form>

            @if ($pedido->puedeCerrarConPendiente())
                <form method="POST" action="{{ route('admin.compras.pedidos.cerrar-pendiente', $pedido) }}" class="admin-card space-y-4 border-warning/40 p-4 lg:p-6">
                    @csrf
                    @method('PATCH')
                    <h2 class="text-base font-semibold text-foreground">Cerrar con pendiente</h2>
                    <p class="text-xs text-muted-foreground">Usalo si se decide no esperar la mercancia pendiente. El stock no cambia; solo se cierra el pedido con motivo.</p>
                    <div>
                        <x-input-label for="motivo_cierre" value="Motivo de cierre" />
                        <textarea id="motivo_cierre" name="motivo_cierre" rows="4" class="admin-input mt-1 block w-full" required>{{ old('motivo_cierre') }}</textarea>
                        <x-input-error :messages="$errors->get('motivo_cierre')" class="mt-2" />
                    </div>
                    <x-primary-button class="w-full justify-center">Cerrar pedido</x-primary-button>
                </form>
            @endif
        </aside>
    </div>
</x-app-layout>
