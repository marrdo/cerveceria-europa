<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <section class="admin-card p-4 lg:p-6">
        <h2 class="mb-4 text-base font-semibold text-foreground">Datos del pedido</h2>
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <x-input-label for="proveedor_id" value="Proveedor" />
                <select id="proveedor_id" name="proveedor_id" class="admin-input mt-1 block h-10 w-full" required>
                    <option value="">Seleccionar proveedor</option>
                    @foreach ($proveedores as $proveedor)
                        <option value="{{ $proveedor->id }}" @selected(old('proveedor_id', $pedido->proveedor_id) === $proveedor->id)>{{ $proveedor->nombre }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('proveedor_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="fecha_pedido" value="Fecha pedido" />
                <x-text-input id="fecha_pedido" name="fecha_pedido" type="date" class="mt-1 block h-10 w-full" :value="old('fecha_pedido', $pedido->fecha_pedido?->format('Y-m-d'))" />
                <x-input-error :messages="$errors->get('fecha_pedido')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="fecha_prevista" value="Fecha prevista" />
                <x-text-input id="fecha_prevista" name="fecha_prevista" type="date" class="mt-1 block h-10 w-full" :value="old('fecha_prevista', $pedido->fecha_prevista?->format('Y-m-d'))" />
                <x-input-error :messages="$errors->get('fecha_prevista')" class="mt-2" />
            </div>
        </div>

        <div class="mt-4">
            <x-input-label for="notas" value="Notas" />
            <textarea id="notas" name="notas" rows="3" class="admin-input mt-1 block w-full">{{ old('notas', $pedido->notas) }}</textarea>
            <x-input-error :messages="$errors->get('notas')" class="mt-2" />
        </div>
    </section>

    <section class="admin-card overflow-hidden" data-pedido-lineas>
        <div class="border-b border-border p-4">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-foreground">Lineas del pedido</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Anade todas las lineas que necesites. Las vacias se ignoran.</p>
                </div>

                <div class="flex flex-wrap items-end gap-2">
                    <button type="button" class="admin-btn-outline" data-anadir-linea>Anadir linea</button>
                    <div>
                        <x-input-label for="numero_lineas_pedido" value="Mostrar lineas" />
                        <div class="mt-1 flex gap-2">
                            <x-text-input id="numero_lineas_pedido" type="number" min="1" max="100" class="h-10 w-24" data-total-lineas />
                            <button type="button" class="admin-btn-primary" data-ajustar-lineas>Aplicar</button>
                        </div>
                    </div>
                </div>
            </div>
            <x-input-error :messages="$errors->get('lineas')" class="mt-2" />
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50">
                        <th class="min-w-64 px-4 py-3 text-left font-medium text-foreground">Producto</th>
                        <th class="min-w-56 px-4 py-3 text-left font-medium text-foreground">Descripcion</th>
                        <th class="w-32 px-4 py-3 text-left font-medium text-foreground">Cantidad</th>
                        <th class="w-36 px-4 py-3 text-left font-medium text-foreground">Coste</th>
                        <th class="w-28 px-4 py-3 text-left font-medium text-foreground">IVA %</th>
                    </tr>
                </thead>
                <tbody data-lineas-tbody>
                    @php
                        $lineasFormulario = old('lineas', $pedido->lineas?->map(fn ($linea) => [
                            'producto_id' => $linea->producto_id,
                            'descripcion' => $linea->descripcion,
                            'cantidad' => $linea->cantidad,
                            'coste_unitario' => $linea->coste_unitario,
                            'iva_porcentaje' => $linea->iva_porcentaje,
                        ])->all() ?? []);
                        $totalFilas = max(5, count($lineasFormulario));
                    @endphp

                    @for ($indice = 0; $indice < $totalFilas; $indice++)
                        @php($linea = $lineasFormulario[$indice] ?? [])
                        <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                            <td class="px-4 py-3">
                                <select name="lineas[{{ $indice }}][producto_id]" class="admin-input block h-10 w-full">
                                    <option value="">Seleccionar</option>
                                    @foreach ($productos as $producto)
                                        <option value="{{ $producto->id }}" @selected(($linea['producto_id'] ?? '') === $producto->id)>{{ $producto->nombre }} ({{ $producto->codigoUnidad() }})</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('lineas.'.$indice.'.producto_id')" class="mt-2" />
                            </td>
                            <td class="px-4 py-3">
                                <x-text-input name="lineas[{{ $indice }}][descripcion]" class="block h-10 w-full" :value="$linea['descripcion'] ?? ''" maxlength="191" />
                                <x-input-error :messages="$errors->get('lineas.'.$indice.'.descripcion')" class="mt-2" />
                            </td>
                            <td class="px-4 py-3">
                                <x-text-input name="lineas[{{ $indice }}][cantidad]" type="number" step="0.001" min="0" class="block h-10 w-full" :value="$linea['cantidad'] ?? ''" />
                                <x-input-error :messages="$errors->get('lineas.'.$indice.'.cantidad')" class="mt-2" />
                            </td>
                            <td class="px-4 py-3">
                                <x-text-input name="lineas[{{ $indice }}][coste_unitario]" type="number" step="0.01" min="0" class="block h-10 w-full" :value="$linea['coste_unitario'] ?? ''" />
                                <x-input-error :messages="$errors->get('lineas.'.$indice.'.coste_unitario')" class="mt-2" />
                            </td>
                            <td class="px-4 py-3">
                                <x-text-input name="lineas[{{ $indice }}][iva_porcentaje]" type="number" step="0.01" min="0" max="100" class="block h-10 w-full" :value="$linea['iva_porcentaje'] ?? 21" />
                                <x-input-error :messages="$errors->get('lineas.'.$indice.'.iva_porcentaje')" class="mt-2" />
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <template data-plantilla-linea>
            <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                <td class="px-4 py-3">
                    <select data-field="producto_id" class="admin-input block h-10 w-full">
                        <option value="">Seleccionar</option>
                        @foreach ($productos as $producto)
                            <option value="{{ $producto->id }}">{{ $producto->nombre }} ({{ $producto->codigoUnidad() }})</option>
                        @endforeach
                    </select>
                </td>
                <td class="px-4 py-3">
                    <input data-field="descripcion" class="admin-input block h-10 w-full rounded-md border-input bg-background text-foreground shadow-sm focus:border-ring focus:ring-ring" maxlength="191" type="text">
                </td>
                <td class="px-4 py-3">
                    <input data-field="cantidad" class="admin-input block h-10 w-full rounded-md border-input bg-background text-foreground shadow-sm focus:border-ring focus:ring-ring" type="number" step="0.001" min="0">
                </td>
                <td class="px-4 py-3">
                    <input data-field="coste_unitario" class="admin-input block h-10 w-full rounded-md border-input bg-background text-foreground shadow-sm focus:border-ring focus:ring-ring" type="number" step="0.01" min="0">
                </td>
                <td class="px-4 py-3">
                    <input data-field="iva_porcentaje" class="admin-input block h-10 w-full rounded-md border-input bg-background text-foreground shadow-sm focus:border-ring focus:ring-ring" type="number" step="0.01" min="0" max="100" value="21">
                </td>
            </tr>
        </template>
    </section>

    <div class="flex items-center justify-end gap-3 border-t border-border pt-4">
        <a href="{{ route('admin.compras.pedidos.index') }}" class="admin-btn-outline">Cancelar</a>
        <x-primary-button>Guardar pedido</x-primary-button>
    </div>
</form>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-pedido-lineas]').forEach((contenedor) => {
                const tbody = contenedor.querySelector('[data-lineas-tbody]');
                const plantilla = contenedor.querySelector('[data-plantilla-linea]');
                const botonAnadir = contenedor.querySelector('[data-anadir-linea]');
                const botonAjustar = contenedor.querySelector('[data-ajustar-lineas]');
                const inputTotal = contenedor.querySelector('[data-total-lineas]');

                if (!tbody || !plantilla || !botonAnadir || !botonAjustar || !inputTotal) {
                    return;
                }

                const contarLineas = () => tbody.querySelectorAll('tr').length;
                const sincronizarInput = () => {
                    inputTotal.value = contarLineas();
                };
                const prepararLinea = (fila, indice) => {
                    fila.querySelectorAll('[data-field]').forEach((campo) => {
                        campo.name = `lineas[${indice}][${campo.dataset.field}]`;
                    });
                };
                const anadirLinea = () => {
                    const indice = contarLineas();
                    const fragmento = plantilla.content.cloneNode(true);
                    const fila = fragmento.querySelector('tr');

                    prepararLinea(fila, indice);
                    tbody.appendChild(fragmento);
                    sincronizarInput();
                };
                const ajustarLineas = () => {
                    const objetivo = Math.max(1, Math.min(100, Number(inputTotal.value || 1)));

                    while (contarLineas() < objetivo) {
                        anadirLinea();
                    }

                    while (contarLineas() > objetivo) {
                        tbody.lastElementChild?.remove();
                    }

                    sincronizarInput();
                };

                botonAnadir.addEventListener('click', anadirLinea);
                botonAjustar.addEventListener('click', ajustarLineas);
                sincronizarInput();
            });
        });
    </script>
@endonce
