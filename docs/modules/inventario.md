# Modulo Inventario

## Estado

Fase 1.3 implementada.

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
- Alertas de stock bajo y productos sin stock.
- Informe paginado de movimientos.
- Filtros de movimientos por fecha, producto, proveedor, ubicacion y tipo.
- Exportaciones CSV UTF-8 de productos, movimientos y alertas.
- Lotes de inventario.
- Caducidad opcional por lote.
- Validacion de caducidad obligatoria para entradas de productos perecederos.
- Consumo FEFO para productos con caducidad.
- Alertas de lotes caducados y proximos a caducar.
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
- `lotes_inventario`

## Diferencias frente al proyecto de bicicletas

La gestion de lotes se ha adaptado al uso real del bar: entradas manuales, caducidad opcional, FEFO para perecederos y alertas operativas. La recepcion completa de pedidos a proveedor no se implementa aqui; se hara en el modulo `Compras`.

Tampoco se ha implementado todavia la integracion con compras a proveedor. Esa parte se hara en el modulo `Compras`, equivalente traducido de `Purchasing`.

## Reglas de arquitectura

- `Inventario` es dueno del stock real.
- Ningun modulo externo debe actualizar `stock_inventario` directamente.
- Las entradas, salidas, ajustes y transferencias pasan por `RegistrarMovimientoInventarioAction`.
- Los informes leen movimientos y stock ya registrado; no modifican inventario.
- Las exportaciones CSV se generan en UTF-8 con BOM para evitar problemas de acentos en Excel.
- Los productos con `controla_caducidad` requieren `caduca_el` en entradas.
- Las salidas de productos con caducidad consumen lotes por FEFO.
- El formulario manual de movimientos es una herramienta interna, no el flujo principal de recepcion de pedidos.
- Los productos pueden existir sin stock si `controla_stock` esta desactivado.
- Los nombres de tablas, modelos, controladores y vistas son espanoles.

## Recepcion de pedidos

El flujo real para recibir mercancia debe vivir en `Compras`:

```text
Pedido proveedor
-> Recepcion de mercancia
-> Lineas recibidas
-> Reparto por ubicacion
-> Registro de lote/caducidad si aplica
-> RegistrarMovimientoInventarioAction
-> Stock y lotes actualizados
```

Ejemplo:

```text
Cerveza IPA x 24
-> 12 unidades a Almacen
-> 12 unidades a Camara fria
```

Cada reparto debe crear su movimiento de entrada y su lote correspondiente. El lector de codigo de barras se tratara como entrada de teclado sobre un buscador por `codigo_barras`, SKU o nombre.

## Siguiente fase recomendada

1. Modulo `Compras` con proveedores, pedidos y recepciones.
2. Integracion de recepciones de compra con entradas reales en inventario y lotes.
3. Busqueda por codigo de barras en recepciones.
4. Propuestas de compra desde productos con stock bajo.
