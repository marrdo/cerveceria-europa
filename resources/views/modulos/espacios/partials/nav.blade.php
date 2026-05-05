<nav class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('admin.espacios.recintos.index') }}" class="admin-btn-outline {{ request()->routeIs('admin.espacios.recintos.*') ? 'border-primary text-primary' : '' }}">Recintos</a>
    <a href="{{ route('admin.espacios.zonas.index') }}" class="admin-btn-outline {{ request()->routeIs('admin.espacios.zonas.*') ? 'border-primary text-primary' : '' }}">Zonas</a>
    <a href="{{ route('admin.espacios.mesas.index') }}" class="admin-btn-outline {{ request()->routeIs('admin.espacios.mesas.*') ? 'border-primary text-primary' : '' }}">Mesas</a>
</nav>
