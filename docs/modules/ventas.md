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
