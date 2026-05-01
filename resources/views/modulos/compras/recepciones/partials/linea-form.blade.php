<tr class="border-b border-border last:border-0 odd:bg-card even:bg-muted/20">
    <td class="px-4 py-3">
        <select name="lineas[{{ $indice }}][linea_pedido_compra_id]" class="admin-input block h-10 w-full">
            <option value="">Seleccionar</option>
            @foreach ($pedido->lineas as $lineaPedido)
                <option value="{{ $lineaPedido->id }}" @selected(($lineaFormulario['linea_pedido_compra_id'] ?? '') === $lineaPedido->id)>
                    {{ $lineaPedido->descripcion }} - pendiente {{ $lineaPedido->producto?->formatearCantidad($lineaPedido->cantidadPendiente()) ?? $lineaPedido->cantidadPendiente() }} {{ $lineaPedido->producto?->codigoUnidad() }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('lineas.'.$indice.'.linea_pedido_compra_id')" class="mt-2" />
    </td>
    <td class="px-4 py-3">
        <select name="lineas[{{ $indice }}][ubicacion_inventario_id]" class="admin-input block h-10 w-full">
            <option value="">Seleccionar</option>
            @foreach ($ubicaciones as $ubicacion)
                <option value="{{ $ubicacion->id }}" @selected(($lineaFormulario['ubicacion_inventario_id'] ?? '') === $ubicacion->id)>{{ $ubicacion->nombre }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('lineas.'.$indice.'.ubicacion_inventario_id')" class="mt-2" />
    </td>
    <td class="px-4 py-3">
        <x-text-input name="lineas[{{ $indice }}][cantidad]" type="number" step="0.001" min="0" class="block h-10 w-full" :value="$lineaFormulario['cantidad'] ?? ''" />
        <x-input-error :messages="$errors->get('lineas.'.$indice.'.cantidad')" class="mt-2" />
    </td>
    <td class="px-4 py-3">
        <x-text-input name="lineas[{{ $indice }}][codigo_lote]" class="block h-10 w-full" :value="$lineaFormulario['codigo_lote'] ?? ''" maxlength="100" />
        <x-input-error :messages="$errors->get('lineas.'.$indice.'.codigo_lote')" class="mt-2" />
    </td>
    <td class="px-4 py-3">
        <x-text-input name="lineas[{{ $indice }}][caduca_el]" type="date" class="block h-10 w-full" :value="$lineaFormulario['caduca_el'] ?? ''" />
        <x-input-error :messages="$errors->get('lineas.'.$indice.'.caduca_el')" class="mt-2" />
    </td>
    <td class="px-4 py-3">
        <x-text-input name="lineas[{{ $indice }}][notas]" class="block h-10 w-full" :value="$lineaFormulario['notas'] ?? ''" />
        <x-input-error :messages="$errors->get('lineas.'.$indice.'.notas')" class="mt-2" />
    </td>
</tr>
