# Modulo Espacios

## Objetivo

Gestionar la estructura fisica y operativa del negocio sin mezclarla con inventario.

Este modulo debe servir para bares, restaurantes, cafeterias o negocios similares, no solo para Cerveceria Europa.

## Idea base

```text
Recinto
-> zonas
-> mesas
-> comandas/cuentas
```

## Submodulo Recinto

Representa el local o unidad operativa donde se presta el servicio.

Ejemplo:

```text
Cerveceria Europa
```

Campos recomendados:

- Nombre comercial.
- Nombre fiscal opcional.
- Direccion.
- Localidad.
- Provincia.
- Codigo postal.
- Pais.
- Telefonos de contacto.
- Emails de referencia.
- Notas internas.
- Activo.

Decision pendiente: parte de estos datos tambien podrian pertenecer a un futuro modulo de empresa/facturacion. Para no bloquear ventas, `espacios` puede guardar los datos operativos minimos y dejar fiscalidad para otro modulo.

## Submodulo Zonas

Representa areas del recinto donde se atienden clientes.

Ejemplos:

```text
Terraza 1
Terraza 2
Salon alto
Salon bajo
Barra
Salon de celebraciones
```

Campos recomendados:

- Recinto.
- Nombre.
- Codigo interno opcional.
- Orden.
- Activa.
- Notas internas.

Reglas:

- Una zona inactiva no aparece al crear nuevas comandas.
- Una zona inactiva conserva historial.
- El propietario y el encargado pueden activar o desactivar zonas segun operativa diaria.

## Submodulo Mesas

Representa mesas, puestos de barra o puntos de servicio dentro de una zona.

Campos recomendados:

- Zona.
- Nombre o numero visible.
- Capacidad opcional.
- Orden.
- Activa.
- Notas internas.

Reglas:

- Una mesa inactiva no aparece para nuevas comandas.
- Una mesa puede tener una cuenta abierta.
- Una mesa puede acumular varias comandas antes del cobro final.

## Relacion con Ventas

Ventas no debe usar `ubicacion_inventario_id` como ubicacion del cliente.

La comanda deberia apuntar a:

```text
recinto_id
zona_id
mesa_id
cuenta_id
ubicacion_inventario_id
```

Responsabilidades:

- `zona_id` y `mesa_id`: donde esta sentado o atendido el cliente.
- `cuenta_id`: agrupacion de consumos que se cobraran juntos.
- `ubicacion_inventario_id`: de donde se descuenta stock al servir.

## Estado actual

Primera version implementada.

Incluye:

- CRUD de recintos.
- CRUD de zonas.
- CRUD de mesas.
- Activar/desactivar recintos, zonas y mesas.
- Acceso de propietario, encargado y superadmin.
- Bloqueo de camareros en administracion de espacios.
- Vinculacion de comandas con `recinto_id`, `zona_id` y `mesa_id`.
- Selector de recinto, zona y mesa al crear o editar una comanda.
- Conservacion del campo manual `mesa` como fallback mientras se cargan todas las mesas.

No incluye todavia:

- Cuenta agrupadora.
- Cobro final por mesa con varias comandas.
- Vista tipo mapa/estado de sala.

## Fase propuesta siguiente

Este modulo debe implementarse despues de cerrar la toma de comandas basica y antes de cerrar definitivamente el cobro.

Motivo: el cobro profesional debe hacerse por mesa/cuenta completa, no por una comanda aislada si el cliente sigue pidiendo.
