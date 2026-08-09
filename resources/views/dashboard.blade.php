<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Dashboard" description="Punto de partida para recorrer una jornada completa del negocio." />
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-admin.kpi-card title="Productos" :value="$totalProductos ?? '0'" description="Catalogo activo" icon="P" />
        <x-admin.kpi-card title="Bajo stock" :value="$productosBajoStock ?? '0'" description="Requieren reposicion" variant="warning" icon="!" />
        <x-admin.kpi-card title="Movimientos" :value="$movimientosRecientes ?? '0'" description="Registrados recientemente" variant="success" icon="M" />
        <x-admin.kpi-card title="Usuario" :value="auth()->user()->rol->value" :description="auth()->user()->nombre" icon="U" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-semibold text-foreground">Accesos rapidos</h2>
            </div>
            <div class="admin-card p-4">
                <div class="flex flex-wrap gap-2">
                    @if (auth()->user()->puedeAccederModulo('inventario'))
                        <a href="{{ route('admin.inventario.productos.create') }}" class="admin-btn-outline">Nuevo producto</a>
                        <a href="{{ route('admin.inventario.productos.index') }}" class="admin-btn-outline">Ver inventario</a>
                        <a href="{{ route('admin.inventario.proveedores.index') }}" class="admin-btn-outline">Proveedores</a>
                        <a href="{{ route('admin.inventario.ubicaciones.index') }}" class="admin-btn-outline">Ubicaciones</a>
                    @endif
                    @if (auth()->user()->puedeAccederModulo('compras'))
                        <a href="{{ route('admin.compras.pedidos.index') }}" class="admin-btn-outline">Pedidos de compra</a>
                    @endif
                    @if (auth()->user()->puedeAccederModulo('ventas'))
                        <a href="{{ route('admin.ventas.comandas.create') }}" class="admin-btn-outline">Nueva comanda</a>
                        <a href="{{ route('admin.ventas.comandas.index') }}" class="admin-btn-outline">Comandas abiertas</a>
                        @if (auth()->user()->puedeGestionarCaja())
                            <a href="{{ route('admin.ventas.caja.index') }}" class="admin-btn-outline">Caja</a>
                        @endif
                        @if (auth()->user()->puedeConsultarInformesVentas())
                            <a href="{{ route('admin.ventas.informes.index') }}" class="admin-btn-outline">Informes ventas</a>
                        @endif
                    @endif
                    @if (auth()->user()->puedeAccederModulo('espacios'))
                        <a href="{{ route('admin.espacios.recintos.index') }}" class="admin-btn-outline">Espacios</a>
                        <a href="{{ route('admin.espacios.mesas.index') }}" class="admin-btn-outline">Mesas</a>
                    @endif
                    @if (auth()->user()->puedeAccederModulo('personal'))
                        <a href="{{ route('admin.personal.usuarios.create') }}" class="admin-btn-outline">Anadir usuario</a>
                        <a href="{{ route('admin.personal.index') }}" class="admin-btn-outline">Personal</a>
                    @endif
                    @if (auth()->user()->puedeAccederModulo('web_publica'))
                        <a href="{{ route('admin.web-publica.contenidos.index') }}" class="admin-btn-outline">Gestionar web</a>
                        @if (\App\Models\Modulo::activo('web_publica'))
                            <a href="{{ route('web.inicio') }}" class="admin-btn-outline">Ver web publica</a>
                        @endif
                    @endif
                </div>
            </div>
        </section>

        <section>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-semibold text-foreground">Escenario preparado</h2>
            </div>
            <div class="admin-card p-4">
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between border-b border-border pb-3">
                        <span class="text-foreground">Comandas operativas</span>
                        <span class="font-bold tabular-nums text-foreground">{{ $recorridoDemo['resumen']['comandas_activas'] }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-border pb-3">
                        <span class="text-foreground">Ventas cobradas</span>
                        <span class="font-bold tabular-nums text-foreground">{{ $recorridoDemo['resumen']['ventas_cobradas'] }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-border pb-3">
                        <span class="text-foreground">Mesas configuradas</span>
                        <span class="font-bold tabular-nums text-foreground">{{ $recorridoDemo['resumen']['mesas'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-foreground">Personas operativas</span>
                        <span class="font-bold tabular-nums text-foreground">{{ $recorridoDemo['resumen']['equipo'] }}</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <section class="mt-6" aria-labelledby="recorrido-demo-heading">
        <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 id="recorrido-demo-heading" class="text-base font-semibold text-foreground">Recorrido recomendado</h2>
                <p class="mt-1 text-sm text-muted-foreground">Sigue estos pasos para enseñar cómo una venta termina afectando a caja, inventario, compras e informes.</p>
            </div>
            <x-admin.status-badge variant="success">Datos conectados</x-admin.status-badge>
        </div>

        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($recorridoDemo['pasos'] as $indice => $paso)
                <article class="admin-card group relative flex min-h-56 flex-col overflow-hidden p-5">
                    <div class="absolute end-4 top-3 text-5xl font-black leading-none text-primary/10" aria-hidden="true">
                        {{ str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT) }}
                    </div>
                    <div class="relative flex items-center gap-2">
                        <span @class([
                            'h-2.5 w-2.5 rounded-full',
                            'bg-success' => $paso['estado'] === 'preparado',
                            'bg-warning' => $paso['estado'] !== 'preparado',
                        ])></span>
                        <span class="text-[10px] font-black uppercase tracking-[0.16em] text-muted-foreground">
                            {{ $paso['estado'] === 'preparado' ? 'Preparado' : 'Pendiente' }}
                        </span>
                    </div>
                    <h3 class="relative mt-4 text-lg font-bold text-foreground">{{ $paso['titulo'] }}</h3>
                    <p class="relative mt-2 flex-1 text-sm leading-6 text-muted-foreground">{{ $paso['descripcion'] }}</p>
                    <p class="relative mt-4 rounded-md bg-muted/40 px-3 py-2 text-xs font-semibold text-foreground">{{ $paso['detalle'] }}</p>

                    @if ($paso['url'])
                        <a href="{{ $paso['url'] }}" class="relative mt-3 inline-flex items-center gap-1 text-sm font-bold text-primary transition group-hover:gap-2">
                            {{ $paso['accion'] }}
                            <span aria-hidden="true">→</span>
                        </a>
                    @else
                        <p class="relative mt-3 text-xs text-muted-foreground">Disponible con un perfil autorizado.</p>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    @if (auth()->user()->rol === \App\Enums\RolUsuario::Superadmin)
        <section class="mt-6">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-success/30 bg-success/10 px-4 py-3 text-sm text-success">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-destructive">{{ session('error') }}</div>
            @endif
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-semibold text-foreground">Modulos contratados</h2>
            </div>
            <div class="admin-card overflow-hidden">
                <div class="border-b border-border p-4">
                    <p class="text-sm text-muted-foreground">Zona tecnica solo para superadmin. Sirve para activar o desactivar partes vendibles del proyecto segun lo que tenga contratado el cliente.</p>
                </div>

                <div class="divide-y divide-border">
                    @forelse (($modulosConfigurables ?? collect()) as $item)
                        @php($modulo = $item['modulo'])
                        <div class="flex flex-wrap items-center justify-between gap-4 p-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-semibold text-foreground">{{ $modulo->nombre }}</h3>
                                    <x-admin.status-badge :variant="$modulo->activo ? 'success' : 'default'">{{ $modulo->activo ? 'Activo' : 'Inactivo' }}</x-admin.status-badge>
                                </div>
                                <p class="mt-1 text-sm text-muted-foreground">{{ $modulo->descripcion ?: 'Sin descripcion.' }}</p>
                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted-foreground">
                                    @if ($item['dependencias'] !== [])
                                        <span><strong class="text-foreground">Requiere:</strong> {{ implode(', ', $item['dependencias']) }}</span>
                                    @else
                                        <span><strong class="text-foreground">Requiere:</strong> ninguno</span>
                                    @endif
                                    @if ($item['integraciones'] !== [])
                                        <span><strong class="text-foreground">Integra con:</strong> {{ implode(', ', $item['integraciones']) }}</span>
                                    @endif
                                </div>
                                @if ($item['bloqueo'])
                                    <p class="mt-2 text-xs font-semibold text-warning">{{ $item['bloqueo'] }}</p>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('admin.modulos.toggle', $modulo) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" @disabled($item['bloqueo']) class="{{ $modulo->activo ? 'admin-btn-outline' : 'admin-btn-primary' }} disabled:cursor-not-allowed disabled:opacity-40">
                                    {{ $modulo->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="p-4 text-sm text-muted-foreground">No hay modulos configurables. Ejecuta `php artisan db:seed --class=ModuloSeeder`.</div>
                    @endforelse
                </div>
            </div>
        </section>
    @endif
</x-app-layout>
