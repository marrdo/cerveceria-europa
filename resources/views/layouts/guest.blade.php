<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Cerveceria Europa') }}</title>

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
                    document.documentElement.dataset.theme = 'light';
                }
            })();
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans">
        <div class="flex min-h-screen flex-col bg-background">
            <div class="absolute right-4 top-4">
                <x-admin.theme-toggle />
            </div>

            <main class="flex flex-1 items-center justify-center p-4">
                <div class="w-full max-w-sm">
                    <div class="mb-8 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-primary text-lg font-bold text-primary-foreground">
                            <x-brand.beer-icon class="h-6 w-6 text-primary-foreground" />
                        </div>
                        <h1 class="text-xl font-semibold text-foreground">Cerveceria Europa</h1>
                        <p class="mt-1 text-sm text-muted-foreground">Panel de administracion</p>
                    </div>

                    <div class="admin-card p-6">
                        {{ $slot }}
                    </div>
                </div>
            </main>

            <footer class="py-4 text-center text-xs text-muted-foreground">
                <p>Cerveceria Europa &copy; {{ date('Y') }}. Todos los derechos reservados.</p>
            </footer>
        </div>
    </body>
</html>
