<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo producto</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('modulos.inventario.partials.nav')
            @include('modulos.inventario.productos.partials.form', [
                'producto' => new \App\Modulos\Inventario\Models\Producto(),
                'action' => route('admin.inventario.productos.store'),
                'method' => 'POST',
            ])
        </div>
    </div>
</x-app-layout>
