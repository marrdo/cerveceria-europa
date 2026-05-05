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

## Tablas

```text
comandas
lineas_comanda
```

## Modelos

```text
App\Modulos\Ventas\Models\Comanda
App\Modulos\Ventas\Models\LineaComanda
```

## Acciones

```text
App\Modulos\Ventas\Actions\CrearComandaAction
App\Modulos\Ventas\Actions\ServirLineaComandaAction
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

Objetivo: diferenciar `servido` de `pagado`.

- Estado `pagada` o `cerrada`.
- Tabla `pagos_comanda`.
- Metodo de pago: efectivo, tarjeta, bizum, invitacion u otro.
- Importe recibido.
- Cambio calculado.
- Usuario que cobra.
- Fecha de cobro.
- Pantalla de cobrar comanda.

### Fase 3 - Edicion operativa

Objetivo: hacer la toma de comandas comoda para uso real.

- Anadir lineas a una comanda abierta.
- Modificar cantidades antes de servir.
- Cancelar lineas no servidas.
- Mover mesa.
- Notas por linea.
- Preparar estado `en_preparacion` para cocina/barra.

Regla: una linea servida no se edita directamente; se corrige con anulacion o ajuste trazable.

### Fase 4 - Caja y turnos

Objetivo: control diario para encargados y propietario.

- Apertura de caja.
- Cierre de caja.
- Saldo inicial.
- Efectivo esperado.
- Efectivo contado.
- Descuadre.
- Ventas por metodo de pago.
- Ventas por camarero.

### Fase 5 - Informes de ventas

Objetivo: explotar informacion de negocio.

- Ventas por dia.
- Productos mas vendidos.
- Ventas por categoria.
- Ticket medio.
- Comandas canceladas.
- Ventas por camarero.
- Margen estimado si hay coste fiable.

### Fase 6 - Escandallos / recetas

Objetivo: descontar ingredientes reales en platos de cocina.

- Receta por plato.
- Ingredientes vinculados a productos de inventario.
- Cantidad por racion.
- Descuento automatico al servir.

### Fase 7 - Tickets e integraciones

Objetivo: acercarse a TPV formal si el negocio lo necesita.

- Ticket imprimible.
- Numeracion de tickets.
- Impresion en cocina/barra.
- Exportacion diaria.
- Integracion con impresora termica.
- Integracion fiscal si procede.
