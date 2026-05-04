<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="$post->exists ? 'Editar post' : 'Nuevo post'" description="Modulo opcional de blog para la web publica." />
    </x-slot>

    @include('modulos.web-publica.partials.nav')

    <form method="POST" enctype="multipart/form-data" action="{{ $post->exists ? route('admin.web-publica.blog.update', $post) : route('admin.web-publica.blog.store') }}" class="admin-card max-w-4xl space-y-6 p-6">
        @csrf
        @if ($post->exists)
            @method('PUT')
        @endif

        <div>
            <x-input-label for="titulo" value="Titulo" />
            <x-text-input id="titulo" name="titulo" class="mt-1 block h-10 w-full" :value="old('titulo', $post->titulo)" required maxlength="191" />
            <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="resumen" value="Resumen" />
            <x-text-input id="resumen" name="resumen" class="mt-1 block h-10 w-full" :value="old('resumen', $post->resumen)" maxlength="500" />
            <p class="mt-1 text-xs text-muted-foreground">Entradilla visible en listados y tarjetas.</p>
            <x-input-error :messages="$errors->get('resumen')" class="mt-2" />
        </div>

        <div>
            <x-input-label value="Categorias" />
            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                @foreach ($categorias as $categoria)
                    <label class="flex items-center gap-2 rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground">
                        <input
                            type="checkbox"
                            name="categorias[]"
                            value="{{ $categoria->id }}"
                            class="rounded border-input bg-background text-primary focus:ring-ring"
                            @checked(in_array($categoria->id, old('categorias', $post->categorias?->pluck('id')->all() ?? []), true))
                        >
                        {{ $categoria->nombre }}
                    </label>
                @endforeach
            </div>
            <p class="mt-1 text-xs text-muted-foreground">Permite seccionar el blog por cerveza, cocina, eventos u otras categorias.</p>
            <x-input-error :messages="$errors->get('categorias')" class="mt-2" />
            <x-input-error :messages="$errors->get('categorias.*')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="contenido" value="Contenido" />
            <textarea id="contenido" name="contenido" rows="10" class="admin-input mt-1 block w-full" required>{{ old('contenido', $post->contenido) }}</textarea>
            <x-input-error :messages="$errors->get('contenido')" class="mt-2" />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="autor" value="Autor" />
                <x-text-input id="autor" name="autor" class="mt-1 block h-10 w-full" :value="old('autor', $post->autor)" maxlength="191" />
                <x-input-error :messages="$errors->get('autor')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="publicado_at" value="Fecha publicacion" />
                <x-text-input id="publicado_at" name="publicado_at" type="datetime-local" class="mt-1 block h-10 w-full" :value="old('publicado_at', $post->publicado_at?->format('Y-m-d\TH:i'))" />
                <x-input-error :messages="$errors->get('publicado_at')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="imagen" value="Imagen" />
            <input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp" class="admin-input mt-1 block w-full p-2" />
            @if ($post->urlImagen())
                <img src="{{ $post->urlImagen() }}" alt="{{ $post->titulo }}" class="mt-3 h-32 w-48 rounded-md object-cover">
            @endif
            <x-input-error :messages="$errors->get('imagen')" class="mt-2" />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="flex items-center gap-2 text-sm text-foreground">
                <input type="checkbox" name="publicado" value="1" class="rounded border-input bg-background text-primary focus:ring-ring" @checked(old('publicado', $post->publicado ?? true))>
                Publicado
            </label>
            <label class="flex items-center gap-2 text-sm text-foreground">
                <input type="checkbox" name="destacado" value="1" class="rounded border-input bg-background text-primary focus:ring-ring" @checked(old('destacado', $post->destacado))>
                Destacado
            </label>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.web-publica.blog.index') }}" class="admin-btn-outline">Cancelar</a>
            <button type="submit" class="admin-btn-primary">Guardar</button>
        </div>
    </form>
</x-app-layout>
