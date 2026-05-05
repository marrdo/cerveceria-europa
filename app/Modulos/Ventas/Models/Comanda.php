<?php

namespace App\Modulos\Ventas\Models;

use App\Models\Usuario;
use App\Modulos\Inventario\Models\UbicacionInventario;
use App\Modulos\Ventas\Enums\EstadoComanda;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comanda extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'comandas';

    protected $fillable = [
        'numero',
        'mesa',
        'cliente_nombre',
        'estado',
        'ubicacion_inventario_id',
        'subtotal',
        'impuestos',
        'total',
        'notas',
        'creado_por',
        'actualizado_por',
        'servida_at',
        'cerrada_at',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoComanda::class,
            'subtotal' => 'decimal:2',
            'impuestos' => 'decimal:2',
            'total' => 'decimal:2',
            'servida_at' => 'datetime',
            'cerrada_at' => 'datetime',
        ];
    }

    /** @return HasMany<LineaComanda> */
    public function lineas(): HasMany
    {
        return $this->hasMany(LineaComanda::class, 'comanda_id')->orderBy('orden')->orderBy('created_at');
    }

    /** @return BelongsTo<UbicacionInventario, $this> */
    public function ubicacionInventario(): BelongsTo
    {
        return $this->belongsTo(UbicacionInventario::class, 'ubicacion_inventario_id')->withTrashed();
    }

    /** @return BelongsTo<Usuario, $this> */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por')->withTrashed();
    }

    public function puedeEditar(): bool
    {
        return in_array($this->estado, [EstadoComanda::Abierta, EstadoComanda::EnPreparacion], true);
    }

    public function recalcularTotales(): void
    {
        $this->loadMissing('lineas');

        $subtotal = round((float) $this->lineas->sum('subtotal'), 2);
        $impuestos = round((float) $this->lineas->sum('impuestos'), 2);

        $this->update([
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'total' => round($subtotal + $impuestos, 2),
        ]);
    }
}
