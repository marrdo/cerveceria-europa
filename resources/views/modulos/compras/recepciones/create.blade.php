<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Registrar recepcion" :description="$pedido->numero.' - '.$pedido->proveedor?->nombre">
            <x-slot name="actions">
                <a href="{{ route('admin.compras.pedidos.show', $pedido) }}" class="admin-btn-outline">Volver</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.compras.partials.nav')

    <form method="POST" action="{{ route('admin.compras.pedidos.recepciones.store', $pedido) }}" class="space-y-6" data-recepcion-lineas>
        @csrf

        <section class="admin-card p-4 lg:p-6">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-input-label for="fecha_recepcion" value="Fecha recepcion" />
                    <x-text-input id="fecha_recepcion" name="fecha_recepcion" type="date" class="mt-1 block h-10 w-full" :value="old('fecha_recepcion', now()->format('Y-m-d'))" required />
                    <p class="mt-1 text-xs text-muted-foreground">Dia real en el que entra la mercancia en el bar.</p>
                    <x-input-error :messages="$errors->get('fecha_recepcion')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="notas" value="Notas" />
                    <x-text-input id="notas" name="notas" class="mt-1 block h-10 w-full" :value="old('notas')" maxlength="191" />
                    <p class="mt-1 text-xs text-muted-foreground">Comentario interno de la recepcion, por ejemplo albaran, incidencia o reparto especial.</p>
                    <x-input-error :messages="$errors->get('notas')" class="mt-2" />
                </div>
            </div>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-border p-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-foreground">Lineas recibidas</h2>
                        <p class="mt-1 text-sm text-muted-foreground">Puedes repetir una linea para repartirla entre varias ubicaciones.</p>
                    </div>
                    <button type="button" class="admin-btn-outline" data-anadir-recepcion>Anadir linea</button>
                </div>
                <x-input-error :messages="$errors->get('lineas')" class="mt-2" />
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/50 align-top">
                            <th class="min-w-72 px-4 py-3 text-left font-medium text-foreground">
                                Linea pedido
                                <p class="mt-1 text-xs font-normal text-muted-foreground">Producto pedido que estas recibiendo.</p>
                            </th>
                            <th class="min-w-48 px-4 py-3 text-left font-medium text-foreground">
                                Ubicacion
                                <p class="mt-1 text-xs font-normal text-muted-foreground">Donde queda guardada esta cantidad.</p>
                            </th>
                            <th class="w-32 px-4 py-3 text-left font-medium text-foreground">
                                Cantidad
                                <p class="mt-1 text-xs font-normal text-muted-foreground">Cantidad que entra en esa ubicacion.</p>
                            </th>
                            <th class="w-40 px-4 py-3 text-left font-medium text-foreground">
                                Lote
                                <p class="mt-1 text-xs font-normal text-muted-foreground">Codigo del lote si aparece en caja, barril o albaran.</p>
                            </th>
                            <th class="w-40 px-4 py-3 text-left font-medium text-foreground">
                                Caducidad
                                <p class="mt-1 text-xs font-normal text-muted-foreground">Obligatoria en productos que controlan caducidad.</p>
                            </th>
                            <th class="min-w-48 px-4 py-3 text-left font-medium text-foreground">
                                Notas
                                <p class="mt-1 text-xs font-normal text-muted-foreground">Detalle interno de esta linea recibida.</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody data-recepcion-tbody>
                        @php
                            $lineasFormulario = old('lineas', $pedido->lineas->map(fn ($linea) => [
                                'linea_pedido_compra_id' => $linea->id,
                                'ubicacion_inventario_id' => '',
                                'cantidad' => $linea->cantidadPendiente() > 0 ? $linea->cantidadPendiente() : '',
                                'codigo_lote' => '',
                                'caduca_el' => '',
                                'notas' => '',
                            ])->filter(fn ($linea) => $linea['cantidad'] !== '')->values()->all());
                        @endphp

                        @foreach ($lineasFormulario as $indice => $lineaFormulario)
                            @include('modulos.compras.recepciones.partials.linea-form', [
                                'indice' => $indice,
                                'lineaFormulario' => $lineaFormulario,
                                'pedido' => $pedido,
                                'ubicaciones' => $ubicaciones,
                            ])
                        @endforeach
                    </tbody>
                </table>
            </div>

            <template data-plantilla-recepcion>
                @include('modulos.compras.recepciones.partials.linea-form', [
                    'indice' => '__INDEX__',
                    'lineaFormulario' => [],
                    'pedido' => $pedido,
                    'ubicaciones' => $ubicaciones,
                ])
            </template>
        </section>

        <div class="flex items-center justify-end gap-3 border-t border-border pt-4">
            <a href="{{ route('admin.compras.pedidos.show', $pedido) }}" class="admin-btn-outline">Cancelar</a>
            <x-primary-button>Confirmar recepcion</x-primary-button>
        </div>
    </form>

    @once
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-recepcion-lineas]').forEach((formulario) => {
                    const tbody = formulario.querySelector('[data-recepcion-tbody]');
                    const plantilla = formulario.querySelector('[data-plantilla-recepcion]');
                    const boton = formulario.querySelector('[data-anadir-recepcion]');

                    if (!tbody || !plantilla || !boton) {
                        return;
                    }

                    boton.addEventListener('click', () => {
                        const indice = tbody.querySelectorAll('tr').length;
                        const html = plantilla.innerHTML.replaceAll('__INDEX__', String(indice));

                        tbody.insertAdjacentHTML('beforeend', html);
                    });
                });
            });
        </script>
    @endonce
</x-app-layout>
