<x-app-layout>
    <x-slot name="header">
        <x-admin.page-header title="Editar pedido" :description="$pedido->numero" />
    </x-slot>

    @include('modulos.compras.partials.nav')

    @include('modulos.compras.pedidos.partials.form', [
        'action' => route('admin.compras.pedidos.update', $pedido),
        'method' => 'PUT',
        'pedido' => $pedido,
    ])
</x-app-layout>
