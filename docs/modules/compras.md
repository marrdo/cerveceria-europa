# Modulo Compras

## Estado

Fase 2.1 implementada.

El modulo `Compras` gestiona pedidos a proveedor y recepciones de mercancia. Las recepciones crean entradas reales usando `RegistrarMovimientoInventarioAction`; el modulo no modifica stock directamente.

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

## Tablas

- `pedidos_compra`
- `lineas_pedido_compra`
- `eventos_pedido_compra`
- `recepciones_compra`
- `lineas_recepcion_compra`

## Reglas de arquitectura

- `Compras` no modifica `stock_inventario` directamente.
- Toda entrada de stock desde compras pasa por `RegistrarMovimientoInventarioAction`.
- El acceso al modulo se controla con middleware `modulo:compras`.
- `encargado`, `propietario` y `superadmin` pueden acceder a compras.
- Los pedidos solo se pueden editar en estado `borrador`.
- Los pedidos se pueden recibir mientras no esten en `borrador`, `cerrado` o `cancelado` y tengan cantidades pendientes.
- La recepcion no puede superar la cantidad pendiente de cada linea.
- Los estados `recibido_parcial` y `recibido` se calculan desde recepciones reales; no se seleccionan manualmente.
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

## Siguiente fase

Fase 2.2:

- Incidencias de recepcion.
- Cierre de pedidos parcialmente recibidos.
- Motivos de diferencia entre pedido y recepcion.
