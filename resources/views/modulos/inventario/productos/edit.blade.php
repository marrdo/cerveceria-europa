<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Editar producto" :description="$producto->nombre" />
    </x-slot>

    <div class="max-w-4xl">
            @include('modulos.inventario.partials.nav')
            @include('modulos.inventario.productos.partials.form', [
                'action' => route('admin.inventario.productos.update', $producto),
                'method' => 'PUT',
            ])
    </div>
</x-app-layout>
