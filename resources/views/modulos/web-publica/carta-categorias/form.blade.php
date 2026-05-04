<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="$categoria->exists ? 'Editar categoria de carta' : 'Nueva categoria de carta'" description="Define la estructura publica de la carta." />
    </x-slot>

    @include('modulos.web-publica.partials.nav')

    <form method="POST" action="{{ $categoria->exists ? route('admin.web-publica.carta-categorias.update', $categoria) : route('admin.web-publica.carta-categorias.store') }}" class="admin-card max-w-3xl space-y-6 p-6">
        @csrf
        @if ($categoria->exists)
            @method('PUT')
        @endif

        <div>
            <x-input-label for="categoria_padre_id" value="Categoria padre" />
            <select id="categoria_padre_id" name="categoria_padre_id" class="admin-input mt-1 block h-10 w-full">
                <option value="">Categoria principal</option>
                @foreach ($categoriasPadre as $categoriaPadre)
                    <option value="{{ $categoriaPadre->id }}" @selected(old('categoria_padre_id', $categoria->categoria_padre_id) === $categoriaPadre->id)>
                        {{ $categoriaPadre->nombreJerarquico() }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-muted-foreground">Usalo para montar secciones tipo Bebidas / Cervezas de barril o Cocina / Tapas.</p>
            <x-input-error :messages="$errors->get('categoria_padre_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="nombre" value="Nombre" />
            <x-text-input id="nombre" name="nombre" class="mt-1 block h-10 w-full" :value="old('nombre', $categoria->nombre)" required maxlength="191" />
            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="descripcion" value="Descripcion" />
            <textarea id="descripcion" name="descripcion" rows="4" class="admin-input mt-1 block w-full">{{ old('descripcion', $categoria->descripcion) }}</textarea>
            <p class="mt-1 text-xs text-muted-foreground">Texto opcional para explicar la seccion en la carta publica.</p>
            <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="orden" value="Orden" />
            <x-text-input id="orden" name="orden" type="number" min="0" class="mt-1 block h-10 w-full" :value="old('orden', $categoria->orden ?? 0)" />
            <p class="mt-1 text-xs text-muted-foreground">Menor numero aparece antes dentro de su mismo nivel.</p>
            <x-input-error :messages="$errors->get('orden')" class="mt-2" />
        </div>

        <label class="flex items-center gap-2 text-sm text-foreground">
            <input type="checkbox" name="activo" value="1" class="rounded border-input bg-background text-primary focus:ring-ring" @checked(old('activo', $categoria->activo ?? true))>
            Activa
        </label>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.web-publica.carta-categorias.index') }}" class="admin-btn-outline">Cancelar</a>
            <button type="submit" class="admin-btn-primary">Guardar</button>
        </div>
    </form>
</x-app-layout>
