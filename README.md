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
- El usuario `superadmin` inicial queda protegido y no puede eliminarse desde el perfil.

## Modulos previstos

- `Inventario`: productos, categorias, unidades, ubicaciones, stock y movimientos.
- `Compras`: futuro modulo de proveedores, pedidos, recepciones y entradas reales en inventario.
- `LecturasDocumentos`: futuro modulo para lectura asistida de albaranes/facturas mediante OCR o IA.

Documentacion del inventario:

```text
docs/modules/inventario.md
```

## Roadmap por fases

### FASE 1.0 - Base admin e inventario inicial

Estado: implementada.

Objetivo:
Tener una base funcional sobre la que construir el panel real del bar.

Incluye:

- Laravel 12 con Breeze.
- Login privado.
- Registro publico deshabilitado.
- Usuario `superadmin` inicial protegido.
- Usuarios con roles iniciales: `camarero`, `encargado`, `propietario`, `superadmin`.
- Soft deletes en usuarios.
- Configuracion UTF-8 para espanol.
- Modulo `Inventario` inicial.
- Proveedores.
- Categorias de producto.
- Unidades de inventario.
- Ubicaciones de inventario.
- Productos.
- Stock por producto y ubicacion.
- Movimientos manuales de entrada, salida, ajuste y transferencia.
- Seeders iniciales orientados a un bar.
- Tests basicos de autenticacion, perfil e inventario.

### FASE 1.1 - Endurecer inventario

Estado: implementada.

Objetivo:
Hacer que el inventario inicial sea mas solido antes de conectarlo con compras.

Tareas:

- Revisar todos los formularios del modulo.
- Revisar mensajes de error en espanol.
- Anadir mensajes especificos en `FormRequest` cuando la validacion generica no sea suficiente.
- Mejorar validacion de movimientos:
  - entrada con ubicacion obligatoria,
  - salida con ubicacion obligatoria,
  - ajuste con ubicacion obligatoria,
  - transferencia con origen y destino obligatorios,
  - origen y destino distintos.
- Probar errores visibles en vistas Blade.
- Anadir filtros en productos:
  - busqueda,
  - categoria,
  - proveedor,
  - estado de stock,
  - activo/inactivo.
- Añadir filtros en proveedores.
  - Nombre
  - Codigo/Contacto
  - Estado
- Añadir filtros en ubicaciones.
  - Nombre
  - Codigo/Contacto
  - Estado
- Añadir filtros en Categorías.
  - Nombre
  - Codigo/Contacto
  - Estado
- Añadir filtros en Unidades.
  - Nombre
  - Codigo/Contacto
  - Estado
- Anadir tests funcionales de validacion y filtros.

Implementado:

- Validaciones especificas de movimientos con mensajes en espanol.
- Transferencias con origen y destino obligatorios y distintos.
- Filtros en productos por busqueda, categoria, proveedor, estado de stock y estado activo.
- Filtros en catalogos por nombre, codigo/contacto cuando aplica y estado activo.
- Tests funcionales de validacion de movimientos y filtros.

### FASE 1.2 - Alertas e informes de inventario

Estado: implementada.

Objetivo:
Dar visibilidad operativa al encargado o propietario.

Tareas:

- Alertas de stock bajo.
- Productos sin stock.
- Ultimos movimientos.
- Informe de movimientos por fechas.
- Filtros por producto, proveedor, ubicacion y tipo de movimiento.
- Exportacion CSV UTF-8 de productos.
- Exportacion CSV UTF-8 de movimientos.
- Exportacion CSV UTF-8 de alertas de stock.

Implementado:

- Nueva pantalla `Inventario > Alertas` con productos sin stock y productos con stock bajo.
- Nueva pantalla `Inventario > Movimientos` con ultimos movimientos paginados.
- Filtros de movimientos por fecha, producto, proveedor, ubicacion y tipo.
- Exportacion CSV UTF-8 con BOM para compatibilidad con Excel en Windows:
  - productos,
  - movimientos filtrados,
  - alertas de stock.
- Enlaces de informes en navegacion de inventario y sidebar admin.
- Tests funcionales de alertas, filtros de movimientos y exportaciones CSV.

### FASE 1.3 - Lotes y caducidad

Estado: implementada.

Objetivo:
Controlar caducidad y rotacion solo si el bar lo necesita realmente para cocina, barriles o productos perecederos.

Tareas:

- Tabla `lotes_inventario`.
- Asociar entradas de inventario a lotes.
- Caducidad opcional por lote.
- Consumo FIFO para productos sin caducidad.
- Consumo FEFO para productos con caducidad.
- Alertas de lotes caducados.
- Alertas de lotes proximos a caducar.

Decision:
Se implementa una base funcional porque el bar trabaja con cerveza, cocina y productos perecederos. El formulario manual de movimientos sigue siendo una herramienta interna; la recepcion comoda de pedidos se hara en el modulo `Compras`.

