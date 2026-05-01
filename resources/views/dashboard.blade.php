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
                    <a href="{{ route('admin.inventario.productos.create') }}" class="admin-btn-outline">Nuevo producto</a>
                    <a href="{{ route('admin.inventario.productos.index') }}" class="admin-btn-outline">Ver inventario</a>
                    <a href="{{ route('admin.inventario.proveedores.index') }}" class="admin-btn-outline">Proveedores</a>
                    <a href="{{ route('admin.inventario.ubicaciones.index') }}" class="admin-btn-outline">Ubicaciones</a>
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
</x-app-layout>
