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
        <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden bg-background">
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                class="fixed inset-0 z-40 bg-foreground/20 backdrop-blur-sm lg:hidden"
                @click="sidebarOpen = false"
            ></div>

            <aside
                class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-sidebar-border bg-sidebar transition-transform duration-200 lg:static lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            >
                <div class="flex h-16 items-center justify-between border-b border-sidebar-border px-4">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-lg font-bold text-primary-foreground">
                            <x-brand.beer-icon class="h-6 w-6 text-primary-foreground" />
                        </span>
                        <span class="flex flex-col">
                            <span class="text-sm font-semibold text-sidebar-foreground">Cerveceria</span>
                            <span class="text-xs text-muted-foreground">Europa</span>
                        </span>
                    </a>
                    <button type="button" class="rounded-md p-1.5 text-sidebar-foreground hover:bg-sidebar-accent lg:hidden" @click="sidebarOpen = false" aria-label="Cerrar menu">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 space-y-1 p-3" aria-label="Navegacion principal">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-sidebar-accent text-primary' : 'text-sidebar-foreground hover:bg-sidebar-accent' }}">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-card/70">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2 7-7 7 7 2 2M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" />
                            </svg>
                        </span>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.inventario.productos.index') }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.inventario.productos.*') ? 'bg-sidebar-accent text-primary' : 'text-sidebar-foreground hover:bg-sidebar-accent' }}">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-card/70">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7m16 0H4m16 0l-2-3H6L4 7m4 4h8m-8 4h5" />
                            </svg>
                        </span>
                        Productos
                    </a>
                    <a href="{{ route('admin.inventario.proveedores.index') }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.inventario.proveedores.*') ? 'bg-sidebar-accent text-primary' : 'text-sidebar-foreground hover:bg-sidebar-accent' }}">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-card/70">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17h6m-8 4h10a2 2 0 002-2V7l-4-4H7a2 2 0 00-2 2v14a2 2 0 002 2zm8-18v4h4" />
                            </svg>
                        </span>
                        Proveedores
                    </a>
                    <a href="{{ route('admin.inventario.ubicaciones.index') }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.inventario.ubicaciones.*') ? 'bg-sidebar-accent text-primary' : 'text-sidebar-foreground hover:bg-sidebar-accent' }}">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-card/70">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21s7-4.6 7-11a7 7 0 10-14 0c0 6.4 7 11 7 11zm0-8a3 3 0 100-6 3 3 0 000 6z" />
                            </svg>
                        </span>
                        Ubicaciones
                    </a>
                    <a href="{{ route('admin.inventario.categorias.index') }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.inventario.categorias.*') ? 'bg-sidebar-accent text-primary' : 'text-sidebar-foreground hover:bg-sidebar-accent' }}">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-card/70">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h10" />
                            </svg>
                        </span>
                        Categorias
                    </a>
                    <a href="{{ route('admin.inventario.unidades.index') }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.inventario.unidades.*') ? 'bg-sidebar-accent text-primary' : 'text-sidebar-foreground hover:bg-sidebar-accent' }}">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-card/70">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4v16M17 4v16M4 8h16M4 16h16" />
                            </svg>
                        </span>
                        Unidades
                    </a>
                </nav>

                <div class="border-t border-sidebar-border p-3">
                    <div class="rounded-md bg-sidebar-accent px-3 py-2">
                        <p class="text-xs font-medium text-sidebar-foreground">Version 1.0.0</p>
                        <p class="text-xs text-muted-foreground">Panel de administracion</p>
                    </div>
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
                <header class="flex h-16 shrink-0 items-center justify-between border-b border-border bg-card px-4">
                    <div class="flex items-center gap-3">
                        <button type="button" class="rounded-md p-2 text-foreground hover:bg-muted lg:hidden" @click="sidebarOpen = true" aria-label="Abrir menu">
                            <span class="block h-0.5 w-5 bg-current"></span>
                            <span class="mt-1 block h-0.5 w-5 bg-current"></span>
                            <span class="mt-1 block h-0.5 w-5 bg-current"></span>
                        </button>
                        <p class="hidden text-lg font-semibold text-foreground lg:block">Panel de gestion</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <x-admin.theme-toggle />

                        <a href="{{ route('profile.edit') }}" class="hidden rounded-md px-3 py-2 text-sm text-muted-foreground hover:bg-muted hover:text-foreground sm:block">
                            {{ Auth::user()->nombre }}
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-md px-3 py-2 text-sm text-destructive hover:bg-destructive/10">Salir</button>
                        </form>
                    </div>
                </header>

                <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                    @isset($header)
                        {{ $header }}
                    @endisset

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
