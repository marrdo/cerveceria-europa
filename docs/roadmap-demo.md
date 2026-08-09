# Roadmap de la demo modular

La **Fase 2: Planificación de turnos** está completada funcionalmente. La siguiente iteración es la **Fase 3: Recorrido integral de demo**; el pulido visual transversal se realizará al terminar todas las fases, tal como se acordó.

## Estado rápido

| Fase | Estado | Resultado esperado |
|---|---|---|
| 1. Base neutral reutilizable | Completada | Demo sin datos ni marcas de clientes reales |
| 2. Planificación de turnos | Completada | Crear, revisar, publicar y exportar semanas completas |
| 3. Recorrido integral de demo | Pendiente | Datos coherentes entre ventas, compras, inventario, espacios y personal |
| 4. Modularización definitiva | Pendiente | Módulos aislados, activables y con dependencias explícitas |
| 5. Web pública productizable | Pendiente | Identidad, contenidos y SEO configurables por negocio |
| 6. Calidad y entrega | Pendiente | Instalación, restauración de demo, seguridad y documentación finales |

## Fase 2 en detalle

| Bloque | Estado | Alcance |
|---|---|---|
| 2.1. Dominio y reglas base | Completado | Cuadrantes, jornadas, pausas, turnos partidos/nocturnos, solapamientos y publicación |
| 2.2. Datos realistas | Completado | 22 personas ficticias, 17 en sala/barra y 5 en trastienda |
| 2.3. Experiencia operativa | Completado | Matriz por empleado y día, filtros, densidad, totales y navegación fija |
| 2.4. Incidencias laborales | Completado | Descansos, vacaciones, bajas, ausencias, festivos y conflictos |
| 2.5. Productividad y control | Completado | Copiar semanas, plantillas, edición en bloque, horas contratadas y cobertura |
| 2.6. Excel de publicación | Completado | `.xlsx` privado, automático, versionado y descargable al publicar |

## Criterios de cierre de la Fase 2

- [x] La cuadrícula gestiona el escenario ficticio completo de 22 empleados.
- [x] Las incidencias laborales se distinguen de los turnos de trabajo.
- [x] Se detectan excesos, carencias y conflictos antes de publicar.
- [x] Copiar o reutilizar una planificación semanal requiere pocos pasos.
- [x] Publicar bloquea la versión y genera automáticamente su Excel.
- [x] El archivo publicado puede descargarse después sin recalcular datos manualmente.
- [x] El Excel está preparado para impresión horizontal y la interfaz mantiene su adaptación responsive.

La aceptación visual fina en escritorio, tableta y móvil queda agrupada en la revisión global posterior a todas las fases; no bloquea el cierre funcional de este módulo.

## Decisión para el Excel

El Excel será un **resultado versionado del cuadrante publicado**, no una fuente de datos paralela. Tendrá la estructura visual del documento de referencia, pero se rellenará únicamente con empleados, turnos e incidencias almacenados en la aplicación.

Al reabrir un cuadrante se conserva el último archivo publicado como evidencia. La siguiente publicación genera una versión nueva y descargable. Los archivos viven en almacenamiento privado y pueden trasladarse de disco local a S3 mediante configuración, sin cambiar el dominio.

## Siguiente paso

Continuar con la **Fase 3: Recorrido integral de demo**, conectando datos y recorridos coherentes entre ventas, compras, inventario, espacios y personal.
