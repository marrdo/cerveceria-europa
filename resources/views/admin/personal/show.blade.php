<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="$usuario->nombre" description="Ficha del usuario operativo.">
            <x-slot name="actions">
                <a href="{{ route('admin.personal.usuarios.edit', $usuario) }}" class="admin-btn-primary">Editar</a>
                <a href="{{ route('admin.personal.index') }}" class="admin-btn-outline">Volver</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <div class="grid gap-4 lg:grid-cols-[1fr_320px]">
        <section class="admin-card p-4">
            <h2 class="text-base font-semibold text-foreground">Datos principales</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase text-muted-foreground">Nombre</dt>
                    <dd class="mt-1 text-sm text-foreground">{{ $usuario->nombre }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-muted-foreground">Email</dt>
                    <dd class="mt-1 text-sm text-foreground">{{ $usuario->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-muted-foreground">Rol</dt>
                    <dd class="mt-1"><x-admin.status-badge>{{ $usuario->rol->etiqueta() }}</x-admin.status-badge></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-muted-foreground">Email verificado</dt>
                    <dd class="mt-1 text-sm text-foreground">{{ $usuario->email_verified_at?->format('d/m/Y H:i') ?? 'No' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-muted-foreground">Alta</dt>
                    <dd class="mt-1 text-sm text-foreground">{{ $usuario->created_at?->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-muted-foreground">Ultima actualizacion</dt>
                    <dd class="mt-1 text-sm text-foreground">{{ $usuario->updated_at?->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </section>

        <aside class="admin-card p-4">
            <h2 class="text-base font-semibold text-foreground">Acciones</h2>
            <div class="mt-4 space-y-2">
                <a href="{{ route('admin.personal.usuarios.edit', $usuario) }}" class="admin-btn-outline w-full">Editar usuario</a>
                <form method="POST" action="{{ route('admin.personal.usuarios.destroy', $usuario) }}" onsubmit="return confirm('Vas a eliminar este usuario.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="admin-btn-outline w-full text-destructive">Eliminar usuario</button>
                </form>
            </div>
        </aside>
    </div>
</x-app-layout>
