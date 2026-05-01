<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $titulo }}s</h2>
            <a href="{{ route($rutaBase.'.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white">Nuevo</a>
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
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Codigo / contacto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($items as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->nombre }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $item->codigo ?? $item->email ?? $item->telefono ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $item->activo ? 'Activo' : 'Inactivo' }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route($rutaBase.'.edit', $item) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                    <form method="POST" action="{{ route($rutaBase.'.destroy', $item) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ms-3 text-red-600 hover:text-red-900">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No hay registros todavia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $items->links() }}</div>
        </div>
    </div>
</x-app-layout>
