<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('admin.compras.pedidos.index') }}" class="rounded-md border px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.compras.pedidos.*') ? 'border-primary bg-primary text-primary-foreground' : 'border-border bg-card text-foreground hover:bg-muted' }}">Pedidos</a>
    <a href="{{ route('admin.compras.propuestas.index') }}" class="rounded-md border px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.compras.propuestas.*') ? 'border-primary bg-primary text-primary-foreground' : 'border-border bg-card text-foreground hover:bg-muted' }}">Propuestas</a>
    <a href="{{ route('admin.compras.documentos.index') }}" class="rounded-md border px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.compras.documentos.*') ? 'border-primary bg-primary text-primary-foreground' : 'border-border bg-card text-foreground hover:bg-muted' }}">Documentos</a>
</div>
