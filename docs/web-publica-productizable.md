# Web pública productizable

La web pública usa la misma instalación Laravel que el panel, pero su identidad y su contenido ya no dependen de una marca escrita en las plantillas.

## Qué puede configurar el propietario

En **Configuración del negocio**:

- nombre comercial, eslogan y datos de contacto;
- logo, favicon e imagen para redes sociales;
- colores principal, secundario, fondo, superficie y texto;
- título y descripción SEO;
- permiso para indexar o mantener la instalación fuera de buscadores;
- enlaces de web, Instagram, Google Maps y reservas.

En **Web pública > Secciones**:

- cabecera de la portada e imagen principal;
- títulos de sugerencias y productos destacados;
- tres valores o argumentos comerciales;
- contenido e imagen de contacto;
- visibilidad independiente de cada bloque.

Los títulos se muestran siempre como texto escapado: el editor no admite HTML ejecutable.

## SEO técnico

La aplicación genera dinámicamente:

- canonical, robots, Open Graph y Twitter Cards;
- JSON-LD de tipo `Restaurant`;
- `/site.webmanifest`;
- `/robots.txt`;
- `/sitemap.xml` con páginas públicas y artículos publicados.

Las instalaciones demo nacen con la indexación desactivada. Antes de publicar una instalación real hay que configurar `web_url`, revisar el contenido SEO y activar la indexación.

## Archivos y almacenamiento

Los recursos se guardan en el disco `public`, bajo `negocio/identidad` y `web-publica/secciones`. Al sustituir o eliminar una imagen, el archivo anterior también se elimina si pertenece al almacenamiento local. En producción debe existir el enlace creado por `php artisan storage:link`.

Los paths se almacenan como `TEXT`. Esto evita agotar el tamaño máximo de fila de InnoDB con columnas `VARCHAR(2048)` y codificación `utf8mb4`.

## Preparar una demo

```powershell
php artisan migrate --force
php artisan db:seed --class=WebPublicaSeeder --force
php artisan storage:link
```

El seeder crea la estructura inicial con `firstOrCreate`, de modo que volver a ejecutarlo no sobrescribe los textos que ya haya editado el negocio.
