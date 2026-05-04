<x-publico-layout title="Contacto | Cerveceria Europa">
    <section class="bg-public-background py-16">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div>
                <p class="text-sm font-black uppercase tracking-[0.2em] text-public-primary">Contacto</p>
                <h1 class="mt-3 text-5xl font-black text-public-foreground">{{ $seccion->titulo ?: 'Ven a Cerveceria Europa' }}</h1>
                <p class="mt-5 text-lg leading-8 text-public-muted">{{ $seccion->subtitulo ?: 'Cervezas de importacion, artesanas y cocina de bar para compartir.' }}</p>
                @if ($seccion->contenido)
                    <p class="mt-4 text-public-muted">{{ $seccion->contenido }}</p>
                @endif
                @php($datos = $seccion->datos ?? [])
                <div class="mt-8 space-y-4 text-public-muted">
                    <p><span class="font-bold text-public-foreground">Ubicacion:</span> {{ $datos['ubicacion'] ?? 'Sevilla' }}</p>
                    <p><span class="font-bold text-public-foreground">Reservas:</span> {{ $datos['reservas'] ?? 'pendiente de configurar' }}</p>
                    <p><span class="font-bold text-public-foreground">Horario:</span> {{ $datos['horario'] ?? 'pendiente de configurar' }}</p>
                </div>
            </div>
            <div class="min-h-96 rounded-lg border border-public-border/15 bg-[linear-gradient(135deg,rgba(208,138,36,.25),rgba(31,91,69,.18)),url('https://images.unsplash.com/photo-1559925393-8be0ec4767c8?auto=format&fit=crop&w=1200&q=80')] bg-cover bg-center shadow-2xl shadow-black/30"></div>
        </div>
    </section>
</x-publico-layout>
