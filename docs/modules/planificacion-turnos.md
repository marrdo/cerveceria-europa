# Módulo Planificación de turnos

## Objetivo

Sustituir los cuadrantes mantenidos mediante colores en Excel por una planificación semanal explícita, legible y validada. El módulo está pensado para bares, cafeterías y otros negocios de hostelería con uno o varios equipos operativos.

## Alcance del MVP

- Crear un cuadrante para una semana natural; la fecha se normaliza siempre al lunes.
- Organizar el trabajo por áreas operativas, inicialmente `Sala y barra` y `Trastienda`.
- Asignar uno o varios tramos diarios a cada empleado para representar turnos partidos.
- Registrar pausas y jornadas que terminan al día siguiente.
- Calcular horas efectivas por tramo, día y semana.
- Impedir solapamientos para un mismo empleado.
- Publicar el cuadrante para bloquear su edición y reabrirlo cuando sea necesario corregirlo.
- Mostrar una vista semanal adaptable a escritorio, tableta y móvil.

## Modelo de dominio

### `CuadranteLaboral`

Representa una semana completa y conserva su estado (`borrador` o `publicado`) y sus notas internas.

### `AreaTrabajo`

Representa una responsabilidad operativa. Es independiente de los recintos, zonas y mesas del módulo `Espacios`, porque un área laboral describe dónde o para qué trabaja una persona, no dónde se sienta un cliente.

### `JornadaLaboral`

Representa un tramo continuo de trabajo. Un turno partido se guarda como dos jornadas para el mismo empleado y fecha. Esta decisión evita columnas horarias rígidas y permite calcular, validar y reutilizar los datos.

## Acceso y activación

- El módulo se registra con la clave `planificacion_turnos` y nace desactivado.
- Solo `superadmin` puede activarlo o desactivarlo.
- `encargado`, `propietario` y `superadmin` pueden gestionar cuadrantes cuando está activo.
- `camarero` no puede acceder a la gestión.

## Reglas importantes

- Una jornada debe pertenecer a la semana del cuadrante.
- El área seleccionada debe estar activa.
- La pausa no puede consumir toda la duración del tramo.
- Dos jornadas del mismo empleado no pueden solaparse, incluidas las que cruzan medianoche.
- Un cuadrante publicado no admite altas ni eliminaciones hasta que se reabre.

## Evolución prevista

La siguiente iteración debe añadir ausencias explícitas (`descanso`, `vacaciones`, `baja` y otras incidencias), avisos de cobertura mínima y una vista por empleado con el total de horas semanales. Los colores serán una ayuda visual derivada de los datos, nunca la fuente de verdad.

## Verificación

```powershell
php artisan test --filter=PlanificacionTurnosModuleTest
npm.cmd run build
```
