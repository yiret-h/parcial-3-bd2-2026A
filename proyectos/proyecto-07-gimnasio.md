# Proyecto N°07 — Gestión de gimnasio

## 1. Contexto
Un gimnasio de barrio ofrece entrenamiento libre y clases dirigidas (spinning, yoga, funcional, etc.). Vende membresías mensuales, trimestrales y anuales. Pierde control de quién está al día con la membresía, cuántos afiliados asisten realmente y cuántos cupos tiene cada clase.

## 2. Objetivo
Construir un aplicativo que gestione afiliados, planes de membresía, clases dirigidas con cupos, inscripciones y asistencia diaria al gimnasio, con persistencia en una BD relacional.

## 3. Requisitos funcionales mínimos
- **RF1**: Registrar afiliados con datos básicos y datos físicos opcionales (estatura, peso).
- **RF2**: Gestionar planes de membresía (nombre, duración en días, precio, beneficios).
- **RF3**: Vender una membresía a un afiliado, calculando fecha inicio y fecha fin.
- **RF4**: Gestionar clases dirigidas (instructor, día/hora, cupo máximo).
- **RF5**: Permitir que los afiliados se inscriban a clases respetando el cupo.
- **RF6**: Registrar la asistencia diaria al gimnasio (entrada del afiliado).
- **RF7**: Alertar membresías próximas a vencer (en menos de N días).

## 4. Entidades sugeridas (guía, no MER cerrado)
- Afiliado
- Plan
- Membresía (venta concreta de un plan a un afiliado)
- Instructor
- Clase
- Inscripción (relación N a M entre Afiliado y Clase)
- Asistencia

> El estudiante debe diseñar el MER completo, identificar atributos, definir cardinalidades y normalizar hasta 3FN.

## 5. Entregables
- Aplicativo funcionando (lenguaje y tipo de UI libres: escritorio o web).
- Diagrama MER completo con PK, FK y cardinalidades.
- Script DDL completo (`.sql`) para MySQL o PostgreSQL.
- Datos de prueba (al menos 15 afiliados, 4 planes, 6 clases dirigidas, 25 asistencias y 20 inscripciones).
- Breve README con instrucciones de instalación y ejecución.

## 6. Rúbrica de evaluación docente

| Criterio | Peso |
|---|---|
| MER completo, correcto y normalizado hasta 3FN (con PK, FK, cardinalidades) | 40% |
| Aplicativo funcionando y usando realmente la BD | 35% |
| Cumplimiento de los requisitos funcionales mínimos (RF1–RF7) | 20% |
| Sustentación clara del diseño de BD | 5% |

## 7. Ideas para diferenciar las dos versiones
- Marcación por código/QR vs registro manual de asistencia.
- Gráficos de asistencia mensual del afiliado.
- Módulo de seguimiento físico (medidas periódicas).
- Renovación automática sugerida cerca del vencimiento.
- Bloqueo del ingreso cuando la membresía esté vencida.
