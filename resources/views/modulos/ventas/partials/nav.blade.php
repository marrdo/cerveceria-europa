<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('admin.ventas.comandas.create') }}" class="{{ request()->routeIs('admin.ventas.comandas.create') ? 'admin-btn-primary' : 'admin-btn-outline' }}">Nueva comanda</a>
    <a href="{{ route('admin.ventas.comandas.index') }}" class="{{ request()->routeIs('admin.ventas.comandas.index') || request()->routeIs('admin.ventas.comandas.show') ? 'admin-btn-primary' : 'admin-btn-outline' }}">Comandas</a>
</div>
