<?php

namespace App\Modulos\PlanificacionTurnos\Models;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Patrón semanal reutilizable construido desde un cuadrante existente.
 */
class PlantillaCuadranteLaboral extends Model
{
    use HasUuids;

    protected $table = 'plantillas_cuadrantes_laborales';

    /** @var list<string> */
    protected $fillable = ['nombre', 'descripcion', 'creado_por_id'];

    /** @return HasMany<PlantillaJornadaLaboral, $this> */
    public function jornadas(): HasMany
    {
        return $this->hasMany(PlantillaJornadaLaboral::class, 'plantilla_id');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por_id')->withTrashed();
    }
}
