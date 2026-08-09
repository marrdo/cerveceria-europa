<?php

namespace App\Modulos\Sistema\Modulos;

use App\Enums\RolUsuario;

/**
 * Describe un módulo vendible y sus límites operativos.
 */
final readonly class DefinicionModulo
{
    /**
     * @param  list<RolUsuario>  $roles
     * @param  list<string>  $dependencias
     * @param  list<string>  $integraciones
     */
    public function __construct(
        public string $clave,
        public string $nombre,
        public string $descripcion,
        public string $grupo,
        public int $orden,
        public bool $activoPorDefecto,
        public array $roles,
        public array $dependencias = [],
        public array $integraciones = [],
    ) {}

    public function permiteRol(RolUsuario $rol): bool
    {
        return in_array($rol, $this->roles, true);
    }

    /** @return array<string, bool|int|string> */
    public function atributosPersistibles(): array
    {
        return [
            'clave' => $this->clave,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'grupo' => $this->grupo,
            'orden' => $this->orden,
            'activo' => $this->activoPorDefecto,
        ];
    }
}
