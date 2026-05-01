# Modulo Compras

## Estado

Fase 2.0 implementada.

El modulo `Compras` gestiona pedidos a proveedor. En esta fase no actualiza inventario: prepara la estructura para que la fase 2.1 registre recepciones reales y use `RegistrarMovimientoInventarioAction`.

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

## Tablas

- `pedidos_compra`
- `lineas_pedido_compra`
- `eventos_pedido_compra`

## Reglas de arquitectura

- `Compras` no modifica `stock_inventario` en fase 2.0.
- `Compras` no crea `movimientos_inventario` hasta la fase de recepciones.
- El acceso al modulo se controla con middleware `modulo:compras`.
- `encargado`, `propietario` y `superadmin` pueden acceder a compras.
- Los pedidos solo se pueden editar en estado `borrador`.
- Todo cambio importante debe dejar evento en `eventos_pedido_compra`.
- Los importes se recalculan desde las lineas, no se introducen manualmente.
- Los nombres de tablas, modelos, controladores y vistas son espanoles.

## Flujo actual

```text
Crear pedido en borrador
-> Anadir lineas
-> Revisar totales
-> Marcar como pedido
-> Registrar eventos de cambios
```

## Siguiente fase

Fase 2.1:

```text
Pedido a proveedor
-> Recepcion de mercancia
-> Reparto por ubicacion
-> Lote/caducidad si aplica
-> RegistrarMovimientoInventarioAction
-> Stock actualizado
```

El lector de codigo de barras se integrara en la pantalla de recepcion como entrada de busqueda por `codigo_barras`, SKU o nombre.
