<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Revisar borrador" :description="$borrador->documento->nombre_original">
            <x-slot name="actions">
                <a href="{{ route('admin.compras.documentos.show', $borrador->documento) }}" class="admin-btn-outline">Volver</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.compras.partials.nav')

    @php
        $datos = $borrador->datos_borrador ?? [];
        $lineasFormulario = old('lineas', $datos['lineas'] ?? []);
        $lineasFormulario = array_pad($lineasFormulario, max(5, count($lineasFormulario)), []);
    @endphp

    <form method="POST" action="{{ route('admin.compras.documentos.borradores.update', $borrador) }}" class="space-y-6">
        @csrf

        <section class="admin-card p-4 lg:p-6">
            <h2 class="mb-4 text-base font-semibold text-foreground">Datos del documento</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-input-label for="proveedor_id" value="Proveedor" />
                    <select id="proveedor_id" name="proveedor_id" class="admin-input mt-1 block h-10 w-full">
                        <option value="">Sin proveedor</option>
                        @foreach ($proveedores as $proveedor)
                            <option value="{{ $proveedor->id }}" @selected(old('proveedor_id', $datos['proveedor_id'] ?? $borrador->documento->proveedor_id) === $proveedor->id)>{{ $proveedor->nombre }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-muted-foreground">Proveedor detectado o elegido manualmente antes de crear el pedido.</p>
                    <x-input-error :messages="$errors->get('proveedor_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="fecha_documento" value="Fecha documento" />
                    <x-text-input id="fecha_documento" name="fecha_documento" type="date" class="mt-1 block h-10 w-full" :value="old('fecha_documento', $datos['fecha_documento'] ?? '')" />
                    <p class="mt-1 text-xs text-muted-foreground">Fecha visible en la factura o albaran. Se usara como fecha del pedido si se genera.</p>
                    <x-input-error :messages="$errors->get('fecha_documento')" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="numero_documento" value="Numero documento" />
                    <x-text-input id="numero_documento" name="numero_documento" class="mt-1 block h-10 w-full" :value="old('numero_documento', $datos['numero_documento'] ?? '')" maxlength="100" />
                    <p class="mt-1 text-xs text-muted-foreground">Numero de factura o albaran si aparece en el documento original.</p>
                    <x-input-error :messages="$errors->get('numero_documento')" class="mt-2" />
                </div>
            </div>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-border p-4">
                <h2 class="text-base font-semibold text-foreground">Lineas revisadas</h2>
                <p class="mt-1 text-sm text-muted-foreground">Ahora mismo el sistema no analiza la imagen automaticamente. Introduce las lineas manualmente o corrige las que rellene un OCR o IA cuando lo conectemos. El producto debe existir en el catalogo para poder crear el pedido.</p>
                <x-input-error :messages="$errors->get('lineas')" class="mt-2" />
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50 align-top">
                            <th class="min-w-64 px-4 py-3 text-left font-medium text-foreground">Producto</th>
                            <th class="min-w-56 px-4 py-3 text-left font-medium text-foreground">Descripcion</th>
                            <th class="w-32 px-4 py-3 text-left font-medium text-foreground">Cantidad</th>
                            <th class="w-36 px-4 py-3 text-left font-medium text-foreground">Coste sin IVA</th>
                            <th class="w-28 px-4 py-3 text-left font-medium text-foreground">IVA %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lineasFormulario as $indice => $linea)
                            <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                                <td class="px-4 py-3">
                                    <select name="lineas[{{ $indice }}][producto_id]" class="admin-input block h-10 w-full">
                                        <option value="">Sin producto</option>
                                        @foreach ($productos as $producto)
                                            <option value="{{ $producto->id }}" @selected(($linea['producto_id'] ?? '') === $producto->id)>{{ $producto->nombre }} ({{ $producto->codigoUnidad() }})</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('lineas.'.$indice.'.producto_id')" class="mt-2" />
                                </td>
                                <td class="px-4 py-3">
                                    <x-text-input name="lineas[{{ $indice }}][descripcion]" class="block h-10 w-full" :value="$linea['descripcion'] ?? ''" maxlength="191" />
                                </td>
                                <td class="px-4 py-3">
                                    <x-text-input name="lineas[{{ $indice }}][cantidad]" type="number" step="0.001" min="0.001" class="block h-10 w-full" :value="$linea['cantidad'] ?? ''" />
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card p-4 lg:p-6">
            <x-input-label for="notas_revision" value="Notas de revision" />
            <textarea id="notas_revision" name="notas_revision" rows="4" class="admin-input mt-1 block w-full">{{ old('notas_revision', $borrador->notas_revision) }}</textarea>
            <p class="mt-1 text-xs text-muted-foreground">Anota dudas, correcciones hechas o cualquier decision antes de crear el pedido.</p>
            <x-input-error :messages="$errors->get('notas_revision')" class="mt-2" />
        </section>

        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-border pt-4">
            <a href="{{ route('admin.compras.documentos.show', $borrador->documento) }}" class="admin-btn-outline">Cancelar</a>
            <button type="submit" class="admin-btn-outline">Guardar revision</button>
            @if (! $borrador->pedido_compra_id)
                <button type="submit" formaction="{{ route('admin.compras.documentos.borradores.generar-pedido', $borrador) }}" class="admin-btn-primary">Generar pedido borrador</button>
            @endif
        </div>
    </form>
</x-app-layout>
