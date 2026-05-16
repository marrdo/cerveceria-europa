<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $pageTitle = $title ?? 'Cerveceria Europa';
            $pageDescription = $description ?? 'Cerveceria Europa, bar de Sevilla especializado en cervezas de importacion, artesanas y cocina para maridar.';
            $publicLinks = [
                ['label' => 'Carta', 'route' => 'web.carta'],
                ['label' => 'Cervezas', 'route' => 'web.cervezas'],
                ['label' => 'Fuera de carta', 'route' => 'web.fuera-carta'],
                ['label' => 'Recomendaciones', 'route' => 'web.recomendaciones'],
                ['label' => 'Contacto', 'route' => 'web.contacto'],
            ];
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
        <meta name="theme-color" content="#d08a24">
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="manifest" href="/site.webmanifest">

        <script>
            (() => {
                try {
                    const storedPreference = localStorage.getItem('cerveceria-theme-preference') ?? 'system';
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    const resolvedTheme = storedPreference === 'dark' || (storedPreference === 'system' && prefersDark)
                        ? 'dark'
                        : 'light';

                    document.documentElement.classList.toggle('dark', resolvedTheme === 'dark');
                    document.documentElement.dataset.theme = resolvedTheme;
                    document.documentElement.style.colorScheme = resolvedTheme;
                } catch (error) {
                    document.documentElement.dataset.theme = 'dark';
                }
            })();
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col bg-public-background font-sans text-public-foreground">
        <header class="sticky top-0 z-40 border-b border-public-border/15 bg-public-background/90 backdrop-blur" x-data="{ menuAbierto: false }">
                <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-4 sm:px-6 lg:px-8">
                    <span class="order-1 md:order-3">
                        <x-admin.theme-toggle size="sm" />
                    </span>

                    <a href="{{ route('web.inicio') }}" class="order-2 mx-auto flex items-center gap-3 md:order-1 md:mx-0">
                        <span class="flex h-10 w-10 items-center justify-center rounded-md bg-[#d08a24] text-[#23180f]">
                            <x-brand.beer-icon class="h-6 w-6" />
                        </span>
                        <span>
                            <span class="block text-sm font-black uppercase tracking-[0.18em] text-public-primary">Cerveceria</span>
                            <span class="block text-lg font-black leading-5 text-public-foreground">Europa</span>
                        </span>
                    </a>

                    <nav class="order-2 ml-auto hidden items-center gap-6 text-sm font-semibold text-public-muted md:flex" aria-label="Navegacion principal">
                        @foreach ($publicLinks as $link)
                            <a href="{{ route($link['route']) }}" class="hover:text-public-primary">{{ $link['label'] }}</a>
                        @endforeach
                        @if (\App\Models\Modulo::activo('blog'))
                            <a href="{{ route('web.blog') }}" class="hover:text-public-primary">Blog</a>
                        @endif
                    </nav>

                    <button
                        type="button"
                        class="order-3 inline-flex h-10 w-10 items-center justify-center rounded-lg border border-public-border/25 bg-public-surface text-public-foreground transition hover:border-public-primary hover:text-public-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-public-primary/40 md:hidden"
                        title="Abrir menu"
                        aria-label="Abrir menu de navegacion"
                        aria-controls="menu-publico-movil"
                        :aria-expanded="menuAbierto.toString()"
                        @click="menuAbierto = ! menuAbierto"
                    >
                        <svg x-show="! menuAbierto" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                        <svg x-show="menuAbierto" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>

                <nav
                    id="menu-publico-movil"
                    class="border-t border-public-border/15 bg-public-background px-4 py-3 shadow-lg shadow-black/5 md:hidden"
                    aria-label="Navegacion movil"
                    x-show="menuAbierto"
                    x-transition
                    x-cloak
                    @click.outside="menuAbierto = false"
                >
                    <ul class="space-y-1 text-sm font-black uppercase tracking-wide text-public-foreground">
                        @foreach ($publicLinks as $link)
                            <li>
                                <a href="{{ route($link['route']) }}" class="block rounded-md px-3 py-3 transition hover:bg-public-surface hover:text-public-primary" @click="menuAbierto = false">{{ $link['label'] }}</a>
                            </li>
                        @endforeach
                        @if (\App\Models\Modulo::activo('blog'))
                            <li>
                                <a href="{{ route('web.blog') }}" class="block rounded-md px-3 py-3 transition hover:bg-public-surface hover:text-public-primary" @click="menuAbierto = false">Blog</a>
                            </li>
                        @endif
                    </ul>
                </nav>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="border-t border-public-border/15 bg-public-surface" aria-labelledby="footer-heading">
                <h2 id="footer-heading" class="sr-only">Informacion de Cerveceria Europa</h2>
                <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 text-sm text-public-muted sm:px-6 md:grid-cols-[1.2fr_.8fr_.8fr_.8fr] lg:px-8">
                    <section aria-labelledby="footer-brand">
                        <p id="footer-brand" class="text-lg font-black text-public-foreground">Cerveceria Europa</p>
                        <p class="mt-3 max-w-sm leading-6">Cervezas de importacion, artesanas y cocina de bar para compartir en Sevilla.</p>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ route('web.carta') }}" class="rounded-md bg-public-primary px-4 py-2 text-xs font-black uppercase text-[#23180f] hover:bg-[#e3a13a]">Ver carta</a>
                            <a href="{{ route('web.contacto') }}" class="rounded-md border border-public-border/25 px-4 py-2 text-xs font-black uppercase text-public-foreground hover:border-public-primary hover:text-public-primary">Contacto</a>
                        </div>
                    </section>

                    <nav aria-labelledby="footer-nav">
                        <p id="footer-nav" class="font-black text-public-foreground">Carta</p>
                        <ul class="mt-3 space-y-2">
                            <li><a href="{{ route('web.carta') }}" class="hover:text-public-primary">Carta completa</a></li>
                            <li><a href="{{ route('web.cervezas') }}" class="hover:text-public-primary">Cervezas</a></li>
                            <li><a href="{{ route('web.fuera-carta') }}" class="hover:text-public-primary">Fuera de carta</a></li>
                            <li><a href="{{ route('web.recomendaciones') }}" class="hover:text-public-primary">Recomendaciones</a></li>
                            @if (\App\Models\Modulo::activo('blog'))
                                <li><a href="{{ route('web.blog') }}" class="hover:text-public-primary">Blog</a></li>
                            @endif
                        </ul>
                    </nav>

                    <section aria-labelledby="footer-specialties">
                        <p id="footer-specialties" class="font-black text-public-foreground">Especialidades</p>
                        <ul class="mt-3 space-y-2">
                            <li>Cervezas de importacion</li>
                            <li>Cervezas artesanas</li>
                            <li>Cocina para maridar</li>
                            <li>Novedades de temporada</li>
                        </ul>
                    </section>

                    <section aria-labelledby="footer-contact">
                        <p id="footer-contact" class="font-black text-public-foreground">Visitanos</p>
                        <address class="mt-3 not-italic leading-6">
                            <span class="block">Sevilla</span>
                            <a href="{{ route('web.contacto') }}" class="mt-2 inline-flex font-bold text-public-primary hover:underline">Consultar horario y reservas</a>
                        </address>
                    </section>
                </div>
                <div class="border-t border-public-border/15">
                    <p class="mx-auto max-w-7xl px-4 py-5 text-xs text-public-muted sm:px-6 lg:px-8">
                        &copy; {{ now()->year }} Cerveceria Europa. Carta, cervezas y novedades actualizadas por el equipo del local.
                    </p>
                </div>
        </footer>
    </body>
</html>
