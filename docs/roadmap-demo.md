# Roadmap de la demo modular

La demo está en la **Fase 2: Planificación de turnos**. La nueva vista operativa ya está implementada, pero la Fase 2 no se cerrará hasta completar incidencias laborales, utilidades de planificación y el Excel automático de publicación.

## Estado rápido

| Fase | Estado | Resultado esperado |
|---|---|---|
| 1. Base neutral reutilizable | Completada | Demo sin datos ni marcas de clientes reales |
| 2. Planificación de turnos | En curso | Crear, revisar, publicar y exportar semanas completas |
| 3. Recorrido integral de demo | Pendiente | Datos coherentes entre ventas, compras, inventario, espacios y personal |
| 4. Modularización definitiva | Pendiente | Módulos aislados, activables y con dependencias explícitas |
| 5. Web pública productizable | Pendiente | Identidad, contenidos y SEO configurables por negocio |
| 6. Calidad y entrega | Pendiente | Instalación, restauración de demo, seguridad y documentación finales |

## Fase 2 en detalle

| Bloque | Estado | Alcance |
|---|---|---|
| 2.1. Dominio y reglas base | Completado | Cuadrantes, jornadas, pausas, turnos partidos/nocturnos, solapamientos y publicación |
| 2.2. Datos realistas | Completado | 22 personas ficticias, 17 en sala/barra y 5 en trastienda |
| 2.3. Experiencia operativa | En revisión | Matriz por empleado y día, filtros, densidad, totales y navegación fija |
| 2.4. Incidencias laborales | Completado | Descansos, vacaciones, bajas, ausencias, festivos y conflictos |
| 2.5. Productividad y control | Completado | Copiar semanas, plantillas, edición en bloque, horas contratadas y cobertura |
| 2.6. Excel de publicación | Pendiente | Generación automática de `.xlsx` al publicar el cuadrante |

## Criterios de cierre de la Fase 2

- [ ] La cuadrícula resulta cómoda con al menos 22 empleados.
- [x] Las incidencias laborales se distinguen de los turnos de trabajo.
- [x] Se detectan excesos, carencias y conflictos antes de publicar.
- [x] Copiar o reutilizar una planificación semanal requiere pocos pasos.
- [ ] Publicar bloquea la versión y genera automáticamente su Excel.
- [ ] El archivo publicado puede descargarse después sin recalcular datos manualmente.
- [ ] Escritorio, tableta, móvil e impresión mantienen una lectura clara.

## Decisión para el Excel

El Excel será un **resultado versionado del cuadrante publicado**, no una fuente de datos paralela. Tendrá la estructura visual del documento de referencia, pero se rellenará únicamente con empleados, turnos e incidencias almacenados en la aplicación.

Al reabrir un cuadrante se conservará el último archivo publicado como evidencia. La siguiente publicación generará una versión nueva y descargable.

## Siguiente paso

Continuar con la **Fase 2.6: Excel automático de publicación**, sin cerrar la revisión visual global hasta completar todas las fases de la demo.
