<?php

namespace App\Modulos\Compras\Models;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LecturaDocumentoCompra extends Model
{
    use HasUuids;

    protected $table = 'lecturas_documentos';

    protected $fillable = [
        'documento_compra_id',
        'motor',
        'estado',
        'texto_extraido',
        'datos_extraidos',
        'mensaje_error',
        'procesado_por',
    ];

    protected function casts(): array
    {
        return [
            'datos_extraidos' => 'array',
        ];
    }

    /** @return BelongsTo<DocumentoCompra, $this> */
    public function documento(): BelongsTo
    {
        return $this->belongsTo(DocumentoCompra::class, 'documento_compra_id');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function procesador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'procesado_por')->withTrashed();
    }
}
