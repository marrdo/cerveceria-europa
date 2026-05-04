<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasUuids;

    protected $table = 'modulos';

    protected $fillable = [
        'clave',
        'nombre',
        'descripcion',
        'grupo',
        'activo',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'orden' => 'integer',
        ];
    }

    /**
     * Indica si un modulo contratado/opcional esta activo.
     */
    public static function activo(string $clave): bool
    {
        return (bool) self::query()
            ->where('clave', $clave)
            ->value('activo');
    }
}
