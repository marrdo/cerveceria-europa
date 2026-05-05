<?php

namespace App\Modulos\Ventas\Models;

use App\Models\Usuario;
use App\Modulos\Ventas\Enums\MetodoPagoComanda;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoComanda extends Model
{
    use HasUuids;

    protected $table = 'pagos_comanda';

    protected $fillable = [
        'comanda_id',
        'metodo',
        'importe',
        'recibido',
        'cambio',
        'referencia',
        'notas',
        'cobrado_por',
        'cobrado_at',
    ];

    protected function casts(): array
    {
        return [
            'metodo' => MetodoPagoComanda::class,
            'importe' => 'decimal:2',
            'recibido' => 'decimal:2',
            'cambio' => 'decimal:2',
            'cobrado_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Comanda, $this> */
    public function comanda(): BelongsTo
    {
        return $this->belongsTo(Comanda::class, 'comanda_id');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function cobrador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cobrado_por')->withTrashed();
    }
}
