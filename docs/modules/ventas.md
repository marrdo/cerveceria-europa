# Modulo Ventas

## Objetivo

Gestionar comandas de sala/barra desde la carta publicada y conectar el consumo real con el inventario.

El modulo esta pensado para camareros, encargados, propietarios y superadmin.

## Flujo recomendado

```text
Camarero crea comanda
-> selecciona productos de carta
-> la comanda queda abierta
-> al servir una linea se descuenta stock
-> cuando todas las lineas estan servidas, la comanda pasa a servida
```

## Decision importante de stock

El stock no se descuenta al crear la comanda.

Se descuenta al servir la linea porque una comanda puede corregirse, cancelarse o duplicarse antes de llegar al cliente. Esta es la forma mas segura para mantener inventario coherente.

## Decision importante de ubicaciones

La `ubicacion_inventario_id` de una comanda no representa donde esta sentado el cliente.

Su unica responsabilidad es indicar de que ubicacion de stock se descuenta el producto cuando se sirve una linea. Ejemplos validos:

```text
Barra
Almacen principal
Camara fria
```

La zona o mesa del cliente debe modelarse aparte para no mezclar inventario con sala. Ejemplos de zonas reales:

```text
Terraza 1
Terraza 2
Salon alto
Salon bajo
Barra
Salon de celebraciones
```

Esto se desarrollara como gestion de espacios/mesas en una fase posterior.

## Tablas

```text
comandas
lineas_comanda
pagos_comanda
```

## Modelos

```text
App\Modulos\Ventas\Models\Comanda
App\Modulos\Ventas\Models\LineaComanda
App\Modulos\Ventas\Models\PagoComanda
```

## Acciones

```text
App\Modulos\Ventas\Actions\CrearComandaAction
App\Modulos\Ventas\Actions\ServirLineaComandaAction
App\Modulos\Ventas\Actions\RegistrarPagoComandaAction
App\Modulos\Ventas\Actions\ActualizarComandaOperativaAction
App\Modulos\Ventas\Actions\AgregarLineasComandaAction
```

`ServirLineaComandaAction` reutiliza:

```text
App\Modulos\Inventario\Actions\RegistrarMovimientoInventarioAction
```

Esto evita duplicar logica de stock y mantiene trazabilidad en `movimientos_inventario`.

## Relacion con carta e inventario

Una linea de comanda puede venir de:

```text
contenidos_web
tarifas_contenido_web
productos
```

La carta aporta el nombre y precio visible. El producto de inventario aporta el stock.

Si el contenido de carta no esta vinculado a un producto de inventario, se puede vender igualmente, pero no descontara stock.

## Estados

Comanda:

```text
abierta
en_preparacion
servida
pagada
cancelada
```

Linea:

```text
pendiente
en_preparacion
servida
cancelada
```

## Pendiente profesional para fases futuras

Para platos de cocina no conviene descontar siempre un unico producto.

Lo profesional es anadir recetas o escandallos:

```text
plato de carta
-> receta
-> ingredientes de inventario
-> cantidades por racion
```

Ejemplo:

```text
Papas aliniadas
-> patata 0.250 kg
-> atun 0.080 kg
-> aceite 0.010 l
```

Para botellas o productos unitarios, la vinculacion directa `contenido_web -> producto` es suficiente.

## Faseado de desarrollo

### Fase 1 - Comandas base

Estado: implementada.

- Crear comanda desde carta.
- Crear lineas de comanda.
- Estados basicos de comanda y linea.
- Servir linea.
- Descontar stock al servir.
- Permisos para camarero, encargado, propietario y superadmin.

### Fase 2 - Cobros

Estado: implementada.

Objetivo: diferenciar `servido` de `pagado`.

- Estado `pagada`.
- Tabla `pagos_comanda`.
- Metodo de pago: efectivo, tarjeta, bizum, invitacion u otro.
- Importe cobrado.
- Importe recibido en efectivo.
- Cambio calculado.
- Referencia opcional para tarjeta, bizum u otro metodo.
- Usuario que cobra.
- Fecha de cobro.
- Pantalla de cobrar comanda desde la ficha.

Reglas:

- Solo se cobra una comanda `servida`.
- Una comanda puede tener varios pagos parciales.
- El importe cobrado no puede superar el pendiente.
- En efectivo, el recibido no puede ser menor que el importe.
- Cuando el total pagado cubre el total de la comanda, pasa a `pagada`.

### Fase 3 - Edicion operativa

Estado: implementada.

Objetivo: hacer la toma de comandas comoda para uso real.

- Anadir lineas a una comanda abierta.
- Modificar cantidades antes de servir.
- Cancelar lineas no servidas.
- Mover mesa.
- Notas por linea.
- Preparar estado `en_preparacion` para cocina/barra.

Reglas implementadas:

- Las lineas servidas no se editan directamente.
- Las lineas no servidas pueden cambiar cantidad y notas.
- Una linea no servida con cantidad cero se cancela.
- Una linea no servida puede cancelarse manualmente.
- Las lineas canceladas no suman en el total.
- Una comanda servida puede recibir nuevos productos antes de pagarse.
- Si una comanda servida recibe nuevos productos, vuelve a `en_preparacion`.
- Una comanda pagada o cancelada no acepta edicion operativa.

Regla: una linea servida no se edita directamente; se corrige con anulacion o ajuste trazable.

Pendiente relacionado: el cobro final por mesa o cuenta completa se cerrara con el modulo de espacios/mesas, porque necesita `zona`, `mesa` y `cuenta` para agrupar consumos.

### Fase 4 - Espacios y mesas

Objetivo: separar sala de inventario y permitir una operativa real de bar/restaurante.

- Zonas configurables.
- Mesas configurables por zona.
- Activar o desactivar zonas segun operativa diaria.
- Activar o desactivar mesas.
- Asignar comanda a zona y mesa.
- Mover comanda entre mesas.
- Consultar mesas abiertas, servidas y pendientes de cobro.
- Agrupar comandas de una misma mesa en una cuenta final.

Regla: una zona inactiva no debe aparecer para nuevas comandas, pero debe conservar historial.

### Fase 5 - Caja y turnos

Objetivo: control diario para encargados y propietario.

- Apertura de caja.
- Cierre de caja.
- Saldo inicial.
- Efectivo esperado.
- Efectivo contado.
- Descuadre.
- Ventas por metodo de pago.
- Ventas por camarero.

### Fase 6 - Informes de ventas

Objetivo: explotar informacion de negocio.

- Ventas por dia.
- Productos mas vendidos.
- Ventas por categoria.
- Ticket medio.
- Comandas canceladas.
- Ventas por camarero.
- Margen estimado si hay coste fiable.

### Fase 7 - Escandallos / recetas

Objetivo: descontar ingredientes reales en platos de cocina.

- Receta por plato.
- Ingredientes vinculados a productos de inventario.
- Cantidad por racion.
- Descuento automatico al servir.

### Fase 8 - Tickets e integraciones

Objetivo: acercarse a TPV formal si el negocio lo necesita.

- Ticket imprimible.
- Numeracion de tickets.
- Impresion en cocina/barra.
- Exportacion diaria.
- Integracion con impresora termica.
- Integracion fiscal si procede.
