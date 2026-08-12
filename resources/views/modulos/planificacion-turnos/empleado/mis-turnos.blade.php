<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header
            title="Mis turnos"
            description="Consulta tu horario semanal. Solo aparecen cuadrantes publicados por la persona responsable."
        />
    </x-slot>

    @if ($cuadrantes->isEmpty())
        <section class="admin-card mx-auto max-w-3xl p-8 text-center sm:p-12">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 2v3m8-3v3M3 9h18M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm3 9h3m3 0h3m-6 4h3" />
                </svg>
            </span>
            <h2 class="mt-4 text-xl font-bold text-foreground">Todavía no tienes turnos publicados</h2>
            <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-muted-foreground">
                Cuando se publique un cuadrante que te incluya, podrás elegir aquí la semana y consultar cada tramo de trabajo.
            </p>
        </section>
    @else
        @php
            $nombresDia = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
            $totalMinutos = $cuadrante->jornadas->sum(fn ($jornada) => $jornada->minutosEfectivos());
            $diasConTurno = $cuadrante->jornadas->pluck(fn ($jornada) => $jornada->fecha->toDateString())->unique()->count();
        @endphp

        <section class="admin-card mb-5 overflow-hidden">
            <div class="grid gap-5 p-5 lg:grid-cols-[minmax(260px,1fr)_auto] lg:items-end">
                <form method="GET" action="{{ route('admin.mis-turnos.index') }}" class="max-w-xl">
                    <x-input-label for="semana" value="Semana publicada" />
                    <div class="mt-1.5 flex flex-col gap-2 sm:flex-row">
                        <select id="semana" name="semana" class="admin-input block h-11 w-full" aria-describedby="semana-ayuda">
                            @foreach ($cuadrantes as $opcion)
                                <option value="{{ $opcion->semana_inicio->toDateString() }}" @selected($opcion->is($cuadrante))>
                                    {{ $opcion->semana_inicio->format('d/m/Y') }} – {{ $opcion->semanaFin()->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="admin-btn-primary min-h-11 shrink-0">Ver semana</button>
                    </div>
                    <p id="semana-ayuda" class="mt-2 text-xs text-muted-foreground">Puedes volver a cualquier semana que siga publicada.</p>
                </form>

                <div class="flex flex-wrap gap-2 lg:justify-end">
                    <span class="inline-flex items-center gap-2 rounded-full bg-success/10 px-3 py-1.5 text-xs font-bold text-success">
                        <span class="h-2 w-2 rounded-full bg-success"></span>
                        Publicado {{ $cuadrante->publicado_at?->format('d/m/Y H:i') }}
                    </span>
                </div>
            </div>
        </section>

        <section class="mb-5 grid gap-3 sm:grid-cols-3" aria-label="Resumen de la semana">
            <article class="admin-card p-4">
                <p class="text-xs font-bold uppercase tracking-[0.1em] text-muted-foreground">Horas previstas</p>
                <p class="mt-2 text-2xl font-black tabular-nums text-foreground">{{ number_format($totalMinutos / 60, 1, ',', '.') }} h</p>
            </article>
            <article class="admin-card p-4">
                <p class="text-xs font-bold uppercase tracking-[0.1em] text-muted-foreground">Días con turno</p>
                <p class="mt-2 text-2xl font-black tabular-nums text-foreground">{{ $diasConTurno }}</p>
            </article>
            <article class="admin-card p-4">
                <p class="text-xs font-bold uppercase tracking-[0.1em] text-muted-foreground">Tramos</p>
                <p class="mt-2 text-2xl font-black tabular-nums text-foreground">{{ $cuadrante->jornadas->count() }}</p>
            </article>
        </section>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-7" aria-label="Turnos de la semana seleccionada">
            @foreach ($dias as $indice => $dia)
                @php
                    $jornadasDia = $cuadrante->jornadas->filter(fn ($jornada) => $jornada->fecha->isSameDay($dia));
                    $incidenciasDia = $incidenciasPorDia->get($dia->toDateString(), collect());
                @endphp

                <article @class([
                    'admin-card min-w-0 overflow-hidden',
                    'ring-2 ring-primary ring-offset-2 ring-offset-background' => $dia->isToday(),
                ])>
                    <header @class([
                        'border-b border-border px-4 py-3',
                        'bg-primary text-primary-foreground' => $dia->isToday(),
                        'bg-muted/50' => ! $dia->isToday(),
                    ])>
                        <p class="text-xs font-bold uppercase tracking-[0.12em] {{ $dia->isToday() ? 'text-primary-foreground/80' : 'text-muted-foreground' }}">{{ $nombresDia[$indice] }}</p>
                        <p class="mt-0.5 text-lg font-black">{{ $dia->format('d/m') }}</p>
                        @if ($dia->isToday())
                            <p class="mt-1 text-[10px] font-bold uppercase">Hoy</p>
                        @endif
                    </header>

                    <div class="space-y-2 p-3">
                        @foreach ($incidenciasDia as $incidencia)
                            <div class="rounded-lg px-3 py-2 text-white" style="background-color: {{ $incidencia->tipo->color() }}">
                                <p class="text-[10px] font-black uppercase tracking-wide">{{ $incidencia->tipo->etiqueta() }}</p>
                                @if ($incidencia->notas)
                                    <p class="mt-1 text-[10px] leading-4 text-white/85">{{ $incidencia->notas }}</p>
                                @endif
                            </div>
                        @endforeach

                        @forelse ($jornadasDia as $jornada)
                            <div class="rounded-lg border border-border bg-background p-3" style="border-left: 4px solid {{ $jornada->areaTrabajo?->color ?? '#64748B' }}">
                                <p class="whitespace-nowrap text-sm font-black tabular-nums text-foreground">
                                    {{ Str::of($jornada->hora_inicio)->substr(0, 5) }}–{{ Str::of($jornada->hora_fin)->substr(0, 5) }}@if ($jornada->termina_dia_siguiente)<sup class="ms-0.5 text-primary">+1</sup>@endif
                                </p>
                                <p class="mt-1 text-xs font-semibold text-foreground">{{ $jornada->areaTrabajo?->nombre ?? 'Sin área' }}</p>
                                <p class="mt-1 text-[11px] text-muted-foreground">
                                    {{ number_format($jornada->horasEfectivas(), 2, ',', '.') }} h efectivas
                                    @if ($jornada->minutos_descanso > 0)
                                        · {{ $jornada->minutos_descanso }} min de pausa
                                    @endif
                                </p>
                                @if ($jornada->notas)
                                    <p class="mt-2 border-t border-border pt-2 text-[11px] leading-4 text-muted-foreground">{{ $jornada->notas }}</p>
                                @endif
                            </div>
                        @empty
                            @if ($incidenciasDia->isEmpty())
                                <div class="flex min-h-24 items-center justify-center rounded-lg border border-dashed border-border bg-muted/20 px-3 text-center text-xs font-medium text-muted-foreground">
                                    Sin turno
                                </div>
                            @endif
                        @endforelse
                    </div>
                </article>
            @endforeach
        </section>

        <p class="mt-5 rounded-xl border border-border bg-muted/30 px-4 py-3 text-xs leading-5 text-muted-foreground">
            Este horario es informativo y corresponde a la última publicación de la semana. Si se reabre para hacer cambios, dejará de mostrarse hasta que se publique de nuevo.
        </p>
    @endif
</x-app-layout>
