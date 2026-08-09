<?php

namespace App\Modulos\Sistema\Modulos;

use App\Enums\RolUsuario;
use Illuminate\Support\Collection;

/**
 * Fuente única de verdad de los módulos disponibles en la instalación.
 */
final class CatalogoModulos
{
    /**
     * @return Collection<string, DefinicionModulo>
     */
    public function todos(): Collection
    {
        return collect([
            new DefinicionModulo(
                clave: 'inventario',
                nombre: 'Inventario',
                descripcion: 'Gestión de productos, proveedores, ubicaciones, stock y movimientos.',
                grupo: 'panel',
                orden: 10,
                activoPorDefecto: true,
                roles: [RolUsuario::Encargado, RolUsuario::Propietario],
                integraciones: ['ventas', 'web_publica'],
            ),
            new DefinicionModulo(
                clave: 'compras',
                nombre: 'Compras a proveedor',
                descripcion: 'Pedidos, recepciones, incidencias, devoluciones y propuestas de compra.',
                grupo: 'panel',
                orden: 20,
                activoPorDefecto: true,
                roles: [RolUsuario::Encargado, RolUsuario::Propietario],
                dependencias: ['inventario'],
            ),
            new DefinicionModulo(
                clave: 'web_publica',
                nombre: 'Web pública',
                descripcion: 'Página web gestionable conectada con la carta y la identidad del negocio.',
                grupo: 'web',
                orden: 30,
                activoPorDefecto: true,
                roles: [RolUsuario::Propietario],
                integraciones: ['inventario'],
            ),
            new DefinicionModulo(
                clave: 'blog',
                nombre: 'Blog',
                descripcion: 'Noticias, artículos y categorías editoriales dentro de la web pública.',
                grupo: 'web',
                orden: 40,
                activoPorDefecto: true,
                roles: [RolUsuario::Propietario],
                dependencias: ['web_publica'],
            ),
            new DefinicionModulo(
                clave: 'ventas',
                nombre: 'Ventas',
                descripcion: 'Comandas, servicio, cobros, caja e informes comerciales.',
                grupo: 'panel',
                orden: 50,
                activoPorDefecto: true,
                roles: [RolUsuario::Camarero, RolUsuario::Encargado, RolUsuario::Propietario],
                dependencias: ['inventario', 'web_publica'],
                integraciones: ['espacios'],
            ),
            new DefinicionModulo(
                clave: 'espacios',
                nombre: 'Espacios y mesas',
                descripcion: 'Gestión de recintos, zonas y mesas del establecimiento.',
                grupo: 'panel',
                orden: 52,
                activoPorDefecto: true,
                roles: [RolUsuario::Encargado, RolUsuario::Propietario],
                integraciones: ['ventas', 'reservas'],
            ),
            new DefinicionModulo(
                clave: 'personal',
                nombre: 'Gestión de personal',
                descripcion: 'Alta de usuarios operativos y permisos por rol.',
                grupo: 'panel',
                orden: 55,
                activoPorDefecto: true,
                roles: [RolUsuario::Encargado, RolUsuario::Propietario],
            ),
            new DefinicionModulo(
                clave: 'planificacion_turnos',
                nombre: 'Planificación de turnos',
                descripcion: 'Cuadrantes, incidencias, coberturas, publicación y exportación laboral.',
                grupo: 'personal',
                orden: 57,
                activoPorDefecto: true,
                roles: [RolUsuario::Encargado, RolUsuario::Propietario],
                dependencias: ['personal'],
            ),
            new DefinicionModulo(
                clave: 'reservas',
                nombre: 'Reservas',
                descripcion: 'Módulo futuro para gestionar reservas desde la web.',
                grupo: 'web',
                orden: 60,
                activoPorDefecto: false,
                roles: [RolUsuario::Encargado, RolUsuario::Propietario],
                dependencias: ['web_publica', 'espacios'],
            ),
            new DefinicionModulo(
                clave: 'lectura_documentos',
                nombre: 'Lectura asistida de documentos',
                descripcion: 'Módulo futuro para OCR o IA de albaranes y facturas.',
                grupo: 'compras',
                orden: 70,
                activoPorDefecto: false,
                roles: [RolUsuario::Encargado, RolUsuario::Propietario],
                dependencias: ['compras'],
            ),
        ])->keyBy(fn (DefinicionModulo $modulo): string => $modulo->clave);
    }

    public function buscar(string $clave): ?DefinicionModulo
    {
        return $this->todos()->get($clave);
    }

    public function contiene(string $clave): bool
    {
        return $this->buscar($clave) !== null;
    }

    /**
     * Devuelve módulos que dependen directa o indirectamente de la clave.
     *
     * @return Collection<string, DefinicionModulo>
     */
    public function dependientesDe(string $clave): Collection
    {
        return $this->todos()->filter(
            fn (DefinicionModulo $modulo): bool => in_array($clave, $this->dependenciasTransitivas($modulo->clave), true),
        );
    }

    /** @return list<string> */
    public function dependenciasTransitivas(string $clave): array
    {
        $visitadas = [];
        $pendientes = $this->buscar($clave)?->dependencias ?? [];

        while ($dependencia = array_shift($pendientes)) {
            if (in_array($dependencia, $visitadas, true)) {
                continue;
            }

            $visitadas[] = $dependencia;
            array_push($pendientes, ...($this->buscar($dependencia)?->dependencias ?? []));
        }

        return $visitadas;
    }
}
