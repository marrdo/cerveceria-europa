<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="'Editar '.$seccion->nombre" description="Texto editable de la web publica." />
    </x-slot>

    @include('modulos.web-publica.partials.nav')

    @php($datos = $seccion->datos ?? [])

    <form method="POST" action="{{ route('admin.web-publica.secciones.update', $seccion) }}" enctype="multipart/form-data" class="admin-card max-w-4xl space-y-6 p-6">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="titulo" value="Titulo" />
            <x-text-input id="titulo" name="titulo" class="mt-1 block h-10 w-full" :value="old('titulo', $seccion->titulo)" maxlength="191" />
            <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="subtitulo" value="Subtitulo" />
            <x-text-input id="subtitulo" name="subtitulo" class="mt-1 block h-10 w-full" :value="old('subtitulo', $seccion->subtitulo)" maxlength="500" />
            <x-input-error :messages="$errors->get('subtitulo')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="contenido" value="Contenido" />
            <textarea id="contenido" name="contenido" rows="5" class="admin-input mt-1 block w-full">{{ old('contenido', $seccion->contenido) }}</textarea>
            <x-input-error :messages="$errors->get('contenido')" class="mt-2" />
        </div>

        <div class="rounded-lg border border-border bg-muted/20 p-4">
            <x-input-label for="imagen" value="Imagen de la sección" />
            @if ($seccion->urlImagen())
                <img src="{{ $seccion->urlImagen() }}" alt="Imagen actual de {{ $seccion->nombre }}" class="mt-3 h-48 w-full rounded-md border border-border bg-background object-cover">
                <label class="mt-3 flex items-center gap-2 text-xs text-muted-foreground">
                    <input type="checkbox" name="eliminar_imagen" value="1" class="rounded border-input bg-background text-primary focus:ring-ring">
                    Eliminar la imagen actual
                </label>
            @endif
            <input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp" class="mt-3 block w-full text-xs text-muted-foreground file:me-3 file:rounded-md file:border-0 file:bg-primary/10 file:px-3 file:py-2 file:font-semibold file:text-primary">
            <p class="mt-1 text-xs text-muted-foreground">JPG, PNG o WebP · máximo 4 MB.</p>
            <x-input-error :messages="$errors->get('imagen')" class="mt-2" />
        </div>

        @if ($seccion->clave === 'inicio_hero')
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <x-input-label for="eyebrow" value="Texto superior" />
                    <x-text-input id="eyebrow" name="eyebrow" class="mt-1 block h-10 w-full" :value="old('eyebrow', $datos['eyebrow'] ?? '')" maxlength="120" />
                    <x-input-error :messages="$errors->get('eyebrow')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="cta_principal" value="Botón principal" />
                    <x-text-input id="cta_principal" name="cta_principal" class="mt-1 block h-10 w-full" :value="old('cta_principal', $datos['cta_principal'] ?? '')" maxlength="50" />
                    <x-input-error :messages="$errors->get('cta_principal')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="cta_secundaria" value="Botón secundario" />
                    <x-text-input id="cta_secundaria" name="cta_secundaria" class="mt-1 block h-10 w-full" :value="old('cta_secundaria', $datos['cta_secundaria'] ?? '')" maxlength="50" />
                    <x-input-error :messages="$errors->get('cta_secundaria')" class="mt-2" />
                </div>
            </div>
        @endif

        @if ($seccion->clave === 'inicio_valores')
            <div class="grid gap-4 md:grid-cols-3">
                @foreach (range(1, 3) as $numero)
                    <fieldset class="rounded-lg border border-border p-4">
                        <legend class="px-2 text-sm font-semibold text-foreground">Valor {{ $numero }}</legend>
                        <x-input-label :for="'valor_'.$numero.'_titulo'" value="Título" />
                        <x-text-input :id="'valor_'.$numero.'_titulo'" :name="'valor_'.$numero.'_titulo'" class="mt-1 block h-10 w-full" :value="old('valor_'.$numero.'_titulo', $datos['valor_'.$numero.'_titulo'] ?? '')" maxlength="80" />
                        <x-input-label :for="'valor_'.$numero.'_descripcion'" value="Descripción" class="mt-4" />
                        <textarea id="valor_{{ $numero }}_descripcion" name="valor_{{ $numero }}_descripcion" rows="4" maxlength="300" class="admin-input mt-1 block w-full">{{ old('valor_'.$numero.'_descripcion', $datos['valor_'.$numero.'_descripcion'] ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('valor_'.$numero.'_descripcion')" class="mt-2" />
                    </fieldset>
                @endforeach
            </div>
        @endif

        @if ($seccion->clave === 'contacto')
            <div>
                <x-input-label for="reservas" value="Indicaciones para reservas" />
                <textarea id="reservas" name="reservas" rows="3" class="admin-input mt-1 block w-full">{{ old('reservas', $datos['reservas'] ?? '') }}</textarea>
                <p class="mt-1 text-xs text-muted-foreground">El telefono, email, direccion y horario se editan en Configuracion del negocio.</p>
                <x-input-error :messages="$errors->get('reservas')" class="mt-2" />
            </div>
        @endif

        <label class="flex items-center gap-2 text-sm text-foreground">
            <input type="checkbox" name="activo" value="1" class="rounded border-input bg-background text-primary focus:ring-ring" @checked(old('activo', $seccion->activo ?? true))>
            Activa
        </label>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.web-publica.secciones.index') }}" class="admin-btn-outline">Cancelar</a>
            <button type="submit" class="admin-btn-primary">Guardar</button>
        </div>
    </form>
</x-app-layout>
