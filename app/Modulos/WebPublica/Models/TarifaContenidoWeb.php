<?php

namespace App\Modulos\WebPublica\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifaContenidoWeb extends Model
{
    use HasUuids;

    protected $table = 'tarifas_contenido_web';

    protected $fillable = [
        'contenido_web_id',
        'nombre',
        'precio',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'orden' => 'integer',
        ];
    }

    /** @return BelongsTo<ContenidoWeb, $this> */
    public function contenido(): BelongsTo
    {
        return $this->belongsTo(ContenidoWeb::class, 'contenido_web_id');
    }

    /**
     * Texto de precio listo para mostrar.
     */
    public function precioFormateado(): string
    {
        return number_format((float) $this->precio, 2, ',', '.').' EUR';
    }
}
