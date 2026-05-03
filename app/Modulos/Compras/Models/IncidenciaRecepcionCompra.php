<?php

namespace App\Modulos\Compras\Models;

use App\Models\Usuario;
use App\Modulos\Compras\Enums\TipoIncidenciaRecepcionCompra;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidenciaRecepcionCompra extends Model
{
    use HasUuids;

    protected $table = 'incidencias_recepcion_compra';

    protected $fillable = [
        'pedido_compra_id',
        'recepcion_compra_id',
        'linea_pedido_compra_id',
        'tipo',
        'cantidad_afectada',
        'descripcion',
        'resuelta',
        'registrada_por',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoIncidenciaRecepcionCompra::class,
            'cantidad_afectada' => 'decimal:3',
            'resuelta' => 'boolean',
        ];
    }

    /** @return BelongsTo<PedidoCompra, $this> */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(PedidoCompra::class, 'pedido_compra_id');
    }

    /** @return BelongsTo<RecepcionCompra, $this> */
    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(RecepcionCompra::class, 'recepcion_compra_id');
    }

    /** @return BelongsTo<LineaPedidoCompra, $this> */
    public function lineaPedido(): BelongsTo
    {
        return $this->belongsTo(LineaPedidoCompra::class, 'linea_pedido_compra_id');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function registrador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'registrada_por')->withTrashed();
    }
}
