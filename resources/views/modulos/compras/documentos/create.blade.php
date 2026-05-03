<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Subir documento" description="Foto o PDF de albaran/factura para revision asistida">
            <x-slot name="actions">
                <a href="{{ route('admin.compras.documentos.index') }}" class="admin-btn-outline">Volver</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.compras.partials.nav')

    <form method="POST" action="{{ route('admin.compras.documentos.store') }}" enctype="multipart/form-data" class="admin-card max-w-3xl space-y-6 p-4 lg:p-6">
        @csrf

        <div>
            <x-input-label for="tipo_documento" value="Tipo de documento" />
            <select id="tipo_documento" name="tipo_documento" class="admin-input mt-1 block h-10 w-full" required>
                <option value="">Selecciona tipo</option>
                @foreach ($tiposDocumento as $tipo)
                    <option value="{{ $tipo->value }}" @selected(old('tipo_documento') === $tipo->value)>{{ $tipo->etiqueta() }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-muted-foreground">Indica si es albaran, factura u otro documento recibido del proveedor.</p>
            <x-input-error :messages="$errors->get('tipo_documento')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="proveedor_id" value="Proveedor" />
            <select id="proveedor_id" name="proveedor_id" class="admin-input mt-1 block h-10 w-full">
                <option value="">Sin proveedor confirmado</option>
                @foreach ($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}" @selected(old('proveedor_id') === $proveedor->id)>{{ $proveedor->nombre }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-muted-foreground">Opcional. Si no esta claro en la foto, se puede dejar sin proveedor y revisarlo despues.</p>
            <x-input-error :messages="$errors->get('proveedor_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="archivo" value="Archivo" />
            <input id="archivo" name="archivo" type="file" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" class="admin-input mt-1 block w-full p-2" required>
            <p class="mt-1 text-xs text-muted-foreground">Sube una foto JPG/PNG o un PDF. Maximo 10 MB. El archivo se guarda privado para trazabilidad.</p>
            <x-input-error :messages="$errors->get('archivo')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="notas" value="Notas" />
            <textarea id="notas" name="notas" rows="4" class="admin-input mt-1 block w-full">{{ old('notas') }}</textarea>
            <p class="mt-1 text-xs text-muted-foreground">Observaciones internas: estado del documento, dudas, proveedor posible o numero de albaran visible.</p>
            <x-input-error :messages="$errors->get('notas')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-border pt-4">
            <a href="{{ route('admin.compras.documentos.index') }}" class="admin-btn-outline">Cancelar</a>
            <x-primary-button>Subir documento</x-primary-button>
        </div>
    </form>
</x-app-layout>
