<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="'Comanda '.$comanda->numero" :description="$comanda->mesaEspacio ? 'Mesa '.$comanda->mesaEspacio->nombre : ($comanda->mesa ? 'Mesa '.$comanda->mesa : 'Sin mesa asignada')">
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
        <div class="space-y-4">
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

        @if ($comanda->puedeRecibirLineas())
            <section class="admin-card overflow-hidden" x-data="{ busquedaCarta: '' }">
                <div class="border-b border-border p-4">
                    <div class="grid gap-3 lg:grid-cols-[1fr_320px] lg:items-end">
                        <div>
                            <h2 class="text-base font-semibold text-foreground">Anadir productos</h2>
                            <p class="mt-1 text-sm text-muted-foreground">Amplia la comanda si el cliente sigue pidiendo.</p>
                        </div>
                        <div>
                            <x-input-label for="busqueda_carta_extra" value="Buscar" />
                            <x-text-input id="busqueda_carta_extra" type="search" class="mt-1 block h-10 w-full" x-model.debounce.150ms="busquedaCarta" placeholder="Nombre, estilo, categoria..." />
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.ventas.comandas.lineas.store', $comanda) }}">
                    @csrf
                    <div class="divide-y divide-border">
                        @php $indiceNuevaLinea = 0; @endphp
                        @foreach ($seccionesCarta as $seccionIndice => $seccion)
                            <section x-data="{ abierta: false }" class="bg-card">
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
                                    <span class="rounded-md border border-primary/30 bg-background/60 px-2 py-1 text-xs font-semibold text-foreground">{{ $seccion['total'] }}</span>
                                </button>

                                <div x-show="abierta || busquedaCarta.length > 0" x-transition class="border-t border-border bg-background/40">
                                    @foreach ($seccion['categorias'] as $categoriaIndice => $categoria)
                                        <div x-data="{ abiertaCategoria: false }" class="border-b border-border last:border-b-0">
                                            <button
                                                type="button"
                                                class="flex w-full items-center justify-between gap-3 border-l-4 px-4 py-3 text-left transition"
                                                :class="abiertaCategoria ? 'border-accent bg-accent/10 hover:bg-accent/15' : 'border-transparent bg-background/50 hover:bg-muted/40'"
                                                @click="abiertaCategoria = ! abiertaCategoria"
                                                :aria-expanded="abiertaCategoria"
                                                title="Abrir o cerrar categoria"
                                            >
                                                <span class="text-sm font-semibold text-foreground">{{ $categoria['nombre'] }}</span>
                                                <span class="rounded-md border border-accent/30 bg-background/70 px-2 py-1 text-xs font-semibold text-foreground">{{ $categoria['contenidos']->count() }}</span>
                                            </button>

                                            <div x-show="abiertaCategoria || busquedaCarta.length > 0" x-transition class="divide-y divide-border bg-card">
                                                @foreach ($categoria['contenidos'] as $contenido)
                                                    @php
                                                        $tarifas = $contenido->tarifas;
                                                        $textoBusqueda = mb_strtolower(trim($seccion['nombre'].' '.$categoria['nombre'].' '.$contenido->titulo.' '.$contenido->descripcion_corta));
                                                    @endphp
                                                    <div
                                                        class="grid gap-3 p-4 lg:grid-cols-[1fr_190px_150px] lg:items-center"
                                                        x-data="{ cantidad: 0 }"
                                                        x-show="busquedaCarta === '' || '{{ e($textoBusqueda) }}'.includes(busquedaCarta.toLowerCase())"
                                                    >
                                                        <input type="hidden" name="lineas[{{ $indiceNuevaLinea }}][contenido_web_id]" value="{{ $contenido->id }}">
                                                        <input type="hidden" name="lineas[{{ $indiceNuevaLinea }}][cantidad]" x-model="cantidad">

                                                        <div>
                                                            <h3 class="text-sm font-semibold text-foreground">{{ $contenido->titulo }}</h3>
                                                            @if ($contenido->descripcion_corta)
                                                                <p class="mt-1 text-sm text-muted-foreground">{{ $contenido->descripcion_corta }}</p>
                                                            @endif
                                                        </div>

                                                        <div>
                                                            <x-input-label for="tarifa_extra_{{ $indiceNuevaLinea }}" value="Tarifa" />
                                                            <select id="tarifa_extra_{{ $indiceNuevaLinea }}" name="lineas[{{ $indiceNuevaLinea }}][tarifa_contenido_web_id]" class="admin-input mt-1 block h-10 w-full">
                                                                @forelse ($tarifas as $tarifa)
                                                                    <option value="{{ $tarifa->id }}">{{ $tarifa->nombre ?: 'Tarifa' }} - {{ $tarifa->precioFormateado() }}</option>
                                                                @empty
                                                                    <option value="">Precio base - {{ $contenido->precioFormateado() ?? '0,00 EUR' }}</option>
                                                                @endforelse
                                                            </select>
                                                        </div>

                                                        <div>
                                                            <x-input-label value="Cantidad" />
                                                            <div class="mt-2 flex h-12 items-center justify-start gap-3 sm:justify-center">
                                                                <button type="button" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-border bg-background text-xl font-medium leading-none text-muted-foreground shadow-sm transition hover:border-primary/60 hover:bg-muted hover:text-foreground disabled:cursor-not-allowed disabled:opacity-30" title="Restar unidad" aria-label="Restar unidad" @click="cantidad = Math.max(0, Number(cantidad || 0) - 1)" :disabled="Number(cantidad || 0) <= 0">-</button>
                                                                <span class="flex min-w-8 items-center justify-center text-lg font-semibold tabular-nums text-foreground" x-text="cantidad" aria-live="polite"></span>
                                                                <button type="button" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-border bg-background text-xl font-medium leading-none text-foreground shadow-sm transition hover:border-primary/70 hover:bg-primary/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/40" title="Sumar unidad" aria-label="Sumar unidad" @click="cantidad = Number(cantidad || 0) + 1">+</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @php $indiceNuevaLinea++; @endphp
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>

                    <div class="border-t border-border p-4">
                        <button type="submit" class="admin-btn-primary w-full">Anadir productos seleccionados</button>
                    </div>
                </form>
            </section>
        @endif

        </div>

        <aside class="space-y-4">
            <section class="admin-card p-4">
                <h2 class="text-base font-semibold text-foreground">Resumen</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Zona</dt>
                        <dd class="text-right text-foreground">{{ $comanda->zona?->nombre ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Mesa</dt>
                        <dd class="text-right text-foreground">{{ $comanda->mesaEspacio?->nombre ?? $comanda->mesa ?? '-' }}</dd>
                    </div>
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

            @if ($comanda->puedeEditarOperativa())
                <section class="admin-card p-4">
                    <h2 class="text-base font-semibold text-foreground">Editar comanda</h2>
                    <form method="POST" action="{{ route('admin.ventas.comandas.operativa.update', $comanda) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')

                        <div class="grid gap-3">
                            <div>
                                <x-input-label for="recinto_id" value="Recinto" />
                                <select id="recinto_id" name="recinto_id" class="admin-input mt-1 block h-10 w-full">
                                    <option value="">Sin recinto</option>
                                    @foreach ($recintos as $recinto)
                                        <option value="{{ $recinto->id }}" @selected(old('recinto_id', $comanda->recinto_id) === $recinto->id)>{{ $recinto->nombre_comercial }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="zona_id" value="Zona" />
                                <select id="zona_id" name="zona_id" class="admin-input mt-1 block h-10 w-full">
                                    <option value="">Sin zona</option>
                                    @foreach ($zonas as $zona)
                                        <option value="{{ $zona->id }}" @selected(old('zona_id', $comanda->zona_id) === $zona->id)>{{ $zona->recinto?->nombre_comercial }} - {{ $zona->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="mesa_id" value="Mesa configurada" />
                                <select id="mesa_id" name="mesa_id" class="admin-input mt-1 block h-10 w-full">
                                    <option value="">Sin mesa configurada</option>
                                    @foreach ($mesas as $mesa)
                                        <option value="{{ $mesa->id }}" @selected(old('mesa_id', $comanda->mesa_id) === $mesa->id)>{{ $mesa->zona?->nombre }} - {{ $mesa->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="mesa" value="Mesa manual" />
                                <x-text-input id="mesa" name="mesa" class="mt-1 block h-10 w-full" :value="old('mesa', $comanda->mesa)" maxlength="50" />
                            </div>
                            <div>
                                <x-input-label for="cliente_nombre" value="Cliente" />
                                <x-text-input id="cliente_nombre" name="cliente_nombre" class="mt-1 block h-10 w-full" :value="old('cliente_nombre', $comanda->cliente_nombre)" maxlength="191" />
                            </div>
                            <div>
                                <x-input-label for="ubicacion_inventario_id" value="Descuento de inventario" />
                                <select id="ubicacion_inventario_id" name="ubicacion_inventario_id" class="admin-input mt-1 block h-10 w-full">
                                    <option value="">Sin descuento automatico</option>
                                    @foreach ($ubicaciones as $ubicacion)
                                        <option value="{{ $ubicacion->id }}" @selected(old('ubicacion_inventario_id', $comanda->ubicacion_inventario_id) === $ubicacion->id)>{{ $ubicacion->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="notas" value="Notas" />
                                <textarea id="notas" name="notas" rows="3" class="admin-input mt-1 block w-full">{{ old('notas', $comanda->notas) }}</textarea>
                            </div>
                        </div>

                        @php
                            $lineasEditables = $comanda->lineas->filter(fn ($linea) => ! $linea->estaServida() && $linea->estado !== \App\Modulos\Ventas\Enums\EstadoLineaComanda::Cancelada);
                        @endphp

                        @if ($lineasEditables->isNotEmpty())
                            <div class="space-y-3 border-t border-border pt-4">
                                <h3 class="text-sm font-semibold text-foreground">Lineas pendientes</h3>
                                @foreach ($lineasEditables as $linea)
                                    <div class="rounded-md border border-border p-3" x-data="{ cantidad: {{ (float) $linea->cantidad }} }">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-sm font-semibold text-foreground">{{ $linea->nombre }}</p>
                                                <p class="mt-1 text-xs text-muted-foreground">{{ number_format((float) $linea->precio_unitario, 2, ',', '.') }} EUR unidad</p>
                                            </div>
                                            <label class="flex items-center gap-2 text-xs text-muted-foreground">
                                                <input type="checkbox" name="lineas[{{ $linea->id }}][cancelar]" value="1" class="rounded border-input bg-background text-primary focus:ring-ring">
                                                Cancelar
                                            </label>
                                        </div>

                                        <input type="hidden" name="lineas[{{ $linea->id }}][cantidad]" x-model="cantidad">
                                        <div class="mt-3 flex h-12 items-center justify-start gap-3">
                                            <button type="button" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-border bg-background text-xl font-medium leading-none text-muted-foreground shadow-sm transition hover:border-primary/60 hover:bg-muted hover:text-foreground disabled:cursor-not-allowed disabled:opacity-30" title="Restar unidad" aria-label="Restar unidad" @click="cantidad = Math.max(0, Number(cantidad || 0) - 1)" :disabled="Number(cantidad || 0) <= 0">-</button>
                                            <span class="flex min-w-8 items-center justify-center text-lg font-semibold tabular-nums text-foreground" x-text="cantidad" aria-live="polite"></span>
                                            <button type="button" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-border bg-background text-xl font-medium leading-none text-foreground shadow-sm transition hover:border-primary/70 hover:bg-primary/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/40" title="Sumar unidad" aria-label="Sumar unidad" @click="cantidad = Number(cantidad || 0) + 1">+</button>
                                        </div>

                                        <div class="mt-3">
                                            <x-input-label for="linea_notas_{{ $linea->id }}" value="Notas de linea" />
                                            <x-text-input id="linea_notas_{{ $linea->id }}" name="lineas[{{ $linea->id }}][notas]" class="mt-1 block h-10 w-full" :value="old('lineas.'.$linea->id.'.notas', $linea->notas)" maxlength="500" />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <button type="submit" class="admin-btn-primary w-full">Guardar cambios</button>
                    </form>
                </section>
            @endif

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
