<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header :title="$contenido->exists ? 'Editar contenido' : 'Nuevo contenido'" description="Contenido visible en la web publica." />
    </x-slot>

    @include('modulos.web-publica.partials.nav')

    <form method="POST" enctype="multipart/form-data" action="{{ $contenido->exists ? route('admin.web-publica.contenidos.update', $contenido) : route('admin.web-publica.contenidos.store') }}" class="admin-card max-w-4xl space-y-6 p-6">
        @csrf
        @if ($contenido->exists)
            @method('PUT')
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="tipo" value="Tipo" />
                <select id="tipo" name="tipo" class="admin-input mt-1 block h-10 w-full" required>
                    @foreach ($tipos as $tipo)
                        <option value="{{ $tipo->value }}" @selected(old('tipo', $contenido->tipo?->value) === $tipo->value)>{{ $tipo->etiqueta() }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-muted-foreground">Define donde aparecera: carta, cervezas, recomendaciones o blog.</p>
                <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="producto_id" value="Producto de inventario" />
                <select id="producto_id" name="producto_id" class="admin-input mt-1 block h-10 w-full">
                    <option value="">Sin producto vinculado</option>
                    @foreach ($productos as $producto)
                        <option value="{{ $producto->id }}" @selected(old('producto_id', $contenido->producto_id) === $producto->id)>
                            {{ $producto->nombre }} · stock {{ $producto->formatearCantidad($producto->cantidadStock()) }} {{ $producto->codigoUnidad() }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-muted-foreground">Si vinculas un producto con control de stock, se ocultara de la web cuando su stock sea 0.</p>
                <x-input-error :messages="$errors->get('producto_id')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="categoria_carta_id" value="Categoria de carta" />
                <select id="categoria_carta_id" name="categoria_carta_id" class="admin-input mt-1 block h-10 w-full">
                    <option value="">Sin categoria</option>
                    @foreach ($categoriasCarta as $categoriaCarta)
                        <option value="{{ $categoriaCarta->id }}" @selected(old('categoria_carta_id', $contenido->categoria_carta_id) === $categoriaCarta->id)>
                            {{ $categoriaCarta->nombreJerarquico() }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-muted-foreground">Organiza la carta publica por secciones: Bebidas / Cervezas de barril, Cocina / Tapas, etc.</p>
                <x-input-error :messages="$errors->get('categoria_carta_id')" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="titulo" value="Titulo" />
                <x-text-input id="titulo" name="titulo" class="mt-1 block h-10 w-full" :value="old('titulo', $contenido->titulo)" maxlength="191" required />
                <p class="mt-1 text-xs text-muted-foreground">Nombre publico del plato, cerveza o publicacion.</p>
                <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="descripcion_corta" value="Descripcion corta" />
            <x-text-input id="descripcion_corta" name="descripcion_corta" class="mt-1 block h-10 w-full" :value="old('descripcion_corta', $contenido->descripcion_corta)" maxlength="500" />
            <p class="mt-1 text-xs text-muted-foreground">Texto breve visible en tarjetas y listados.</p>
            <x-input-error :messages="$errors->get('descripcion_corta')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="contenido" value="Contenido ampliado" />
            <textarea id="contenido" name="contenido" rows="5" class="admin-input mt-1 block w-full">{{ old('contenido', $contenido->contenido) }}</textarea>
            <p class="mt-1 text-xs text-muted-foreground">Detalles, maridaje, historia de la cerveza o texto de blog.</p>
            <x-input-error :messages="$errors->get('contenido')" class="mt-2" />
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <x-input-label for="precio" value="Precio" />
                <x-text-input id="precio" name="precio" type="number" step="0.01" min="0" class="mt-1 block h-10 w-full" :value="old('precio', $contenido->precio)" />
                <p class="mt-1 text-xs text-muted-foreground">Precio simple. Si anades tarifas abajo, la web mostrara las tarifas.</p>
                <x-input-error :messages="$errors->get('precio')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="orden" value="Orden" />
                <x-text-input id="orden" name="orden" type="number" min="0" class="mt-1 block h-10 w-full" :value="old('orden', $contenido->orden ?? 0)" />
                <p class="mt-1 text-xs text-muted-foreground">Menor numero aparece antes.</p>
                <x-input-error :messages="$errors->get('orden')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="alergenos" value="Alergenos" />
                <x-text-input id="alergenos" name="alergenos" class="mt-1 block h-10 w-full" :value="old('alergenos', $contenido->alergenos ? implode(', ', $contenido->alergenos) : '')" maxlength="1000" />
                <p class="mt-1 text-xs text-muted-foreground">Separados por comas: gluten, lacteos, frutos secos.</p>
                <x-input-error :messages="$errors->get('alergenos')" class="mt-2" />
            </div>
        </div>

        @php
            $tarifasFormulario = old('tarifas');

            if ($tarifasFormulario === null) {
                $tarifasFormulario = $contenido->relationLoaded('tarifas')
                    ? $contenido->tarifas->map(fn ($tarifa) => ['nombre' => $tarifa->nombre, 'precio' => $tarifa->precio])->values()->all()
                    : [];
            }

            if ($tarifasFormulario === []) {
                $tarifasFormulario = [['nombre' => '', 'precio' => '']];
            }
        @endphp

        <section class="rounded-lg border border-border bg-muted/20 p-4" x-data="{ tarifas: @js($tarifasFormulario) }">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-foreground">Tarifas de carta</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Usalas para formatos como Tapa/Plato, 25cl/50cl o Copa/Botella.</p>
                </div>
                <button type="button" class="admin-btn-outline" @click="tarifas.push({ nombre: '', precio: '' })">Anadir tarifa</button>
            </div>

            <div class="space-y-3">
                <template x-for="(tarifa, indice) in tarifas" :key="indice">
                    <div class="grid gap-3 rounded-md border border-border bg-card p-3 md:grid-cols-[1fr_180px_auto]">
                        <div>
                            <x-input-label value="Formato" />
                            <input type="text" x-model="tarifa.nombre" :name="`tarifas[${indice}][nombre]`" class="admin-input mt-1 block h-10 w-full" maxlength="80" placeholder="Tapa, Plato, 25cl, Botella">
                        </div>
                        <div>
                            <x-input-label value="Precio" />
                            <input type="number" x-model="tarifa.precio" :name="`tarifas[${indice}][precio]`" class="admin-input mt-1 block h-10 w-full" min="0" step="0.01" placeholder="0.00">
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="admin-btn-outline text-destructive" @click="tarifas.splice(indice, 1); if (tarifas.length === 0) tarifas.push({ nombre: '', precio: '' })">Quitar</button>
                        </div>
                    </div>
                </template>
            </div>

            @foreach ($errors->get('tarifas.*.nombre') as $mensajes)
                <x-input-error :messages="$mensajes" class="mt-2" />
            @endforeach
            @foreach ($errors->get('tarifas.*.precio') as $mensajes)
                <x-input-error :messages="$mensajes" class="mt-2" />
            @endforeach
        </section>

        <div>
            <x-input-label for="imagen" value="Imagen" />
            <input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp" class="admin-input mt-1 block w-full p-2" />
            <p class="mt-1 text-xs text-muted-foreground">Foto publica del plato, cerveza o noticia. JPG, PNG o WEBP hasta 4 MB.</p>
            @if ($contenido->urlImagen())
                <img src="{{ $contenido->urlImagen() }}" alt="{{ $contenido->titulo }}" class="mt-3 h-32 w-48 rounded-md object-cover">
            @endif
            <x-input-error :messages="$errors->get('imagen')" class="mt-2" />
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <label class="flex items-center gap-2 text-sm text-foreground">
                <input type="checkbox" name="publicado" value="1" class="rounded border-input bg-background text-primary focus:ring-ring" @checked(old('publicado', $contenido->publicado ?? true))>
                Publicado
            </label>
            <label class="flex items-center gap-2 text-sm text-foreground">
                <input type="checkbox" name="destacado" value="1" class="rounded border-input bg-background text-primary focus:ring-ring" @checked(old('destacado', $contenido->destacado))>
                Destacado en portada
            </label>
            <label class="flex items-center gap-2 text-sm text-foreground">
                <input type="checkbox" name="fuera_carta" value="1" class="rounded border-input bg-background text-primary focus:ring-ring" @checked(old('fuera_carta', $contenido->fuera_carta))>
                Fuera de carta
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="publicado_desde" value="Publicado desde" />
                <x-text-input id="publicado_desde" name="publicado_desde" type="date" class="mt-1 block h-10 w-full" :value="old('publicado_desde', $contenido->publicado_desde?->format('Y-m-d'))" />
                <x-input-error :messages="$errors->get('publicado_desde')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="publicado_hasta" value="Publicado hasta" />
                <x-text-input id="publicado_hasta" name="publicado_hasta" type="date" class="mt-1 block h-10 w-full" :value="old('publicado_hasta', $contenido->publicado_hasta?->format('Y-m-d'))" />
                <x-input-error :messages="$errors->get('publicado_hasta')" class="mt-2" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.web-publica.contenidos.index') }}" class="admin-btn-outline">Cancelar</a>
            <button type="submit" class="admin-btn-primary">Guardar</button>
        </div>
    </form>
</x-app-layout>
