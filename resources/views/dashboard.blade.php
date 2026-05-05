<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Dashboard" description="Resumen del estado actual del inventario y la operativa." />
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-admin.kpi-card title="Productos" :value="$totalProductos ?? '0'" description="Catalogo activo" icon="P" />
        <x-admin.kpi-card title="Bajo stock" :value="$productosBajoStock ?? '0'" description="Requieren reposicion" variant="warning" icon="!" />
        <x-admin.kpi-card title="Movimientos" :value="$movimientosRecientes ?? '0'" description="Registrados recientemente" variant="success" icon="M" />
        <x-admin.kpi-card title="Usuario" :value="auth()->user()->rol->value" :description="auth()->user()->nombre" icon="U" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-semibold text-foreground">Accesos rapidos</h2>
            </div>
            <div class="admin-card p-4">
                <div class="flex flex-wrap gap-2">
                    @if (auth()->user()->puedeAccederModulo('inventario'))
                        <a href="{{ route('admin.inventario.productos.create') }}" class="admin-btn-outline">Nuevo producto</a>
                        <a href="{{ route('admin.inventario.productos.index') }}" class="admin-btn-outline">Ver inventario</a>
                        <a href="{{ route('admin.inventario.proveedores.index') }}" class="admin-btn-outline">Proveedores</a>
                        <a href="{{ route('admin.inventario.ubicaciones.index') }}" class="admin-btn-outline">Ubicaciones</a>
                    @endif
                    @if (auth()->user()->puedeAccederModulo('compras'))
                        <a href="{{ route('admin.compras.pedidos.index') }}" class="admin-btn-outline">Pedidos de compra</a>
                    @endif
                    @if (auth()->user()->puedeAccederModulo('ventas'))
                        <a href="{{ route('admin.ventas.comandas.create') }}" class="admin-btn-outline">Nueva comanda</a>
                        <a href="{{ route('admin.ventas.comandas.index') }}" class="admin-btn-outline">Comandas abiertas</a>
                    @endif
                    @if (auth()->user()->puedeAccederModulo('espacios'))
                        <a href="{{ route('admin.espacios.recintos.index') }}" class="admin-btn-outline">Espacios</a>
                        <a href="{{ route('admin.espacios.mesas.index') }}" class="admin-btn-outline">Mesas</a>
                    @endif
                    @if (auth()->user()->puedeAccederModulo('personal'))
                        <a href="{{ route('admin.personal.usuarios.create') }}" class="admin-btn-outline">Anadir usuario</a>
                        <a href="{{ route('admin.personal.index') }}" class="admin-btn-outline">Personal</a>
                    @endif
                    @if (auth()->user()->puedeAccederModulo('web_publica'))
                        <a href="{{ route('admin.web-publica.contenidos.index') }}" class="admin-btn-outline">Gestionar web</a>
                        @if (\App\Models\Modulo::activo('web_publica'))
                            <a href="{{ route('web.inicio') }}" class="admin-btn-outline">Ver web publica</a>
                        @endif
                    @endif
                </div>
            </div>
        </section>

        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-semibold text-foreground">Siguientes modulos</h2>
            </div>
            <div class="admin-card p-4">
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between border-b border-border pb-3">
                        <span class="text-foreground">Compras a proveedor</span>
                        <x-admin.status-badge variant="warning">Pendiente</x-admin.status-badge>
                    </div>
                    <div class="flex items-center justify-between border-b border-border pb-3">
                        <span class="text-foreground">Recepciones conectadas con stock</span>
                        <x-admin.status-badge variant="warning">Pendiente</x-admin.status-badge>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-foreground">Lectura asistida de albaranes</span>
                        <x-admin.status-badge variant="info">Roadmap</x-admin.status-badge>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @if (auth()->user()->rol === \App\Enums\RolUsuario::Superadmin)
        <section class="mt-6">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-semibold text-foreground">Modulos contratados</h2>
            </div>
            <div class="admin-card overflow-hidden">
                <div class="border-b border-border p-4">
                    <p class="text-sm text-muted-foreground">Zona tecnica solo para superadmin. Sirve para activar o desactivar partes vendibles del proyecto segun lo que tenga contratado el cliente.</p>
                </div>

                <div class="divide-y divide-border">
                    @forelse (($modulos ?? collect()) as $modulo)
                        <div class="flex flex-wrap items-center justify-between gap-4 p-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-semibold text-foreground">{{ $modulo->nombre }}</h3>
                                    <x-admin.status-badge :variant="$modulo->activo ? 'success' : 'default'">{{ $modulo->activo ? 'Activo' : 'Inactivo' }}</x-admin.status-badge>
                                </div>
                                <p class="mt-1 text-sm text-muted-foreground">{{ $modulo->descripcion ?: 'Sin descripcion.' }}</p>
                                @if ($modulo->clave === 'web_publica')
                                    <p class="mt-1 text-xs text-muted-foreground">Al desactivarlo, la web publica devuelve 404 y el propietario deja de ver el modulo.</p>
                                @elseif ($modulo->clave === 'blog')
                                    <p class="mt-1 text-xs text-muted-foreground">Al desactivarlo, se ocultan enlace, rutas y administracion del blog.</p>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('admin.modulos.toggle', $modulo) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="{{ $modulo->activo ? 'admin-btn-outline' : 'admin-btn-primary' }}">
                                    {{ $modulo->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="p-4 text-sm text-muted-foreground">No hay modulos configurables. Ejecuta `php artisan db:seed --class=ModuloSeeder`.</div>
                    @endforelse
                </div>
            </div>
        </section>
    @endif
</x-app-layout>
