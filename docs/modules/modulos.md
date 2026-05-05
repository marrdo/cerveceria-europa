# Modulo Sistema: Modulos Contratados

## Objetivo

Centralizar que funcionalidades estan activas en el proyecto.

Esto permite vender el panel por partes sin crear columnas nuevas cada vez que aparezca un modulo.

## Tabla

```text
modulos
```

## Modelo

```text
App\Models\Modulo
```

## Campos

```text
id
clave
nombre
descripcion
grupo
activo
orden
created_at
updated_at
```

## Regla de diseno

No se deben crear columnas tipo:

```text
web_publica_activo
blog_activo
reservas_activo
```

La forma correcta es una fila por modulo:

```text
web_publica
blog
reservas
ventas
lectura_documentos
```

## Modulos actuales

```text
inventario
compras
web_publica
blog
ventas
reservas
lectura_documentos
```

## Permisos

- `superadmin`: puede ver y cambiar todos los modulos desde el dashboard.
- `propietario`: ve los modulos activos que le correspondan.
- `encargado`: accede a operativa interna permitida por rol, incluyendo ventas.
- `camarero`: accede al modulo de ventas cuando esta activo.

## Comandos utiles

```powershell
php artisan db:seed --class=ModuloSeeder
```

## Flujo comercial

```text
Cliente compra modulo
-> superadmin lo activa en dashboard
-> aparece en navegacion/permisos
-> rutas y pantallas quedan disponibles
```

## Notas

- `web_publica` desactivado hace que la web publica responda 404.
- `blog` desactivado oculta rutas y administracion del blog.
- `ventas` desactivado oculta comandas y bloquea el acceso operativo a camareros y encargados.
- Nuevos modulos deben registrarse en `ModuloSeeder`.
