<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="'Editar '.$seccion->nombre" description="Texto editable de la web publica." />
    </x-slot>

    @include('modulos.web-publica.partials.nav')

    @php($datos = $seccion->datos ?? [])

    <form method="POST" action="{{ route('admin.web-publica.secciones.update', $seccion) }}" class="admin-card max-w-4xl space-y-6 p-6">
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

        @if ($seccion->clave === 'contacto')
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <x-input-label for="ubicacion" value="Ubicacion" />
                    <textarea id="ubicacion" name="ubicacion" rows="3" class="admin-input mt-1 block w-full">{{ old('ubicacion', $datos['ubicacion'] ?? '') }}</textarea>
                    <x-input-error :messages="$errors->get('ubicacion')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="reservas" value="Reservas" />
                    <textarea id="reservas" name="reservas" rows="3" class="admin-input mt-1 block w-full">{{ old('reservas', $datos['reservas'] ?? '') }}</textarea>
                    <p class="mt-1 text-xs text-muted-foreground">Telefono, WhatsApp, email o enlace de reservas.</p>
                    <x-input-error :messages="$errors->get('reservas')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="horario" value="Horario" />
                    <textarea id="horario" name="horario" rows="3" class="admin-input mt-1 block w-full">{{ old('horario', $datos['horario'] ?? '') }}</textarea>
                    <x-input-error :messages="$errors->get('horario')" class="mt-2" />
                </div>
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
