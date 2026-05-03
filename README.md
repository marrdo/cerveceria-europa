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
- La navegacion lateral se organiza por modulos, no por tablas sueltas.
- El acceso a modulos se controla por rol mediante middleware `modulo`.

## Permisos iniciales por modulo

- `camarero`: futuro modulo `Ventas`.
- `encargado`: `Inventario` y `Compras`.
- `propietario`: acceso completo.
- `superadmin`: acceso completo tecnico.

## Modulos previstos

- `Inventario`: productos, categorias, unidades, ubicaciones, stock y movimientos.
- `Compras`: futuro modulo de proveedores, pedidos, recepciones y entradas reales en inventario.
- `LecturasDocumentos`: futuro modulo para lectura asistida de albaranes/facturas mediante OCR o IA.

Documentacion por modulos:

```text
docs/modules/inventario.md
docs/modules/compras.md
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

Estado: implementada.

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

Implementado:

- Modulo `Compras` con estructura propia.
- Nueva pantalla `Compras > Pedidos`.
- Crear pedidos de compra en estado `borrador`.
- Numeracion anual de pedidos con formato `PC-00001-2026`.
- Anadir lineas con producto, descripcion, cantidad, coste unitario e IVA.
- Calculo automatico de subtotal, impuestos y total.
- Editar pedidos solo mientras estan en `borrador`.
- Cambiar estado manual del pedido desde el detalle para marcarlo como `pedido`, `cerrado` o `cancelado`.
- Registrar eventos historicos de creacion, actualizacion y cambio de estado.
- Filtros por numero, proveedor y estado.
- Tests funcionales del flujo base de pedidos.

### FASE 2.1 - Recepciones de compra e inventario

Estado: implementada.

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

Implementado:

- Nueva tabla `recepciones_compra`.
- Nueva tabla `lineas_recepcion_compra`.
- Pantalla para registrar recepcion desde el detalle del pedido.
- Reparto de una misma linea entre varias ubicaciones.
- Registro de lote y caducidad por linea recibida.
- Productos con caducidad exigen fecha de caducidad al recibir.
- Cada linea recibida genera un movimiento real de entrada en inventario mediante `RegistrarMovimientoInventarioAction`.
- Actualizacion automatica del estado del pedido:
  - `recibido_parcial`,
  - `recibido`.
- Los estados `recibido_parcial` y `recibido` no se pueden seleccionar manualmente: solo nacen de recepciones reales.
- Historico de eventos de recepcion.
- Tests funcionales de recepcion completa, parcial, reparto por ubicacion y caducidad.

### FASE 2.2 - Incidencias y cierre de pedidos

Estado: implementada.

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

Implementado:

- Nueva tabla `incidencias_recepcion_compra`.
- Registro de incidencias desde el detalle del pedido.
- Tipos de incidencia:
  - falta de mercancia,
  - llega menos cantidad,
  - producto equivocado,
  - producto roto,
  - producto en mal estado,
  - otro motivo.
- Vinculacion opcional de incidencia con recepcion y linea de pedido.
- Cantidad afectada opcional para documentar diferencias.
- Evento historico al registrar incidencia.
- Cierre de pedidos `recibido_parcial` con mercancia pendiente y motivo obligatorio.
- El cierre con pendiente no modifica inventario; solo cierra el pedido y deja trazabilidad.

### FASE 2.3 - Devoluciones a proveedor

Estado: implementada.

Objetivo:
Permitir devolver mercancia y reflejarlo en inventario.

Tablas previstas:

- `devoluciones_proveedor`
- `lineas_devolucion_proveedor`

Regla:
Una devolucion confirmada debe crear una salida real en `movimientos_inventario`.

Implementado:

- Nueva tabla `devoluciones_proveedor`.
- Nueva tabla `lineas_devolucion_proveedor`.
- Registro de devoluciones desde el detalle del pedido.
- Cada devolucion crea una salida real en inventario mediante `RegistrarMovimientoInventarioAction`.
- La devolucion queda vinculada a pedido, proveedor, linea de pedido, ubicacion y movimiento de inventario.
- No se puede devolver mas cantidad de la recibida pendiente de devolver.
- La salida respeta las mismas reglas de stock y lotes que una salida manual.
- Historico de evento `devolucion_proveedor` en el pedido.

### FASE 2.4 - Propuestas de compra

Estado: implementada.

Objetivo:
Ayudar a reponer stock desde alertas.

Funcionalidad:

- Detectar productos bajo stock.
- Agrupar necesidades por proveedor.
- Proponer cantidades de compra.
- Generar borradores de pedido.

Implementado:

- Pantalla `Compras > Propuestas`.
- Deteccion de productos activos con control de stock en estado `sin_stock` o `bajo`.
- Agrupacion de productos por proveedor principal.
- Calculo inicial de cantidad sugerida:
  - si hay alerta configurada, propone reponer hasta el doble de la alerta,
  - si no hay alerta, propone minimo 1 unidad.
- Edicion manual de cantidad propuesta antes de crear pedido.
- Generacion de pedido en estado `borrador` desde una propuesta.
- Evento historico `propuesta_compra`.
- Aviso de productos sin proveedor principal para corregir su ficha antes de generar pedidos.

### FASE 3.0 - Lectura asistida de albaranes

Estado: implementada como base de captura y trazabilidad.

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

Implementado:

- Nueva pantalla `Compras > Documentos`.
- Subida de fotos JPG/PNG o PDF de albaranes/facturas.
- Almacenamiento privado del archivo original.
- Nueva tabla `documentos_compra`.
- Nueva tabla `lecturas_documentos`.
- Nueva tabla `borradores_compra_documento`.
- Creacion automatica de lectura pendiente y borrador pendiente de revision al subir documento.
- Trazabilidad de proveedor opcional, tipo de documento, usuario que sube, archivo original y notas.
- Pantalla de detalle con archivo, lecturas y borrador asociado.
- Eliminacion de documentos equivocados mientras no hayan generado pedido.

Pendiente para fases posteriores:

- Integracion con IA multimodal para fotos/PDF complejos.
- Extraccion automatica de lineas.
- Conversion del borrador revisado en recepcion.

### FASE 3.1 - Revision de borradores de documento

Estado: implementada.

Objetivo:
Permitir que una persona revise manualmente el borrador asociado a un documento y lo convierta en pedido de compra en estado `borrador`.

Implementado:

- Pantalla para revisar `borradores_compra_documento`.
- Edicion manual de proveedor, fecha, numero de documento y lineas.
- Guardado de revision sin crear pedido ni tocar inventario.
- Conversion del borrador revisado en `PedidoCompra` en estado `borrador`.
- Evento historico `documento_compra` en el pedido generado.
- El documento pasa a `en_revision` al guardar revision y a `procesado` al generar pedido.

Regla:
La conversion desde documento solo crea un pedido borrador. No crea recepciones ni movimientos de inventario.

Nota operativa:
En esta fase no se conecta OCR ni IA. Las lineas se introducen manualmente seleccionando productos existentes. Cuando conectemos IA, el sistema propondra lineas intentando emparejar por codigo de barras, SKU, referencia de proveedor o nombre aproximado, siempre con revision humana.

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
