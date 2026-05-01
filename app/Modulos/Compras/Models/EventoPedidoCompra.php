<?php

namespace App\Modulos\Compras\Models;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoPedidoCompra extends Model
{
    use HasUuids;

    protected $table = 'eventos_pedido_compra';

    protected $fillable = [
        'pedido_compra_id',
        'tipo',
        'estado_anterior',
        'estado_nuevo',
        'descripcion',
        'usuario_id',
    ];

    /** @return BelongsTo<PedidoCompra, $this> */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(PedidoCompra::class, 'pedido_compra_id');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id')->withTrashed();
    }
}
