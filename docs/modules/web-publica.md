# Modulo Web Publica

## Estado

Fase 5.0 iniciada.

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
- Blog como modulo opcional vendible por separado.

## Gestion desde admin

El panel deberia permitir:

- Subir imagenes.
- Crear y editar platos publicados.
- Crear y editar cervezas publicadas.
- Marcar elementos como destacados.
- Marcar fuera de carta.
- Publicar u ocultar contenido con clicks.
- Programar recomendaciones por fecha.
- Publicar, ocultar o archivar contenido.

## Implementado

- Tabla `contenidos_web`.
- Tabla `categorias_carta`.
- Tabla `tarifas_contenido_web`.
- Modelo `ContenidoWeb`.
- Modelo `CategoriaCarta`.
- Modelo `TarifaContenidoWeb`.
- Tipos de contenido:
  - `plato`,
  - `cerveza`,
  - `recomendacion_chef`,
  - `recomendacion_cerveza`.
- Tabla general `modulos`.
- Tabla `posts_blog`.
- Tabla `categorias_blog`.
- Tabla pivote `categoria_blog_post`.
- Tabla `secciones_web`.
- Modulo principal `web_publica` activable/desactivable por `superadmin`.
- Modulo opcional `blog` activable/desactivable desde admin.
- Categorias de blog gestionables desde admin.
- Posts asignables a una o varias categorias.
- Secciones editables desde admin, empezando por `contacto`.
- Web publica en `/`, `/carta`, `/cervezas`, `/recomendaciones`, `/blog` y `/contacto`.
- Layout publico con identidad industrial/cervecera.
- CRUD admin en `/admin/web-publica/contenidos`.
- CRUD admin en `/admin/web-publica/carta-categorias`.
- Subida de imagenes a disco `public`.
- Flags rapidos de `publicado`, `destacado` y `fuera_carta`.
- Precio opcional y alergenos por contenido.
- Tarifas multiples por contenido:
  - Tapa / Plato,
  - Copa / Botella,
  - 25cl / 33cl / 50cl,
  - cualquier formato configurable por el bar.
- Vinculacion opcional con productos de inventario.
- Clasificacion de carta por categorias jerarquicas editables:
  - categoria padre,
  - secciones hijas,
  - contenidos publicados dentro de cada seccion.
- Ocultacion automatica en web publica si el producto vinculado controla stock y esta a 0.
- Seeder inicial `WebPublicaSeeder`.

## Reglas

- La web publica no debe depender de textos fijos si el bar necesita cambiarlos.
- El contenido editable debe vivir en tablas propias.
- Los platos/cervezas que correspondan a productos reales deben poder vincularse a inventario.
- La estructura de carta publica no debe depender de categorias de inventario: inventario clasifica para gestion interna y carta clasifica para venta al cliente.
- La carta publica debe poder imitar un esquema tipo `categoria padre > secciones > productos`, similar a cartas digitales externas, pero gestionado desde nuestra base de datos.
- Un contenido puede tener precio simple o varias tarifas; si tiene tarifas, la web publica muestra las tarifas.
- Si un contenido tiene producto vinculado y no hay stock disponible, no debe mostrarse en la carta publica.
- Si el modulo `web_publica` esta desactivado, las rutas publicas responden 404 y el propietario no ve el modulo en el panel.
- El `superadmin` puede entrar al panel de web publica aunque el modulo este desactivado para activarlo o revisar configuracion.
- Si el modulo `blog` esta desactivado, no se muestra enlace de blog y las rutas publicas/admin del blog responden 404.
- El blog debe poder filtrarse por categorias publicas.
- Textos estructurales como contacto, reservas, ubicacion y horario deben vivir en `secciones_web`.
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

## Modelo comercial

El modulo `web_publica` representa la contratacion de una web gestionable dentro del mismo Laravel.

- Proyecto con solo panel interno: `web_publica` desactivado.
- Proyecto con web publica contratada: `web_publica` activado.
- Blog es un submodulo opcional dentro de web publica.
- La activacion/desactivacion del modulo principal queda reservada a `superadmin`.

## Dependencia con tabla `modulos`

La web publica ya no usa una tabla propia de activacion. El estado comercial vive en:

```text
modulos
```

Filas relevantes:

```text
web_publica
blog
reservas
```

Reglas:

- `web_publica` controla si existe web publica para el cliente.
- `blog` controla si existe blog dentro de la web publica.
- `reservas` queda preparado como modulo futuro.
- Solo `superadmin` puede cambiar estos estados desde el dashboard.
- El propietario solo ve lo que este activo y contratado.
