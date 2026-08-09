<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $pageTitle = filled($title) ? $title.' | '.$negocio->nombre_comercial : $negocio->nombre_comercial;
            $pageDescription = $description ?? $negocio->descripcion_corta ?? $negocio->eslogan;
            $ogImage = $ogImage ?? asset('favicon.svg');
        @endphp
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $pageDescription }}">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ url()->current() }}">

        <meta property="og:site_name" content="{{ $negocio->nombre_comercial }}">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $pageDescription }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:locale" content="es_ES">
        <meta name="twitter:card" content="summary_large_image">

        <meta name="theme-color" content="#0f0a06">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="manifest" href="/site.webmanifest">

        {{-- La web publica v2 es siempre oscura --}}
        <script>document.documentElement.classList.add('dark');</script>

        {{-- Bebas Neue (display) + Inter (body) + JetBrains Mono (precios) --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="flex min-h-screen flex-col bg-public-background font-sans text-public-foreground">
        <header class="sticky top-0 z-40 border-b border-public-border/15 bg-public-background/90 backdrop-blur">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <a href="{{ route('web.inicio') }}" class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-md bg-[#d08a24] text-[#23180f]">
                            <x-brand.hospitality-icon class="h-6 w-6" />
                        </span>
                        <span class="min-w-0">
                            <span class="block max-w-48 truncate text-lg font-black leading-5 text-public-foreground">{{ $negocio->nombre_comercial }}</span>
                            @if ($negocio->localidad)
                                <span class="block text-xs font-semibold uppercase tracking-[0.18em] text-public-primary">{{ $negocio->localidad }}</span>
                            @endif
                        </span>
                    </a>
                    <nav class="hidden items-center gap-6 text-sm font-semibold text-public-muted md:flex" aria-label="Navegacion principal">
                        <a href="{{ route('web.carta') }}" class="hover:text-public-primary">Carta</a>
                        <a href="{{ route('web.cervezas') }}" class="hover:text-public-primary">Cervezas</a>
                        <a href="{{ route('web.fuera-carta') }}" class="hover:text-public-primary">Fuera de carta</a>
                        <a href="{{ route('web.recomendaciones') }}" class="hover:text-public-primary">Recomendaciones</a>
                        @if (\App\Models\Modulo::activo('blog'))
                            <a href="{{ route('web.blog') }}" class="hover:text-public-primary">Blog</a>
                        @endif
                        <a href="{{ route('web.contacto') }}" class="hover:text-public-primary">Contacto</a>
                    </nav>
                    <x-admin.theme-toggle size="sm" />
                </div>

                <nav class="mt-3 flex gap-5 overflow-x-auto border-t border-public-border/10 pt-3 text-xs font-semibold text-public-muted md:hidden" aria-label="Navegacion movil">
                    <a href="{{ route('web.carta') }}" class="shrink-0 hover:text-public-primary">Carta</a>
                    <a href="{{ route('web.cervezas') }}" class="shrink-0 hover:text-public-primary">Cervezas</a>
                    <a href="{{ route('web.fuera-carta') }}" class="shrink-0 hover:text-public-primary">Fuera de carta</a>
                    <a href="{{ route('web.recomendaciones') }}" class="shrink-0 hover:text-public-primary">Recomendaciones</a>
                    @if (\App\Models\Modulo::activo('blog'))
                        <a href="{{ route('web.blog') }}" class="shrink-0 hover:text-public-primary">Blog</a>
                    @endif
                    <a href="{{ route('web.contacto') }}" class="shrink-0 hover:text-public-primary">Contacto</a>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="border-t bg-gradient-to-b from-stout to-[#060403] px-8 pb-6 pt-20" style="border-color: var(--v2-line);" aria-labelledby="footer-heading">
            <h2 id="footer-heading" class="sr-only">Informacion de {{ $negocio->nombre_comercial }}</h2>
            <div class="mx-auto grid max-w-[1440px] grid-cols-1 gap-10 pb-14 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr]" style="border-bottom: 1px solid var(--v2-line);">
                <section>
                    <div class="max-w-[12ch] font-display text-[56px] leading-[0.9] tracking-[0.005em] text-ink">{{ $negocio->nombre_comercial }}</div>
                    <p class="my-4 max-w-[36ch] text-sm leading-6 text-ink-mute">{{ $negocio->eslogan ?: $negocio->descripcion_corta }}</p>
                    <a href="{{ route('web.contacto') }}" class="v2-btn v2-btn-primary">
                        Reservar mesa
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </a>
                </section>
                <nav aria-labelledby="f-carta">
                    <h4 id="f-carta" class="mb-4 text-[11px] font-bold uppercase tracking-[0.2em] text-amber-bright">Carta</h4>
                    <ul class="flex flex-col gap-2.5 text-sm text-ink">
                        <li><a href="{{ route('web.carta') }}" class="hover:text-amber-bright">Carta completa</a></li>
                        <li><a href="{{ route('web.cervezas') }}" class="hover:text-amber-bright">Cervezas</a></li>
                        <li><a href="{{ route('web.fuera-carta') }}" class="hover:text-amber-bright">Fuera de carta</a></li>
                        <li><a href="{{ route('web.recomendaciones') }}" class="hover:text-amber-bright">Recomendaciones</a></li>
                        @if (\App\Models\Modulo::activo('blog'))
                            <li><a href="{{ route('web.blog') }}" class="hover:text-amber-bright">Diario de barra</a></li>
                        @endif
                    </ul>
                </nav>
                <section aria-labelledby="f-local">
                    <h4 id="f-local" class="mb-4 text-[11px] font-bold uppercase tracking-[0.2em] text-amber-bright">Local</h4>
                    <ul class="flex flex-col gap-2.5 text-sm text-ink">
                        @if ($negocio->direccionCompleta())
                            <li>{{ $negocio->direccionCompleta() }}</li>
                        @elseif ($negocio->localidad)
                            <li>{{ $negocio->localidad }}</li>
                        @endif
                        @if ($negocio->horario)
                            <li class="whitespace-pre-line">{{ $negocio->horario }}</li>
                        @endif
                    </ul>
                </section>
                <section aria-labelledby="f-contact">
                    <h4 id="f-contact" class="mb-4 text-[11px] font-bold uppercase tracking-[0.2em] text-amber-bright">Hablamos</h4>
                    <ul class="flex flex-col gap-2.5 text-sm text-ink">
                        @if ($negocio->telefono)<li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $negocio->telefono) }}" class="hover:text-amber-bright">{{ $negocio->telefono }}</a></li>@endif
                        @if ($negocio->email)<li><a href="mailto:{{ $negocio->email }}" class="hover:text-amber-bright">{{ $negocio->email }}</a></li>@endif
                        @if ($negocio->instagram_url)<li><a href="{{ $negocio->instagram_url }}" target="_blank" rel="noopener noreferrer" class="hover:text-amber-bright">Instagram ↗</a></li>@endif
                        @if ($negocio->google_maps_url)<li><a href="{{ $negocio->google_maps_url }}" target="_blank" rel="noopener noreferrer" class="hover:text-amber-bright">Google Maps ↗</a></li>@endif
                    </ul>
                </section>
            </div>

            <div class="mx-auto mt-6 flex max-w-[1440px] flex-wrap items-center justify-between gap-3 text-[11px] uppercase tracking-[0.08em] text-ink-mute">
                <span>&copy; {{ now()->year }} {{ $negocio->nombre_comercial }}@if($negocio->localidad) &middot; {{ $negocio->localidad }}@endif</span>
                <span class="font-mono text-[10.5px] tracking-wide normal-case">Gestion conectada con el local</span>
            </div>
        </footer>
    </body>
</html>
