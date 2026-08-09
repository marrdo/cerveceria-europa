# Roadmap de la demo modular

La **Fase 5: Web pública productizable** está completada. La siguiente iteración es la **Fase 6: Calidad y entrega**; incluirá la revisión global y la decisión de infraestructura acordada.

## Estado rápido

| Fase | Estado | Resultado esperado |
|---|---|---|
| 1. Base neutral reutilizable | Completada | Demo sin datos ni marcas de clientes reales |
| 2. Planificación de turnos | Completada | Crear, revisar, publicar y exportar semanas completas |
| 3. Recorrido integral de demo | Completada | Datos coherentes entre ventas, compras, inventario, espacios y personal |
| 4. Modularización definitiva | Completada | Módulos aislados, activables y con dependencias explícitas |
| 5. Web pública productizable | Completada | Identidad, contenidos y SEO configurables por negocio |
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

## Fase 3 en detalle

| Bloque | Estado | Alcance |
|---|---|---|
| 3.1. Espacios de servicio | Completado | Recinto configurable, sala/barra, terraza y ocho mesas ficticias |
| 3.2. Jornada comercial | Completado | Cuatro comandas en estados abierta, preparación, servida y pagada |
| 3.3. Caja e inventario | Completado | Pago asociado a caja y salidas de stock generadas al servir |
| 3.4. Compras conectadas | Completado | Pedido pendiente, recepción parcial, entrada de stock y lote |
| 3.5. Guía comercial | Completado | Dashboard con ocho pasos y enlaces adaptados al rol autenticado |

### Criterios de cierre de la Fase 3

- [x] La carta pública y las comandas comparten los mismos contenidos.
- [x] Las comandas usan recinto, zona y mesa reales del módulo Espacios.
- [x] Servir productos inventariables descuenta sus existencias.
- [x] Cobrar una comanda la enlaza con el turno de caja abierto.
- [x] Recibir un pedido incrementa stock y conserva su trazabilidad.
- [x] El escenario puede reconstruirse sin duplicar datos.
- [x] El Dashboard explica un recorrido completo sin memorizar URLs.

## Fase 4 en detalle

| Bloque | Estado | Alcance |
|---|---|---|
| 4.1. Catálogo único | Completado | Metadatos, roles, dependencias e integraciones versionados en una fuente única |
| 4.2. Contratos coherentes | Completado | Activación transaccional y bloqueo de estados que romperían otros módulos |
| 4.3. Acceso uniforme | Completado | Middleware central para administración y superficies públicas |
| 4.4. Rutas aisladas | Completado | Un archivo de rutas por módulo y núcleo reducido |
| 4.5. Diagnóstico | Completado | Auditoría ejecutable de catálogo, base de datos, ciclos y dependencias |

### Criterios de cierre de la Fase 4

- [x] Una clave ausente o desconocida no concede acceso accidentalmente.
- [x] No puede activarse un módulo si sus dependencias no están operativas.
- [x] No puede desactivarse una dependencia utilizada por módulos activos.
- [x] El seeder actualiza nombres y descripciones sin cambiar el contrato existente.
- [x] Rutas administrativas y públicas comparten la misma resolución de estado.
- [x] El superadmin puede preparar un módulo inactivo sin publicarlo al cliente.
- [x] El dashboard explica requisitos, integraciones y bloqueos.
- [x] La auditoría modular confirma que la instalación es coherente.

## Fase 5 en detalle

| Bloque | Estado | Alcance |
|---|---|---|
| 5.1. Identidad | Completado | Logo, favicon, imagen social y datos del negocio |
| 5.2. Apariencia | Completado | Paleta pública aplicada mediante variables CSS dinámicas |
| 5.3. Portada editable | Completado | Cabecera, sugerencias, destacados, valores e imágenes gestionables |
| 5.4. SEO técnico | Completado | Canonical, robots, Open Graph, JSON-LD, manifest y sitemap dinámicos |
| 5.5. Demo segura | Completado | Seeders idempotentes que conservan textos editados y nacen sin indexación |

### Criterios de cierre de la Fase 5

- [x] No hay una marca de cliente fijada en la estructura pública.
- [x] Los recursos de identidad pueden reemplazarse sin dejar archivos huérfanos.
- [x] La portada puede adaptarse a otro negocio sin editar Blade.
- [x] Los títulos editables se escapan y no ejecutan HTML.
- [x] Metadatos, datos estructurados y documentos SEO salen de la configuración.
- [x] El seeder respeta los contenidos previamente editados.
- [x] La migración funciona con MySQL 8.4, InnoDB y `utf8mb4`.

## Siguiente paso

Continuar con la **Fase 6: Calidad y entrega**, revisar visualmente todos los módulos, endurecer instalación y seguridad, preparar restauración de demo y decidir el despliegue (incluida la valoración de AWS).
