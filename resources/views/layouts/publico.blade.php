<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $pageTitle = $title ?? 'Cerveceria Europa';
            $pageDescription = $description ?? 'Cerveceria Europa, bar de Sevilla especializado en cervezas de importacion, artesanas y cocina para maridar.';
            $ogImage = $ogImage ?? asset('img/og-default.jpg');
        @endphp
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $pageDescription }}">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ url()->current() }}">

        <meta property="og:site_name" content="Cerveceria Europa">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $pageDescription }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:locale" content="es_ES">
        <meta name="twitter:card" content="summary_large_image">

        <meta name="theme-color" content="#0f0a06">
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
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
                <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ route('web.inicio') }}" class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-md bg-[#d08a24] text-[#23180f]">
                            <x-brand.beer-icon class="h-6 w-6" />
                        </span>
                        <span>
                            <span class="block text-sm font-black uppercase tracking-[0.18em] text-public-primary">Cerveceria</span>
                            <span class="block text-lg font-black leading-5 text-public-foreground">Europa</span>
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
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="border-t bg-gradient-to-b from-stout to-[#060403] px-8 pb-6 pt-20" style="border-color: var(--v2-line);" aria-labelledby="footer-heading">
            <h2 id="footer-heading" class="sr-only">Informacion de Cerveceria Europa</h2>
            <div class="mx-auto grid max-w-[1440px] grid-cols-1 gap-10 pb-14 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr]" style="border-bottom: 1px solid var(--v2-line);">
                <section>
                    <div class="font-display text-[56px] leading-[0.9] tracking-[0.005em] text-ink">Cerveceria<br>Europa</div>
                    <p class="my-4 max-w-[36ch] text-sm leading-6 text-ink-mute">Cervezas de importacion, artesanas y cocina de bar para compartir. Sevilla, desde hace bastante.</p>
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
                        <li>Calle Trajano · Sevilla</li>
                        <li>Mar–Jue · 12:00 — 24:00</li>
                        <li>Vie–Sab · 12:00 — 01:30</li>
                        <li>Dom · 12:00 — 17:00</li>
                        <li>Lunes cerrado</li>
                    </ul>
                </section>
                <section aria-labelledby="f-contact">
                    <h4 id="f-contact" class="mb-4 text-[11px] font-bold uppercase tracking-[0.2em] text-amber-bright">Hablamos</h4>
                    <ul class="flex flex-col gap-2.5 text-sm text-ink">
                        <li>+34 955 00 00 00</li>
                        <li>hola@cerveceriaeuropa.es</li>
                        <li><a href="#" class="hover:text-amber-bright">Instagram ↗</a></li>
                        <li><a href="#" class="hover:text-amber-bright">Google Maps ↗</a></li>
                    </ul>
                </section>
            </div>

            <div class="mx-auto mt-6 flex max-w-[1440px] flex-wrap items-center justify-between gap-3 text-[11px] uppercase tracking-[0.08em] text-ink-mute">
                <span>&copy; {{ now()->year }} Cerveceria Europa &middot; Sevilla</span>
                <span class="font-mono text-[10.5px] tracking-wide normal-case">v2 · del barril a la pantalla</span>
            </div>
        </footer>
    </body>
</html>
