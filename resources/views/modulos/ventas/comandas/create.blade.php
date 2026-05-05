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

    <form method="POST" action="{{ route('admin.ventas.comandas.store') }}" class="space-y-4" x-data="{ busquedaCarta: '' }">
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
                <x-input-label for="ubicacion_inventario_id" value="Descuento de inventario" />
                <select id="ubicacion_inventario_id" name="ubicacion_inventario_id" class="admin-input mt-1 block h-10 w-full">
                    <option value="">Sin descuento automatico</option>
                    @foreach ($ubicaciones as $ubicacion)
                        <option value="{{ $ubicacion->id }}" @selected(old('ubicacion_inventario_id') === $ubicacion->id)>{{ $ubicacion->nombre }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-muted-foreground">Opcional. Indica de que ubicacion se descuenta stock al servir. Si no hay inventario cargado, deja esta opcion vacia.</p>
            </div>
            <div>
                <x-input-label for="notas" value="Notas" />
                <x-text-input id="notas" name="notas" class="mt-1 block h-10 w-full" :value="old('notas')" maxlength="1000" />
            </div>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-border p-4">
                <div class="grid gap-3 lg:grid-cols-[1fr_320px] lg:items-end">
                    <div>
                        <h2 class="text-base font-semibold text-foreground">Carta</h2>
                        <p class="mt-1 text-sm text-muted-foreground">Abre una categoria y usa los botones para sumar o restar unidades.</p>
                    </div>
                    <div>
                        <x-input-label for="busqueda_carta" value="Buscar" />
                        <x-text-input id="busqueda_carta" type="search" class="mt-1 block h-10 w-full" x-model.debounce.150ms="busquedaCarta" placeholder="Nombre, estilo, categoria..." />
                    </div>
                </div>
            </div>

            <div class="divide-y divide-border">
                @php $indice = 0; @endphp
                @forelse ($seccionesCarta as $seccionIndice => $seccion)
                    <section x-data="{ abierta: {{ $seccionIndice === 0 ? 'true' : 'false' }} }" class="bg-card">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-3 border-l-4 p-4 text-left transition"
                            :class="abierta ? 'border-primary bg-primary/15 hover:bg-primary/20' : 'border-transparent bg-card hover:bg-muted/50'"
                            @click="abierta = ! abierta"
                            :aria-expanded="abierta"
                            title="Abrir o cerrar seccion"
                        >
                            <span>
                                <span class="block text-sm font-bold uppercase tracking-wide text-foreground">{{ $seccion['nombre'] }}</span>
                                @if ($seccion['descripcion'])
                                    <span class="mt-1 block text-sm text-muted-foreground">{{ $seccion['descripcion'] }}</span>
                                @endif
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="rounded-md border border-primary/30 bg-background/60 px-2 py-1 text-xs font-semibold text-foreground">{{ $seccion['total'] }}</span>
                                <svg class="h-4 w-4 text-muted-foreground transition" :class="abierta ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 18 6-6-6-6" />
                                </svg>
                            </span>
                        </button>

                        <div x-show="abierta || busquedaCarta.length > 0" x-transition class="border-t border-border bg-background/40">
                            @foreach ($seccion['categorias'] as $categoriaIndice => $categoria)
                                <div x-data="{ abiertaCategoria: {{ $categoriaIndice === 0 ? 'true' : 'false' }} }" class="border-b border-border last:border-b-0">
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between gap-3 border-l-4 px-4 py-3 text-left transition"
                                        :class="abiertaCategoria ? 'border-accent bg-accent/10 hover:bg-accent/15' : 'border-transparent bg-background/50 hover:bg-muted/40'"
                                        @click="abiertaCategoria = ! abiertaCategoria"
                                        :aria-expanded="abiertaCategoria"
                                        title="Abrir o cerrar categoria"
                                    >
                                        <span class="text-sm font-semibold text-foreground">{{ $categoria['nombre'] }}</span>
                                        <span class="flex items-center gap-2">
                                            <span class="rounded-md border border-accent/30 bg-background/70 px-2 py-1 text-xs font-semibold text-foreground">{{ $categoria['contenidos']->count() }}</span>
                                            <svg class="h-4 w-4 text-muted-foreground transition" :class="abiertaCategoria ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 18 6-6-6-6" />
                                            </svg>
                                        </span>
                                    </button>

                                    <div x-show="abiertaCategoria || busquedaCarta.length > 0" x-transition class="divide-y divide-border bg-card">
                                        @foreach ($categoria['contenidos'] as $contenido)
                                            @php
                                                $tarifas = $contenido->tarifas;
                                                $lineaOld = old("lineas.$indice", []);
                                                $cantidadInicial = (float) ($lineaOld['cantidad'] ?? 0);
                                                $textoBusqueda = mb_strtolower(trim($seccion['nombre'].' '.$categoria['nombre'].' '.$contenido->titulo.' '.$contenido->descripcion_corta));
                                            @endphp
                                            <div
                                                class="grid gap-3 p-4 lg:grid-cols-[1fr_190px_150px] lg:items-center"
                                                x-data="{ cantidad: {{ $cantidadInicial }} }"
                                                x-show="busquedaCarta === '' || '{{ e($textoBusqueda) }}'.includes(busquedaCarta.toLowerCase())"
                                            >
                                                <input type="hidden" name="lineas[{{ $indice }}][contenido_web_id]" value="{{ $contenido->id }}">
                                                <input type="hidden" name="lineas[{{ $indice }}][cantidad]" x-model="cantidad">

                                                <div>
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <h3 class="text-sm font-semibold text-foreground">{{ $contenido->titulo }}</h3>
                                                        @if ($contenido->destacado)
                                                            <x-admin.status-badge variant="warning">Destacado</x-admin.status-badge>
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
                                                            <option value="{{ $tarifa->id }}" @selected(($lineaOld['tarifa_contenido_web_id'] ?? '') === $tarifa->id)>{{ $tarifa->nombre ?: 'Tarifa' }} - {{ $tarifa->precioFormateado() }}</option>
                                                        @empty
                                                            <option value="">Precio base - {{ $contenido->precioFormateado() ?? '0,00 EUR' }}</option>
                                                        @endforelse
                                                    </select>
                                                </div>

                                                <div>
                                                    <x-input-label value="Cantidad" />
                                                    <div class="mt-2 flex h-12 items-center justify-start gap-3 sm:justify-center">
                                                        <button
                                                            type="button"
                                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-border bg-background text-xl font-medium leading-none text-muted-foreground shadow-sm transition hover:border-primary/60 hover:bg-muted hover:text-foreground disabled:cursor-not-allowed disabled:opacity-30"
                                                            title="Restar unidad"
                                                            aria-label="Restar unidad"
                                                            @click="cantidad = Math.max(0, Number(cantidad || 0) - 1)"
                                                            :disabled="Number(cantidad || 0) <= 0"
                                                        >-</button>
                                                        <span
                                                            class="flex min-w-8 items-center justify-center text-lg font-semibold tabular-nums text-foreground"
                                                            x-text="cantidad"
                                                            aria-live="polite"
                                                        ></span>
                                                        <button
                                                            type="button"
                                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-border bg-background text-xl font-medium leading-none text-foreground shadow-sm transition hover:border-primary/70 hover:bg-primary/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/40"
                                                            title="Sumar unidad"
                                                            aria-label="Sumar unidad"
                                                            @click="cantidad = Number(cantidad || 0) + 1"
                                                        >+</button>
                                                    </div>
                                                </div>
                                            </div>
                                            @php $indice++; @endphp
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
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
