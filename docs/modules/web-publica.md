# Modulo Web Publica

## Estado

Pendiente.

## Objetivo

Crear una web publica para Cerveceria Europa dentro del mismo proyecto Laravel, manteniendo el panel de gestion en `/admin`.

La idea es que el bar pueda modificar contenido desde el panel sin tocar codigo.

## Alcance inicial

- Pagina de inicio publica.
- Carta o seccion de cocina.
- Cervezas destacadas.
- Platos fuera de carta.
- Recomendaciones del chef.
- Recomendaciones de cerveza de la semana.
- Galeria de fotos.
- Contacto, ubicacion y horarios.
- Blog o noticias si aporta valor comercial.

## Gestion desde admin

El panel deberia permitir:

- Subir imagenes.
- Crear y editar platos publicados.
- Crear y editar cervezas publicadas.
- Marcar elementos como destacados.
- Marcar fuera de carta.
- Programar recomendaciones por fecha.
- Publicar, ocultar o archivar contenido.

## Reglas

- La web publica no debe depender de textos fijos si el bar necesita cambiarlos.
- El contenido editable debe vivir en tablas propias.
- Las imagenes deben guardarse con trazabilidad basica.
- El panel admin seguira protegido por login.
- El contenido publico solo mostrara elementos activos/publicados.

## Propuesta de rutas

```text
/
/carta
/cervezas
/recomendaciones
/blog
/contacto
/admin
```

## Fases sugeridas

1. Estructura publica base y layout.
2. Modelo de contenido publicable.
3. CRUD admin para platos y cervezas.
4. Subida de imagenes.
5. Recomendaciones y fuera de carta.
6. SEO basico, rendimiento y preparacion para despliegue.
