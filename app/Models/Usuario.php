<?php

namespace App\Models;

use App\Enums\RolUsuario;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UsuarioFactory> */
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    /**
     * Tabla principal de usuarios del panel.
     *
     * Se usa nombre en espanol para mantener coherencia con el dominio.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'rol',
        'es_protegido',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Devuelve los casts del modelo.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'rol' => RolUsuario::class,
            'es_protegido' => 'boolean',
        ];
    }

    /**
     * Indica si este usuario no debe poder eliminarse desde el panel.
     */
    public function esProtegido(): bool
    {
        return $this->es_protegido || $this->rol === RolUsuario::Superadmin;
    }
}
