<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="$recinto->exists ? 'Editar recinto' : 'Nuevo recinto'" description="Datos operativos del local" />
    </x-slot>

    @include('modulos.espacios.partials.nav')

    <form method="POST" action="{{ $recinto->exists ? route('admin.espacios.recintos.update', $recinto) : route('admin.espacios.recintos.store') }}" class="admin-card max-w-4xl space-y-5 p-6">
        @csrf
        @if ($recinto->exists)
            @method('PUT')
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="nombre_comercial" value="Nombre comercial" />
                <x-text-input id="nombre_comercial" name="nombre_comercial" class="mt-1 block h-10 w-full" :value="old('nombre_comercial', $recinto->nombre_comercial)" required maxlength="191" />
                <x-input-error :messages="$errors->get('nombre_comercial')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="nombre_fiscal" value="Nombre fiscal" />
                <x-text-input id="nombre_fiscal" name="nombre_fiscal" class="mt-1 block h-10 w-full" :value="old('nombre_fiscal', $recinto->nombre_fiscal)" maxlength="191" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="direccion" value="Direccion" />
                <x-text-input id="direccion" name="direccion" class="mt-1 block h-10 w-full" :value="old('direccion', $recinto->direccion)" maxlength="191" />
            </div>
            <div>
                <x-input-label for="localidad" value="Localidad" />
                <x-text-input id="localidad" name="localidad" class="mt-1 block h-10 w-full" :value="old('localidad', $recinto->localidad)" maxlength="100" />
            </div>
            <div>
                <x-input-label for="provincia" value="Provincia" />
                <x-text-input id="provincia" name="provincia" class="mt-1 block h-10 w-full" :value="old('provincia', $recinto->provincia)" maxlength="100" />
            </div>
            <div>
                <x-input-label for="codigo_postal" value="Codigo postal" />
                <x-text-input id="codigo_postal" name="codigo_postal" class="mt-1 block h-10 w-full" :value="old('codigo_postal', $recinto->codigo_postal)" maxlength="20" />
            </div>
            <div>
                <x-input-label for="pais" value="Pais" />
                <x-text-input id="pais" name="pais" class="mt-1 block h-10 w-full" :value="old('pais', $recinto->pais ?: 'Espana')" maxlength="100" />
            </div>
            <div>
                <x-input-label for="telefono" value="Telefono" />
                <x-text-input id="telefono" name="telefono" class="mt-1 block h-10 w-full" :value="old('telefono', $recinto->telefono)" maxlength="30" />
                <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block h-10 w-full" :value="old('email', $recinto->email)" maxlength="191" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="notas" value="Notas internas" />
            <textarea id="notas" name="notas" rows="4" class="admin-input mt-1 block w-full">{{ old('notas', $recinto->notas) }}</textarea>
        </div>

        <label class="flex items-center gap-2 text-sm text-foreground">
            <input type="checkbox" name="activo" value="1" class="rounded border-input bg-background text-primary focus:ring-ring" @checked(old('activo', $recinto->activo ?? true))>
            Activo
        </label>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.espacios.recintos.index') }}" class="admin-btn-outline">Cancelar</a>
            <button type="submit" class="admin-btn-primary">Guardar</button>
        </div>
    </form>
</x-app-layout>
