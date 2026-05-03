<?php

namespace App\Modulos\Compras\Models;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecepcionCompra extends Model
{
    use HasUuids;

    protected $table = 'recepciones_compra';

    protected $fillable = [
        'pedido_compra_id',
        'numero',
        'fecha_recepcion',
        'notas',
        'recibido_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_recepcion' => 'date',
        ];
    }

    /** @return BelongsTo<PedidoCompra, $this> */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(PedidoCompra::class, 'pedido_compra_id');
    }

    /** @return HasMany<LineaRecepcionCompra> */
    public function lineas(): HasMany
    {
        return $this->hasMany(LineaRecepcionCompra::class, 'recepcion_compra_id');
    }

    /** @return HasMany<IncidenciaRecepcionCompra> */
    public function incidencias(): HasMany
    {
        return $this->hasMany(IncidenciaRecepcionCompra::class, 'recepcion_compra_id');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function receptor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'recibido_por')->withTrashed();
    }
}
