<?php

namespace App\Modulos\Inventario\Models;

use App\Modulos\Inventario\Enums\EstadoStockProducto;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'categoria_producto_id',
        'proveedor_id',
        'unidad_inventario_id',
        'nombre',
        'sku',
        'codigo_barras',
        'referencia_proveedor',
        'descripcion',
        'precio_venta',
        'precio_coste',
        'controla_stock',
        'controla_caducidad',
        'cantidad_alerta_stock',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_venta' => 'decimal:2',
            'precio_coste' => 'decimal:2',
            'controla_stock' => 'boolean',
            'controla_caducidad' => 'boolean',
            'cantidad_alerta_stock' => 'decimal:3',
            'activo' => 'boolean',
        ];
    }

    /** @return BelongsTo<CategoriaProducto, $this> */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoria_producto_id')->withTrashed();
    }

    /** @return BelongsTo<Proveedor, $this> */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id')->withTrashed();
    }

    /** @return BelongsTo<UnidadInventario, $this> */
    public function unidad(): BelongsTo
    {
        return $this->belongsTo(UnidadInventario::class, 'unidad_inventario_id')->withTrashed();
    }

    /** @return HasMany<StockInventario> */
    public function stock(): HasMany
    {
        return $this->hasMany(StockInventario::class, 'producto_id');
    }

    /** @return HasMany<MovimientoInventario> */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id')->latest('created_at');
    }

    /** @return HasMany<LoteInventario> */
    public function lotes(): HasMany
    {
        return $this->hasMany(LoteInventario::class, 'producto_id')->latest('recibido_el');
    }

    /**
     * Devuelve el stock total del producto en todas las ubicaciones.
     */
    public function cantidadStock(): float
    {
        if ($this->relationLoaded('stock')) {
            return round((float) $this->stock->sum('cantidad'), 3);
        }

        return round((float) $this->stock()->sum('cantidad'), 3);
    }

    /**
     * Calcula el estado operativo del stock.
     */
    public function estadoStock(): EstadoStockProducto
    {
        if (! $this->controla_stock) {
            return EstadoStockProducto::SinControl;
        }

        $cantidad = $this->cantidadStock();

        return match (true) {
            $cantidad <= 0 => EstadoStockProducto::SinStock,
            (float) $this->cantidad_alerta_stock > 0 && $cantidad <= (float) $this->cantidad_alerta_stock => EstadoStockProducto::Bajo,
            default => EstadoStockProducto::Correcto,
        };
    }

    /**
     * Formatea una cantidad con los decimales adecuados para su unidad.
     */
    public function formatearCantidad(float|string|null $cantidad): string
    {
        $decimales = $this->unidad?->permite_decimal ? 3 : 0;
        $formateada = number_format((float) ($cantidad ?? 0), $decimales, ',', '.');

        return $decimales === 0 ? $formateada : rtrim(rtrim($formateada, '0'), ',');
    }

    /**
     * Devuelve el codigo visible de unidad.
     */
    public function codigoUnidad(): string
    {
        return $this->unidad?->codigo ?? 'ud';
    }
}
