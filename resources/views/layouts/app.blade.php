<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full overflow-hidden bg-background">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $negocio->nombre_comercial }} | Panel de gestion</title>

        <script>
            (() => {
                try {
                    const storedPreference = localStorage.getItem('panel-theme-preference')
                        ?? localStorage.getItem('panel-hosteleria-theme-preference')
                        ?? 'system';
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
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full overflow-hidden bg-background font-admin">
        @php
            $moduloInicial = match (true) {
                request()->routeIs('admin.configuracion.*') => 'configuracion',
                request()->routeIs('admin.compras.*') => 'compras',
                request()->routeIs('admin.inventario.*') => 'inventario',
                request()->routeIs('admin.ventas.*') => 'ventas',
                request()->routeIs('admin.espacios.*') => 'espacios',
                request()->routeIs('admin.personal.*') => 'personal',
                request()->routeIs('admin.planificacion-turnos.*') => 'planificacion_turnos',
                request()->routeIs('admin.web-publica.*') => 'web_publica',
                default => '',
            };
        @endphp

        <div x-data="{ sidebarOpen: false, moduloAbierto: '{{ $moduloInicial }}' }" class="fixed inset-0 flex min-h-0 overflow-hidden bg-background">
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                class="fixed inset-0 z-40 bg-foreground/20 backdrop-blur-sm lg:hidden"
                @click="sidebarOpen = false"
            ></div>

            <aside
                class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-sidebar-border bg-sidebar transition-transform duration-200 lg:static lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            >
                <div class="flex h-16 items-center justify-between border-b border-sidebar-border px-4">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-lg font-bold text-primary-foreground">
                            <x-brand.hospitality-icon class="h-6 w-6 text-primary-foreground" />
                        </span>
                        <span class="min-w-0">
                            <span class="block max-w-40 truncate text-sm font-semibold text-sidebar-foreground">{{ $negocio->nombre_comercial }}</span>
                            <span class="block text-xs text-sidebar-foreground/65">Panel de gestión</span>
                        </span>
                    </a>
                    <button type="button" class="rounded-md p-1.5 text-sidebar-foreground hover:bg-sidebar-accent lg:hidden" @click="sidebarOpen = false" aria-label="Cerrar menu">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @php
                    $usuario = Auth::user();
                    $inventarioActivo = request()->routeIs('admin.inventario.*');
                    $comprasActivo = request()->routeIs('admin.compras.*');
                @endphp

                <nav class="flex-1 space-y-2 overflow-y-auto p-3" aria-label="Navegación principal">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground hover:bg-sidebar-accent' }}">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-white/10">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2 7-7 7 7 2 2M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" />
                            </svg>
                        </span>
                        Dashboard
                    </a>

                    @if ($usuario?->puedeAccederModulo('inventario'))
                        <div>
                            <div class="flex items-center gap-1 rounded-md transition {{ $inventarioActivo ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground hover:bg-sidebar-accent' }}">
                                <a href="{{ route('admin.inventario.index') }}" class="flex min-w-0 flex-1 items-center gap-3 px-3 py-2 text-sm font-semibold">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-md bg-white/10">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7m16 0H4m16 0l-2-3H6L4 7m4 4h8m-8 4h5" />
                                        </svg>
                                    </span>
                                    <span class="truncate">Inventario</span>
                                </a>
                                <button type="button" class="me-2 rounded-md p-1.5 hover:bg-sidebar-accent" @click="moduloAbierto = moduloAbierto === 'inventario' ? '' : 'inventario'" :aria-expanded="moduloAbierto === 'inventario'" aria-label="Mostrar secciones de inventario">
                                    <svg class="h-4 w-4 transition" :class="moduloAbierto === 'inventario' ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 18 6-6-6-6" />
                                    </svg>
                                </button>
                            </div>
                            <section x-show="moduloAbierto === 'inventario'" x-transition class="space-y-1 pb-2 pt-2 ps-14">
                                <a href="{{ route('admin.inventario.productos.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.inventario.productos.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Productos</a>
                                <a href="{{ route('admin.inventario.proveedores.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.inventario.proveedores.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Proveedores</a>
                                <a href="{{ route('admin.inventario.alertas.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.inventario.alertas.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Alertas</a>
                                <a href="{{ route('admin.inventario.movimientos.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.inventario.movimientos.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Movimientos</a>
                                <a href="{{ route('admin.inventario.ubicaciones.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.inventario.ubicaciones.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Ubicaciones</a>
                                <a href="{{ route('admin.inventario.categorias.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.inventario.categorias.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Categorias</a>
                                <a href="{{ route('admin.inventario.unidades.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.inventario.unidades.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Unidades</a>
                            </section>
                        </div>
                    @endif

                    @if ($usuario?->puedeAccederModulo('compras'))
                        <div>
                            <button type="button" class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-start text-sm font-semibold text-sidebar-foreground transition hover:bg-sidebar-accent" @click="moduloAbierto = moduloAbierto === 'compras' ? '' : 'compras'" :aria-expanded="moduloAbierto === 'compras'">
                                <span class="flex h-8 w-8 items-center justify-center rounded-md bg-white/10">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h10M7 11h10M7 15h6M5 3h14a2 2 0 012 2v14l-3-2-3 2-3-2-3 2-3-2-3 2V5a2 2 0 012-2z" />
                                    </svg>
                                </span>
                                <span class="min-w-0 flex-1 truncate text-start">Compras a proveedor</span>
                                <svg class="h-4 w-4 transition" :class="moduloAbierto === 'compras' ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                            <div x-show="moduloAbierto === 'compras'" x-transition class="space-y-1 pb-2 ps-14 pt-1">
                                <a href="{{ route('admin.compras.pedidos.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.compras.pedidos.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Pedidos</a>
                                <a href="{{ route('admin.compras.propuestas.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.compras.propuestas.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Propuestas</a>
                                <a href="{{ route('admin.compras.documentos.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.compras.documentos.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Documentos</a>
                            </div>
                        </div>
                    @endif

                    @if ($usuario?->puedeAccederModulo('ventas'))
                        <div>
                            <button type="button" class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-start text-sm font-semibold text-sidebar-foreground transition hover:bg-sidebar-accent" @click="moduloAbierto = moduloAbierto === 'ventas' ? '' : 'ventas'" :aria-expanded="moduloAbierto === 'ventas'">
                                <span class="flex h-8 w-8 items-center justify-center rounded-md bg-white/10">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h16M6 7l1 13h10l1-13M9 7V5a3 3 0 016 0v2" />
                                    </svg>
                                </span>
                                <span class="min-w-0 flex-1 truncate text-start">Ventas</span>
                                <svg class="h-4 w-4 transition" :class="moduloAbierto === 'ventas' ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                            <div x-show="moduloAbierto === 'ventas'" x-transition class="space-y-1 pb-2 ps-14 pt-1">
                                <a href="{{ route('admin.ventas.comandas.create') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.ventas.comandas.create') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Nueva comanda</a>
                                <a href="{{ route('admin.ventas.comandas.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.ventas.comandas.index') || request()->routeIs('admin.ventas.comandas.show') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Comandas</a>
                                @if ($usuario?->puedeGestionarCaja())
                                    <a href="{{ route('admin.ventas.caja.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.ventas.caja.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Caja</a>
                                @endif
                                @if ($usuario?->puedeConsultarInformesVentas())
                                    <a href="{{ route('admin.ventas.informes.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.ventas.informes.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Informes</a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($usuario?->puedeAccederModulo('espacios'))
                        <div>
                            <button type="button" class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-start text-sm font-semibold text-sidebar-foreground transition hover:bg-sidebar-accent" @click="moduloAbierto = moduloAbierto === 'espacios' ? '' : 'espacios'" :aria-expanded="moduloAbierto === 'espacios'">
                                <span class="flex h-8 w-8 items-center justify-center rounded-md bg-white/10">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 10h.01M15 10h.01" />
                                    </svg>
                                </span>
                                <span class="min-w-0 flex-1 truncate text-start">Espacios</span>
                                <svg class="h-4 w-4 transition" :class="moduloAbierto === 'espacios' ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                            <div x-show="moduloAbierto === 'espacios'" x-transition class="space-y-1 pb-2 ps-14 pt-1">
                                <a href="{{ route('admin.espacios.recintos.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.espacios.recintos.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Recintos</a>
                                <a href="{{ route('admin.espacios.zonas.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.espacios.zonas.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Zonas</a>
                                <a href="{{ route('admin.espacios.mesas.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.espacios.mesas.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Mesas</a>
                            </div>
                        </div>
                    @endif

                    @if ($usuario?->puedeConsultarTurnosPublicados())
                        <a href="{{ route('admin.mis-turnos.index') }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.mis-turnos.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground hover:bg-sidebar-accent' }}">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 2v3m8-3v3M3 9h18M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm2.5 9.5 2.5 2.5 4.5-5" />
                                </svg>
                            </span>
                            <span class="truncate">Mis turnos</span>
                        </a>
                    @endif

                    @if ($usuario?->puedeAccederModulo('planificacion_turnos'))
                        <a href="{{ route('admin.planificacion-turnos.cuadrantes.index') }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.planificacion-turnos.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground hover:bg-sidebar-accent' }}">
                            <span class="flex h-8 w-8 items-center justify-center rounded-md bg-white/10">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 2v3m8-3v3M3 9h18M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm2 9h3m4 0h3m-8 4h3m4 0h3" />
                                </svg>
                            </span>
                            <span class="truncate">Planificación de turnos</span>
                        </a>
                    @endif

                    @if ($usuario?->puedeAccederModulo('personal'))
                        <div>
                            <button type="button" class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-start text-sm font-semibold text-sidebar-foreground transition hover:bg-sidebar-accent" @click="moduloAbierto = moduloAbierto === 'personal' ? '' : 'personal'" :aria-expanded="moduloAbierto === 'personal'">
                                <span class="flex h-8 w-8 items-center justify-center rounded-md bg-white/10">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 14a4 4 0 10-8 0M4 21a8 8 0 0116 0M18 8h4M20 6v4" />
                                    </svg>
                                </span>
                                <span class="min-w-0 flex-1 truncate text-start">Personal</span>
                                <svg class="h-4 w-4 transition" :class="moduloAbierto === 'personal' ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                            <div x-show="moduloAbierto === 'personal'" x-transition class="space-y-1 pb-2 ps-14 pt-1">
                                <a href="{{ route('admin.personal.usuarios.create') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.personal.usuarios.create') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Anadir usuario</a>
                                <a href="{{ route('admin.personal.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.personal.index') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Usuarios</a>
                            </div>
                        </div>
                    @endif

                    @if ($usuario?->puedeAccederModulo('web_publica'))
                        <div>
                            <button type="button" class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-start text-sm font-semibold text-sidebar-foreground transition hover:bg-sidebar-accent" @click="moduloAbierto = moduloAbierto === 'web_publica' ? '' : 'web_publica'" :aria-expanded="moduloAbierto === 'web_publica'">
                                <span class="flex h-8 w-8 items-center justify-center rounded-md bg-white/10">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5h16M4 12h16M4 19h16" />
                                    </svg>
                                </span>
                                <span class="min-w-0 flex-1 truncate text-start">Web pública</span>
                                <svg class="h-4 w-4 transition" :class="moduloAbierto === 'web_publica' ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                            <div x-show="moduloAbierto === 'web_publica'" x-transition class="space-y-1 pb-2 ps-14 pt-1">
                                <a href="{{ route('admin.web-publica.contenidos.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.web-publica.contenidos.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Contenidos</a>
                                <a href="{{ route('admin.web-publica.carta-categorias.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.web-publica.carta-categorias.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Categorias carta</a>
                                <a href="{{ route('admin.web-publica.secciones.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.web-publica.secciones.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Secciones</a>
                                @if ($usuario?->puedeAccederModulo('blog'))
                                    <a href="{{ route('admin.web-publica.blog.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.web-publica.blog.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Blog</a>
                                    <a href="{{ route('admin.web-publica.blog-categorias.index') }}" class="block rounded-md px-3 py-1.5 text-sm {{ request()->routeIs('admin.web-publica.blog-categorias.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground' }}">Categorias blog</a>
                                @endif
                                <a href="{{ route('web.inicio') }}" target="_blank" class="block rounded-md px-3 py-1.5 text-sm text-sidebar-foreground/65 hover:bg-sidebar-accent hover:text-sidebar-foreground">Ver web</a>
                            </div>
                        </div>
                    @endif

                    @if ($usuario?->puedeConfigurarNegocio())
                        <a href="{{ route('admin.configuracion.negocio.edit') }}" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-semibold transition {{ request()->routeIs('admin.configuracion.*') ? 'bg-primary text-primary-foreground shadow-sm' : 'text-sidebar-foreground hover:bg-sidebar-accent' }}">
                            <span class="flex h-8 w-8 items-center justify-center rounded-md bg-white/10">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7zM19.4 15a1.7 1.7 0 00.34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0015 19.4a1.7 1.7 0 00-1 .6l-.04.08H10l-.04-.08a1.7 1.7 0 00-1-.6 1.7 1.7 0 00-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 004.6 15a1.7 1.7 0 00-.6-1l-.08-.04V10l.08-.04a1.7 1.7 0 00.6-1 1.7 1.7 0 00-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 009 4.6a1.7 1.7 0 001-.6l.04-.08H14l.04.08a1.7 1.7 0 001 .6 1.7 1.7 0 001.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0019.4 9c.08.4.3.76.6 1l.08.04V14l-.08.04c-.3.24-.52.6-.6.96z" />
                                </svg>
                            </span>
                            <span class="truncate">Configuracion</span>
                        </a>
                    @endif
                </nav>

                <div class="border-t border-sidebar-border p-3">
                    <div class="rounded-md bg-sidebar-accent px-3 py-2">
                        <p class="text-xs font-medium text-sidebar-foreground">Version 1.0.0</p>
                        <p class="text-xs text-sidebar-foreground/65">Panel de administración</p>
                    </div>
                </div>
            </aside>

            <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
                <header class="flex min-h-16 shrink-0 items-center justify-between gap-3 border-b border-border bg-card/95 px-3 py-2 shadow-sm backdrop-blur sm:px-4">
                    <div class="flex items-center gap-3">
                        <button type="button" class="flex h-11 w-11 items-center justify-center rounded-lg border border-border text-foreground shadow-sm hover:bg-muted lg:hidden" @click="sidebarOpen = true" aria-label="Abrir menú">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="hidden min-w-0 min-[420px]:block">
                            <p class="truncate text-sm font-extrabold text-foreground sm:text-base">Panel de gestión</p>
                            <p class="hidden truncate text-xs text-muted-foreground sm:block">{{ $negocio->nombre_comercial }}</p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                        <x-admin.theme-toggle />

                        <a href="{{ route('profile.edit') }}" class="hidden min-h-11 items-center rounded-lg px-3 py-2 text-sm font-semibold text-muted-foreground hover:bg-muted hover:text-foreground md:inline-flex">
                            {{ Auth::user()->nombre }}
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="inline-flex min-h-11 items-center gap-2 rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2 text-xs font-extrabold text-destructive shadow-sm transition hover:bg-destructive hover:text-destructive-foreground focus:ring-2 focus:ring-destructive/40 sm:text-sm">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 17l5-5-5-5m5 5H3m10-9h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5" />
                                </svg>
                                <span>Cerrar sesión</span>
                            </button>
                        </form>
                    </div>
                </header>

                <main class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-3 sm:p-4 lg:p-6 xl:p-8">
                    @isset($header)
                        {{ $header }}
                    @endisset

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
