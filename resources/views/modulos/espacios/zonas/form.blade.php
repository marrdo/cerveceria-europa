<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="$zona->exists ? 'Editar zona' : 'Nueva zona'" description="Areas operativas del recinto" />
    </x-slot>

    @include('modulos.espacios.partials.nav')

    <form method="POST" action="{{ $zona->exists ? route('admin.espacios.zonas.update', $zona) : route('admin.espacios.zonas.store') }}" class="admin-card max-w-3xl space-y-5 p-6">
        @csrf
        @if ($zona->exists)
            @method('PUT')
        @endif

        <div>
            <x-input-label for="recinto_id" value="Recinto" />
            <select id="recinto_id" name="recinto_id" class="admin-input mt-1 block h-10 w-full" required>
                <option value="">Selecciona recinto</option>
                @foreach ($recintos as $recinto)
                    <option value="{{ $recinto->id }}" @selected(old('recinto_id', $zona->recinto_id) === $recinto->id)>{{ $recinto->nombre_comercial }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('recinto_id')" class="mt-2" />
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-2">
                <x-input-label for="nombre" value="Nombre" />
                <x-text-input id="nombre" name="nombre" class="mt-1 block h-10 w-full" :value="old('nombre', $zona->nombre)" required maxlength="191" />
                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="orden" value="Orden" />
                <x-text-input id="orden" name="orden" type="number" min="0" class="mt-1 block h-10 w-full" :value="old('orden', $zona->orden ?? 0)" />
            </div>
            <div>
                <x-input-label for="codigo" value="Codigo" />
                <x-text-input id="codigo" name="codigo" class="mt-1 block h-10 w-full" :value="old('codigo', $zona->codigo)" maxlength="50" />
            </div>
        </div>

        <div>
            <x-input-label for="notas" value="Notas internas" />
            <textarea id="notas" name="notas" rows="4" class="admin-input mt-1 block w-full">{{ old('notas', $zona->notas) }}</textarea>
        </div>

        <label class="flex items-center gap-2 text-sm text-foreground">
            <input type="checkbox" name="activa" value="1" class="rounded border-input bg-background text-primary focus:ring-ring" @checked(old('activa', $zona->activa ?? true))>
            Activa
        </label>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.espacios.zonas.index') }}" class="admin-btn-outline">Cancelar</a>
            <button type="submit" class="admin-btn-primary">Guardar</button>
        </div>
    </form>
</x-app-layout>
