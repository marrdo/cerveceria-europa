@php
    use App\Support\Validacion\AtributosValidacion;
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="$item->exists ? 'Editar '.$titulo : 'Nuevo '.$titulo" description="Gestion de catalogos del inventario" />
    </x-slot>

    <div class="max-w-3xl">
            @include('modulos.inventario.partials.nav')

            <form method="POST" action="{{ $item->exists ? route($rutaBase.'.update', $item) : route($rutaBase.'.store') }}" class="admin-card space-y-6 p-6">
                @csrf
                @if ($item->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="nombre" value="Nombre" />
                        <x-text-input id="nombre" name="nombre" class="mt-1 block h-10 w-full" :value="old('nombre', $item->nombre)" required maxlength="191" />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>

                @if (str_contains($rutaBase, 'unidades') || str_contains($rutaBase, 'ubicaciones'))
                    <div>
                        <x-input-label for="codigo" value="Codigo" />
                        <x-text-input id="codigo" name="codigo" class="mt-1 block h-10 w-full" :value="old('codigo', $item->codigo)" maxlength="50" />
                        <x-input-error :messages="$errors->get('codigo')" class="mt-2" />
                    </div>
                @endif

                @if (str_contains($rutaBase, 'proveedores'))
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <x-input-label for="cif_nif" value="CIF/NIF" />
                            <x-text-input
                                id="cif_nif"
                                name="cif_nif"
                                class="mt-1 block h-10 w-full"
                                :value="old('cif_nif', $item->cif_nif)"
                                maxlength="50"
                                pattern="{{ AtributosValidacion::PATRON_DOCUMENTO_IDENTIDAD_ESPANOL }}"
                                title="{{ AtributosValidacion::TITULO_DOCUMENTO_IDENTIDAD_ESPANOL }}"
                                autocomplete="off"
                            />
                            <x-input-error :messages="$errors->get('cif_nif')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="persona_contacto" value="Persona de contacto" />
                            <x-text-input id="persona_contacto" name="persona_contacto" class="mt-1 block h-10 w-full" :value="old('persona_contacto', $item->persona_contacto)" maxlength="191" autocomplete="name" />
                            <x-input-error :messages="$errors->get('persona_contacto')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input
                                id="email"
                                name="email"
                                type="email"
                                class="mt-1 block h-10 w-full"
                                :value="old('email', $item->email)"
                                maxlength="191"
                                title="{{ AtributosValidacion::TITULO_EMAIL }}"
                                autocomplete="email"
                            />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="telefono" value="Telefono" />
                            <x-text-input
                                id="telefono"
                                name="telefono"
                                type="tel"
                                class="mt-1 block h-10 w-full"
                                :value="old('telefono', $item->telefono)"
                                maxlength="30"
                                inputmode="tel"
                                pattern="{{ AtributosValidacion::PATRON_TELEFONO_ESPANOL }}"
                                title="{{ AtributosValidacion::TITULO_TELEFONO_ESPANOL }}"
                                autocomplete="tel"
                            />
                            <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                        </div>
                    </div>
                @endif

                <div>
                    <x-input-label for="descripcion" value="Descripcion / notas" />
                    <textarea id="descripcion" name="{{ str_contains($rutaBase, 'proveedores') ? 'notas' : 'descripcion' }}" rows="4" class="admin-input mt-1 block w-full shadow-sm">{{ old(str_contains($rutaBase, 'proveedores') ? 'notas' : 'descripcion', $item->notas ?? $item->descripcion) }}</textarea>
                    <x-input-error :messages="$errors->get(str_contains($rutaBase, 'proveedores') ? 'notas' : 'descripcion')" class="mt-2" />
                </div>

                @if (str_contains($rutaBase, 'unidades'))
                    <label class="flex items-center gap-2 text-sm text-foreground">
                    <input type="checkbox" name="permite_decimal" value="1" class="rounded border-input bg-background text-primary focus:ring-ring" @checked(old('permite_decimal', $item->permite_decimal))>
                        Permite cantidades decimales
                    </label>
                @endif

                <label class="flex items-center gap-2 text-sm text-foreground">
                    <input type="checkbox" name="activo" value="1" class="rounded border-input bg-background text-primary focus:ring-ring" @checked(old('activo', $item->activo ?? true))>
                    Activo
                </label>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route($rutaBase.'.index') }}" class="admin-btn-outline">Cancelar</a>
                    <x-primary-button>Guardar</x-primary-button>
                </div>
            </form>
    </div>
</x-app-layout>
