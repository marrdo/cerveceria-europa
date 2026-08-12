# Sistema de módulos contratados

## Objetivo

Activar o desactivar funcionalidades vendibles sin duplicar reglas entre la
base de datos, los permisos, las rutas y la interfaz.

La configuración modular no elimina tablas ni datos. Decide qué superficies
están operativas para cada perfil y conserva la información si un contrato se
desactiva temporalmente.

## Arquitectura

| Pieza | Responsabilidad |
|---|---|
| `CatalogoModulos` | Fuente única de claves, nombres, roles y relaciones |
| `GestorModulos` | Acceso, estados operativos y cambios transaccionales |
| `AuditorModulos` | Detecta catálogo, base o dependencias incoherentes |
| `ModuloSeeder` | Sincroniza metadatos sin reiniciar el contrato |
| `modulo:{clave}` | Protege rutas administrativas según usuario y contrato |
| `modulo.publico:{clave}` | Devuelve 404 si una superficie pública está inactiva |

La tabla `modulos` conserva únicamente el estado contractual y los metadatos
consultables. Las dependencias viven en código para que puedan revisarse,
probarse y versionarse junto a la funcionalidad.

## Dependencias obligatorias

| Módulo | Requiere | Motivo |
|---|---|---|
| Compras | Inventario | Productos, proveedores, entradas y devoluciones |
| Ventas | Inventario y Web pública | Carta vendible y trazabilidad de consumos |
| Blog | Web pública | Superficie editorial pública |
| Planificación de turnos | Personal | Empleados y permisos laborales |
| Reservas | Web pública y Espacios | Captación pública y asignación física |
| Lectura de documentos | Compras | Generación asistida de pedidos |

`Ventas` puede integrarse con `Espacios`, pero la mesa es opcional y por eso no
se considera una dependencia obligatoria.

## Reglas de cambio

- No puede activarse un módulo si falta alguna dependencia.
- No puede desactivarse un módulo que necesite otro módulo activo.
- Una clave desconocida o una fila ausente falla de forma cerrada.
- Los perfiles operativos solo acceden a módulos activos permitidos por su rol.
- `superadmin` puede entrar en administración aunque el contrato esté inactivo,
  para preparar datos antes de activarlo.
- Las rutas públicas siempre quedan ocultas con 404 cuando el módulo no opera.

El dashboard explica qué requiere cada módulo, sus integraciones y el motivo
por el que un botón de activación o desactivación está bloqueado.

## Rutas aisladas

Cada módulo registra sus rutas en su propio archivo:

```text
routes/modulos/personal.php
routes/modulos/planificacion-turnos.php
routes/modulos/inventario.php
routes/modulos/compras.php
routes/modulos/ventas.php
routes/modulos/espacios.php
routes/modulos/web-publica.php
```

`routes/web.php` queda reservado para dashboard, perfil, configuración y carga
explícita de los módulos.

## Añadir un módulo

1. Añadir su `DefinicionModulo` en `CatalogoModulos`.
2. Declarar roles, dependencias e integraciones reales.
3. Crear `routes/modulos/{clave}.php` con su middleware.
4. Añadir controladores, Requests, Actions, Policies o servicios de dominio.
5. Ejecutar el seeder sin alterar estados contratados existentes.
6. Añadir pruebas de acceso activo, inactivo y dependencias.
7. Ejecutar la auditoría modular.

## Comprobación

```powershell
php artisan db:seed --class=ModuloSeeder
php artisan modulos:auditar
php artisan route:list --except-vendor
php artisan test --filter=ModuloPermisosTest
```

La auditoría devuelve código distinto de cero si detecta módulos ausentes,
claves desconocidas, ciclos o dependencias activas incoherentes.
