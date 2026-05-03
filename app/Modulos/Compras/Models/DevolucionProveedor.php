<?php

namespace App\Modulos\Compras\Models;

use App\Models\Usuario;
use App\Modulos\Inventario\Models\Proveedor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DevolucionProveedor extends Model
{
    use HasUuids;

    protected $table = 'devoluciones_proveedor';

    protected $fillable = [
        'pedido_compra_id',
        'proveedor_id',
        'numero',
        'fecha_devolucion',
        'motivo',
        'notas',
        'registrada_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_devolucion' => 'date',
        ];
    }

    /** @return BelongsTo<PedidoCompra, $this> */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(PedidoCompra::class, 'pedido_compra_id');
    }

    /** @return BelongsTo<Proveedor, $this> */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id')->withTrashed();
    }

    /** @return HasMany<LineaDevolucionProveedor> */
    public function lineas(): HasMany
    {
        return $this->hasMany(LineaDevolucionProveedor::class, 'devolucion_proveedor_id');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function registrador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'registrada_por')->withTrashed();
    }
}
