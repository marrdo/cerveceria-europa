<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Web publica" description="Gestiona carta, cervezas, fuera de carta, recomendaciones y blog.">
            <x-slot name="actions">
                <a href="{{ route('admin.web-publica.contenidos.create') }}" class="admin-btn-primary">Nuevo contenido</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.web-publica.partials.nav')

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    @if ($moduloWebPublica && auth()->user()?->rol === \App\Enums\RolUsuario::Superadmin)
        <section class="admin-card mb-4 flex flex-wrap items-center justify-between gap-4 p-4">
            <div>
                <h2 class="text-base font-semibold text-foreground">{{ $moduloWebPublica->nombre }}</h2>
                <p class="mt-1 text-sm text-muted-foreground">{{ $moduloWebPublica->descripcion }}</p>
                <p class="mt-1 text-xs text-muted-foreground">Si esta desactivado, la web publica responde 404 y el propietario no ve este modulo en el panel.</p>
            </div>
            <form method="POST" action="{{ route('admin.modulos.toggle', $moduloWebPublica) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="{{ $moduloWebPublica->activo ? 'admin-btn-outline' : 'admin-btn-primary' }}">
                    {{ $moduloWebPublica->activo ? 'Desactivar web publica' : 'Activar web publica' }}
                </button>
            </form>
        </section>
    @endif

    @if ($moduloBlog && auth()->user()?->rol === \App\Enums\RolUsuario::Superadmin)
        <section class="admin-card mb-4 flex flex-wrap items-center justify-between gap-4 p-4">
            <div>
                <h2 class="text-base font-semibold text-foreground">{{ $moduloBlog->nombre }}</h2>
                <p class="mt-1 text-sm text-muted-foreground">{{ $moduloBlog->descripcion }}</p>
            </div>
            <form method="POST" action="{{ route('admin.modulos.toggle', $moduloBlog) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="{{ $moduloBlog->activo ? 'admin-btn-outline' : 'admin-btn-primary' }}">
                    {{ $moduloBlog->activo ? 'Desactivar blog' : 'Activar blog' }}
                </button>
            </form>
        </section>
    @endif

    <form method="GET" action="{{ route('admin.web-publica.contenidos.index') }}" class="admin-card mb-4 grid gap-3 p-4 md:grid-cols-5">
        <div>
            <x-input-label for="busqueda" value="Titulo" />
            <x-text-input id="busqueda" name="busqueda" class="mt-1 block h-10 w-full" :value="$filtros['busqueda']" maxlength="191" />
        </div>
        <div>
            <x-input-label for="tipo" value="Tipo" />
            <select id="tipo" name="tipo" class="admin-input mt-1 block h-10 w-full">
                <option value="">Todos</option>
                @foreach ($tipos as $tipo)
                    <option value="{{ $tipo->value }}" @selected($filtros['tipo'] === $tipo->value)>{{ $tipo->etiqueta() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="categoria_carta_id" value="Categoria carta" />
            <select id="categoria_carta_id" name="categoria_carta_id" class="admin-input mt-1 block h-10 w-full">
                <option value="">Todas</option>
                @foreach ($categoriasCarta as $categoriaCarta)
                    <option value="{{ $categoriaCarta->id }}" @selected($filtros['categoria_carta_id'] === $categoriaCarta->id)>{{ $categoriaCarta->nombreJerarquico() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="publicado" value="Publicado" />
            <select id="publicado" name="publicado" class="admin-input mt-1 block h-10 w-full">
                <option value="">Todos</option>
                <option value="1" @selected($filtros['publicado'] === '1')>Publicado</option>
                <option value="0" @selected($filtros['publicado'] === '0')>Oculto</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="admin-btn-primary">Filtrar</button>
            <a href="{{ route('admin.web-publica.contenidos.index') }}" class="admin-btn-outline">Limpiar</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg border border-border bg-card">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/50">
                    <th class="px-4 py-3 text-left font-medium text-foreground">Contenido</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Tipo / categoria / inventario</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Estados</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Precio</th>
                    <th class="px-4 py-3 text-right font-medium text-foreground">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contenidos as $contenido)
                    <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                        <td class="px-4 py-3">
                            <div class="font-medium text-foreground">{{ $contenido->titulo }}</div>
                            <div class="text-xs text-muted-foreground">{{ $contenido->descripcion_corta ?: 'Sin descripcion corta' }}</div>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            <div>{{ $contenido->tipo->etiqueta() }}</div>
                            <div class="text-xs">
                                {{ $contenido->categoriaCarta?->nombreJerarquico() ?? 'Sin categoria de carta' }}
                            </div>
                            @if ($contenido->producto)
                                <div class="text-xs">
                                    {{ $contenido->producto->nombre }} · stock {{ $contenido->producto->formatearCantidad($contenido->producto->cantidadStock()) }} {{ $contenido->producto->codigoUnidad() }}
                                </div>
                            @else
                                <div class="text-xs">Sin producto vinculado</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('admin.web-publica.contenidos.toggle', [$contenido, 'publicado']) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"><x-admin.status-badge :variant="$contenido->publicado ? 'success' : 'default'">{{ $contenido->publicado ? 'Publicado' : 'Oculto' }}</x-admin.status-badge></button>
                                </form>
                                <form method="POST" action="{{ route('admin.web-publica.contenidos.toggle', [$contenido, 'destacado']) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"><x-admin.status-badge :variant="$contenido->destacado ? 'warning' : 'default'">{{ $contenido->destacado ? 'Destacado' : 'No destacado' }}</x-admin.status-badge></button>
                                </form>
                                <form method="POST" action="{{ route('admin.web-publica.contenidos.toggle', [$contenido, 'fuera_carta']) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"><x-admin.status-badge :variant="$contenido->fuera_carta ? 'info' : 'default'">{{ $contenido->fuera_carta ? 'Fuera carta' : 'Carta' }}</x-admin.status-badge></button>
                                </form>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            @if ($contenido->tieneTarifas())
                                <div class="space-y-1 text-xs">
                                    @foreach ($contenido->tarifas as $tarifa)
                                        <div>{{ $tarifa->nombre ?: 'Precio' }}: {{ $tarifa->precioFormateado() }}</div>
                                    @endforeach
                                </div>
                            @else
                                {{ $contenido->precioFormateado() ?? '-' }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.web-publica.contenidos.edit', $contenido) }}" class="text-primary hover:underline">Editar</a>
                            <form method="POST" action="{{ route('admin.web-publica.contenidos.destroy', $contenido) }}" class="inline" onsubmit="return confirm('Eliminar este contenido?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ms-3 text-destructive hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-muted-foreground">Todavia no hay contenido web.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $contenidos->links() }}</div>
</x-app-layout>
