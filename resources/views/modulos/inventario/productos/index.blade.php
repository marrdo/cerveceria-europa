<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Productos</h2>
            <a href="{{ route('admin.inventario.productos.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white">Nuevo producto</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('modulos.inventario.partials.nav')

            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Categoria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($productos as $producto)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $producto->nombre }}</div>
                                    <div class="text-xs text-gray-500">{{ $producto->sku ?? 'Sin SKU' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $producto->categoria?->nombre }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $producto->formatearCantidad($producto->cantidadStock()) }} {{ $producto->codigoUnidad() }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $producto->estadoStock()->etiqueta() }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('admin.inventario.productos.stock', $producto) }}" class="text-gray-700 hover:text-gray-900">Stock</a>
                                    <a href="{{ route('admin.inventario.productos.edit', $producto) }}" class="ms-3 text-indigo-600 hover:text-indigo-900">Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No hay productos todavia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $productos->links() }}</div>
        </div>
    </div>
</x-app-layout>
