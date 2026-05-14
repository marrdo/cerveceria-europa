# Modulo Inventario

## Estado

Fase 2.4 implementada.

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
- Grafica de entradas vs salidas por dia en el dashboard.
- Representacion lineal SVG para entradas vs salidas sin dependencia externa.
- Grafica de movimientos por tipo en el dashboard.
- Grafica de salidas por categoria en el dashboard.
- Grafica de stock por ubicacion en el dashboard.
- Servicio reusable para metricas agregadas del dashboard de inventario.
- Soft deletes en entidades principales.
- Seeders iniciales orientados a un bar.
- Seeder demo de cervezas reales importadas desde `Cartasdesdenumier.txt`.
- Stock demo repartido entre Almacen, Camara fria y Barra, con productos con stock, bajo minimo y sin stock.
- Movimientos demo de entrada, salida, ajuste y transferencia para validar graficas.
- Acciones de productos con iconos reutilizables y `title` accesible.
- Acciones de catalogos de inventario con iconos reutilizables para editar y eliminar.
- Confirmacion previa en borrados de catalogos para evitar eliminaciones accidentales.
- URLs de ficha de stock por SKU cuando existe, manteniendo UUID solo como respaldo tecnico.
- Ficha de stock de producto con visualizacion por ubicacion y actividad reciente.
- Filtro de productos por ubicacion desde el listado y desde el dashboard.
- Control inteligente de stock en dashboard.
- Reposicion urgente por stock actual, umbral minimo y consumo medio diario.
- Estimacion de dias restantes de stock cuando hay salidas recientes.
- Deteccion de stock parado con unidades disponibles y sin movimiento reciente.
- Busqueda operativa de productos preparada para lector de codigo, SKU, codigo de barras, referencia de proveedor o nombre.
- Priorizacion de resultados por coincidencia exacta de SKU, codigo de barras o referencia de proveedor.
- Acciones rapidas de entrada, salida, ajuste y transferencia desde la ficha de stock.
- Formulario de movimientos adaptado a PDA/movil con selector visual de tipo y control de cantidad con botones `-` y `+`.
- Cantidades rapidas configuradas segun si la unidad permite decimales.
- Persistencia de valores del formulario cuando hay errores de validacion.

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

Estado: implementada.

Graficas recomendadas:

- Entradas vs salidas por dia en los ultimos 14 o 30 dias.
- Movimientos por tipo: entrada, salida, ajuste y transferencia.
- Salidas por categoria de producto.
- Top productos con mas salidas.
- Stock por ubicacion.

La grafica filtrable de productos concretos por ubicacion se ha retirado porque aportaba poco al flujo real del encargado y anadia complejidad visual. El dashboard mantiene la lectura agregada por ubicacion y los productos sin stock se revisan desde las alertas/KPIs.

Lectura esperada:

- Si suben las salidas y bajan las entradas, puede faltar reposicion.
- Si hay demasiados ajustes, puede haber errores operativos o mala medicion.
- Si una categoria concentra muchas salidas, conviene reforzar compras y control.
- Si una ubicacion acumula stock sin rotacion, puede haber mala distribucion.

### Fase 2.3 - Control de stock inteligente

Convertir el dashboard en una herramienta de decision, no solo en un panel de datos.

Estado: implementada.

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

Implementacion actual:

- `DashboardInventarioMetricas::reposicionUrgente()` calcula stock actual, salidas de los ultimos 30 dias, consumo medio diario y dias estimados restantes.
- La reposicion urgente entra si el producto no tiene stock, esta por debajo de `cantidad_alerta_stock` o se agota en 7 dias o menos segun consumo reciente.
- `DashboardInventarioMetricas::stockSinMovimientoReciente()` detecta productos activos con stock disponible y sin movimientos en 30 dias o sin movimientos registrados.
- El dashboard muestra ambos bloques con acceso directo a la ficha de stock del producto.

### Fase 2.4 - Agilidad para encargado y PDA

Optimizar el uso operativo del modulo en movil, tablet o PDA.

Estado: implementada.

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

Mejoras ya aplicadas durante la fase de correccion:

- En la tabla de productos, las acciones principales usan iconos reutilizables con `title` y `aria-label`.
- La pantalla de stock usa SKU en la URL siempre que exista para evitar exponer UUID en el flujo normal.
- El campo cantidad usa incrementos enteros cuando la unidad no permite decimales.
- El dashboard enlaza cada ubicacion de stock con el listado de productos filtrado por esa ubicacion.

Implementacion actual:

- El listado de productos tiene un buscador preparado para escanear codigo de barras o buscar por SKU, referencia de proveedor y nombre.
- Cuando se busca, el listado ordena primero coincidencias exactas de SKU, codigo de barras y referencia de proveedor antes que coincidencias parciales por nombre.
- La ficha de stock incorpora accesos rapidos para abrir el formulario con el tipo de movimiento ya seleccionado.
- El formulario de movimiento usa botones grandes para elegir entrada, salida, ajuste o transferencia.
- Los campos de transferencia se muestran solo cuando el tipo seleccionado es `transferencia`; las ubicaciones normales se usan para entradas, salidas y ajustes.
- La cantidad se puede ajustar con botones circulares `-` y `+`, manteniendo incrementos enteros para unidades no decimales.
- Las cantidades rapidas facilitan recepciones habituales: `1`, `6`, `12` y `24` para unidades enteras; `0,25`, `0,5`, `1` y `2` para unidades decimales.
- El campo motivo incluye sugerencias frecuentes para reducir escritura en PDA.

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

1. Empezar Fase 2.5 cruzando productos de carta con productos de inventario para detectar ventas sin descuento de stock.
2. Crear propuestas automaticas de compra desde productos bajo minimo o con dias restantes criticos.
3. Revisar si conviene sustituir las graficas HTML/CSS por una libreria ligera de graficas.
4. Anadir filtros temporales configurables en el dashboard: 7, 14, 30 y 90 dias.
