<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header
            title="Planificación de turnos"
            description="Crea cuadrantes semanales claros, calcula horas y evita solapamientos."
        />
    </x-slot>

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <section class="admin-card overflow-hidden" aria-labelledby="cuadrantes-heading">
            <div class="flex items-center justify-between gap-4 border-b border-border p-5">
                <div>
                    <h2 id="cuadrantes-heading" class="font-semibold text-foreground">Semanas planificadas</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Cada semana conserva su propio historial y estado.</p>
                </div>
            </div>

            <div class="divide-y divide-border">
                @forelse ($cuadrantes as $cuadrante)
                    <a href="{{ route('admin.planificacion-turnos.cuadrantes.show', $cuadrante) }}" class="group flex flex-col gap-3 p-5 transition hover:bg-muted/40 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <span class="text-[10px] font-bold uppercase tracking-wide">Sem</span>
                                <span class="text-lg font-black leading-none">{{ $cuadrante->semana_inicio->weekOfYear }}</span>
                            </div>
                            <div>
                                <p class="font-semibold text-foreground">
                                    {{ $cuadrante->semana_inicio->format('d/m/Y') }} — {{ $cuadrante->semanaFin()->format('d/m/Y') }}
                                </p>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{ $cuadrante->jornadas_count }} {{ Str::plural('tramo', $cuadrante->jornadas_count) }} planificados
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span @class([
                                'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                'bg-warning/10 text-warning' => $cuadrante->estado->value === 'borrador',
                                'bg-success/10 text-success' => $cuadrante->estado->value === 'publicado',
                            ])>{{ $cuadrante->estado->etiqueta() }}</span>
                            <svg class="h-4 w-4 text-muted-foreground transition group-hover:translate-x-0.5 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 18 6-6-6-6" />
                            </svg>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-14 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 2v3m8-3v3M3 9h18M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-foreground">Todavía no hay cuadrantes</h3>
                        <p class="mx-auto mt-1 max-w-sm text-sm text-muted-foreground">Crea la primera semana y empieza a asignar jornadas al equipo.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <aside class="admin-card h-fit p-5" aria-labelledby="nueva-semana-heading">
            <div class="mb-5">
                <h2 id="nueva-semana-heading" class="font-semibold text-foreground">Nueva semana</h2>
                <p class="mt-1 text-sm text-muted-foreground">Puedes elegir cualquier día; guardaremos el lunes correspondiente.</p>
            </div>

            <form method="POST" action="{{ route('admin.planificacion-turnos.cuadrantes.store') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="semana_inicio" value="Semana" />
                    <x-text-input id="semana_inicio" name="semana_inicio" type="date" class="mt-1 block h-10 w-full" :value="old('semana_inicio', $proximoLunes)" required />
                    <x-input-error :messages="$errors->get('semana_inicio')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="notas" value="Notas internas" />
                    <textarea id="notas" name="notas" rows="3" maxlength="2000" class="admin-input mt-1 block w-full" placeholder="Festivos, eventos o necesidades especiales...">{{ old('notas') }}</textarea>
                    <x-input-error :messages="$errors->get('notas')" class="mt-2" />
                </div>

                <button type="submit" class="admin-btn-primary w-full justify-center">Crear cuadrante</button>
            </form>
        </aside>
    </div>

    <div class="mt-4">{{ $cuadrantes->links() }}</div>
</x-app-layout>
