<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel de administracion
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500">Inventario</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">Pendiente</p>
                        <p class="mt-2 text-sm text-gray-600">Productos, ubicaciones, stock y movimientos.</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500">Compras</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">Pendiente</p>
                        <p class="mt-2 text-sm text-gray-600">Pedidos, recepciones y proveedores.</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500">Usuario</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ auth()->user()->rol->value }}</p>
                        <p class="mt-2 text-sm text-gray-600">{{ auth()->user()->nombre }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