Implementado:

- Nueva tabla `lotes_inventario`.
- Relacion opcional entre movimiento y lote mediante `lote_inventario_id`.
- Entrada manual crea lote automaticamente.
- Productos con `controla_caducidad` exigen fecha de caducidad en entradas.
- Salidas consumen lotes por FEFO cuando hay caducidad.
- Productos sin caducidad pueden usar FIFO sin bloquear el stock antiguo.
- Transferencias mueven lotes entre ubicaciones cuando existen lotes disponibles.
- Pantalla de stock muestra lotes disponibles por producto.
- Pantalla de alertas muestra lotes caducados y proximos a caducar.
- Tests funcionales de validacion, creacion de lotes, consumo FEFO y alertas.

### FASE 2.0 - Compras base

Estado: pendiente.

Objetivo:
Crear el modulo `Compras`, equivalente traducido del modulo `Purchasing` del proyecto de referencia.

Tablas previstas:

- `pedidos_compra`
- `lineas_pedido_compra`
- `eventos_pedido_compra`

Funcionalidad:

- Crear pedidos a proveedor.
- Anadir lineas con productos, cantidades y costes.
- Estados de pedido:
  - `borrador`,
  - `pedido`,
  - `recibido_parcial`,
  - `recibido`,
  - `cerrado`,
  - `cancelado`.
- Editar pedidos mientras esten en borrador.
- Registrar historico operativo de cambios.

### FASE 2.1 - Recepciones de compra e inventario

Estado: pendiente.

Objetivo:
Unir `Compras` con `Inventario` de forma segura.

Tablas previstas:

- `recepciones_compra`
- `lineas_recepcion_compra`

Flujo:

```text
Pedido a proveedor
-> recepcion de mercancia
-> lineas recibidas
-> movimiento de entrada en inventario
-> actualizacion de stock
-> actualizacion del estado del pedido
```

Regla:
`Compras` no actualiza `stock_inventario` directamente. Debe usar `RegistrarMovimientoInventarioAction`.

Nota operativa:
El encargado no deberia recibir un pedido grande entrando producto por producto desde el formulario manual de stock. La pantalla correcta sera `Recepciones`, donde podra repartir una misma linea entre varias ubicaciones, por ejemplo mitad en `Almacen` y mitad en `Camara fria`. El lector de codigo de barras se integrara aqui como un input de busqueda por `codigo_barras`, SKU o nombre.

### FASE 2.2 - Incidencias y cierre de pedidos

Estado: pendiente.

Objetivo:
Cubrir problemas reales al recibir mercancia.

Casos:

- Falta mercancia.
- Llega menos cantidad.
- Llega producto equivocado.
- Producto roto o en mal estado.
- Pedido parcialmente recibido que se decide cerrar.

Tabla prevista:

- `incidencias_recepcion_compra`

### FASE 2.3 - Devoluciones a proveedor

Estado: pendiente.

Objetivo:
Permitir devolver mercancia y reflejarlo en inventario.

Tablas previstas:

- `devoluciones_proveedor`
- `lineas_devolucion_proveedor`

Regla:
Una devolucion confirmada debe crear una salida real en `movimientos_inventario`.

### FASE 2.4 - Propuestas de compra

Estado: pendiente.

Objetivo:
Ayudar a reponer stock desde alertas.

Funcionalidad:

- Detectar productos bajo stock.
- Agrupar necesidades por proveedor.
- Proponer cantidades de compra.
- Generar borradores de pedido.

### FASE 3.0 - Lectura asistida de albaranes

Estado: pendiente.

Objetivo:
Permitir subir foto o PDF de albaran/factura para generar un borrador revisable.

Tablas previstas:

- `documentos_compra`
- `lecturas_documentos`
- `borradores_compra_documento`

Flujo:

```text
Foto/PDF de albaran
-> lectura OCR o IA
-> borrador de compra
-> revision humana
-> compra/recepcion confirmada
-> entrada en inventario
```

Regla:
La lectura de documentos nunca debe actualizar stock sin confirmacion humana.

### FASE 4.0 - Panel visual definitivo

Estado: pendiente.

Objetivo:
Aplicar el diseno visual generado con Vercel/v0 sobre la arquitectura Laravel existente.

Alcance:

- Layout admin definitivo.
- Sidebar.
- Topbar.
- Modo claro/oscuro.
- Login adaptado a la marca Cerveceria Europa.
- Dashboard visual.
- Tablas.
- Formularios.
- Estados vacios.
- Badges de estado.
- Componentes Blade reutilizables.

Reglas:

- El diseno se adapta a Blade y Tailwind.
- No se introduce React, Vue ni TypeScript salvo decision explicita posterior.
- La capa visual no debe cambiar la arquitectura de modulos.
- Primero se integran componentes comunes; despues se aplican a inventario y compras.

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
