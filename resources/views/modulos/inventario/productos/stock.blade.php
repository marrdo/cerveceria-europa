<x-app-layout>
    <x-slot name="header">
        Stock de {{ $producto->nombre }}
    </x-slot>

    @include('modulos.inventario.partials.nav')

    @php
        $stockPorUbicacion = $producto->stock->sortByDesc(fn ($stock) => (float) $stock->cantidad)->values();
        $stockTotal = max(0, (float) $stockPorUbicacion->sum('cantidad'));
        $stockMaximo = max(1, (float) $stockPorUbicacion->max('cantidad'));
        $movimientosPorDia = $producto->movimientos
            ->filter(fn ($movimiento): bool => $movimiento->created_at?->gte(now()->subDays(13)) ?? false)
            ->groupBy(fn ($movimiento): string => $movimiento->created_at->toDateString());
        $serieMovimientos = collect(range(13, 0))->map(function (int $dias) use ($movimientosPorDia): array {
            $fecha = now()->subDays($dias);
            $grupo = $movimientosPorDia->get($fecha->toDateString(), collect());
            $entradas = (float) $grupo->where('tipo.value', 'entrada')->sum('cantidad');
            $salidas = (float) $grupo->where('tipo.value', 'salida')->sum('cantidad');

            return [
                'fecha' => $fecha->toDateString(),
                'etiqueta' => $fecha->format('d/m'),
                'entradas' => $entradas,
                'salidas' => $salidas,
                'maximo' => max($entradas, $salidas),
            ];
        });
        $maxMovimiento = max(1, (float) $serieMovimientos->max('maximo'));
        $cantidadStep = $producto->unidad?->permite_decimal ? '0.001' : '1';
        $cantidadMin = $producto->unidad?->permite_decimal ? '0.001' : '1';
        $tipoSeleccionado = old('tipo', request('tipo', 'entrada'));
        $cantidadInicial = old('cantidad', $cantidadMin);
        $referenciaProducto = $producto->sku ?: $producto->id;
        $quickAmounts = $producto->unidad?->permite_decimal ? [0.25, 0.5, 1, 2] : [1, 6, 12, 24];
    @endphp

    <x-admin.page-header
        titulo="Stock de {{ $producto->nombre }}"
        subtitulo="Consulta existencias por ubicacion y registra entradas, salidas, ajustes o transferencias."
    >
        <a href="{{ route('admin.inventario.productos.edit', $producto) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-warning/40 bg-warning/20 text-warning-foreground transition hover:bg-warning/30" title="Editar" aria-label="Editar {{ $producto->nombre }}">
            <x-admin.icon name="edit" />
        </a>
        <a href="{{ route('admin.inventario.productos.index') }}" class="admin-btn-outline">Volver</a>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <section class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Acciones rapidas de stock">
        @foreach ($tiposMovimiento as $tipo)
            @php
                $variantClass = match ($tipo->value) {
                    'entrada' => 'border-success/30 bg-success/10 text-success hover:bg-success/15',
                    'salida' => 'border-destructive/30 bg-destructive/10 text-destructive hover:bg-destructive/15',
                    'ajuste' => 'border-warning/40 bg-warning/15 text-warning-foreground hover:bg-warning/25',
                    'transferencia' => 'border-accent/40 bg-accent/10 text-accent hover:bg-accent/15',
                    default => 'border-border bg-muted/30 text-foreground hover:bg-muted/50',
                };
            @endphp
            <a href="{{ route('admin.inventario.productos.stock', [$referenciaProducto, 'tipo' => $tipo->value]).'#registrar-movimiento' }}" class="rounded-lg border p-4 transition {{ $variantClass }}" title="Registrar {{ strtolower($tipo->etiqueta()) }}">
                <span class="block text-sm font-semibold">{{ $tipo->etiqueta() }}</span>
                <span class="mt-1 block text-xs opacity-80">
                    @switch($tipo->value)
                        @case('entrada')
                            Sumar unidades recibidas.
                            @break
                        @case('salida')
                            Descontar unidades manualmente.
                            @break
                        @case('ajuste')
                            Fijar una cantidad real.
                            @break
                        @case('transferencia')
                            Mover stock entre ubicaciones.
                            @break
                    @endswitch
                </span>
            </a>
        @endforeach
    </section>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="admin-card p-4 lg:p-6">
                @php
                    $estadoStock = $producto->estadoStock();
                    $estadoStockVariant = match ($estadoStock->value) {
                        'correcto' => 'success',
                        'bajo' => 'warning',
                        'sin_stock' => 'danger',
                        default => 'default',
                    };
                @endphp

                <div class="mb-4 flex items-center justify-between gap-4">
                    <h3 class="text-base font-semibold text-foreground">Stock por ubicacion</h3>
                    <x-admin.status-badge :variant="$estadoStockVariant">
                        {{ $estadoStock->etiqueta() }}
                    </x-admin.status-badge>
                </div>

                <figure class="mb-5 rounded-lg border border-border bg-muted/20 p-4">
                    <figcaption class="mb-3 flex items-center justify-between gap-3 text-sm">
                        <span class="font-medium text-foreground">Distribucion visual</span>
                        <span class="font-semibold text-foreground">{{ $producto->formatearCantidadConUnidad($stockTotal) }}</span>
                    </figcaption>
                    <div class="space-y-3">
                        @forelse ($stockPorUbicacion as $stock)
                            <div class="grid gap-2 md:grid-cols-[8rem_1fr_5rem] md:items-center">
                                <span class="truncate text-xs font-medium text-muted-foreground">{{ $stock->ubicacion?->nombre }}</span>
                                <div class="h-3 overflow-hidden rounded-full bg-muted">
                                    <div class="h-full rounded-full bg-primary" style="width: {{ ((float) $stock->cantidad / $stockMaximo) * 100 }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-foreground md:text-right">{{ $producto->formatearCantidad($stock->cantidad) }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-muted-foreground">Todavia no hay stock registrado.</p>
                        @endforelse
                    </div>
                </figure>

                <div class="divide-y divide-border">
                    @forelse ($stockPorUbicacion as $stock)
                        <div class="flex items-center justify-between gap-4 py-3 text-sm">
                            <span class="text-muted-foreground">{{ $stock->ubicacion?->nombre }}</span>
                            <span class="font-semibold text-foreground">{{ $producto->formatearCantidadConUnidad($stock->cantidad) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-muted-foreground">Todavia no hay stock registrado.</p>
                    @endforelse
                </div>
            </section>

            <section class="admin-card p-4 lg:p-6">
                <h3 class="mb-4 text-base font-semibold text-foreground">Ultimos movimientos</h3>
                <figure class="mb-5 rounded-lg border border-border bg-muted/20 p-4">
                    <figcaption class="mb-3 flex flex-wrap gap-4 text-xs text-muted-foreground">
                        <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-success"></span>Entradas</span>
                        <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-destructive"></span>Salidas</span>
                    </figcaption>
                    <ol class="grid grid-cols-7 gap-2 lg:grid-cols-[repeat(14,minmax(0,1fr))]" aria-label="Movimientos diarios de los ultimos 14 dias">
                        @foreach ($serieMovimientos as $dia)
                            <li class="flex min-h-28 flex-col justify-end gap-1">
                                <div class="flex h-20 items-end justify-center gap-1">
                                    <span class="w-2 rounded-t bg-success" style="height: {{ max(3, ($dia['entradas'] / $maxMovimiento) * 80) }}%" title="Entradas {{ $producto->formatearCantidad($dia['entradas']) }}"></span>
                                    <span class="w-2 rounded-t bg-destructive" style="height: {{ max(3, ($dia['salidas'] / $maxMovimiento) * 80) }}%" title="Salidas {{ $producto->formatearCantidad($dia['salidas']) }}"></span>
                                </div>
                                <time datetime="{{ $dia['fecha'] }}" class="text-center text-[0.65rem] text-muted-foreground">{{ $dia['etiqueta'] }}</time>
                            </li>
                        @endforeach
                    </ol>
                </figure>

                <div class="divide-y divide-border">
                    @forelse ($producto->movimientos->take(20) as $movimiento)
                        <div class="py-3 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium text-foreground">{{ $movimiento->tipo->etiqueta() }} - {{ $movimiento->motivo }}</span>
                                <span class="whitespace-nowrap font-semibold text-foreground">{{ $producto->formatearCantidadConUnidad($movimiento->cantidad) }}</span>
                            </div>
                            <p class="mt-1 text-muted-foreground">{{ $movimiento->created_at->format('d/m/Y H:i') }} - Stock: {{ $producto->formatearCantidad($movimiento->stock_antes) }} -> {{ $producto->formatearCantidad($movimiento->stock_despues) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-muted-foreground">Todavia no hay movimientos.</p>
                    @endforelse
                </div>
            </section>

            <section class="admin-card p-4 lg:p-6">
                <h3 class="mb-4 text-base font-semibold text-foreground">Lotes y caducidad</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/50">
                                <th class="px-3 py-2 text-left font-medium text-foreground">Lote</th>
                                <th class="px-3 py-2 text-left font-medium text-foreground">Ubicacion</th>
                                <th class="px-3 py-2 text-left font-medium text-foreground">Disponible</th>
                                <th class="px-3 py-2 text-left font-medium text-foreground">Caduca</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($producto->lotes->where('cantidad_disponible', '>', 0) as $lote)
                                @php
                                    $caducidadVariant = match (true) {
                                        $lote->caduca_el?->isPast() => 'danger',
                                        $lote->caduca_el?->lte(now()->addDays(30)) => 'warning',
                                        default => 'default',
                                    };
                                @endphp
                                <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                    <td class="px-3 py-2 text-foreground">{{ $lote->codigo_lote ?: 'Sin lote' }}</td>
                                    <td class="px-3 py-2 text-muted-foreground">{{ $lote->ubicacion?->nombre ?? 'Sin ubicacion' }}</td>
                                    <td class="px-3 py-2 text-foreground">{{ $producto->formatearCantidadConUnidad($lote->cantidad_disponible) }}</td>
                                    <td class="px-3 py-2">
                                        @if ($lote->caduca_el)
                                            <x-admin.status-badge :variant="$caducidadVariant">{{ $lote->caduca_el->format('d/m/Y') }}</x-admin.status-badge>
                                        @else
                                            <span class="text-muted-foreground">Sin caducidad</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-sm text-muted-foreground">Todavia no hay lotes disponibles.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <form
            id="registrar-movimiento"
            method="POST"
            action="{{ route('admin.inventario.productos.stock.movimientos.store', $referenciaProducto) }}"
            class="admin-card space-y-4 p-4 lg:p-6"
            x-data="{
                tipo: @js($tipoSeleccionado),
                cantidad: @js((string) $cantidadInicial),
                step: {{ $cantidadStep }},
                min: {{ $cantidadMin }},
                normalizar(valor) {
                    const numero = Number(String(valor).replace(',', '.'));
                    return Number.isFinite(numero) ? numero : this.min;
                },
                formatear(valor) {
                    if (this.step >= 1) {
                        return String(Math.max(this.min, Math.round(valor)));
                    }

                    return String(Math.max(this.min, valor).toFixed(3)).replace(/\.?0+$/, '');
                },
                ajustar(delta) {
                    this.cantidad = this.formatear(this.normalizar(this.cantidad) + (delta * this.step));
                },
                fijar(valor) {
                    this.cantidad = this.formatear(valor);
                },
            }"
        >
            @csrf
            <h3 class="text-base font-semibold text-foreground">Registrar movimiento</h3>

            <div>
                <x-input-label value="Tipo" />
                <div class="mt-2 grid grid-cols-2 gap-2">
                    @foreach ($tiposMovimiento as $tipo)
                        <label class="cursor-pointer">
                            <input type="radio" name="tipo" value="{{ $tipo->value }}" class="sr-only" x-model="tipo" @checked($tipoSeleccionado === $tipo->value)>
                            <span
                                class="flex min-h-16 items-center rounded-lg border px-3 py-2 text-sm font-semibold transition"
                                :class="tipo === @js($tipo->value) ? 'border-primary bg-primary/10 text-primary' : 'border-border bg-muted/20 text-muted-foreground hover:bg-muted/40'"
                            >
                                {{ $tipo->etiqueta() }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-muted-foreground">Entrada suma stock, salida descuenta, ajuste fija una cantidad y transferencia mueve entre ubicaciones.</p>
                <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
            </div>

            <div x-show="tipo !== 'transferencia'" x-cloak>
                <x-input-label for="ubicacion_inventario_id" value="Ubicacion" />
                <select id="ubicacion_inventario_id" name="ubicacion_inventario_id" class="admin-input mt-1 block h-10 w-full">
                    @foreach ($ubicaciones as $ubicacion)
                        <option value="{{ $ubicacion->id }}" @selected(old('ubicacion_inventario_id') === $ubicacion->id)>{{ $ubicacion->nombre }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-muted-foreground">Ubicacion usada en entradas, salidas y ajustes.</p>
                <x-input-error :messages="$errors->get('ubicacion_inventario_id')" class="mt-2" />
            </div>

            <div class="grid gap-3 md:grid-cols-2" x-show="tipo === 'transferencia'" x-cloak>
                <div>
                    <x-input-label for="ubicacion_origen_id" value="Origen transferencia" />
                    <select id="ubicacion_origen_id" name="ubicacion_origen_id" class="admin-input mt-1 block h-10 w-full">
                        <option value="">-</option>
                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}" @selected(old('ubicacion_origen_id') === $ubicacion->id)>{{ $ubicacion->nombre }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-muted-foreground">Solo para transferencias: de donde sale el stock.</p>
                    <x-input-error :messages="$errors->get('ubicacion_origen_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="ubicacion_destino_id" value="Destino transferencia" />
                    <select id="ubicacion_destino_id" name="ubicacion_destino_id" class="admin-input mt-1 block h-10 w-full">
                        <option value="">-</option>
                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}" @selected(old('ubicacion_destino_id') === $ubicacion->id)>{{ $ubicacion->nombre }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-muted-foreground">Solo para transferencias: donde entra el stock.</p>
                    <x-input-error :messages="$errors->get('ubicacion_destino_id')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="proveedor_id" value="Proveedor" />
                <select id="proveedor_id" name="proveedor_id" class="admin-input mt-1 block h-10 w-full">
                    <option value="">Sin proveedor</option>
                    @foreach ($proveedores as $proveedor)
                        <option value="{{ $proveedor->id }}" @selected(old('proveedor_id') === $proveedor->id)>{{ $proveedor->nombre }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-muted-foreground">Opcional. Sirve para dejar trazabilidad cuando la entrada viene de un proveedor.</p>
                <x-input-error :messages="$errors->get('proveedor_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="cantidad" value="Cantidad" />
                <div class="mt-2 flex items-center justify-center gap-4 rounded-lg border border-border bg-muted/20 p-3">
                    <button type="button" class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-border bg-card text-foreground shadow-sm transition hover:bg-muted disabled:cursor-not-allowed disabled:opacity-40" title="Restar cantidad" aria-label="Restar cantidad" x-on:click="ajustar(-1)" x-bind:disabled="normalizar(cantidad) <= min">
                        <x-admin.icon name="minus" class="h-5 w-5" />
                    </button>
                    <input
                        id="cantidad"
                        name="cantidad"
                        type="number"
                        step="{{ $cantidadStep }}"
                        min="{{ $cantidadMin }}"
                        inputmode="{{ $producto->unidad?->permite_decimal ? 'decimal' : 'numeric' }}"
                        autocomplete="off"
                        enterkeyhint="done"
                        class="admin-input h-12 w-28 border-0 bg-transparent text-center text-2xl font-bold shadow-none focus:ring-0"
                        x-model="cantidad"
                        required
                    >
                    <button type="button" class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-border bg-card text-foreground shadow-sm transition hover:bg-muted" title="Sumar cantidad" aria-label="Sumar cantidad" x-on:click="ajustar(1)">
                        <x-admin.icon name="plus" class="h-5 w-5" />
                    </button>
                </div>
                <div class="mt-2 grid grid-cols-4 gap-2">
                    @foreach ($quickAmounts as $amount)
                        <button type="button" class="rounded-md border border-border bg-card px-2 py-2 text-sm font-semibold text-foreground transition hover:bg-primary/10" x-on:click="fijar({{ $amount }})">
                            {{ $producto->formatearCantidad($amount) }}
                        </button>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-muted-foreground">Numero de unidades que entran, salen, se ajustan o se transfieren.</p>
                <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="motivo" value="Motivo" />
                <x-text-input id="motivo" name="motivo" class="mt-1 block h-10 w-full" :value="old('motivo')" list="motivos-inventario" required maxlength="191" autocomplete="off" />
                <datalist id="motivos-inventario">
                    <option value="Entrada manual">
                    <option value="Salida manual">
                    <option value="Ajuste de inventario">
                    <option value="Transferencia entre ubicaciones">
                    <option value="Rotura o merma">
                </datalist>
                <p class="mt-1 text-xs text-muted-foreground">Explica por que se hace el movimiento. Ejemplo: inventario inicial, rotura, merma o ajuste.</p>
                <x-input-error :messages="$errors->get('motivo')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="referencia" value="Referencia documento" />
                <x-text-input id="referencia" name="referencia" class="mt-1 block h-10 w-full" :value="old('referencia')" maxlength="191" autocomplete="off" />
                <p class="mt-1 text-xs text-muted-foreground">Numero de albaran, factura o documento relacionado, si existe.</p>
                <x-input-error :messages="$errors->get('referencia')" class="mt-2" />
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <x-input-label for="codigo_lote" value="Codigo lote" />
                    <x-text-input id="codigo_lote" name="codigo_lote" class="mt-1 block h-10 w-full" :value="old('codigo_lote')" maxlength="100" autocomplete="off" />
                    <p class="mt-1 text-xs text-muted-foreground">Codigo impreso en el producto o albaran para seguir el lote.</p>
                    <x-input-error :messages="$errors->get('codigo_lote')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="caduca_el" value="Fecha caducidad" />
                    <x-text-input id="caduca_el" name="caduca_el" type="date" class="mt-1 block h-10 w-full" :value="old('caduca_el')" />
                    <p class="mt-1 text-xs text-muted-foreground">Obligatoria si el producto controla caducidad.</p>
                    <x-input-error :messages="$errors->get('caduca_el')" class="mt-2" />
                </div>
            </div>

            <x-primary-button class="w-full justify-center">Registrar</x-primary-button>
        </form>
    </div>
</x-app-layout>
