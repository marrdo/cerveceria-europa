<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Personal" description="Usuarios operativos que puedes gestionar.">
            <x-slot name="actions">
                <a href="{{ route('admin.personal.usuarios.create') }}" class="admin-btn-primary">Anadir usuario</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <div class="admin-card overflow-hidden">
        <div class="border-b border-border p-4">
            <p class="text-sm text-muted-foreground">
                Puedes crear: {{ $rolesGestionables->map(fn ($rol) => $rol->etiqueta())->join(', ') }}.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium text-foreground">Nombre</th>
                        <th class="px-4 py-3 text-left font-medium text-foreground">Email</th>
                        <th class="px-4 py-3 text-left font-medium text-foreground">Rol</th>
                        <th class="px-4 py-3 text-left font-medium text-foreground">Alta</th>
                        <th class="px-4 py-3 text-right font-medium text-foreground">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $usuario)
                        <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                            <td class="px-4 py-3 font-medium text-foreground">{{ $usuario->nombre }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $usuario->email }}</td>
                            <td class="px-4 py-3"><x-admin.status-badge>{{ $usuario->rol->etiqueta() }}</x-admin.status-badge></td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $usuario->created_at?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.personal.usuarios.show', $usuario) }}" class="rounded-md p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground" title="Ver ficha" aria-label="Ver ficha">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.personal.usuarios.edit', $usuario) }}" class="rounded-md p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground" title="Editar" aria-label="Editar">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L9.75 16.902 6 18l1.098-3.75L16.862 4.487Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 7.125 16.875 4.5M18 14v5.25A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.personal.usuarios.destroy', $usuario) }}" onsubmit="return confirm('Vas a eliminar este usuario. Esta accion se puede auditar, pero el usuario dejara de acceder.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md p-2 text-destructive transition hover:bg-destructive/10" title="Eliminar" aria-label="Eliminar">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m14.74 9-.346 9M9.606 18 9.26 9M19.5 6l-.867 13.142A2 2 0 0 1 16.638 21H7.362a2 2 0 0 1-1.995-1.858L4.5 6M21 6h-5.25M3 6h5.25m0 0V4.5A1.5 1.5 0 0 1 9.75 3h4.5a1.5 1.5 0 0 1 1.5 1.5V6m-7.5 0h7.5" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-muted-foreground">No hay usuarios para los roles que puedes gestionar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $usuarios->links() }}</div>
</x-app-layout>
