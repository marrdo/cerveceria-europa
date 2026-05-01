<?php

namespace App\Modulos\Inventario\Models;

use App\Modulos\Inventario\Enums\TipoMovimientoInventario;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    use HasUuids;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'producto_id',
        'proveedor_id',
        'ubicacion_inventario_id',
        'ubicacion_origen_id',
        'ubicacion_destino_id',
        'tipo',
        'cantidad',
        'stock_antes',
        'stock_despues',
        'coste_unitario',
        'motivo',
        'referencia',
        'caduca_el',
        'notas',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoMovimientoInventario::class,
            'cantidad' => 'decimal:3',
            'stock_antes' => 'decimal:3',
            'stock_despues' => 'decimal:3',
            'coste_unitario' => 'decimal:2',
            'caduca_el' => 'date',
        ];
    }

    /** @return BelongsTo<Producto, $this> */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /** @return BelongsTo<Proveedor, $this> */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id')->withTrashed();
    }

    /** @return BelongsTo<UbicacionInventario, $this> */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(UbicacionInventario::class, 'ubicacion_inventario_id')->withTrashed();
    }

    /** @return BelongsTo<Usuario, $this> */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por')->withTrashed();
    }
}
