# Modulo Compras

## Estado

Fase 2.4 implementada.

El modulo `Compras` gestiona pedidos a proveedor, recepciones de mercancia, incidencias operativas, devoluciones a proveedor y propuestas de compra desde alertas de stock. Las recepciones crean entradas reales y las devoluciones crean salidas reales usando `RegistrarMovimientoInventarioAction`; el modulo no modifica stock directamente.

## Implementado

- Pedidos de compra.
- Lineas de pedido.
- Eventos de pedido.
- Estados de pedido:
  - `borrador`,
  - `pedido`,
  - `recibido_parcial`,
  - `recibido`,
  - `cerrado`,
  - `cancelado`.
- Creacion de pedidos en borrador.
- Numeracion anual de pedidos con formato `PC-00001-2026`.
- Edicion solo mientras el pedido esta en borrador.
- Calculo de subtotal, impuestos y total.
- Cambio manual de estado con evento historico.
- Listado con filtros por numero, proveedor y estado.
- Pantalla de detalle con lineas, resumen y eventos.
- Recepciones de compra.
- Lineas de recepcion.
- Reparto de una misma linea entre varias ubicaciones.
- Registro de lote/caducidad al recibir.
- Actualizacion de stock mediante movimientos de entrada.
- Estado automatico `recibido_parcial` o `recibido`.
- Incidencias de recepcion.
- Cierre de pedidos parcialmente recibidos con motivo.
- Devoluciones a proveedor.
- Salidas reales de inventario asociadas a devoluciones.
- Propuestas de compra desde productos bajo stock o sin stock.
- Generacion de pedidos borrador desde propuestas agrupadas por proveedor.

## Tablas

- `pedidos_compra`
- `lineas_pedido_compra`
- `eventos_pedido_compra`
- `recepciones_compra`
- `lineas_recepcion_compra`
- `incidencias_recepcion_compra`
- `devoluciones_proveedor`
- `lineas_devolucion_proveedor`

## Reglas de arquitectura

- `Compras` no modifica `stock_inventario` directamente.
- Toda entrada de stock desde compras pasa por `RegistrarMovimientoInventarioAction`.
- El acceso al modulo se controla con middleware `modulo:compras`.
- `encargado`, `propietario` y `superadmin` pueden acceder a compras.
- Los pedidos solo se pueden editar en estado `borrador`.
- El numero del pedido se genera por anio usando el ultimo `PC-xxxxx-anio` existente.
- Los pedidos se pueden recibir mientras no esten en `borrador`, `cerrado` o `cancelado` y tengan cantidades pendientes.
- La recepcion no puede superar la cantidad pendiente de cada linea.
- Los estados `recibido_parcial` y `recibido` se calculan desde recepciones reales; no se seleccionan manualmente.
- Las incidencias documentan diferencias con proveedor, pero no cambian stock por si solas.
- Un pedido `recibido_parcial` se puede cerrar con mercancia pendiente si se registra motivo.
- Cerrar con pendiente no modifica inventario; solo cambia el estado del pedido a `cerrado` y registra evento.
- Las devoluciones a proveedor si cambian inventario: cada devolucion confirmada crea una salida real.
- No se puede devolver mas cantidad de la recibida pendiente de devolver.
- La salida de devolucion respeta stock disponible y reglas de lotes/caducidad.
- Las propuestas no crean movimientos de inventario; solo generan pedidos borrador.
- Una propuesta solo puede generar pedido si el producto tiene proveedor principal.
- La cantidad sugerida es una ayuda editable, no una obligacion operativa.
- Todo cambio importante debe dejar evento en `eventos_pedido_compra`.
- Los importes se recalculan desde las lineas, no se introducen manualmente.
- Los nombres de tablas, modelos, controladores y vistas son espanoles.

## Flujo actual

```text
Crear pedido en borrador
-> Anadir lineas
-> Revisar totales
-> Marcar como pedido
-> Registrar recepcion
-> Repartir cantidad por ubicacion
-> Registrar lote/caducidad si aplica
-> Entrada real en inventario
-> Registrar incidencias si hay diferencias
-> Cerrar con pendiente si se decide no esperar el resto
-> Registrar devolucion si mercancia recibida vuelve al proveedor
-> Revisar propuestas para reponer stock bajo
-> Generar pedidos borrador desde propuestas
-> Registrar eventos de cambios
```

## Recepcion de mercancia

```text
Pedido a proveedor
-> Recepcion de mercancia
-> Reparto por ubicacion
-> Lote/caducidad si aplica
-> RegistrarMovimientoInventarioAction
-> Stock actualizado
```

El lector de codigo de barras se integrara en la pantalla de recepcion como entrada de busqueda por `codigo_barras`, SKU o nombre.

## Incidencias y cierre

Las incidencias cubren problemas reales de recepcion:

- Falta mercancia.
- Llega menos cantidad.
- Llega producto equivocado.
- Producto roto.
- Producto en mal estado.
- Otro motivo.

Una incidencia puede vincularse a una recepcion concreta, a una linea del pedido o quedar como incidencia general del pedido. El campo `cantidad_afectada` es opcional porque no todas las incidencias son medibles.

El cierre con pendiente esta pensado para pedidos recibidos parcialmente cuando el bar decide no esperar lo que falta. No genera entradas ni salidas de stock; solo deja el pedido cerrado y registra el motivo en eventos.

## Devoluciones a proveedor

Una devolucion representa mercancia ya recibida que sale del bar y vuelve al proveedor. Por eso no es solo una nota administrativa: debe crear una salida real de inventario.

Flujo:

```text
Pedido con mercancia recibida
-> Registrar devolucion
-> Elegir linea, ubicacion y cantidad
-> RegistrarMovimientoInventarioAction como salida
-> Stock descontado
-> Evento en pedido
```

La devolucion queda trazada contra:

- pedido de compra,
- proveedor,
- linea de pedido,
- producto,
- ubicacion de inventario,
- movimiento de inventario.

## Propuestas de compra

Las propuestas ayudan al encargado a detectar reposicion sin entrar producto por producto.

Flujo:

```text
Productos activos con control de stock
-> Estado sin stock o stock bajo
-> Agrupacion por proveedor principal
-> Cantidad sugerida editable
-> Pedido borrador
```

Criterio inicial:

- Si el producto tiene `cantidad_alerta_stock`, se propone comprar hasta llegar al doble de esa alerta.
- Si el producto esta sin stock y no tiene alerta, se propone 1 unidad.
- Los productos sin proveedor principal se muestran aparte para corregir su ficha.

## Siguiente fase

Fase 3.0:

- Lectura asistida de albaranes.
- Subida de foto o PDF.
- Borrador revisable antes de tocar compras o inventario.
