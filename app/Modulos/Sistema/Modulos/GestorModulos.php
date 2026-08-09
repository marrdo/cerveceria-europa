<?php

namespace App\Modulos\Sistema\Modulos;

use App\Enums\RolUsuario;
use App\Models\Modulo;
use App\Models\Usuario;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Resuelve disponibilidad, permisos y cambios coherentes de módulos.
 */
final readonly class GestorModulos
{
    public function __construct(private CatalogoModulos $catalogo) {}

    /**
     * Un módulo solo está operativo si existe, está activo y también lo están
     * todas sus dependencias obligatorias.
     */
    public function estaOperativo(string $clave): bool
    {
        $definicion = $this->catalogo->buscar($clave);

        if ($definicion === null || ! $this->estaActivo($clave)) {
            return false;
        }

        return collect($definicion->dependencias)
            ->every(fn (string $dependencia): bool => $this->estaOperativo($dependencia));
    }

    public function puedeAcceder(Usuario $usuario, string $clave): bool
    {
        $definicion = $this->catalogo->buscar($clave);

        if ($definicion === null) {
            return false;
        }

        if ($usuario->rol === RolUsuario::Superadmin) {
            return true;
        }

        return $this->estaOperativo($clave) && $definicion->permiteRol($usuario->rol);
    }

    /**
     * Cambia el estado sin permitir combinaciones que rompan otros módulos.
     *
     * @throws DomainException
     */
    public function cambiarEstado(Modulo $modulo, bool $activo): Modulo
    {
        $definicion = $this->catalogo->buscar($modulo->clave)
            ?? throw new DomainException('El módulo no está registrado en el catálogo del sistema.');

        return DB::transaction(function () use ($modulo, $definicion, $activo): Modulo {
            /** @var Modulo $bloqueado */
            $bloqueado = Modulo::query()->lockForUpdate()->findOrFail($modulo->id);

            if ($bloqueado->activo === $activo) {
                return $bloqueado;
            }

            if ($activo) {
                $faltantes = collect($definicion->dependencias)
                    ->reject(fn (string $clave): bool => $this->estaOperativo($clave));

                if ($faltantes->isNotEmpty()) {
                    throw new DomainException(
                        'Activa primero: '.$this->nombres($faltantes).'.',
                    );
                }
            } else {
                $dependientes = $this->dependientesActivos($bloqueado->clave);

                if ($dependientes->isNotEmpty()) {
                    throw new DomainException(
                        'Desactiva primero: '.$dependientes->pluck('nombre')->join(', ').'.',
                    );
                }
            }

            $bloqueado->update(['activo' => $activo]);

            return $bloqueado->refresh();
        });
    }

    /**
     * Datos listos para explicar dependencias y bloqueos en el dashboard.
     *
     * @return Collection<int, array{
     *     modulo: Modulo,
     *     definicion: DefinicionModulo,
     *     dependencias: list<string>,
     *     integraciones: list<string>,
     *     bloqueo: string|null
     * }>
     */
    public function resumenAdministracion(): Collection
    {
        $modelos = Modulo::query()->get()->keyBy('clave');

        return $this->catalogo->todos()
            ->sortBy([['grupo', 'asc'], ['orden', 'asc'], ['nombre', 'asc']])
            ->map(function (DefinicionModulo $definicion) use ($modelos): ?array {
                /** @var Modulo|null $modelo */
                $modelo = $modelos->get($definicion->clave);

                if ($modelo === null) {
                    return null;
                }

                $dependencias = $this->nombres(collect($definicion->dependencias), false);
                $integraciones = $this->nombres(collect($definicion->integraciones), false);
                $dependientes = $modelo->activo ? $this->dependientesActivos($modelo->clave) : collect();
                $faltantes = $modelo->activo
                    ? collect()
                    : collect($definicion->dependencias)->reject(
                        fn (string $clave): bool => $this->estaOperativo($clave),
                    );

                return [
                    'modulo' => $modelo,
                    'definicion' => $definicion,
                    'dependencias' => $dependencias,
                    'integraciones' => $integraciones,
                    'bloqueo' => $modelo->activo && $dependientes->isNotEmpty()
                        ? 'Lo necesitan: '.$dependientes->pluck('nombre')->join(', ')
                        : ($faltantes->isNotEmpty() ? 'Falta activar: '.$this->nombres($faltantes) : null),
                ];
            })
            ->filter()
            ->values();
    }

    private function estaActivo(string $clave): bool
    {
        return Modulo::query()->where('clave', $clave)->where('activo', true)->exists();
    }

    /** @return Collection<int, DefinicionModulo> */
    private function dependientesActivos(string $clave): Collection
    {
        $clavesActivas = Modulo::query()->where('activo', true)->pluck('clave');

        return $this->catalogo->dependientesDe($clave)
            ->filter(fn (DefinicionModulo $modulo): bool => $clavesActivas->contains($modulo->clave))
            ->values();
    }

    /**
     * @param  Collection<int, string>  $claves
     * @return ($comoTexto is true ? string : list<string>)
     */
    private function nombres(Collection $claves, bool $comoTexto = true): string|array
    {
        $nombres = $claves
            ->map(fn (string $clave): string => $this->catalogo->buscar($clave)?->nombre ?? $clave)
            ->values();

        return $comoTexto ? $nombres->join(', ') : $nombres->all();
    }
}
