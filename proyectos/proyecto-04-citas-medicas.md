# Proyecto N°04 — Citas médicas en consultorio

## 1. Contexto
Un consultorio médico con varios profesionales de distintas especialidades atiende pacientes con cita previa. Hoy la programación se hace en una agenda física que produce sobrecupos y citas perdidas. Se requiere un sistema que organice la agenda y guarde el historial básico de cada consulta.

## 2. Objetivo
Construir un aplicativo que permita gestionar pacientes, médicos, sus horarios de atención y la programación de citas, con un registro básico de la consulta realizada, persistiendo todo en una BD relacional.

## 3. Requisitos funcionales mínimos
- **RF1**: Mantener registro de pacientes con datos demográficos básicos.
- **RF2**: Mantener registro de médicos, asociando cada uno a una o más especialidades.
- **RF3**: Configurar los horarios de atención de cada médico (día de semana, hora inicio, hora fin).
- **RF4**: Agendar, modificar y cancelar citas (paciente, médico, fecha, hora).
- **RF5**: Validar conflictos de horario al agendar (un médico no puede tener dos citas a la misma hora; un paciente tampoco).
- **RF6**: Registrar la consulta realizada (motivo, diagnóstico, observaciones, tratamiento recetado).
- **RF7**: Consultar el historial completo de consultas de un paciente.

## 4. Entidades sugeridas (guía, no MER cerrado)
- Paciente
- Médico
- Especialidad (relación N a M con Médico)
- HorarioAtención
- Cita
- Consulta (resultado de una cita atendida)
- Tratamiento / Medicamento recetado

> El estudiante debe diseñar el MER completo, identificar atributos, definir cardinalidades y normalizar hasta 3FN.

## 5. Entregables
- Aplicativo funcionando (lenguaje y tipo de UI libres: escritorio o web).
- Diagrama MER completo con PK, FK y cardinalidades.
- Script DDL completo (`.sql`) para MySQL o PostgreSQL.
- Datos de prueba (al menos 10 pacientes, 5 médicos, 4 especialidades, 25 citas en distintos estados y 15 consultas registradas).
- Breve README con instrucciones de instalación y ejecución.

## 6. Rúbrica de evaluación docente

| Criterio | Peso |
|---|---|
| MER completo, correcto y normalizado hasta 3FN (con PK, FK, cardinalidades) | 40% |
| Aplicativo funcionando y usando realmente la BD | 35% |
| Cumplimiento de los requisitos funcionales mínimos (RF1–RF7) | 20% |
| Sustentación clara del diseño de BD | 5% |

## 7. Ideas para diferenciar las dos versiones
- Vista calendario (semanal/mensual) vs lista de citas.
- Generación de un PDF resumen de la consulta.
- Búsqueda avanzada del historial por síntomas o diagnóstico.
- Recordatorios visibles de citas pendientes próximas.
- Manejo de estados de cita (programada, atendida, no asistió, cancelada).
