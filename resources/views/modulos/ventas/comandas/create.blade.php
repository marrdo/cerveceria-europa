<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Nueva comanda" description="Toma de pedido desde la carta publicada." />
    </x-slot>

    @include('modulos.ventas.partials.nav')

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-destructive/25 bg-destructive/10 p-4 text-sm text-destructive">
            <p class="font-medium">Revisa la comanda antes de guardarla.</p>
            <ul class="mt-2 list-disc space-y-1 ps-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.ventas.comandas.store') }}" class="space-y-4">
        @csrf

        <section class="admin-card grid gap-4 p-4 md:grid-cols-4">
            <div>
                <x-input-label for="mesa" value="Mesa" />
                <x-text-input id="mesa" name="mesa" class="mt-1 block h-10 w-full" :value="old('mesa')" maxlength="50" placeholder="Barra, 4, terraza..." />
            </div>
            <div>
                <x-input-label for="cliente_nombre" value="Cliente" />
                <x-text-input id="cliente_nombre" name="cliente_nombre" class="mt-1 block h-10 w-full" :value="old('cliente_nombre')" maxlength="191" />
            </div>
            <div>
                <x-input-label for="ubicacion_inventario_id" value="Ubicacion de stock" />
                <select id="ubicacion_inventario_id" name="ubicacion_inventario_id" class="admin-input mt-1 block h-10 w-full">
                    <option value="">Sin descuento automatico</option>
                    @foreach ($ubicaciones as $ubicacion)
                        <option value="{{ $ubicacion->id }}" @selected(old('ubicacion_inventario_id') === $ubicacion->id || (! old('ubicacion_inventario_id') && $ubicacion->codigo === 'BARRA'))>{{ $ubicacion->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="notas" value="Notas" />
                <x-text-input id="notas" name="notas" class="mt-1 block h-10 w-full" :value="old('notas')" maxlength="1000" />
            </div>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Carta</h2>
                <p class="mt-1 text-sm text-muted-foreground">Indica cantidades solo en los productos que entren en la comanda.</p>
            </div>

            <div class="divide-y divide-border">
                @forelse ($contenidos as $indice => $contenido)
                    @php
                        $tarifas = $contenido->tarifas;
                        $lineaOld = old("lineas.$indice", []);
                    @endphp
                    <div class="grid gap-3 p-4 lg:grid-cols-[1fr_180px_110px]">
                        <input type="hidden" name="lineas[{{ $indice }}][contenido_web_id]" value="{{ $contenido->id }}">

                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-semibold text-foreground">{{ $contenido->titulo }}</h3>
                                @if ($contenido->categoriaCarta)
                                    <span class="text-xs text-muted-foreground">{{ $contenido->categoriaCarta->nombreJerarquico() }}</span>
                                @endif
                            </div>
                            @if ($contenido->descripcion_corta)
                                <p class="mt-1 text-sm text-muted-foreground">{{ $contenido->descripcion_corta }}</p>
                            @endif
                        </div>

                        <div>
                            <x-input-label for="tarifa_{{ $indice }}" value="Tarifa" />
                            <select id="tarifa_{{ $indice }}" name="lineas[{{ $indice }}][tarifa_contenido_web_id]" class="admin-input mt-1 block h-10 w-full">
                                @forelse ($tarifas as $tarifa)
                                    <option value="{{ $tarifa->id }}" @selected(($lineaOld['tarifa_contenido_web_id'] ?? '') === $tarifa->id)>{{ $tarifa->nombre ?: 'Tarifa' }} · {{ $tarifa->precioFormateado() }}</option>
                                @empty
                                    <option value="">Precio base · {{ $contenido->precioFormateado() ?? '0,00 EUR' }}</option>
                                @endforelse
                            </select>
                        </div>

                        <div>
                            <x-input-label for="cantidad_{{ $indice }}" value="Cantidad" />
                            <input id="cantidad_{{ $indice }}" name="lineas[{{ $indice }}][cantidad]" type="number" step="0.001" min="0" class="admin-input mt-1 block h-10 w-full" value="{{ $lineaOld['cantidad'] ?? '' }}">
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-sm text-muted-foreground">No hay contenidos publicados en la carta.</div>
                @endforelse
            </div>
        </section>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.ventas.comandas.index') }}" class="admin-btn-outline">Cancelar</a>
            <button type="submit" class="admin-btn-primary">Crear comanda</button>
        </div>
    </form>
</x-app-layout>
