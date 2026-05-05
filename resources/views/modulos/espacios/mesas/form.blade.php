<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="$mesa->exists ? 'Editar mesa' : 'Nueva mesa'" description="Mesas, barra o puntos de servicio" />
    </x-slot>

    @include('modulos.espacios.partials.nav')

    <form method="POST" action="{{ $mesa->exists ? route('admin.espacios.mesas.update', $mesa) : route('admin.espacios.mesas.store') }}" class="admin-card max-w-3xl space-y-5 p-6">
        @csrf
        @if ($mesa->exists)
            @method('PUT')
        @endif

        <div>
            <x-input-label for="zona_id" value="Zona" />
            <select id="zona_id" name="zona_id" class="admin-input mt-1 block h-10 w-full" required>
                <option value="">Selecciona zona</option>
                @foreach ($zonas as $zona)
                    <option value="{{ $zona->id }}" @selected(old('zona_id', $mesa->zona_id) === $zona->id)>{{ $zona->recinto?->nombre_comercial }} - {{ $zona->nombre }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('zona_id')" class="mt-2" />
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <x-input-label for="nombre" value="Nombre" />
                <x-text-input id="nombre" name="nombre" class="mt-1 block h-10 w-full" :value="old('nombre', $mesa->nombre)" required maxlength="191" />
                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="capacidad" value="Capacidad" />
                <x-text-input id="capacidad" name="capacidad" type="number" min="1" class="mt-1 block h-10 w-full" :value="old('capacidad', $mesa->capacidad)" />
            </div>
            <div>
                <x-input-label for="orden" value="Orden" />
                <x-text-input id="orden" name="orden" type="number" min="0" class="mt-1 block h-10 w-full" :value="old('orden', $mesa->orden ?? 0)" />
            </div>
        </div>

        <div>
            <x-input-label for="notas" value="Notas internas" />
            <textarea id="notas" name="notas" rows="4" class="admin-input mt-1 block w-full">{{ old('notas', $mesa->notas) }}</textarea>
        </div>

        <label class="flex items-center gap-2 text-sm text-foreground">
            <input type="checkbox" name="activa" value="1" class="rounded border-input bg-background text-primary focus:ring-ring" @checked(old('activa', $mesa->activa ?? true))>
            Activa
        </label>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.espacios.mesas.index') }}" class="admin-btn-outline">Cancelar</a>
            <button type="submit" class="admin-btn-primary">Guardar</button>
        </div>
    </form>
</x-app-layout>
