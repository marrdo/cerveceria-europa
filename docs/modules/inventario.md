# Modulo Inventario

## Estado

Fase 2.1 implementada.

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
- Dashboard operativo de inventario.
- KPIs de productos, stock, movimientos y valor estimado.
- Accesos rapidos a movimientos, alertas, propuestas de compra y ubicaciones.
- Resumen critico de productos sin stock, stock bajo y caducidades.
- Top de productos con mas salidas en los ultimos 30 dias.
- Ultimos movimientos visibles desde la entrada del modulo.
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
- El acceso al modulo se controla con middleware `modulo:inventario`.
- `encargado`, `propietario` y `superadmin` pueden acceder a inventario.
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

## Mejoras pendientes del dashboard de inventario

El modulo ya tiene la base de stock, movimientos, lotes, alertas y conexion con compras/ventas. La siguiente mejora debe centrarse en que el encargado entienda el estado del inventario en pocos segundos y pueda actuar sin entrar primero en listados largos.

### Objetivo operativo

- Ver rapido que falta, que se mueve y que urge.
- Detectar productos sin stock, bajo minimo o con lotes proximos a caducar.
- Consultar entradas y salidas sin depender solo de tablas.
- Lanzar acciones rapidas: entrada, salida, ajuste, transferencia o propuesta de compra.
- Usar `movimientos_inventario` como fuente principal de graficas y metricas.

### Fase 2.1 - Dashboard visual basico

Crear una pantalla principal en `/admin/inventario` con KPIs y accesos directos.

Estado: implementada.

KPIs recomendados:

- Productos activos.
- Productos que controlan stock.
- Productos sin stock.
- Productos bajo minimo.
- Movimientos registrados hoy.
- Entradas de los ultimos 7 dias.
- Salidas de los ultimos 7 dias.
- Valor estimado del stock si el producto tiene `precio_coste`.

Bloques operativos:

- Alertas criticas: sin stock, bajo minimo, lotes caducados y lotes proximos a caducar.
- Ultimos movimientos.
- Top productos con mas salidas recientes.
- Acciones rapidas:
  - Registrar entrada.
  - Registrar salida.
  - Ajustar stock.
  - Transferir entre ubicaciones.
  - Ver alertas.
  - Crear propuesta de compra.

### Fase 2.2 - Graficas de entradas y salidas

Anadir graficas al dashboard usando datos agregados de `movimientos_inventario`.

Graficas recomendadas:

- Entradas vs salidas por dia en los ultimos 14 o 30 dias.
- Movimientos por tipo: entrada, salida, ajuste y transferencia.
- Salidas por categoria de producto.
- Top productos con mas salidas.
- Stock por ubicacion.

Lectura esperada:

- Si suben las salidas y bajan las entradas, puede faltar reposicion.
- Si hay demasiados ajustes, puede haber errores operativos o mala medicion.
- Si una categoria concentra muchas salidas, conviene reforzar compras y control.
- Si una ubicacion acumula stock sin rotacion, puede haber mala distribucion.

### Fase 2.3 - Control de stock inteligente

Convertir el dashboard en una herramienta de decision, no solo en un panel de datos.

Metricas recomendadas:

- Consumo medio diario por producto.
- Stock actual.
- Salidas acumuladas en los ultimos 30 dias.
- Dias estimados restantes.
- Productos con reposicion urgente.
- Productos sin movimiento reciente.

Ejemplo de lectura:

```text
Producto: Leffe Rubia
Stock actual: 24 unidades
Salidas ultimos 30 dias: 60 unidades
Media diaria: 2 unidades/dia
Stock estimado restante: 12 dias
```

Regla recomendada:

- Si `dias_estimados_restantes` es menor que un umbral configurable, marcar el producto como reposicion urgente.
- Si no hay salidas recientes y hay mucho stock, marcar como posible exceso.
- Si el producto tiene lotes proximos a caducar, priorizar su salida en informes y alertas.

### Fase 2.4 - Agilidad para encargado y PDA

Optimizar el uso operativo del modulo en movil, tablet o PDA.

Mejoras recomendadas:

- Buscador rapido por nombre, SKU, codigo de barras o proveedor.
- Acciones grandes y claras para entrada, salida, ajuste y transferencia.
- Formularios con pocos campos y valores por defecto razonables.
- Lector de codigo de barras tratado como entrada de teclado.
- Filtros persistentes para ubicacion y categoria.
- Enlaces directos desde productos criticos a:
  - ver stock,
  - registrar movimiento,
  - crear propuesta de compra,
  - revisar movimientos.

### Fase 2.5 - Integracion avanzada con compras y ventas

Esta fase conecta el dashboard con la operativa completa del negocio.

Mejoras recomendadas:

- Propuestas automaticas de compra desde productos bajo minimo.
- Comparar ventas servidas con salidas reales de inventario.
- Detectar productos de carta sin producto de inventario vinculado.
- Detectar productos inventariables vendidos sin descuento de stock.
- Mostrar coste estimado de consumo por periodo.
- Mostrar margen orientativo si existe precio de venta y precio de coste.

## Siguiente fase recomendada

1. Anadir primera grafica de entradas vs salidas por dia usando `movimientos_inventario`.
2. Anadir grafica de movimientos por tipo: entrada, salida, ajuste y transferencia.
3. Anadir salidas por categoria y stock por ubicacion.
4. Extraer las consultas agregadas a una clase reusable para futuros informes.
