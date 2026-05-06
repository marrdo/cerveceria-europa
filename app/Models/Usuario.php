<?php

namespace App\Models;

use App\Enums\RolUsuario;
use App\Models\Modulo;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

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

    /**
     * Indica si el usuario puede entrar a un modulo del panel.
     */
    public function puedeAccederModulo(string $modulo): bool
    {
        if ($this->rol === RolUsuario::Superadmin) {
            return true;
        }

        $moduloActivo = Modulo::query()->where('clave', $modulo)->value('activo');

        if ($moduloActivo !== null && ! (bool) $moduloActivo) {
            return false;
        }

        if ($this->rol === RolUsuario::Propietario) {
            return true;
        }

        return match ($modulo) {
            'inventario', 'compras' => $this->rol === RolUsuario::Encargado,
            'web_publica' => false,
            'ventas' => in_array($this->rol, [RolUsuario::Camarero, RolUsuario::Encargado], true),
            'espacios' => $this->rol === RolUsuario::Encargado,
            'personal' => in_array($this->rol, [RolUsuario::Encargado, RolUsuario::Propietario], true),
            default => false,
        };
    }

    /**
     * Indica si el usuario puede crear y consultar personal desde el panel.
     */
    public function puedeGestionarPersonal(): bool
    {
        return $this->rolesGestionables()->isNotEmpty();
    }

    /**
     * Indica si el usuario puede abrir, cerrar y revisar caja.
     */
    public function puedeGestionarCaja(): bool
    {
        return in_array($this->rol, [RolUsuario::Superadmin, RolUsuario::Propietario, RolUsuario::Encargado], true)
            && $this->puedeAccederModulo('ventas');
    }

    /**
     * Indica si este usuario puede gestionar a otro usuario concreto.
     */
    public function puedeGestionarUsuario(Usuario $usuario): bool
    {
        if ($this->id === $usuario->id || $usuario->esProtegido()) {
            return false;
        }

        return $this->rolesGestionables()
            ->contains(fn (RolUsuario $rol): bool => $rol === $usuario->rol);
    }

    /**
     * Roles que este usuario puede crear desde gestion de personal.
     *
     * @return Collection<int, RolUsuario>
     */
    public function rolesGestionables(): Collection
    {
        return collect(match ($this->rol) {
            RolUsuario::Superadmin => RolUsuario::cases(),
            RolUsuario::Propietario => [RolUsuario::Camarero, RolUsuario::Encargado],
            RolUsuario::Encargado => [RolUsuario::Camarero],
            default => [],
        });
    }
}
