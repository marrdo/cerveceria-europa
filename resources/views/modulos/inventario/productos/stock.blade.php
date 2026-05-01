<x-app-layout>
    <x-slot name="header">
        Stock de {{ $producto->nombre }}
    </x-slot>

    @include('modulos.inventario.partials.nav')

    <x-admin.page-header
        titulo="Stock de {{ $producto->nombre }}"
        subtitulo="Consulta existencias por ubicacion y registra entradas, salidas, ajustes o transferencias."
    >
        <a href="{{ route('admin.inventario.productos.edit', $producto) }}" class="admin-btn-outline">Editar producto</a>
        <a href="{{ route('admin.inventario.productos.index') }}" class="admin-btn-outline">Volver</a>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="admin-card p-4 lg:p-6">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h3 class="text-base font-semibold text-foreground">Stock por ubicacion</h3>
                    <x-admin.status-badge :variant="$producto->estaBajoStock() ? 'warning' : 'success'">
                        {{ $producto->estaBajoStock() ? 'Bajo stock' : 'Stock correcto' }}
                    </x-admin.status-badge>
                </div>

                <div class="divide-y divide-border">
                    @forelse ($producto->stock as $stock)
                        <div class="flex items-center justify-between gap-4 py-3 text-sm">
                            <span class="text-muted-foreground">{{ $stock->ubicacion?->nombre }}</span>
                            <span class="font-semibold text-foreground">{{ $producto->formatearCantidad($stock->cantidad) }} {{ $producto->codigoUnidad() }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-muted-foreground">Todavia no hay stock registrado.</p>
                    @endforelse
                </div>
            </section>

            <section class="admin-card p-4 lg:p-6">
                <h3 class="mb-4 text-base font-semibold text-foreground">Ultimos movimientos</h3>
                <div class="divide-y divide-border">
                    @forelse ($producto->movimientos->take(20) as $movimiento)
                        <div class="py-3 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium text-foreground">{{ $movimiento->tipo->etiqueta() }} - {{ $movimiento->motivo }}</span>
                                <span class="whitespace-nowrap font-semibold text-foreground">{{ $producto->formatearCantidad($movimiento->cantidad) }} {{ $producto->codigoUnidad() }}</span>
                            </div>
                            <p class="mt-1 text-muted-foreground">{{ $movimiento->created_at->format('d/m/Y H:i') }} - Stock: {{ $producto->formatearCantidad($movimiento->stock_antes) }} -> {{ $producto->formatearCantidad($movimiento->stock_despues) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-muted-foreground">Todavia no hay movimientos.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <form method="POST" action="{{ route('admin.inventario.productos.stock.movimientos.store', $producto) }}" class="admin-card space-y-4 p-4 lg:p-6">
            @csrf
            <h3 class="text-base font-semibold text-foreground">Registrar movimiento</h3>

            <div>
                <x-input-label for="tipo" value="Tipo" />
                <select id="tipo" name="tipo" class="admin-input mt-1 block h-10 w-full">
                    @foreach ($tiposMovimiento as $tipo)
                        <option value="{{ $tipo->value }}">{{ $tipo->etiqueta() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="ubicacion_inventario_id" value="Ubicacion" />
                <select id="ubicacion_inventario_id" name="ubicacion_inventario_id" class="admin-input mt-1 block h-10 w-full">
                    @foreach ($ubicaciones as $ubicacion)
                        <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <x-input-label for="ubicacion_origen_id" value="Origen transferencia" />
                    <select id="ubicacion_origen_id" name="ubicacion_origen_id" class="admin-input mt-1 block h-10 w-full">
                        <option value="">-</option>
                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="ubicacion_destino_id" value="Destino transferencia" />
                    <select id="ubicacion_destino_id" name="ubicacion_destino_id" class="admin-input mt-1 block h-10 w-full">
                        <option value="">-</option>
                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <x-input-label for="proveedor_id" value="Proveedor" />
                <select id="proveedor_id" name="proveedor_id" class="admin-input mt-1 block h-10 w-full">
                    <option value="">Sin proveedor</option>
                    @foreach ($proveedores as $proveedor)
                        <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="cantidad" value="Cantidad" />
                <x-text-input id="cantidad" name="cantidad" type="number" step="0.001" min="0.001" class="mt-1 block h-10 w-full" required />
                <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="motivo" value="Motivo" />
                <x-text-input id="motivo" name="motivo" class="mt-1 block h-10 w-full" required />
                <x-input-error :messages="$errors->get('motivo')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="referencia" value="Referencia documento" />
                <x-text-input id="referencia" name="referencia" class="mt-1 block h-10 w-full" />
                <x-input-error :messages="$errors->get('referencia')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center">Registrar</x-primary-button>
        </form>
    </div>
</x-app-layout>
