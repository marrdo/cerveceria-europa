<?php

namespace App\Modulos\Compras\Models;

use App\Models\Usuario;
use App\Modulos\Compras\Enums\EstadoDocumentoCompra;
use App\Modulos\Compras\Enums\TipoDocumentoCompra;
use App\Modulos\Inventario\Models\Proveedor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoCompra extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'documentos_compra';

    protected $fillable = [
        'proveedor_id',
        'tipo_documento',
        'estado',
        'nombre_original',
        'disco',
        'ruta_archivo',
        'mime_type',
        'tamano_bytes',
        'notas',
        'subido_por',
    ];

    protected function casts(): array
    {
        return [
            'tipo_documento' => TipoDocumentoCompra::class,
            'estado' => EstadoDocumentoCompra::class,
            'tamano_bytes' => 'integer',
        ];
    }

    /** @return BelongsTo<Proveedor, $this> */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id')->withTrashed();
    }

    /** @return BelongsTo<Usuario, $this> */
    public function subidor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'subido_por')->withTrashed();
    }

    /** @return HasMany<LecturaDocumentoCompra> */
    public function lecturas(): HasMany
    {
        return $this->hasMany(LecturaDocumentoCompra::class, 'documento_compra_id')->latest('created_at');
    }

    /** @return HasOne<BorradorCompraDocumento> */
    public function borrador(): HasOne
    {
        return $this->hasOne(BorradorCompraDocumento::class, 'documento_compra_id');
    }
}
