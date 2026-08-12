<x-app-layout>
    <x-slot name="header">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-foreground">Configuracion del negocio</h1>
            <p class="mt-1 text-sm text-muted-foreground">Identidad, apariencia y SEO usados por el panel y la web pública.</p>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.configuracion.negocio.update') }}" enctype="multipart/form-data" class="admin-card max-w-5xl divide-y divide-border">
        @csrf
        @method('PUT')

        <section class="space-y-5 p-6" aria-labelledby="identidad-heading">
            <div>
                <h2 id="identidad-heading" class="text-base font-semibold text-foreground">Identidad</h2>
                <p class="mt-1 text-sm text-muted-foreground">Nombre visible y datos fiscales del local.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-input-label for="nombre_comercial" value="Nombre comercial" />
                    <x-text-input id="nombre_comercial" name="nombre_comercial" class="mt-1 block h-10 w-full" :value="old('nombre_comercial', $configuracion->nombre_comercial)" maxlength="191" required autofocus />
                    <x-input-error :messages="$errors->get('nombre_comercial')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="razon_social" value="Razon social" />
                    <x-text-input id="razon_social" name="razon_social" class="mt-1 block h-10 w-full" :value="old('razon_social', $configuracion->razon_social)" maxlength="191" />
                    <x-input-error :messages="$errors->get('razon_social')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="nif" value="NIF / CIF" />
                    <x-text-input id="nif" name="nif" class="mt-1 block h-10 w-full" :value="old('nif', $configuracion->nif)" maxlength="50" />
                    <x-input-error :messages="$errors->get('nif')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="eslogan" value="Eslogan" />
                    <x-text-input id="eslogan" name="eslogan" class="mt-1 block h-10 w-full" :value="old('eslogan', $configuracion->eslogan)" maxlength="255" />
                    <x-input-error :messages="$errors->get('eslogan')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="descripcion_corta" value="Descripcion corta" />
                <textarea id="descripcion_corta" name="descripcion_corta" rows="3" maxlength="500" class="admin-input mt-1 block w-full">{{ old('descripcion_corta', $configuracion->descripcion_corta) }}</textarea>
                <x-input-error :messages="$errors->get('descripcion_corta')" class="mt-2" />
            </div>
        </section>

        <section class="space-y-6 p-6" aria-labelledby="marca-heading">
            <div>
                <h2 id="marca-heading" class="text-base font-semibold text-foreground">Marca y apariencia pública</h2>
                <p class="mt-1 text-sm text-muted-foreground">Recursos y colores aplicados sin modificar plantillas ni recompilar estilos.</p>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                @foreach ([
                    'logo' => ['Logo', 'logo_path', 'PNG, JPG o WebP · máximo 2 MB'],
                    'favicon' => ['Favicon', 'favicon_path', 'PNG o ICO · máximo 512 KB'],
                    'imagen_social' => ['Imagen social', 'imagen_social_path', 'Mínimo 600 × 315 · máximo 4 MB'],
                ] as $campo => [$etiqueta, $atributo, $ayuda])
                    <div class="rounded-lg border border-border bg-muted/20 p-4">
                        <x-input-label :for="$campo" :value="$etiqueta" />
                        @if ($configuracion->{$atributo})
                            <img src="{{ $configuracion->urlRecurso($configuracion->{$atributo}) }}" alt="{{ $etiqueta }} actual" class="mt-3 h-24 w-full rounded-md border border-border bg-background object-contain p-2">
                            <label class="mt-3 flex items-center gap-2 text-xs text-muted-foreground">
                                <input type="checkbox" name="eliminar_{{ $campo }}" value="1" class="rounded border-input bg-background text-primary focus:ring-ring">
                                Eliminar el archivo actual
                            </label>
                        @endif
                        <input id="{{ $campo }}" name="{{ $campo }}" type="file" accept="{{ $campo === 'favicon' ? '.png,.ico' : 'image/jpeg,image/png,image/webp' }}" class="mt-3 block w-full text-xs text-muted-foreground file:me-3 file:rounded-md file:border-0 file:bg-primary/10 file:px-3 file:py-2 file:font-semibold file:text-primary">
                        <p class="mt-2 text-xs text-muted-foreground">{{ $ayuda }}</p>
                        <x-input-error :messages="$errors->get($campo)" class="mt-2" />
                    </div>
                @endforeach
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ([
                    'color_primario' => 'Principal',
                    'color_secundario' => 'Secundario',
                    'color_fondo' => 'Fondo',
                    'color_superficie' => 'Superficie',
                    'color_texto' => 'Texto',
                ] as $campo => $etiqueta)
                    <div>
                        <x-input-label :for="$campo" :value="$etiqueta" />
                        <div class="mt-1 flex h-11 items-center gap-2 rounded-md border border-input bg-background px-2">
                            <input id="{{ $campo }}" name="{{ $campo }}" type="color" value="{{ old($campo, $configuracion->{$campo}) }}" class="h-7 w-9 cursor-pointer border-0 bg-transparent p-0" required>
                            <span class="font-mono text-xs text-muted-foreground">{{ old($campo, $configuracion->{$campo}) }}</span>
                        </div>
                        <x-input-error :messages="$errors->get($campo)" class="mt-2" />
                    </div>
                @endforeach
            </div>
        </section>

        <section class="space-y-5 p-6" aria-labelledby="contacto-negocio-heading">
            <div>
                <h2 id="contacto-negocio-heading" class="text-base font-semibold text-foreground">Contacto y ubicacion</h2>
                <p class="mt-1 text-sm text-muted-foreground">Estos datos apareceran en la web publica y documentos futuros.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-input-label for="telefono" value="Telefono" />
                    <x-text-input id="telefono" name="telefono" type="tel" class="mt-1 block h-10 w-full" :value="old('telefono', $configuracion->telefono)" maxlength="30" />
                    <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block h-10 w-full" :value="old('email', $configuracion->email)" maxlength="191" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="direccion" value="Direccion" />
                    <x-text-input id="direccion" name="direccion" class="mt-1 block h-10 w-full" :value="old('direccion', $configuracion->direccion)" maxlength="255" />
                    <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="codigo_postal" value="Codigo postal" />
                    <x-text-input id="codigo_postal" name="codigo_postal" inputmode="numeric" class="mt-1 block h-10 w-full" :value="old('codigo_postal', $configuracion->codigo_postal)" maxlength="5" />
                    <x-input-error :messages="$errors->get('codigo_postal')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="localidad" value="Localidad" />
                    <x-text-input id="localidad" name="localidad" class="mt-1 block h-10 w-full" :value="old('localidad', $configuracion->localidad)" maxlength="100" />
                    <x-input-error :messages="$errors->get('localidad')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="provincia" value="Provincia" />
                    <x-text-input id="provincia" name="provincia" class="mt-1 block h-10 w-full" :value="old('provincia', $configuracion->provincia)" maxlength="100" />
                    <x-input-error :messages="$errors->get('provincia')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="pais" value="Pais" />
                    <x-text-input id="pais" name="pais" class="mt-1 block h-10 w-full" :value="old('pais', $configuracion->pais)" maxlength="100" required />
                    <x-input-error :messages="$errors->get('pais')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="horario" value="Horario publico" />
                <textarea id="horario" name="horario" rows="4" maxlength="2000" class="admin-input mt-1 block w-full" placeholder="Martes a jueves: 12:00-00:00&#10;Viernes y sabado: 12:00-01:30">{{ old('horario', $configuracion->horario) }}</textarea>
                <x-input-error :messages="$errors->get('horario')" class="mt-2" />
            </div>
        </section>

        <section class="space-y-5 p-6" aria-labelledby="enlaces-heading">
            <div>
                <h2 id="enlaces-heading" class="text-base font-semibold text-foreground">Enlaces</h2>
                <p class="mt-1 text-sm text-muted-foreground">Usa direcciones completas, incluido https://.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                @foreach ([
                    'web_url' => 'Web',
                    'instagram_url' => 'Instagram',
                    'google_maps_url' => 'Google Maps',
                    'reservas_url' => 'Reservas',
                ] as $campo => $etiqueta)
                    <div>
                        <x-input-label :for="$campo" :value="$etiqueta" />
                        <x-text-input :id="$campo" :name="$campo" type="url" class="mt-1 block h-10 w-full" :value="old($campo, $configuracion->{$campo})" maxlength="2048" placeholder="https://" />
                        <x-input-error :messages="$errors->get($campo)" class="mt-2" />
                    </div>
                @endforeach
            </div>
        </section>

        <section class="space-y-5 p-6" aria-labelledby="regional-heading">
            <div>
                <h2 id="regional-heading" class="text-base font-semibold text-foreground">Ajustes regionales</h2>
                <p class="mt-1 text-sm text-muted-foreground">Se usaran para fechas, caja e importes.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-input-label for="zona_horaria" value="Zona horaria" />
                    <select id="zona_horaria" name="zona_horaria" class="admin-input mt-1 block h-10 w-full" required>
                        <option value="Europe/Madrid" @selected(old('zona_horaria', $configuracion->zona_horaria) === 'Europe/Madrid')>Europe/Madrid</option>
                        <option value="Atlantic/Canary" @selected(old('zona_horaria', $configuracion->zona_horaria) === 'Atlantic/Canary')>Atlantic/Canary</option>
                    </select>
                    <x-input-error :messages="$errors->get('zona_horaria')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="moneda" value="Moneda" />
                    <select id="moneda" name="moneda" class="admin-input mt-1 block h-10 w-full" required>
                        <option value="EUR" @selected(old('moneda', $configuracion->moneda) === 'EUR')>EUR - Euro</option>
                    </select>
                    <x-input-error :messages="$errors->get('moneda')" class="mt-2" />
                </div>
            </div>
        </section>

        <section class="space-y-5 p-6" aria-labelledby="seo-heading">
            <div>
                <h2 id="seo-heading" class="text-base font-semibold text-foreground">SEO y compartición</h2>
                <p class="mt-1 text-sm text-muted-foreground">Valores base para buscadores, redes sociales y datos estructurados del negocio.</p>
            </div>

            <div>
                <x-input-label for="seo_titulo" value="Título SEO de la portada" />
                <x-text-input id="seo_titulo" name="seo_titulo" class="mt-1 block h-10 w-full" :value="old('seo_titulo', $configuracion->seo_titulo)" maxlength="60" placeholder="Nombre del negocio · especialidad y localidad" />
                <div class="mt-1 flex justify-between gap-3 text-xs text-muted-foreground"><span>Si se deja vacío se utiliza el nombre comercial.</span><span>Máximo 60 caracteres</span></div>
                <x-input-error :messages="$errors->get('seo_titulo')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="seo_descripcion" value="Descripción SEO" />
                <textarea id="seo_descripcion" name="seo_descripcion" rows="3" maxlength="160" class="admin-input mt-1 block w-full" placeholder="Describe en una frase qué ofrece el negocio y dónde está.">{{ old('seo_descripcion', $configuracion->seo_descripcion) }}</textarea>
                <div class="mt-1 flex justify-between gap-3 text-xs text-muted-foreground"><span>También se usa al compartir la portada.</span><span>Máximo 160 caracteres</span></div>
                <x-input-error :messages="$errors->get('seo_descripcion')" class="mt-2" />
            </div>

            <label class="flex items-start gap-3 rounded-lg border border-border bg-muted/20 p-4 text-sm text-foreground">
                <input type="checkbox" name="seo_indexar" value="1" class="mt-0.5 rounded border-input bg-background text-primary focus:ring-ring" @checked(old('seo_indexar', $configuracion->seo_indexar ?? true))>
                <span><strong class="block">Permitir indexación</strong><span class="mt-1 block text-xs text-muted-foreground">Desmárcalo mientras preparas una instalación que todavía no debe aparecer en buscadores.</span></span>
            </label>
        </section>

        <div class="flex flex-wrap justify-end gap-3 p-6">
            <a href="{{ route('web.inicio') }}" target="_blank" class="admin-btn-outline">Vista previa pública</a>
            <button type="submit" class="admin-btn-primary">Guardar configuracion</button>
        </div>
    </form>
</x-app-layout>
