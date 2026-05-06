<?php

namespace App\Modulos\Ventas\Models;

use App\Models\Usuario;
use App\Modulos\Espacios\Models\Recinto;
use App\Modulos\Ventas\Enums\EstadoTurnoCaja;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TurnoCaja extends Model
{
    use HasUuids;

    protected $table = 'turnos_caja';

    protected $fillable = [
        'numero',
        'recinto_id',
        'estado',
        'saldo_inicial',
        'efectivo_esperado',
        'efectivo_contado',
        'descuadre',
        'total_ventas',
        'total_efectivo',
        'total_tarjeta',
        'total_bizum',
        'total_invitacion',
        'total_otro',
        'total_cambio',
        'pagos_count',
        'abierta_por',
        'cerrada_por',
        'abierta_at',
        'cerrada_at',
        'notas_apertura',
        'notas_cierre',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoTurnoCaja::class,
            'saldo_inicial' => 'decimal:2',
            'efectivo_esperado' => 'decimal:2',
            'efectivo_contado' => 'decimal:2',
            'descuadre' => 'decimal:2',
            'total_ventas' => 'decimal:2',
            'total_efectivo' => 'decimal:2',
            'total_tarjeta' => 'decimal:2',
            'total_bizum' => 'decimal:2',
            'total_invitacion' => 'decimal:2',
            'total_otro' => 'decimal:2',
            'total_cambio' => 'decimal:2',
            'abierta_at' => 'datetime',
            'cerrada_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Recinto, $this> */
    public function recinto(): BelongsTo
    {
        return $this->belongsTo(Recinto::class, 'recinto_id')->withTrashed();
    }

    /** @return BelongsTo<Usuario, $this> */
    public function abiertoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'abierta_por')->withTrashed();
    }

    /** @return BelongsTo<Usuario, $this> */
    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cerrada_por')->withTrashed();
    }

    /** @return HasMany<PagoComanda> */
    public function pagos(): HasMany
    {
        return $this->hasMany(PagoComanda::class, 'caja_turno_id')->latest('cobrado_at');
    }

    /**
     * Indica si el turno admite pagos y cierre.
     */
    public function estaAbierta(): bool
    {
        return $this->estado === EstadoTurnoCaja::Abierta;
    }
}
