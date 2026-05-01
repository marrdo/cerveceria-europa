<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Nuevo producto" description="Anade un producto al catalogo del bar" />
    </x-slot>

    <div class="max-w-4xl">
            @include('modulos.inventario.partials.nav')
            @include('modulos.inventario.productos.partials.form', [
                'producto' => new \App\Modulos\Inventario\Models\Producto(),
                'action' => route('admin.inventario.productos.store'),
                'method' => 'POST',
            ])
    </div>
</x-app-layout>
