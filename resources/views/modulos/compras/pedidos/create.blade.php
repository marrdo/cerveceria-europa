<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Nuevo pedido de compra" description="Crea un borrador de pedido a proveedor." />
    </x-slot>

    @include('modulos.compras.partials.nav')

    @include('modulos.compras.pedidos.partials.form', [
        'action' => route('admin.compras.pedidos.store'),
        'method' => 'POST',
        'pedido' => new \App\Modulos\Compras\Models\PedidoCompra(),
    ])
</x-app-layout>
