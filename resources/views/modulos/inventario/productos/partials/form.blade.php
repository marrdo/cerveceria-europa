<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <section class="admin-card p-4 lg:p-6">
        <h2 class="mb-4 text-base font-semibold text-foreground">Informacion basica</h2>
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <x-input-label for="nombre" value="Nombre" />
            <x-text-input id="nombre" name="nombre" class="mt-1 block h-10 w-full" :value="old('nombre', $producto->nombre)" required />
            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="sku" value="SKU interno" />
            <x-text-input id="sku" name="sku" class="mt-1 block h-10 w-full" :value="old('sku', $producto->sku)" />
            <x-input-error :messages="$errors->get('sku')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="categoria_producto_id" value="Categoria" />
            <select id="categoria_producto_id" name="categoria_producto_id" class="admin-input mt-1 block h-10 w-full shadow-sm" required>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected(old('categoria_producto_id', $producto->categoria_producto_id) === $categoria->id)>{{ $categoria->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="unidad_inventario_id" value="Unidad" />
            <select id="unidad_inventario_id" name="unidad_inventario_id" class="admin-input mt-1 block h-10 w-full shadow-sm" required>
                @foreach ($unidades as $unidad)
                    <option value="{{ $unidad->id }}" @selected(old('unidad_inventario_id', $producto->unidad_inventario_id) === $unidad->id)>{{ $unidad->nombre }} ({{ $unidad->codigo }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="proveedor_id" value="Proveedor principal" />
            <select id="proveedor_id" name="proveedor_id" class="admin-input mt-1 block h-10 w-full shadow-sm">
                <option value="">Sin proveedor</option>
                @foreach ($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}" @selected(old('proveedor_id', $producto->proveedor_id) === $proveedor->id)>{{ $proveedor->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="referencia_proveedor" value="Referencia proveedor" />
            <x-text-input id="referencia_proveedor" name="referencia_proveedor" class="mt-1 block h-10 w-full" :value="old('referencia_proveedor', $producto->referencia_proveedor)" />
        </div>
        <div class="md:col-span-2">
            <x-input-label for="codigo_barras" value="Codigo de barras" />
            <x-text-input id="codigo_barras" name="codigo_barras" class="mt-1 block h-10 w-full" :value="old('codigo_barras', $producto->codigo_barras)" />
        </div>
    </div>

    <div class="mt-4">
        <x-input-label for="descripcion" value="Descripcion" />
        <textarea id="descripcion" name="descripcion" rows="4" class="admin-input mt-1 block w-full shadow-sm">{{ old('descripcion', $producto->descripcion) }}</textarea>
    </div>
    </section>

    <section class="admin-card p-4 lg:p-6">
        <h2 class="mb-4 text-base font-semibold text-foreground">Precios y stock</h2>
        <div class="grid gap-4 md:grid-cols-3">
        <div>
            <x-input-label for="precio_venta" value="Precio venta" />
            <x-text-input id="precio_venta" name="precio_venta" type="number" step="0.01" min="0" class="mt-1 block h-10 w-full" :value="old('precio_venta', $producto->precio_venta ?? 0)" required />
        </div>
        <div>
            <x-input-label for="precio_coste" value="Precio coste" />
            <x-text-input id="precio_coste" name="precio_coste" type="number" step="0.01" min="0" class="mt-1 block h-10 w-full" :value="old('precio_coste', $producto->precio_coste)" />
        </div>
        <div>
            <x-input-label for="cantidad_alerta_stock" value="Alerta de stock" />
            <x-text-input id="cantidad_alerta_stock" name="cantidad_alerta_stock" type="number" step="0.001" min="0" class="mt-1 block h-10 w-full" :value="old('cantidad_alerta_stock', $producto->cantidad_alerta_stock ?? 0)" required />
            <p class="mt-1 text-xs text-muted-foreground">Aviso cuando el stock baje de este nivel.</p>
        </div>
        </div>
    </section>

    <section class="admin-card flex flex-wrap gap-6 p-4 text-sm text-foreground">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="controla_stock" value="1" class="rounded border-input bg-background text-primary focus:ring-ring" @checked(old('controla_stock', $producto->controla_stock ?? true))>
            Controla stock
        </label>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="controla_caducidad" value="1" class="rounded border-input bg-background text-primary focus:ring-ring" @checked(old('controla_caducidad', $producto->controla_caducidad ?? false))>
            Controla caducidad
        </label>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="activo" value="1" class="rounded border-input bg-background text-primary focus:ring-ring" @checked(old('activo', $producto->activo ?? true))>
            Activo
        </label>
    </section>

    <div class="flex items-center justify-end gap-3 border-t border-border pt-4">
        <a href="{{ route('admin.inventario.productos.index') }}" class="admin-btn-outline">Cancelar</a>
        <x-primary-button>Guardar</x-primary-button>
    </div>
</form>
