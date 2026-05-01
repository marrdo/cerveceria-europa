<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('admin.inventario.productos.index') }}" class="rounded-md border px-3 py-2 text-sm {{ request()->routeIs('admin.inventario.productos.*') ? 'bg-gray-900 text-white' : 'bg-white text-gray-700' }}">Productos</a>
    <a href="{{ route('admin.inventario.proveedores.index') }}" class="rounded-md border px-3 py-2 text-sm {{ request()->routeIs('admin.inventario.proveedores.*') ? 'bg-gray-900 text-white' : 'bg-white text-gray-700' }}">Proveedores</a>
    <a href="{{ route('admin.inventario.categorias.index') }}" class="rounded-md border px-3 py-2 text-sm {{ request()->routeIs('admin.inventario.categorias.*') ? 'bg-gray-900 text-white' : 'bg-white text-gray-700' }}">Categorias</a>
    <a href="{{ route('admin.inventario.unidades.index') }}" class="rounded-md border px-3 py-2 text-sm {{ request()->routeIs('admin.inventario.unidades.*') ? 'bg-gray-900 text-white' : 'bg-white text-gray-700' }}">Unidades</a>
    <a href="{{ route('admin.inventario.ubicaciones.index') }}" class="rounded-md border px-3 py-2 text-sm {{ request()->routeIs('admin.inventario.ubicaciones.*') ? 'bg-gray-900 text-white' : 'bg-white text-gray-700' }}">Ubicaciones</a>
</div>
