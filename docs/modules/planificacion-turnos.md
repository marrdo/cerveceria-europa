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

### `IncidenciaLaboral`

Representa un periodo de `descanso`, `vacaciones`, `baja`, `ausencia` o `festivo`. No pertenece a un cuadrante concreto porque puede atravesar varias semanas. Las incidencias personales bloquean nuevas asignaciones de trabajo; los festivos son globales y permiten mantener turnos cuando el negocio abre ese día.

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
- Las incidencias personales no pueden solaparse entre sí.
- Un turno nuevo no puede coincidir con un descanso, vacaciones, baja o ausencia.
- Un cuadrante con conflictos entre turnos e incidencias no puede publicarse.
- Un cuadrante publicado no admite altas ni eliminaciones hasta que se reabre.

## Escenario de demostración

`DatabaseSeeder` crea un equipo completamente ficticio de 22 personas operativas, sin copiar ningún dato personal del Excel de referencia. El cuadrante de la semana actual distribuye 17 personas en `Sala y barra` y 5 en `Trastienda`, mezcla jornadas continuas y partidas e incluye descansos, vacaciones, una baja, una ausencia y un festivo para revisar todos los estados visuales.

## Fase 2.3: experiencia operativa

La vista semanal utiliza una matriz con una fila estable por empleado y una columna por día. Incluye búsqueda por nombre, filtro por área, modos compacto y detallado, totales semanales y cabecera y columna de empleado fijas durante el desplazamiento. También muestra a las personas sin turnos para que una ausencia de planificación no pase desapercibida.

## Fase 2.4: incidencias laborales

Los periodos personales se registran una sola vez mediante fechas de inicio y fin y aparecen en todas las semanas afectadas. La cuadrícula deriva los colores del tipo guardado, muestra los festivos en la cabecera y señala los turnos incompatibles. Una baja inesperada puede registrarse aunque ya existan turnos para hacer visible el conflicto, pero el cuadrante no podrá publicarse hasta resolverlo.

## Fase 2.5: productividad y control

- Cada empleado dispone de minutos contratados por semana, editables desde `Personal` y comparados con sus horas efectivas planificadas.
- Un cuadrante puede copiarse íntegramente a otra semana disponible.
- Cualquier semana puede guardarse como plantilla y aplicarse después desde el listado de cuadrantes.
- La asignación en bloque crea el mismo turno para varios empleados y días dentro de una única transacción: si una asignación falla, no se guarda ninguna.
- Las reglas de cobertura definen área, día, franja y número mínimo de personas. El sistema revisa intervalos de 30 minutos, agrupa déficits consecutivos y los muestra como avisos no bloqueantes.

Las incidencias no se copian ni se almacenan en plantillas porque ya viven en el calendario real de cada persona y se aplican automáticamente a la semana de destino.

## Fase 2.6: Excel automático al publicar

La publicación definitiva genera automáticamente un archivo `.xlsx` descargable. El documento reproduce la estructura operativa del Excel de referencia —empleados por filas, intervalos de 30 minutos por columnas, separación por áreas y colores para trabajo, descanso, vacaciones, bajas, ausencias y festivos— usando exclusivamente los datos registrados en la aplicación.

La generación está vinculada a la versión publicada: al reabrir se conserva el archivo anterior como evidencia y una nueva publicación genera una versión actualizada. El Excel es una salida del sistema, nunca una segunda fuente de datos editable.

Cada versión registra nombre, ruta privada, disco, tamaño, fecha, autor y huella SHA-256. La publicación y el registro se ejecutan de forma transaccional; si el proceso falla, el cuadrante continúa como borrador y se elimina cualquier archivo huérfano. El disco se configura con `PLANIFICACION_TURNOS_EXPORT_DISK` y usa `local` por defecto, por lo que en una futura infraestructura puede cambiarse a S3 sin modificar el código de negocio.

La hoja se prepara en horizontal, ajustada al ancho de impresión, con cabeceras por día y área, horarios exactos visibles junto al empleado y soporte para turnos partidos y nocturnos.

## Verificación

```powershell
php artisan test --filter=PlanificacionTurnosModuleTest
npm.cmd run build
```
