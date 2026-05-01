<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Stock de {{ $producto->nombre }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('modulos.inventario.partials.nav')

            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Stock por ubicacion</h3>
                        <div class="divide-y">
                            @forelse ($producto->stock as $stock)
                                <div class="flex items-center justify-between py-3 text-sm">
                                    <span class="text-gray-700">{{ $stock->ubicacion?->nombre }}</span>
                                    <span class="font-medium text-gray-900">{{ $producto->formatearCantidad($stock->cantidad) }} {{ $producto->codigoUnidad() }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">Todavia no hay stock registrado.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Ultimos movimientos</h3>
                        <div class="divide-y">
                            @forelse ($producto->movimientos->take(20) as $movimiento)
                                <div class="py-3 text-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-gray-900">{{ $movimiento->tipo->etiqueta() }} - {{ $movimiento->motivo }}</span>
                                        <span>{{ $producto->formatearCantidad($movimiento->cantidad) }} {{ $producto->codigoUnidad() }}</span>
                                    </div>
                                    <p class="mt-1 text-gray-500">{{ $movimiento->created_at->format('d/m/Y H:i') }} · Stock: {{ $producto->formatearCantidad($movimiento->stock_antes) }} -> {{ $producto->formatearCantidad($movimiento->stock_despues) }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">Todavia no hay movimientos.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.inventario.productos.stock.movimientos.store', $producto) }}" class="space-y-4 rounded-lg bg-white p-6 shadow-sm">
                    @csrf
                    <h3 class="text-lg font-medium text-gray-900">Registrar movimiento</h3>

                    <div>
                        <x-input-label for="tipo" value="Tipo" />
                        <select id="tipo" name="tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @foreach ($tiposMovimiento as $tipo)
                                <option value="{{ $tipo->value }}">{{ $tipo->etiqueta() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="ubicacion_inventario_id" value="Ubicacion" />
                        <select id="ubicacion_inventario_id" name="ubicacion_inventario_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @foreach ($ubicaciones as $ubicacion)
                                <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <x-input-label for="ubicacion_origen_id" value="Origen transferencia" />
                            <select id="ubicacion_origen_id" name="ubicacion_origen_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">-</option>
                                @foreach ($ubicaciones as $ubicacion)
                                    <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="ubicacion_destino_id" value="Destino transferencia" />
                            <select id="ubicacion_destino_id" name="ubicacion_destino_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">-</option>
                                @foreach ($ubicaciones as $ubicacion)
                                    <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="proveedor_id" value="Proveedor" />
                        <select id="proveedor_id" name="proveedor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Sin proveedor</option>
                            @foreach ($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="cantidad" value="Cantidad" />
                        <x-text-input id="cantidad" name="cantidad" type="number" step="0.001" min="0.001" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="motivo" value="Motivo" />
                        <x-text-input id="motivo" name="motivo" class="mt-1 block w-full" required />
                    </div>

                    <div>
                        <x-input-label for="referencia" value="Referencia documento" />
                        <x-text-input id="referencia" name="referencia" class="mt-1 block w-full" />
                    </div>

                    <x-primary-button class="w-full justify-center">Registrar</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
