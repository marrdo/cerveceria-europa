@php
    $nombresDia = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    $totalMinutos = $cuadrante->jornadas->sum(fn ($jornada) => $jornada->minutosEfectivos());
    $totalEmpleados = $cuadrante->jornadas->pluck('usuario_id')->unique()->count();
    $ultimaExportacion = $cuadrante->exportaciones->first();
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header
            title="Cuadrante del {{ $cuadrante->semana_inicio->format('d/m') }} al {{ $cuadrante->semanaFin()->format('d/m/Y') }}"
            description="Planificación semanal por empleado, con turnos continuos, partidos y nocturnos."
        >
            <x-slot name="actions">
                <a href="{{ route('admin.planificacion-turnos.cuadrantes.index') }}" class="admin-btn-secondary">Volver</a>

                @if ($cuadrante->esBorrador())
                    <form method="POST" action="{{ route('admin.planificacion-turnos.cuadrantes.publicar', $cuadrante) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="admin-btn-primary" onclick="return confirm('Publicar el cuadrante bloqueará su edición y generará una nueva versión del Excel.');">Publicar semana</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.planificacion-turnos.cuadrantes.reabrir', $cuadrante) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="admin-btn-secondary">Reabrir borrador</button>
                    </form>
                @endif
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    <div
        x-data="{
            formularioAbierto: {{ old('_formulario') === 'turno' ? 'true' : 'false' }},
            incidenciaAbierta: {{ old('_formulario') === 'incidencia' ? 'true' : 'false' }},
            busqueda: '',
            area: 'todas',
            detallada: false,
            normalizar(valor) {
                return valor.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
            },
            coincide(fila) {
                const coincideNombre = this.normalizar(fila.dataset.nombre).includes(this.normalizar(this.busqueda));
                const coincideArea = this.area === 'todas' || fila.dataset.areas.includes(`,${this.area},`);

                return coincideNombre && coincideArea;
            },
        }"
    >
        @if (session('status'))
            <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-md border border-destructive/25 bg-destructive/10 p-4 text-sm text-destructive">
                <p class="font-semibold">Revisa el nuevo turno:</p>
                <ul class="mt-1 list-disc space-y-1 ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($conflictosLaborales->isNotEmpty())
            <div class="mb-4 rounded-md border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive" role="alert">
                <p class="font-semibold">Hay {{ $conflictosLaborales->count() }} {{ Str::plural('turno incompatible', $conflictosLaborales->count()) }} con una incidencia laboral.</p>
                <p class="mt-1 text-xs">Elimina o reasigna esos turnos antes de publicar el cuadrante.</p>
            </div>
        @endif

        <section class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Resumen del cuadrante">
            <div class="admin-card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Estado</p>
                <div class="mt-2 flex items-center gap-2">
                    <span @class([
                        'h-2.5 w-2.5 rounded-full',
                        'bg-warning' => $cuadrante->esBorrador(),
                        'bg-success' => ! $cuadrante->esBorrador(),
                    ])></span>
                    <p class="text-lg font-bold text-foreground">{{ $cuadrante->estado->etiqueta() }}</p>
                </div>
            </div>
            <div class="admin-card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Equipo asignado</p>
                <p class="mt-2 text-2xl font-black text-foreground">{{ $totalEmpleados }} <span class="text-sm font-medium text-muted-foreground">/ {{ $empleados->count() }}</span></p>
            </div>
            <div class="admin-card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Tramos</p>
                <p class="mt-2 text-2xl font-black text-foreground">{{ $cuadrante->jornadas->count() }}</p>
            </div>
            <div class="admin-card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Horas planificadas</p>
                <p class="mt-2 text-2xl font-black text-foreground">{{ number_format($totalMinutos / 60, 1, ',', '.') }} h</p>
            </div>
        </section>

        <section class="admin-card mb-6 overflow-hidden" aria-labelledby="exportaciones-heading">
            <div class="flex flex-col gap-3 border-b border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 id="exportaciones-heading" class="font-semibold text-foreground">Excel del cuadrante</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Cada publicación crea una copia nueva; reabrir el borrador no elimina las anteriores.</p>
                </div>
                @if ($ultimaExportacion)
                    <a href="{{ route('admin.planificacion-turnos.cuadrantes.exportaciones.descargar', [$cuadrante, $ultimaExportacion]) }}" class="admin-btn-primary shrink-0 justify-center">
                        Descargar v{{ str_pad((string) $ultimaExportacion->version, 3, '0', STR_PAD_LEFT) }}
                    </a>
                @endif
            </div>

            @if ($ultimaExportacion)
                <div class="grid gap-4 p-5 lg:grid-cols-[1.2fr_1fr]">
                    <div class="rounded-lg border border-success/25 bg-success/10 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-success">Última publicación</p>
                                <p class="mt-1 font-semibold text-foreground">{{ $ultimaExportacion->nombre_archivo }}</p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ $ultimaExportacion->generado_at->format('d/m/Y H:i') }}
                                    · {{ $ultimaExportacion->generadoPor?->nombre ?? 'Usuario eliminado' }}
                                    · {{ $ultimaExportacion->tamanoLegible() }}
                                </p>
                            </div>
                            <span class="rounded-full bg-success px-2.5 py-1 text-xs font-black text-white">v{{ str_pad((string) $ultimaExportacion->version, 3, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <p class="mt-3 break-all font-mono text-[10px] text-muted-foreground" title="Huella SHA-256 completa">
                            SHA-256 {{ $ultimaExportacion->hash_sha256 }}
                        </p>
                    </div>

                    <details class="rounded-lg border border-border bg-muted/15 p-4" @if ($cuadrante->exportaciones->count() > 1) open @endif>
                        <summary class="cursor-pointer text-sm font-semibold text-foreground">
                            Historial · {{ $cuadrante->exportaciones->count() }} {{ Str::plural('versión', $cuadrante->exportaciones->count()) }}
                        </summary>
                        <div class="mt-3 max-h-52 space-y-2 overflow-y-auto pe-1">
                            @foreach ($cuadrante->exportaciones as $exportacion)
                                <div class="flex items-center justify-between gap-3 rounded-md border border-border bg-background px-3 py-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-semibold text-foreground">v{{ str_pad((string) $exportacion->version, 3, '0', STR_PAD_LEFT) }} · {{ $exportacion->generado_at->format('d/m/Y H:i') }}</p>
                                        <p class="mt-0.5 truncate text-[10px] text-muted-foreground">{{ $exportacion->generadoPor?->nombre ?? 'Usuario eliminado' }} · {{ $exportacion->tamanoLegible() }}</p>
                                    </div>
                                    <a href="{{ route('admin.planificacion-turnos.cuadrantes.exportaciones.descargar', [$cuadrante, $exportacion]) }}" class="rounded-md border border-border px-2.5 py-1.5 text-xs font-semibold text-primary transition hover:bg-primary/10">
                                        Descargar
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </details>
                </div>
            @else
                <div class="p-5">
                    <div class="rounded-lg border border-dashed border-border bg-muted/15 px-4 py-5 text-center">
                        <p class="text-sm font-semibold text-foreground">Todavía no hay ningún Excel publicado</p>
                        <p class="mt-1 text-xs text-muted-foreground">Se generará automáticamente al pulsar «Publicar semana».</p>
                    </div>
                </div>
            @endif
        </section>

        @if ($alertasCobertura->isNotEmpty())
            <section class="mb-6 rounded-lg border border-warning/30 bg-warning/10 p-4" aria-labelledby="alertas-cobertura-heading">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="alertas-cobertura-heading" class="font-semibold text-warning">{{ $alertasCobertura->count() }} avisos de cobertura</h2>
                        <p class="mt-1 text-xs text-muted-foreground">Son avisos operativos; puedes publicar, pero conviene revisarlos.</p>
                    </div>
                </div>
                <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($alertasCobertura as $alerta)
                        <div class="rounded-md border border-warning/20 bg-background/70 px-3 py-2 text-xs">
                            <p class="font-semibold text-foreground">{{ $nombresDia[$alerta['fecha']->dayOfWeekIso - 1] }} · {{ $alerta['area']->nombre }}</p>
                            <p class="mt-0.5 text-muted-foreground">{{ $alerta['hora_inicio'] }}–{{ $alerta['hora_fin'] }}: {{ $alerta['disponibles'] }} de {{ $alerta['minimo'] }} personas</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mb-6 grid gap-4 lg:grid-cols-2" aria-label="Herramientas de reutilización">
            <form method="POST" action="{{ route('admin.planificacion-turnos.cuadrantes.copiar', $cuadrante) }}" class="admin-card p-5">
                @csrf
                <h2 class="font-semibold text-foreground">Copiar esta semana</h2>
                <p class="mt-1 text-sm text-muted-foreground">Crea otro borrador con los mismos empleados, días relativos y horarios.</p>
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <x-input-label for="semana_copia" value="Semana de destino" />
                        <x-text-input id="semana_copia" name="semana_inicio" type="date" class="mt-1 block h-10 w-full" :value="$proximaSemana" required />
                    </div>
                    <button type="submit" class="admin-btn-primary h-10 justify-center">Copiar semana</button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.planificacion-turnos.cuadrantes.plantillas.store', $cuadrante) }}" class="admin-card p-5">
                @csrf
                <h2 class="font-semibold text-foreground">Guardar como plantilla</h2>
                <p class="mt-1 text-sm text-muted-foreground">Conserva este patrón para crear semanas futuras desde el listado.</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
                    <div>
                        <x-input-label for="nombre_plantilla" value="Nombre de la plantilla" />
                        <x-text-input id="nombre_plantilla" name="nombre" class="mt-1 block h-10 w-full" placeholder="Semana habitual de invierno" maxlength="120" required />
                    </div>
                    <button type="submit" class="admin-btn-secondary h-10 justify-center">Guardar plantilla</button>
                </div>
            </form>
        </section>

        @if ($cuadrante->esBorrador())
            <details class="admin-card mb-6 overflow-hidden">
                <summary class="cursor-pointer px-5 py-4 font-semibold text-foreground transition hover:bg-muted/30">Asignar turnos en bloque</summary>
                <form method="POST" action="{{ route('admin.planificacion-turnos.cuadrantes.jornadas.bloque', $cuadrante) }}" class="border-t border-border p-5">
                    @csrf
                    <input type="hidden" name="_formulario" value="bloque">
                    <div class="grid gap-5 xl:grid-cols-[minmax(260px,1fr)_minmax(240px,.8fr)_1.2fr]">
                        <fieldset x-data="{ buscarEmpleado: '' }">
                            <legend class="text-sm font-semibold text-foreground">Empleados</legend>
                            <div class="relative mt-2">
                                <svg class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                </svg>
                                <input type="search" x-model.debounce.150ms="buscarEmpleado" class="admin-input block h-11 w-full ps-9" placeholder="Buscar empleado..." aria-label="Buscar empleados para asignar en bloque">
                            </div>
                            <div class="mt-2 max-h-48 space-y-1 overflow-y-auto rounded-md border border-border p-2">
                                @foreach ($empleados as $empleado)
                                    <label
                                        class="flex min-h-10 items-center gap-2 rounded px-2 py-1.5 text-xs text-foreground hover:bg-muted/40"
                                        x-show="@js(mb_strtolower(\Illuminate\Support\Str::ascii($empleado->nombre.' '.$empleado->rol->etiqueta()))).includes(buscarEmpleado.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim())"
                                    >
                                        <input type="checkbox" name="usuario_ids[]" value="{{ $empleado->id }}" class="rounded border-border text-primary focus:ring-primary">
                                        <span class="min-w-0">
                                            <span class="block truncate font-semibold">{{ $empleado->nombre }}</span>
                                            <span class="block text-[10px] text-muted-foreground">{{ $empleado->rol->etiqueta() }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="text-sm font-semibold text-foreground">Días</legend>
                            <div class="mt-2 grid grid-cols-2 gap-1 rounded-md border border-border p-2">
                                @foreach ($dias as $indice => $dia)
                                    <label class="flex items-center gap-2 rounded px-2 py-1.5 text-xs text-foreground hover:bg-muted/40">
                                        <input type="checkbox" name="fechas[]" value="{{ $dia->toDateString() }}" class="rounded border-border text-primary focus:ring-primary">
                                        {{ Str::limit($nombresDia[$indice], 3, '') }} {{ $dia->format('d/m') }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-input-label for="bloque_area" value="Área" />
                                <select id="bloque_area" name="area_trabajo_id" class="admin-input mt-1 block h-10 w-full" required>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="bloque_inicio" value="Entrada" />
                                <x-text-input id="bloque_inicio" name="hora_inicio" type="time" class="mt-1 block h-10 w-full" value="08:00" required />
                            </div>
                            <div>
                                <x-input-label for="bloque_fin" value="Salida" />
                                <x-text-input id="bloque_fin" name="hora_fin" type="time" class="mt-1 block h-10 w-full" value="16:00" required />
                            </div>
                            <div>
                                <x-input-label for="bloque_descanso" value="Pausa (min)" />
                                <x-text-input id="bloque_descanso" name="minutos_descanso" type="number" min="0" max="720" step="5" class="mt-1 block h-10 w-full" value="30" required />
                            </div>
                            <button type="submit" class="admin-btn-primary h-10 self-end justify-center">Crear asignaciones</button>
                        </div>
                    </div>
                </form>
            </details>
        @endif

        @if ($cuadrante->esBorrador())
            <section class="admin-card mb-6 overflow-hidden" aria-labelledby="nuevo-turno-heading">
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-4 px-5 py-4 text-start transition hover:bg-muted/30"
                    x-on:click="formularioAbierto = ! formularioAbierto"
                    x-bind:aria-expanded="formularioAbierto"
                    aria-controls="formulario-turno"
                >
                    <span>
                        <span id="nuevo-turno-heading" class="block font-semibold text-foreground">Añadir turno</span>
                        <span class="mt-1 block text-sm text-muted-foreground">Abre el formulario solo cuando necesites modificar la planificación.</span>
                    </span>
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary/10 text-xl font-medium text-primary" x-text="formularioAbierto ? '−' : '+'" aria-hidden="true"></span>
                </button>

                <div id="formulario-turno" x-show="formularioAbierto" x-cloak class="border-t border-border">
                    <form method="POST" action="{{ route('admin.planificacion-turnos.cuadrantes.jornadas.store', $cuadrante) }}" class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-8 xl:items-end">
                        @csrf
                        <input type="hidden" name="_formulario" value="turno">

                        <div class="xl:col-span-2">
                            <x-input-label for="usuario_id" value="Empleado" />
                            <x-admin.searchable-select
                                id="usuario_id"
                                name="usuario_id"
                                class="mt-1"
                                :options="$empleados->mapWithKeys(fn ($empleado) => [$empleado->id => $empleado->nombre.' · '.$empleado->rol->etiqueta()])->all()"
                                :selected="old('usuario_id')"
                                search-placeholder="Buscar empleado para el turno..."
                                required
                            />
                        </div>

                        <div>
                            <x-input-label for="fecha" value="Día" />
                            <select id="fecha" name="fecha" class="admin-input mt-1 block h-10 w-full" required>
                                @foreach ($dias as $indice => $dia)
                                    <option value="{{ $dia->toDateString() }}" @selected(old('fecha') === $dia->toDateString())>{{ $nombresDia[$indice] }} {{ $dia->format('d/m') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="area_trabajo_id" value="Área" />
                            <select id="area_trabajo_id" name="area_trabajo_id" class="admin-input mt-1 block h-10 w-full" required>
                                @foreach ($areas as $area)
                                    <option value="{{ $area->id }}" @selected(old('area_trabajo_id') === $area->id)>{{ $area->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="hora_inicio" value="Entrada" />
                            <x-text-input id="hora_inicio" name="hora_inicio" type="time" step="900" class="mt-1 block h-10 w-full" :value="old('hora_inicio', '08:00')" required />
                        </div>

                        <div>
                            <x-input-label for="hora_fin" value="Salida" />
                            <x-text-input id="hora_fin" name="hora_fin" type="time" step="900" class="mt-1 block h-10 w-full" :value="old('hora_fin', '16:00')" required />
                        </div>

                        <div>
                            <x-input-label for="minutos_descanso" value="Pausa (min)" />
                            <x-text-input id="minutos_descanso" name="minutos_descanso" type="number" min="0" max="720" step="5" class="mt-1 block h-10 w-full" :value="old('minutos_descanso', 0)" required />
                        </div>

                        <button type="submit" class="admin-btn-primary h-10 justify-center">Guardar turno</button>

                        <label class="flex min-h-10 items-center gap-2 rounded-md border border-border bg-muted/20 px-3 text-sm text-foreground xl:col-span-2">
                            <input type="checkbox" name="termina_dia_siguiente" value="1" class="rounded border-border text-primary focus:ring-primary" @checked(old('termina_dia_siguiente'))>
                            Termina al día siguiente
                        </label>

                        <div class="md:col-span-2 xl:col-span-6">
                            <x-input-label for="notas" value="Notas del turno" />
                            <x-text-input id="notas" name="notas" class="mt-1 block h-10 w-full" :value="old('notas')" maxlength="500" placeholder="Opcional" />
                        </div>
                    </form>
                </div>
            </section>
        @endif

        <section class="admin-card mb-6 overflow-hidden" aria-labelledby="nueva-incidencia-heading">
            <button
                type="button"
                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-start transition hover:bg-muted/30"
                x-on:click="incidenciaAbierta = ! incidenciaAbierta"
                x-bind:aria-expanded="incidenciaAbierta"
                aria-controls="formulario-incidencia"
            >
                <span>
                    <span id="nueva-incidencia-heading" class="block font-semibold text-foreground">Registrar descanso, vacaciones o incidencia</span>
                    <span class="mt-1 block text-sm text-muted-foreground">Estos periodos pueden abarcar varias semanas y se aplican automáticamente a cada cuadrante.</span>
                </span>
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary/10 text-xl font-medium text-primary" x-text="incidenciaAbierta ? '−' : '+'" aria-hidden="true"></span>
            </button>

            <div id="formulario-incidencia" x-show="incidenciaAbierta" x-cloak class="border-t border-border">
                <form
                    method="POST"
                    action="{{ route('admin.planificacion-turnos.cuadrantes.incidencias.store', $cuadrante) }}"
                    class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-6 xl:items-end"
                    x-data="{ tipoIncidencia: '{{ old('tipo', 'descanso') }}' }"
                >
                    @csrf
                    <input type="hidden" name="_formulario" value="incidencia">

                    <div>
                        <x-input-label for="tipo" value="Tipo" />
                        <select id="tipo" name="tipo" x-model="tipoIncidencia" class="admin-input mt-1 block h-10 w-full" required>
                            @foreach ($tiposIncidencia as $tipoIncidencia)
                                <option value="{{ $tipoIncidencia->value }}" @selected(old('tipo', 'descanso') === $tipoIncidencia->value)>{{ $tipoIncidencia->etiqueta() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-2">
                        <x-input-label for="incidencia_usuario_id" value="Empleado" />
                        <div x-show="tipoIncidencia !== 'festivo'">
                            <x-admin.searchable-select
                                id="incidencia_usuario_id"
                                name="usuario_id"
                                class="mt-1 disabled:cursor-not-allowed disabled:opacity-60"
                                :options="$empleados->mapWithKeys(fn ($empleado) => [$empleado->id => $empleado->nombre.' · '.$empleado->rol->etiqueta()])->all()"
                                :selected="old('usuario_id')"
                                search-placeholder="Buscar empleado para la incidencia..."
                                x-bind:disabled="tipoIncidencia === 'festivo'"
                                x-bind:required="tipoIncidencia !== 'festivo'"
                            />
                        </div>
                        <p x-show="tipoIncidencia === 'festivo'" class="mt-1 text-[10px] text-muted-foreground">El festivo se aplica a todo el calendario.</p>
                    </div>

                    <div>
                        <x-input-label for="fecha_inicio" value="Desde" />
                        <x-text-input id="fecha_inicio" name="fecha_inicio" type="date" class="mt-1 block h-10 w-full" :value="old('fecha_inicio', $cuadrante->semana_inicio->toDateString())" required />
                    </div>

                    <div>
                        <x-input-label for="fecha_fin" value="Hasta" />
                        <x-text-input id="fecha_fin" name="fecha_fin" type="date" class="mt-1 block h-10 w-full" :value="old('fecha_fin', $cuadrante->semana_inicio->toDateString())" required />
                    </div>

                    <button type="submit" class="admin-btn-primary h-10 justify-center">Registrar</button>

                    <div class="md:col-span-2 xl:col-span-6">
                        <x-input-label for="incidencia_notas" value="Notas" />
                        <x-text-input id="incidencia_notas" name="notas" class="mt-1 block h-10 w-full" :value="old('notas')" maxlength="500" placeholder="Motivo o información útil para la planificación" />
                    </div>
                </form>
            </div>
        </section>

        <section class="admin-card overflow-hidden" aria-labelledby="semana-heading">
            <div class="border-b border-border px-5 py-4">
                <div class="flex flex-col justify-between gap-4 xl:flex-row xl:items-end">
                    <div>
                        <h2 id="semana-heading" class="font-semibold text-foreground">Vista por empleado</h2>
                        <p class="mt-1 text-sm text-muted-foreground">Compara toda la semana sin repetir el nombre de cada persona en cada día.</p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(230px,1fr)_190px_auto]">
                        <div>
                            <label for="buscar-empleado" class="sr-only">Buscar empleado</label>
                            <input id="buscar-empleado" type="search" x-model.debounce.200ms="busqueda" class="admin-input h-10 w-full" placeholder="Buscar empleado...">
                        </div>

                        <div>
                            <label for="filtrar-area" class="sr-only">Filtrar por área</label>
                            <select id="filtrar-area" x-model="area" class="admin-input h-10 w-full">
                                <option value="todas">Todas las áreas</option>
                                @foreach ($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="inline-flex h-10 rounded-md border border-border bg-muted/30 p-1" aria-label="Densidad de la tabla">
                            <button type="button" class="rounded px-3 text-xs font-semibold transition" x-on:click="detallada = false" x-bind:class="! detallada ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground'">Compacta</button>
                            <button type="button" class="rounded px-3 text-xs font-semibold transition" x-on:click="detallada = true" x-bind:class="detallada ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground'">Detallada</button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-xs text-muted-foreground">
                    @foreach ($areas as $area)
                        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $area->color }}"></span>{{ $area->nombre }}</span>
                    @endforeach
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-muted-foreground/30"></span>Sin asignar</span>
                </div>
            </div>

            <div class="max-h-[70vh] overflow-auto" tabindex="0" aria-label="Cuadrante semanal desplazable">
                <table class="w-full min-w-[1480px] border-separate border-spacing-0 text-left">
                    <caption class="sr-only">Turnos de cada empleado para los siete días de la semana</caption>
                    <thead class="sticky top-0 z-30 bg-muted/95 backdrop-blur">
                        <tr>
                            <th scope="col" class="sticky left-0 z-40 w-64 border-b border-e border-border bg-muted/95 px-4 py-3">
                                <span class="text-xs font-bold uppercase tracking-[0.12em] text-muted-foreground">Empleado</span>
                            </th>
                            @foreach ($dias as $indice => $dia)
                                @php
                                    $jornadasDia = $cuadrante->jornadas->filter(fn ($jornada) => $jornada->fecha->isSameDay($dia));
                                @endphp
                                <th scope="col" @class([
                                    'w-40 border-b border-e border-border px-3 py-3',
                                    'bg-primary/10' => $dia->isToday(),
                                ])>
                                    <span class="block text-[11px] font-bold uppercase tracking-[0.12em] text-muted-foreground">{{ $nombresDia[$indice] }}</span>
                                    <span class="mt-0.5 block text-base font-black text-foreground">{{ $dia->format('d/m') }}</span>
                                    <span class="mt-0.5 block text-[10px] font-medium text-muted-foreground">{{ number_format($jornadasDia->sum(fn ($jornada) => $jornada->minutosEfectivos()) / 60, 1, ',', '.') }} h</span>
                                    @foreach ($festivosPorDia[$dia->toDateString()] as $festivo)
                                        <div class="mt-1 flex max-w-full items-center gap-1 rounded px-1.5 py-0.5 text-[9px] font-bold text-white" style="background-color: {{ $festivo->tipo->color() }}" title="{{ $festivo->notas ?? $festivo->tipo->etiqueta() }}">
                                            <span class="min-w-0 flex-1 truncate">{{ $festivo->tipo->etiqueta() }}@if ($festivo->notas): {{ $festivo->notas }}@endif</span>
                                            <form method="POST" action="{{ route('admin.planificacion-turnos.cuadrantes.incidencias.destroy', [$cuadrante, $festivo]) }}" class="shrink-0" onsubmit="return confirm('¿Eliminar este festivo?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded text-white/75 hover:text-white" aria-label="Eliminar festivo">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12" /></svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </th>
                            @endforeach
                            <th scope="col" class="w-28 border-b border-border bg-muted/95 px-3 py-3 text-end">
                                <span class="text-xs font-bold uppercase tracking-[0.12em] text-muted-foreground">Total</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filasPlanificacion as $fila)
                            <tr
                                class="group"
                                data-nombre="{{ $fila['busqueda'] }}"
                                data-areas=",{{ $fila['area_ids'] }},"
                                x-show="coincide($el)"
                            >
                                <th scope="row" class="sticky left-0 z-20 border-b border-e border-border bg-card px-4 py-3 align-top group-hover:bg-muted/20">
                                    <p class="truncate text-sm font-bold text-foreground" title="{{ $fila['empleado']->nombre }}">{{ $fila['empleado']->nombre }}</p>
                                    <div class="mt-1 flex items-center gap-2 text-[10px] text-muted-foreground">
                                        <span>{{ $fila['empleado']->rol->etiqueta() }}</span>
                                        @if ($fila['areas']->isNotEmpty())
                                            <span aria-hidden="true">·</span>
                                            <span class="inline-flex items-center gap-1">
                                                @foreach ($fila['areas'] as $areaEmpleado)
                                                    <span class="h-2 w-2 rounded-full" style="background-color: {{ $areaEmpleado->color }}" title="{{ $areaEmpleado->nombre }}"></span>
                                                @endforeach
                                                <span x-show="detallada">{{ $fila['areas']->pluck('nombre')->implode(', ') }}</span>
                                            </span>
                                        @else
                                            <span aria-hidden="true">·</span>
                                            <span>Sin asignar</span>
                                        @endif
                                    </div>
                                </th>

                                @foreach ($dias as $dia)
                                    @php
                                        $jornadasCelda = $fila['jornadas_por_dia'][$dia->toDateString()];
                                        $incidenciasCelda = $fila['incidencias_por_dia'][$dia->toDateString()];
                                    @endphp
                                    <td class="border-b border-e border-border bg-card p-2 align-top group-hover:bg-muted/10">
                                        @if ($jornadasCelda->isEmpty() && $incidenciasCelda->isEmpty())
                                            <div class="flex min-h-10 items-center justify-center rounded-md border border-dashed border-border/70 text-[11px] text-muted-foreground/70">
                                                <span x-show="detallada">Sin turno</span>
                                                <span x-show="! detallada" aria-hidden="true">—</span>
                                            </div>
                                        @else
                                            <div class="space-y-1.5">
                                                @foreach ($incidenciasCelda as $incidencia)
                                                    <article class="relative rounded-md px-2.5 py-2 text-white shadow-sm" style="background-color: {{ $incidencia->tipo->color() }}">
                                                        <div @class(['pe-5' => true])>
                                                            <p class="text-[10px] font-black uppercase tracking-wide">{{ $incidencia->tipo->etiqueta() }}</p>
                                                            <p x-show="detallada" class="mt-0.5 text-[9px] text-white/85">
                                                                {{ $incidencia->fecha_inicio->format('d/m') }}–{{ $incidencia->fecha_fin->format('d/m') }}
                                                                @if ($incidencia->notas)
                                                                    · {{ $incidencia->notas }}
                                                                @endif
                                                            </p>
                                                        </div>

                                                        <form method="POST" action="{{ route('admin.planificacion-turnos.cuadrantes.incidencias.destroy', [$cuadrante, $incidencia]) }}" class="absolute end-1.5 top-1.5" onsubmit="return confirm('¿Eliminar esta incidencia laboral?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="rounded p-0.5 text-white/70 transition hover:bg-black/15 hover:text-white focus:bg-black/15 focus:text-white" aria-label="Eliminar {{ mb_strtolower($incidencia->tipo->etiqueta()) }} de {{ $fila['empleado']->nombre }}">
                                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6 18 18 6M6 6l12 12" /></svg>
                                                            </button>
                                                        </form>
                                                    </article>
                                                @endforeach

                                                @if ($incidenciasCelda->isNotEmpty() && $jornadasCelda->isNotEmpty())
                                                    <p class="rounded bg-destructive/10 px-2 py-1 text-[9px] font-bold text-destructive">Conflicto: turno durante incidencia</p>
                                                @endif

                                                @foreach ($jornadasCelda as $jornada)
                                                    <article class="relative rounded-md border border-border bg-background px-2.5 py-2 shadow-sm" style="border-left: 4px solid {{ $jornada->areaTrabajo?->color ?? '#64748B' }}">
                                                        <div @class(['flex items-start justify-between gap-1', 'pe-5' => $cuadrante->esBorrador()])>
                                                            <p class="whitespace-nowrap font-mono text-xs font-black text-foreground">
                                                                {{ Str::of($jornada->hora_inicio)->substr(0, 5) }}–{{ Str::of($jornada->hora_fin)->substr(0, 5) }}@if ($jornada->termina_dia_siguiente)<sup class="ms-0.5 text-primary">+1</sup>@endif
                                                            </p>
                                                            <span class="whitespace-nowrap text-[9px] font-bold text-primary">{{ number_format($jornada->horasEfectivas(), 2, ',', '.') }} h</span>
                                                        </div>

                                                        <div x-show="detallada" class="mt-1 space-y-0.5 text-[9px] leading-tight text-muted-foreground">
                                                            <p>{{ $jornada->areaTrabajo?->nombre ?? 'Sin área' }}</p>
                                                            @if ($jornada->minutos_descanso > 0)
                                                                <p>Pausa: {{ $jornada->minutos_descanso }} min</p>
                                                            @endif
                                                            @if ($jornada->notas)
                                                                <p class="truncate" title="{{ $jornada->notas }}">{{ $jornada->notas }}</p>
                                                            @endif
                                                        </div>

                                                        @if ($cuadrante->esBorrador())
                                                            <form method="POST" action="{{ route('admin.planificacion-turnos.cuadrantes.jornadas.destroy', [$cuadrante, $jornada]) }}" class="absolute end-1.5 top-1.5" onsubmit="return confirm('¿Eliminar este turno?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="rounded p-0.5 text-muted-foreground opacity-60 transition hover:bg-destructive/10 hover:text-destructive group-hover:opacity-100 focus:opacity-100" aria-label="Eliminar turno de {{ $jornada->usuario->nombre }}">
                                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6 18 18 6M6 6l12 12" /></svg>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </article>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="border-b border-border bg-card px-3 py-3 text-end align-top group-hover:bg-muted/10">
                                    <p class="text-sm font-black text-foreground">{{ number_format($fila['minutos'] / 60, 1, ',', '.') }} h</p>
                                    <p class="mt-1 text-[9px] text-muted-foreground">de {{ number_format($fila['minutos_contrato'] / 60, 1, ',', '.') }} h</p>
                                    <span @class([
                                        'mt-1 inline-flex rounded px-1.5 py-0.5 text-[9px] font-bold',
                                        'bg-success/10 text-success' => abs($fila['desviacion_minutos']) < 30,
                                        'bg-warning/10 text-warning' => abs($fila['desviacion_minutos']) >= 30 && abs($fila['desviacion_minutos']) < 120,
                                        'bg-destructive/10 text-destructive' => abs($fila['desviacion_minutos']) >= 120,
                                    ])>
                                        {{ $fila['desviacion_minutos'] >= 0 ? '+' : '−' }}{{ number_format(abs($fila['desviacion_minutos']) / 60, 1, ',', '.') }} h
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-2 border-t border-border bg-muted/20 px-5 py-3 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
                <p>Mostrando {{ $empleados->count() }} personas. Utiliza los filtros para reducir la tabla.</p>
                <p>En móvil, desplaza la tabla horizontalmente; el empleado permanece visible.</p>
            </div>
        </section>

        <section class="admin-card mt-6 overflow-hidden" aria-labelledby="cobertura-heading">
            <div class="border-b border-border px-5 py-4">
                <h2 id="cobertura-heading" class="font-semibold text-foreground">Cobertura mínima</h2>
                <p class="mt-1 text-sm text-muted-foreground">Define cuántas personas deben coincidir en cada área y franja recurrente.</p>
            </div>
            <div class="grid gap-5 p-5 xl:grid-cols-[1fr_1.3fr]">
                <form method="POST" action="{{ route('admin.planificacion-turnos.coberturas.store') }}" class="grid gap-3 sm:grid-cols-2">
                    @csrf
                    <div>
                        <x-input-label for="cobertura_area" value="Área" />
                        <select id="cobertura_area" name="area_trabajo_id" class="admin-input mt-1 block h-10 w-full" required>
                            @foreach ($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="dia_semana" value="Día" />
                        <select id="dia_semana" name="dia_semana" class="admin-input mt-1 block h-10 w-full" required>
                            @foreach ($nombresDia as $indice => $nombreDia)
                                <option value="{{ $indice + 1 }}">{{ $nombreDia }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="cobertura_inicio" value="Desde" />
                        <x-text-input id="cobertura_inicio" name="hora_inicio" type="time" class="mt-1 block h-10 w-full" value="12:00" required />
                    </div>
                    <div>
                        <x-input-label for="cobertura_fin" value="Hasta" />
                        <x-text-input id="cobertura_fin" name="hora_fin" type="time" class="mt-1 block h-10 w-full" value="16:00" required />
                    </div>
                    <div>
                        <x-input-label for="minimo_personas" value="Personas mínimas" />
                        <x-text-input id="minimo_personas" name="minimo_personas" type="number" min="1" max="50" class="mt-1 block h-10 w-full" value="2" required />
                    </div>
                    <button type="submit" class="admin-btn-secondary h-10 self-end justify-center">Añadir regla</button>
                </form>

                <div class="space-y-2">
                    @forelse ($reglasCobertura as $regla)
                        <div class="flex items-center justify-between gap-3 rounded-md border border-border px-3 py-2">
                            <div class="min-w-0 text-xs">
                                <p class="font-semibold text-foreground">{{ $nombresDia[$regla->dia_semana - 1] }} · {{ $regla->areaTrabajo->nombre }}</p>
                                <p class="mt-0.5 text-muted-foreground">{{ Str::of($regla->hora_inicio)->substr(0, 5) }}–{{ Str::of($regla->hora_fin)->substr(0, 5) }} · mínimo {{ $regla->minimo_personas }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.planificacion-turnos.coberturas.destroy', $regla) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded p-1.5 text-destructive hover:bg-destructive/10" aria-label="Eliminar regla de cobertura">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18 18 6M6 6l12 12" /></svg>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="rounded-md border border-dashed border-border p-5 text-center text-xs text-muted-foreground">Todavía no hay reglas de cobertura.</div>
                    @endforelse
                </div>
            </div>
        </section>

        @if ($cuadrante->notas)
            <section class="admin-card mt-6 p-5">
                <h2 class="text-sm font-semibold text-foreground">Notas de la semana</h2>
                <p class="mt-2 whitespace-pre-line text-sm text-muted-foreground">{{ $cuadrante->notas }}</p>
            </section>
        @endif
    </div>
</x-app-layout>
