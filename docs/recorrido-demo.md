# Recorrido comercial de la demo

La demo incluye una jornada ficticia ya conectada entre carta, mesas, personal, comandas, caja, inventario, compras e informes. El objetivo es poder enseñarla sin preparar datos manualmente antes de cada presentación.

## Ruta rápida

1. Accede con `encargado@demo.local` y contraseña `password`.
2. Abre el **Dashboard** y sigue los ocho pasos de «Recorrido recomendado».
3. Termina en **Informes de ventas** para comprobar que la venta cobrada aparece en los resultados.

## Escenario disponible

| Área | Datos preparados | Qué permite enseñar |
|---|---|---|
| Web y carta | Carta ficticia publicada y conectada con productos | El mismo contenido alimenta la web y la toma de comandas |
| Espacios | Un recinto, sala/barra, terraza y ocho mesas | Asignación real de zona y mesa a cada comanda |
| Personal | 22 personas y un cuadrante semanal completo | Turnos, contratos, incidencias, cobertura y Excel publicado |
| Ventas | Cuatro comandas en estados diferentes | Nueva, en preparación, servida pendiente y pagada |
| Caja | Una caja abierta con una venta de tarjeta asociada | Cobro, efectivo esperado y futuro cierre de caja |
| Inventario | Cuatro movimientos nacidos del servicio o de una recepción | Descuento automático al servir y entrada al recibir mercancía |
| Compras | Un pedido pendiente y otro recibido parcialmente | Reposición, recepciones y mercancía todavía pendiente |
| Informes | Una venta completa dentro del periodo actual | Ticket, facturación y relación entre venta, coste y margen |

## Comandas para continuar

| Número | Estado | Uso recomendado |
|---|---|---|
| `DEMO-COM-004` | Abierta | Empezar el flujo desde una comanda recién tomada |
| `DEMO-COM-003` | En preparación | Servir la línea pendiente y observar el descuento de stock |
| `DEMO-COM-002` | Servida | Registrar el cobro y comprobar su entrada en caja |
| `DEMO-COM-001` | Pagada | Revisar la trazabilidad completa ya terminada |

## Compras para continuar

- `DEMO-PC-001`: pedido enviado al proveedor y pendiente de recibir.
- `DEMO-PC-002`: pedido con una recepción parcial y cantidades pendientes.
- `DEMO-RC-001`: recepción que incrementó existencias y creó el lote ficticio correspondiente.

## Restaurar solo el recorrido

```powershell
php artisan db:seed --class=RecorridoDemoSeeder
```

El seeder reconstruye inventario, carta y operaciones identificadas con prefijos `DEMO-`. No borra comandas, pedidos o cajas creados manualmente con otra numeración.

## Comprobación

- El Dashboard muestra `3 activas · 1 pagada`.
- Hay ocho mesas distribuidas en dos zonas.
- `DEMO-COM-001` tiene un pago enlazado a `DEMO-CAJA-ACTUAL`.
- El stock de cerveza es 21, el de limonada 16 y el de patata 13.
- `DEMO-PC-002` permanece en estado «Recibido parcial».

## Siguiente paso

La Fase 4 endurecerá la modularización: dependencias explícitas, aislamiento de rutas y datos y comportamiento consistente al activar o desactivar módulos.
