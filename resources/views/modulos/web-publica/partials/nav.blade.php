<nav class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('admin.web-publica.contenidos.index') }}" class="admin-btn-outline {{ request()->routeIs('admin.web-publica.contenidos.*') ? 'border-primary text-primary' : '' }}">Contenidos</a>
    <a href="{{ route('admin.web-publica.carta-categorias.index') }}" class="admin-btn-outline {{ request()->routeIs('admin.web-publica.carta-categorias.*') ? 'border-primary text-primary' : '' }}">Categorias carta</a>
    <a href="{{ route('admin.web-publica.secciones.index') }}" class="admin-btn-outline {{ request()->routeIs('admin.web-publica.secciones.*') ? 'border-primary text-primary' : '' }}">Secciones</a>
    @if (\App\Models\Modulo::activo('blog'))
        <a href="{{ route('admin.web-publica.blog.index') }}" class="admin-btn-outline {{ request()->routeIs('admin.web-publica.blog.*') ? 'border-primary text-primary' : '' }}">Blog</a>
        <a href="{{ route('admin.web-publica.blog-categorias.index') }}" class="admin-btn-outline {{ request()->routeIs('admin.web-publica.blog-categorias.*') ? 'border-primary text-primary' : '' }}">Categorias blog</a>
    @endif
    <a href="{{ route('web.inicio') }}" target="_blank" class="admin-btn-outline">Ver web publica</a>
</nav>
