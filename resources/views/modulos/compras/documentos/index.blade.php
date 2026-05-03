<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Documentos de compra" :description="$documentos->total().' documentos subidos'">
            <x-slot name="actions">
                <a href="{{ route('admin.compras.documentos.create') }}" class="admin-btn-primary">Subir documento</a>
            </x-slot>
        </x-admin.page-header>
    </x-slot>

    @include('modulos.compras.partials.nav')

    @if (session('status'))
        <div class="mb-4 rounded-md border border-success/25 bg-success/10 p-4 text-sm text-success">{{ session('status') }}</div>
    @endif

    <section class="admin-card mb-4 p-4 lg:p-6">
        <h2 class="text-base font-semibold text-foreground">Lectura asistida</h2>
        <p class="mt-2 text-sm text-muted-foreground">Aqui se guardan fotos o PDF de albaranes y facturas. En esta fase quedan preparados para lectura OCR o IA, pero nunca actualizan stock sin revision humana.</p>
    </section>

    <div class="overflow-x-auto rounded-lg border border-border bg-card">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border bg-muted/50">
                    <th class="px-4 py-3 text-left font-medium text-foreground">Documento</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Tipo</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Proveedor</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Estado</th>
                    <th class="px-4 py-3 text-left font-medium text-foreground">Subido por</th>
                    <th class="px-4 py-3 text-right font-medium text-foreground">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documentos as $documento)
                    <tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
                        <td class="px-4 py-3">
                            <div class="font-medium text-foreground">{{ $documento->nombre_original }}</div>
                            <div class="text-xs text-muted-foreground">{{ $documento->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $documento->tipo_documento->etiqueta() }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $documento->proveedor?->nombre ?? 'Sin proveedor' }}</td>
                        <td class="px-4 py-3"><x-admin.status-badge :variant="$documento->estado->variante()">{{ $documento->estado->etiqueta() }}</x-admin.status-badge></td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $documento->subidor?->nombre ?? 'Sin usuario' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.compras.documentos.show', $documento) }}" class="text-primary hover:underline">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-muted-foreground">Todavia no hay documentos subidos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $documentos->links() }}</div>
</x-app-layout>
