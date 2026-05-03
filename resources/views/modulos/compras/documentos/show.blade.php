<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Documento {{ $documento->tipo_documento->etiqueta() }}" :description="$documento->nombre_original">
            <x-slot name="actions">
                <a href="{{ route('admin.compras.documentos.index') }}" class="admin-btn-outline">Volver</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.compras.partials.nav')

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section class="admin-card p-4 lg:p-6">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-base font-semibold text-foreground">Archivo original</h2>
                    <x-admin.status-badge :variant="$documento->estado->variante()">{{ $documento->estado->etiqueta() }}</x-admin.status-badge>
                </div>
                <dl class="mt-4 grid gap-4 text-sm md:grid-cols-2">
                    <div>
                        <dt class="text-muted-foreground">Nombre</dt>
                        <dd class="font-medium text-foreground">{{ $documento->nombre_original }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Proveedor</dt>
                        <dd class="font-medium text-foreground">{{ $documento->proveedor?->nombre ?? 'Sin proveedor' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Formato</dt>
                        <dd class="font-medium text-foreground">{{ $documento->mime_type ?? 'Sin tipo' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Archivo guardado</dt>
                        <dd class="font-medium {{ $archivoExiste ? 'text-success' : 'text-destructive' }}">{{ $archivoExiste ? 'Disponible' : 'No encontrado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Subido por</dt>
                        <dd class="font-medium text-foreground">{{ $documento->subidor?->nombre ?? 'Sin usuario' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Fecha subida</dt>
                        <dd class="font-medium text-foreground">{{ $documento->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
                @if ($documento->notas)
                    <div class="mt-4 rounded-md border border-border bg-muted/30 p-3 text-sm text-muted-foreground">{{ $documento->notas }}</div>
                @endif
            </section>

            <section class="admin-card overflow-hidden">
                <div class="border-b border-border p-4">
                    <h2 class="text-base font-semibold text-foreground">Lecturas</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Historial preparado para una futura integracion OCR o IA. En esta fase la revision es manual.</p>
                </div>
                <div class="divide-y divide-border">
                    @foreach ($documento->lecturas as $lectura)
                        <div class="p-4 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium text-foreground">{{ ucfirst($lectura->estado) }}</span>
                                <span class="text-xs text-muted-foreground">{{ $lectura->motor }}</span>
                            </div>
                            <p class="mt-2 text-muted-foreground">{{ $lectura->texto_extraido ?: 'Pendiente de lectura automatica.' }}</p>
                            @if ($lectura->mensaje_error)
                                <p class="mt-2 text-destructive">{{ $lectura->mensaje_error }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="admin-card p-4 lg:p-6">
                <h2 class="text-base font-semibold text-foreground">Borrador revisable</h2>
                <p class="mt-2 text-sm text-muted-foreground">El documento tiene un borrador asociado, pero no se convierte en pedido ni recepcion hasta que una persona lo revise y confirme.</p>
                <p class="mt-2 text-sm text-muted-foreground">Ahora mismo no hay OCR ni IA conectada: las lineas no se rellenan solas. La revision manual permite introducir los datos antes de generar el pedido.</p>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Estado</dt>
                        <dd class="font-medium text-foreground">{{ str_replace('_', ' ', $documento->borrador?->estado ?? 'sin borrador') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Pedido creado</dt>
                        <dd class="font-medium text-foreground">{{ $documento->borrador?->pedido?->numero ?? '-' }}</dd>
                    </div>
                </dl>
                @if ($documento->borrador)
                    <div class="mt-4">
                        <a href="{{ route('admin.compras.documentos.borradores.edit', $documento->borrador) }}" class="admin-btn-primary w-full justify-center">Revisar borrador</a>
                    </div>
                @endif
            </section>

            <section class="admin-card border-warning/40 p-4 lg:p-6">
                <h2 class="text-base font-semibold text-foreground">Regla de seguridad</h2>
                <p class="mt-2 text-sm text-muted-foreground">La lectura de documentos nunca actualiza stock automaticamente. Primero debe convertirse en un borrador revisable y despues confirmarse manualmente.</p>
            </section>

            @if (! $documento->borrador?->pedido_compra_id)
                <section class="admin-card border-destructive/30 p-4 lg:p-6">
                    <h2 class="text-base font-semibold text-foreground">Documento equivocado</h2>
                    <p class="mt-2 text-sm text-muted-foreground">Si el archivo no corresponde o esta mal subido, puedes eliminarlo mientras no haya generado pedido.</p>
                    <form method="POST" action="{{ route('admin.compras.documentos.destroy', $documento) }}" class="mt-4" onsubmit="return confirm('Eliminar este documento? Esta accion quitara el archivo subido.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-btn-outline w-full justify-center border-destructive/50 text-destructive hover:bg-destructive/10">Eliminar documento</button>
                    </form>
                </section>
            @endif
        </aside>
    </div>
</x-app-layout>
