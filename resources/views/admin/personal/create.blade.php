<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Anadir usuario" description="Alta interna de camareros, encargados o perfiles permitidos por tu rol." />
    </x-slot>

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-destructive/25 bg-destructive/10 p-4 text-sm text-destructive">
            <p class="font-medium">Revisa los datos del usuario.</p>
            <ul class="mt-2 list-disc space-y-1 ps-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.personal.usuarios.store') }}" class="admin-card max-w-2xl space-y-4 p-4">
        @csrf

        <div>
            <x-input-label for="nombre" value="Nombre" />
            <x-text-input id="nombre" name="nombre" class="mt-1 block h-10 w-full" :value="old('nombre')" required maxlength="255" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block h-10 w-full" :value="old('email')" required maxlength="191" />
        </div>

        <div>
            <x-input-label for="rol" value="Rol" />
            <select id="rol" name="rol" class="admin-input mt-1 block h-10 w-full" required>
                @foreach ($rolesGestionables as $rol)
                    <option value="{{ $rol->value }}" @selected(old('rol') === $rol->value)>{{ $rol->etiqueta() }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="password" value="Contrasena" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block h-10 w-full" required autocomplete="new-password" />
            </div>
            <div>
                <x-input-label for="password_confirmation" value="Confirmar contrasena" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block h-10 w-full" required autocomplete="new-password" />
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.personal.index') }}" class="admin-btn-outline">Cancelar</a>
            <button type="submit" class="admin-btn-primary">Crear usuario</button>
        </div>
    </form>
</x-app-layout>
