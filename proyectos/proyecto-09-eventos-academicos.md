# Proyecto N°09 — Gestión de eventos académicos

## 1. Contexto
Una universidad organiza con frecuencia congresos, charlas, talleres y seminarios. Cada evento puede tener uno o varios ponentes, lugares y temáticas. Hoy la inscripción de asistentes y la entrega de certificados se hace manualmente, generando errores en los listados y demoras en la emisión.

## 2. Objetivo
Construir un aplicativo que gestione eventos académicos, ponentes, inscripciones de asistentes, control de asistencia y emisión de certificados a quienes cumplan asistencia mínima, con persistencia en una BD relacional.

## 3. Requisitos funcionales mínimos
- **RF1**: Crear y mantener eventos con nombre, categoría, fecha de inicio, fecha de fin, lugar y modalidad (presencial / virtual).
- **RF2**: Asignar uno o más ponentes a un evento, con el tema que abordará cada uno.
- **RF3**: Registrar inscripción de asistentes a eventos.
- **RF4**: Controlar la asistencia (presente / ausente) por sesión o por día del evento.
- **RF5**: Generar certificado para asistentes que cumplan la asistencia mínima exigida por el evento (porcentaje configurable).
- **RF6**: Reporte de eventos más concurridos y de ponentes más activos.
- **RF7**: Búsqueda de eventos por categoría, fecha o ponente.

## 4. Entidades sugeridas (guía, no MER cerrado)
- Evento
- Categoría
- Ponente (relación N a M con Evento, con tema asignado)
- Asistente
- Inscripción
- Asistencia (registro por sesión/día)
- Certificado

> El estudiante debe diseñar el MER completo, identificar atributos, definir cardinalidades y normalizar hasta 3FN.

## 5. Entregables
- Aplicativo funcionando (lenguaje y tipo de UI libres: escritorio o web).
- Diagrama MER completo con PK, FK y cardinalidades.
- Script DDL completo (`.sql`) para MySQL o PostgreSQL.
- Datos de prueba (al menos 6 eventos, 8 ponentes, 30 asistentes inscritos y registro de asistencias).
- Breve README con instrucciones de instalación y ejecución.

## 6. Rúbrica de evaluación docente

| Criterio | Peso |
|---|---|
| MER completo, correcto y normalizado hasta 3FN (con PK, FK, cardinalidades) | 40% |
| Aplicativo funcionando y usando realmente la BD | 35% |
| Cumplimiento de los requisitos funcionales mínimos (RF1–RF7) | 20% |
| Sustentación clara del diseño de BD | 5% |

## 7. Ideas para diferenciar las dos versiones
- PDF real del certificado vs solo registro lógico de emisión.
- Asistencia con código QR vs lista manual.
- Encuestas de satisfacción al cierre del evento.
- Eventos virtuales con enlace y eventos presenciales con cupo de sala.
- Categorías jerárquicas (categoría → subcategoría).
