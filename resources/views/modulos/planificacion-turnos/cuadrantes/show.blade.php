@php
    $nombresDia = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    $totalMinutos = $cuadrante->jornadas->sum(fn ($jornada) => $jornada->minutosEfectivos());
    $totalEmpleados = $cuadrante->jornadas->pluck('usuario_id')->unique()->count();
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header
            title="Cuadrante del {{ $cuadrante->semana_inicio->format('d/m') }} al {{ $cuadrante->semanaFin()->format('d/m/Y') }}"
            description="Planificación semanal por tramos. Un empleado puede tener varios tramos el mismo día."
        >
            <x-slot name="actions">
                <a href="{{ route('admin.planificacion-turnos.cuadrantes.index') }}" class="admin-btn-secondary">Volver</a>

                @if ($cuadrante->esBorrador())
                    <form method="POST" action="{{ route('admin.planificacion-turnos.cuadrantes.publicar', $cuadrante) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="admin-btn-primary" onclick="return confirm('Publicar el cuadrante bloqueará su edición hasta que lo reabras.');">Publicar semana</button>
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

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-destructive/25 bg-destructive/10 p-4 text-sm text-destructive">
            <p class="font-semibold">Revisa el nuevo tramo:</p>
            <ul class="mt-1 list-disc space-y-1 ps-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
            <p class="mt-2 text-2xl font-black text-foreground">{{ $totalEmpleados }}</p>
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

    @if ($cuadrante->esBorrador())
        <section class="admin-card mb-6 overflow-hidden" x-data="{ nocturno: {{ old('termina_dia_siguiente') ? 'true' : 'false' }} }" aria-labelledby="nuevo-tramo-heading">
            <div class="border-b border-border bg-muted/30 px-5 py-4">
                <h2 id="nuevo-tramo-heading" class="font-semibold text-foreground">Añadir tramo de trabajo</h2>
                <p class="mt-1 text-sm text-muted-foreground">Para un turno partido, guarda cada tramo por separado.</p>
            </div>

            <form method="POST" action="{{ route('admin.planificacion-turnos.cuadrantes.jornadas.store', $cuadrante) }}" class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-7 xl:items-end">
                @csrf

                <div class="xl:col-span-2">
                    <x-input-label for="usuario_id" value="Empleado" />
                    <select id="usuario_id" name="usuario_id" class="admin-input mt-1 block h-10 w-full" required>
                        <option value="">Selecciona...</option>
                        @foreach ($empleados as $empleado)
                            <option value="{{ $empleado->id }}" @selected(old('usuario_id') === $empleado->id)>{{ $empleado->nombre }} · {{ $empleado->rol->etiqueta() }}</option>
                        @endforeach
                    </select>
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

                <label class="flex min-h-10 items-center gap-2 rounded-md border border-border bg-muted/20 px-3 text-sm text-foreground xl:col-span-2">
                    <input type="checkbox" name="termina_dia_siguiente" value="1" x-model="nocturno" class="rounded border-border text-primary focus:ring-primary">
                    Termina al día siguiente
                </label>

                <div class="xl:col-span-4">
                    <x-input-label for="notas" value="Notas del tramo" />
                    <x-text-input id="notas" name="notas" class="mt-1 block h-10 w-full" :value="old('notas')" maxlength="500" placeholder="Opcional" />
                </div>

                <button type="submit" class="admin-btn-primary h-10 justify-center">Añadir tramo</button>
            </form>
        </section>
    @endif

    <section class="admin-card overflow-hidden" aria-labelledby="semana-heading">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-5 py-4">
            <div>
                <h2 id="semana-heading" class="font-semibold text-foreground">Vista semanal</h2>
                <p class="mt-1 text-sm text-muted-foreground">Desplaza horizontalmente en pantallas pequenas.</p>
            </div>

            <div class="flex flex-wrap gap-3 text-xs text-muted-foreground">
                @foreach ($areas as $area)
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $area->color }}"></span>{{ $area->nombre }}</span>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            <div class="grid min-w-[1260px] grid-cols-7 divide-x divide-border">
                @foreach ($dias as $indice => $dia)
                    @php
                        $jornadasDia = $cuadrante->jornadas->filter(fn ($jornada) => $jornada->fecha->isSameDay($dia));
                    @endphp
                    <section class="min-h-[420px] bg-card" aria-labelledby="dia-{{ $indice }}">
                        <header @class([
                            'sticky top-0 z-10 border-b border-border px-4 py-3 backdrop-blur',
                            'bg-primary/10' => $dia->isToday(),
                            'bg-card/95' => ! $dia->isToday(),
                        ])>
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-muted-foreground">{{ $nombresDia[$indice] }}</p>
                            <h3 id="dia-{{ $indice }}" class="mt-0.5 text-lg font-black text-foreground">{{ $dia->format('d/m') }}</h3>
                            <p class="mt-1 text-xs text-muted-foreground">{{ number_format($jornadasDia->sum(fn ($jornada) => $jornada->minutosEfectivos()) / 60, 1, ',', '.') }} h</p>
                        </header>

                        <div class="space-y-2 p-3">
                            @forelse ($jornadasDia as $jornada)
                                <article class="group relative rounded-lg border border-border bg-background p-3 shadow-sm transition hover:border-primary/35 hover:shadow-md">
                                    <div @class(['min-w-0', 'pe-6' => $cuadrante->esBorrador()])>
                                            <p class="text-sm font-bold leading-tight text-foreground">{{ $jornada->usuario->nombre }}</p>
                                            <p class="mt-0.5 flex items-center gap-1.5 truncate text-[11px] text-muted-foreground">
                                                <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $jornada->areaTrabajo?->color ?? '#64748B' }}"></span>
                                                {{ $jornada->areaTrabajo?->nombre ?? 'Sin área' }}
                                            </p>

                                        @if ($cuadrante->esBorrador())
                                            <form method="POST" action="{{ route('admin.planificacion-turnos.cuadrantes.jornadas.destroy', [$cuadrante, $jornada]) }}" class="absolute end-2 top-2" onsubmit="return confirm('¿Eliminar este tramo de trabajo?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded p-1 text-muted-foreground opacity-0 transition hover:bg-destructive/10 hover:text-destructive group-hover:opacity-100 focus:opacity-100" aria-label="Eliminar tramo de {{ $jornada->usuario->nombre }}">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18 18 6M6 6l12 12" /></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    <div class="mt-3 flex items-end justify-between gap-2">
                                        <div>
                                            <p class="whitespace-nowrap font-mono text-sm font-bold text-foreground">
                                                {{ Str::of($jornada->hora_inicio)->substr(0, 5) }}–{{ Str::of($jornada->hora_fin)->substr(0, 5) }}@if ($jornada->termina_dia_siguiente)<sup class="ms-0.5 text-primary">+1</sup>@endif
                                            </p>
                                            @if ($jornada->minutos_descanso > 0)
                                                <p class="mt-0.5 text-[10px] text-muted-foreground">Pausa {{ $jornada->minutos_descanso }} min</p>
                                            @endif
                                        </div>
                                        <span class="shrink-0 whitespace-nowrap rounded-md bg-primary/10 px-1.5 py-0.5 text-[10px] font-bold text-primary">{{ number_format($jornada->horasEfectivas(), 2, ',', '.') }} h</span>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-lg border border-dashed border-border px-3 py-8 text-center text-xs text-muted-foreground">Sin jornadas</div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </section>

    @if ($cuadrante->notas)
        <section class="admin-card mt-6 p-5">
            <h2 class="text-sm font-semibold text-foreground">Notas de la semana</h2>
            <p class="mt-2 whitespace-pre-line text-sm text-muted-foreground">{{ $cuadrante->notas }}</p>
        </section>
    @endif
</x-app-layout>
