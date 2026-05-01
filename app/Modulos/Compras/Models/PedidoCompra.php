<?php

namespace App\Modulos\Compras\Models;

use App\Models\Usuario;
use App\Modulos\Compras\Enums\EstadoPedidoCompra;
use App\Modulos\Inventario\Models\Proveedor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoCompra extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'pedidos_compra';

    protected $fillable = [
        'proveedor_id',
        'numero',
        'estado',
        'fecha_pedido',
        'fecha_prevista',
        'subtotal',
        'impuestos',
        'total',
        'notas',
        'creado_por',
        'actualizado_por',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPedidoCompra::class,
            'fecha_pedido' => 'date',
            'fecha_prevista' => 'date',
            'subtotal' => 'decimal:2',
            'impuestos' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Proveedor, $this> */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id')->withTrashed();
    }

    /** @return HasMany<LineaPedidoCompra> */
    public function lineas(): HasMany
    {
        return $this->hasMany(LineaPedidoCompra::class, 'pedido_compra_id')->orderBy('orden');
    }

    /** @return HasMany<EventoPedidoCompra> */
    public function eventos(): HasMany
    {
        return $this->hasMany(EventoPedidoCompra::class, 'pedido_compra_id')->latest('created_at');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por')->withTrashed();
    }

    public function puedeEditar(): bool
    {
        return $this->estado === EstadoPedidoCompra::Borrador;
    }
}
