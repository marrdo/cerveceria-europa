<?php

namespace App\Modulos\Ventas\Models;

use App\Modulos\Inventario\Models\MovimientoInventario;
use App\Modulos\Inventario\Models\Producto;
use App\Modulos\Ventas\Enums\EstadoLineaComanda;
use App\Modulos\WebPublica\Models\ContenidoWeb;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineaComanda extends Model
{
    use HasUuids;

    protected $table = 'lineas_comanda';

    protected $fillable = [
        'comanda_id',
        'contenido_web_id',
        'producto_id',
        'movimiento_inventario_id',
        'nombre',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'impuestos',
        'total',
        'estado',
        'notas',
        'orden',
        'servida_at',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:3',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'impuestos' => 'decimal:2',
            'total' => 'decimal:2',
            'estado' => EstadoLineaComanda::class,
            'servida_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Comanda, $this> */
    public function comanda(): BelongsTo
    {
        return $this->belongsTo(Comanda::class, 'comanda_id');
    }

    /** @return BelongsTo<ContenidoWeb, $this> */
    public function contenidoWeb(): BelongsTo
    {
        return $this->belongsTo(ContenidoWeb::class, 'contenido_web_id')->withTrashed();
    }

    /** @return BelongsTo<Producto, $this> */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id')->withTrashed();
    }

    /** @return BelongsTo<MovimientoInventario, $this> */
    public function movimientoInventario(): BelongsTo
    {
        return $this->belongsTo(MovimientoInventario::class, 'movimiento_inventario_id');
    }

    public function estaServida(): bool
    {
        return $this->estado === EstadoLineaComanda::Servida;
    }
}
