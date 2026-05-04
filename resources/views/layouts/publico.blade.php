<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Cerveceria Europa' }}</title>
        <meta name="description" content="{{ $description ?? 'Cerveceria Europa, bar de Sevilla especializado en cervezas de importacion, artesanas y cocina para maridar.' }}">

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
    <body class="bg-public-background font-sans text-public-foreground">
        <div class="min-h-screen">
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
                    <nav class="hidden items-center gap-6 text-sm font-semibold text-public-muted md:flex">
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

            <main>
                {{ $slot }}
            </main>

            <footer class="border-t border-public-border/15 bg-public-surface">
                <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 text-sm text-public-muted sm:px-6 md:grid-cols-3 lg:px-8">
                    <div>
                        <p class="text-base font-bold text-public-foreground">Cerveceria Europa</p>
                        <p class="mt-2">Cervezas de importacion, artesanas y cocina para maridar en Sevilla.</p>
                    </div>
                    <div>
                        <p class="font-semibold text-public-foreground">Horario</p>
                        <p class="mt-2">Consulta horarios actualizados llamando al local.</p>
                    </div>
                    <div>
                        <p class="font-semibold text-public-foreground">Carta viva</p>
                        <p class="mt-2">Carta, recomendaciones y fuera de carta se publican desde el panel privado.</p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
