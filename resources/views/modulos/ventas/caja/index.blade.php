<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Caja" :description="$turnos->total().' turnos registrados'">
            <x-slot name="actions">
                @if ($turnoAbierto)
                    <a href="{{ route('admin.ventas.caja.show', $turnoAbierto) }}" class="admin-btn-primary">Ver caja abierta</a>
                @endif
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.ventas.partials.nav')

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-destructive/25 bg-destructive/10 p-4 text-sm text-destructive">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-4 lg:grid-cols-[360px_1fr]">
        <section class="admin-card p-4">
            <h2 class="text-base font-semibold text-foreground">Abrir caja</h2>
            <p class="mt-1 text-sm text-muted-foreground">Registra el saldo inicial antes de empezar el turno.</p>

            <form method="POST" action="{{ route('admin.ventas.caja.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <x-input-label for="recinto_id" value="Recinto" />
                    <select id="recinto_id" name="recinto_id" class="admin-input mt-1 block h-10 w-full">
                        <option value="">Caja general</option>
                        @foreach ($recintos as $recinto)
                            <option value="{{ $recinto->id }}" @selected(old('recinto_id') === $recinto->id)>{{ $recinto->nombre_comercial }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="saldo_inicial" value="Saldo inicial" />
                    <input id="saldo_inicial" name="saldo_inicial" type="number" step="0.01" min="0" class="admin-input mt-1 block h-10 w-full" value="{{ old('saldo_inicial', '0.00') }}" required>
                </div>
                <div>
                    <x-input-label for="notas_apertura" value="Notas de apertura" />
                    <textarea id="notas_apertura" name="notas_apertura" rows="3" class="admin-input mt-1 block w-full">{{ old('notas_apertura') }}</textarea>
                </div>
                <button type="submit" class="admin-btn-primary w-full">Abrir caja</button>
            </form>
        </section>

        <section class="overflow-x-auto rounded-lg border border-border bg-card">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50">
                        <th class="px-4 py-3 text-left font-medium text-foreground">Numero</th>
                        <th class="px-4 py-3 text-left font-medium text-foreground">Recinto</th>
                        <th class="px-4 py-3 text-left font-medium text-foreground">Estado</th>
                        <th class="px-4 py-3 text-right font-medium text-foreground">Ventas</th>
                        <th class="px-4 py-3 text-right font-medium text-foreground">Descuadre</th>
                        <th class="px-4 py-3 text-left font-medium text-foreground">Apertura</th>
                        <th class="px-4 py-3 text-right font-medium text-foreground">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($turnos as $turno)
                        <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                            <td class="px-4 py-3 font-medium text-foreground">{{ $turno->numero }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $turno->recinto?->nombre_comercial ?? 'Caja general' }}</td>
                            <td class="px-4 py-3"><x-admin.status-badge :variant="$turno->estado->variante()">{{ $turno->estado->etiqueta() }}</x-admin.status-badge></td>
                            <td class="px-4 py-3 text-right text-foreground">{{ number_format((float) $turno->total_ventas, 2, ',', '.') }} EUR</td>
                            <td class="px-4 py-3 text-right {{ abs((float) $turno->descuadre) > 0.005 ? 'font-semibold text-destructive' : 'text-muted-foreground' }}">{{ number_format((float) $turno->descuadre, 2, ',', '.') }} EUR</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $turno->abierta_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.ventas.caja.show', $turno) }}" class="inline-flex rounded-md p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground" title="Ver caja" aria-label="Ver caja">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-muted-foreground">No hay turnos de caja todavia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>

    <div class="mt-4">{{ $turnos->links() }}</div>
</x-app-layout>
