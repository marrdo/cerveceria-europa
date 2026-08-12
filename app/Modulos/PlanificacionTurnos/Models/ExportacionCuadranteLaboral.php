<?php

namespace App\Modulos\PlanificacionTurnos\Models;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Archivo Excel inmutable generado durante una publicación del cuadrante.
 */
class ExportacionCuadranteLaboral extends Model
{
    use HasUuids;

    protected $table = 'exportaciones_cuadrantes_laborales';

    /** @var list<string> */
    protected $fillable = [
        'cuadrante_laboral_id',
        'version',
        'disk',
        'ruta',
        'nombre_archivo',
        'mime_type',
        'tamano_bytes',
        'hash_sha256',
        'generado_por_id',
        'generado_at',
    ];

    /** @return BelongsTo<CuadranteLaboral, $this> */
    public function cuadrante(): BelongsTo
    {
        return $this->belongsTo(CuadranteLaboral::class, 'cuadrante_laboral_id');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function generadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'generado_por_id')->withTrashed();
    }

    /**
     * Devuelve el tamaño del archivo en una unidad legible para la interfaz.
     */
    public function tamanoLegible(): string
    {
        if ($this->tamano_bytes < 1024) {
            return $this->tamano_bytes.' B';
        }

        if ($this->tamano_bytes < 1024 * 1024) {
            return number_format($this->tamano_bytes / 1024, 1, ',', '.').' KB';
        }

        return number_format($this->tamano_bytes / (1024 * 1024), 1, ',', '.').' MB';
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'tamano_bytes' => 'integer',
            'generado_at' => 'datetime',
        ];
    }
}
