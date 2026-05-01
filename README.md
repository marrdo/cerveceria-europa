# Cerveceria Europa

Panel administrativo Laravel 12 para gestionar inventario y compras a proveedores de un bar en Sevilla.

## Stack objetivo

- PHP 8.4
- Laravel 12
- MySQL 8.4
- WAMP en Windows
- Blade, Tailwind y Laravel Breeze para autenticacion inicial

## Codificacion

El proyecto se configura para UTF-8 estricto:

```env
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_es_0900_ai_ci
APP_LOCALE=es
APP_FAKER_LOCALE=es_ES
```

Base de datos recomendada:

```sql
CREATE DATABASE cerveceria_europa
CHARACTER SET utf8mb4
COLLATE utf8mb4_es_0900_ai_ci;
```

## Convenciones

- Tablas propias en espanol.
- Codigo sin `n` con tilde: se usa `ni` cuando haga falta.
- Modelo principal de autenticacion: `App\Models\Usuario`.
- Tabla principal de autenticacion: `usuarios`.
- Roles iniciales: `camarero`, `encargado`, `propietario`, `superadmin`.

## Modulos previstos

- `Inventory`: productos, categorias, unidades, ubicaciones, stock, movimientos y lotes.
- `Purchasing`: proveedores, pedidos, recepciones y entradas reales en inventario.
- `LecturasDocumentos`: futuro modulo para lectura asistida de albaranes/facturas mediante OCR o IA.

## Arranque local

```powershell
composer install
npm.cmd install
npm.cmd run build
php artisan migrate --seed
php artisan serve
```

Usuario inicial:

```text
admin@cerveceria-europa.local
password
```
