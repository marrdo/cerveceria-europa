<?php

namespace App\Modulos\Compras\Models;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorradorCompraDocumento extends Model
{
    use HasUuids;

    protected $table = 'borradores_compra_documento';

    protected $fillable = [
        'documento_compra_id',
        'pedido_compra_id',
        'estado',
        'datos_borrador',
        'notas_revision',
        'revisado_por',
        'revisado_at',
    ];

    protected function casts(): array
    {
        return [
            'datos_borrador' => 'array',
            'revisado_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<DocumentoCompra, $this> */
    public function documento(): BelongsTo
    {
        return $this->belongsTo(DocumentoCompra::class, 'documento_compra_id');
    }

    /** @return BelongsTo<PedidoCompra, $this> */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(PedidoCompra::class, 'pedido_compra_id');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function revisor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'revisado_por')->withTrashed();
    }
}
