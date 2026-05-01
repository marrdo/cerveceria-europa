# Modulo Inventario

## Estado

Fase 1 implementada.

Esta fase replica la base operativa del modulo `Inventory` del proyecto de bicicletas, pero traducida al dominio de Cerveceria Europa y con nombres de codigo en espanol sin `n` con tilde.

## Implementado

- Proveedores.
- Categorias de producto.
- Unidades de inventario.
- Ubicaciones de inventario.
- Productos.
- Stock por producto y ubicacion.
- Movimientos manuales de entrada, salida, ajuste y transferencia.
- Proteccion contra salidas por encima del stock disponible.
- Soft deletes en entidades principales.
- Seeders iniciales orientados a un bar.

## Tablas

- `proveedores`
- `categorias_productos`
- `unidades_inventario`
- `ubicaciones_inventario`
- `productos`
- `stock_inventario`
- `movimientos_inventario`

## Diferencias frente al proyecto de bicicletas

No se han copiado aun las piezas de lotes avanzados, FIFO/FEFO, informes CSV ni alertas de caducidad. La decision es intencionada: para este proyecto conviene cerrar primero el inventario manual estable y despues sumar caducidad/lotes con tests propios.

Tampoco se ha implementado todavia la integracion con compras a proveedor. Esa parte se hara en el modulo `Compras`, equivalente traducido de `Purchasing`.

## Reglas de arquitectura

- `Inventario` es dueno del stock real.
- Ningun modulo externo debe actualizar `stock_inventario` directamente.
- Las entradas, salidas, ajustes y transferencias pasan por `RegistrarMovimientoInventarioAction`.
- Los productos pueden existir sin stock si `controla_stock` esta desactivado.
- Los nombres de tablas, modelos, controladores y vistas son espanoles.

## Siguiente fase recomendada

1. Tests funcionales del CRUD de productos y movimientos.
2. Modulo `Compras` con proveedores, pedidos y recepciones.
3. Integracion de recepciones de compra con entradas reales en inventario.
4. Lotes/caducidades si el bar necesita controlar productos perecederos.
