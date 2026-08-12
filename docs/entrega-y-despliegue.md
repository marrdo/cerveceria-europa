# Entrega y despliegue de la demo

**Decisión:** para la primera demo accesible por Internet se recomienda **Amazon Lightsail, Ubuntu y un plan de 2 GB**, sin RDS ni balanceador. Es la opción de AWS más simple para validar el producto; no conviene asumir todavía el coste y la operación de una arquitectura distribuida.

## Ruta rápida antes de entregar

```powershell
composer install
npm.cmd ci
npm.cmd run build
php artisan test
php artisan app:auditar-entrega
```

El último comando debe terminar sin `ERROR`. Los `AVISO` exigen revisión, pero no bloquean automáticamente una entrega local.

## Restaurar la demo local

La restauración elimina la base y los archivos generados. Solo funciona fuera de producción cuando `DEMO_MODE=true` y la base conectada coincide exactamente con `DEMO_DATABASE`.

```powershell
php artisan demo:restaurar
```

Para una automatización ya controlada:

```powershell
php artisan demo:restaurar --force
```

No se debe habilitar este comando en una instalación con datos reales.

## Arquitectura inicial recomendada

| Pieza | Primera demo | Cuándo separarla |
|---|---|---|
| Aplicación | Lightsail Ubuntu, 2 GB RAM | Si la CPU o memoria se mantienen por encima del 70 % |
| Servidor web | Nginx + PHP 8.4-FPM | Al añadir varias instancias |
| Base de datos | MySQL 8.4 en la misma instancia | Primer cliente real que requiera recuperación independiente |
| Cola | Un worker Supervisor con cola `database` | Cuando aumenten trabajos o exportaciones |
| Archivos | Disco local + snapshots | Al necesitar varias instancias, usar almacenamiento de objetos |
| TLS | Certbot/Let's Encrypt | Mantener siempre HTTPS |
| Región | `eu-south-2` (España) | Cambiar solo por requisitos de disponibilidad o residencia |

La tarifa oficial de Lightsail consultada en agosto de 2026 sitúa el plan Linux con IPv4, 2 GB de RAM, 2 vCPU y 60 GB SSD en **12 USD/mes**. Los snapshots cuestan **0,05 USD por GB/mes**. Una base gestionada de Lightsail parte de **15 USD/mes**; por eso no compensa añadirla antes de validar la demo.

Fuentes oficiales:

- [Precios de Amazon Lightsail](https://aws.amazon.com/lightsail/pricing/)
- [Planes de instancia Lightsail](https://docs.aws.amazon.com/lightsail/latest/userguide/amazon-lightsail-bundles.html)
- [Regiones disponibles en Lightsail](https://docs.aws.amazon.com/lightsail/latest/userguide/understanding-regions-and-availability-zones-in-amazon-lightsail.html)
- [Snapshots automáticos](https://docs.aws.amazon.com/lightsail/latest/userguide/amazon-lightsail-faq-snapshots.html)

## Configuración del servidor

Requisitos mínimos:

- Ubuntu LTS;
- PHP 8.4 con FPM, MySQL, Mbstring, XML, Curl, Zip, Intl, BCMath y GD;
- MySQL 8.4 con `utf8mb4` e InnoDB;
- Nginx;
- Composer 2;
- Node.js solo durante la construcción de assets;
- Supervisor para la cola;
- cron para el scheduler.

Plantillas incluidas:

- `deploy/nginx.conf.example`;
- `deploy/supervisor-worker.conf.example`;
- `deploy/cron.example`;
- `.env.production.example`.

## Proceso de despliegue

1. Crear una copia de seguridad de base de datos y `storage`.
2. Publicar el código de la versión aprobada.
3. Copiar `.env.production.example` como `.env` y sustituir todos los valores `CAMBIAR_*`.
4. Instalar y compilar:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan storage:link
php artisan migrate --force
php artisan optimize
php artisan app:auditar-entrega
```

5. Reiniciar PHP-FPM y los workers de Supervisor.
6. Comprobar `/up`, la portada, el login, el dashboard y una descarga privada.

No ejecutar `db:seed` en producción. Los seeders crean usuarios y contenido de demostración.

## Variables obligatorias de producción

| Variable | Regla |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | URL HTTPS definitiva |
| `APP_KEY` | Generada una sola vez y custodiada |
| `SESSION_ENCRYPT` | `true` |
| `SESSION_SECURE_COOKIE` | `true` |
| `DEMO_MODE` | `false` |
| `SUPERADMIN_PASSWORD` | Larga, única y distinta de `password` |
| `MAIL_MAILER` | Proveedor SMTP real, nunca `log` |

## Copias de seguridad

- Snapshot automático diario de Lightsail, conservando los siete últimos.
- Copia SQL diaria cifrada fuera de la instancia.
- Copia diaria de `storage/app/private` y `storage/app/public`.
- Prueba mensual de restauración en otra instancia.
- Snapshot manual antes de cada migración relevante.

Un snapshot completo ayuda ante un fallo de servidor, pero no sustituye una copia SQL independiente frente a borrados lógicos o corrupción de datos.

## Checklist de publicación

- [ ] `php artisan test` pasa completamente.
- [ ] `composer audit --locked` y `npm audit --omit=dev` no muestran vulnerabilidades.
- [ ] `php artisan app:auditar-entrega` no devuelve errores.
- [ ] `php artisan migrate:status` no muestra migraciones pendientes.
- [ ] HTTPS y renovación automática del certificado están activos.
- [ ] Solo están abiertos los puertos 80, 443 y SSH restringido.
- [ ] La cola y el scheduler están funcionando.
- [ ] Las copias de seguridad se han creado y probado.
- [ ] La indexación SEO refleja si la web debe ser pública o privada.
- [ ] Las cuentas de demostración no son accesibles con contraseñas conocidas.

## Rollback

1. Activar mantenimiento: `php artisan down --retry=60`.
2. Volver al código de la versión anterior.
3. Restaurar la base solo si la migración no es compatible hacia atrás.
4. Ejecutar `php artisan optimize:clear && php artisan optimize`.
5. Reiniciar workers y PHP-FPM.
6. Ejecutar `php artisan up` y repetir las comprobaciones principales.

La decisión de pasar a RDS, S3 o varias instancias debe basarse en clientes reales, recuperación exigida y métricas, no en previsiones prematuras.
