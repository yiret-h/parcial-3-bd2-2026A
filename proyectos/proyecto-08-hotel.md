# Proyecto N°08 — Hotel pequeño

## 1. Contexto
Un hotel pequeño cuenta con alrededor de 30 habitaciones de distintos tipos (sencilla, doble, suite). Atiende huéspedes con reserva previa y también huéspedes que llegan sin reserva (walk-in). Necesita controlar la disponibilidad por rango de fechas, los consumos adicionales (lavandería, restaurante interno) y la cuenta total.

## 2. Objetivo
Construir un aplicativo que gestione habitaciones, huéspedes, reservas, check-in/check-out y servicios adicionales asociados a la estancia, con persistencia en una BD relacional.

## 3. Requisitos funcionales mínimos
- **RF1**: Mantener catálogo de habitaciones (número, tipo, tarifa por noche, estado).
- **RF2**: Registrar huéspedes con datos personales y documento.
- **RF3**: Crear reservas con fecha de entrada, fecha de salida, habitación, huésped principal y acompañantes.
- **RF4**: Validar disponibilidad de la habitación en el rango de fechas seleccionado.
- **RF5**: Hacer check-in y check-out, cambiando el estado de la reserva y de la habitación.
- **RF6**: Registrar consumos/servicios adicionales asociados a la reserva (catálogo de servicios).
- **RF7**: Generar la cuenta total al cierre y un reporte de ocupación por rango de fechas.

## 4. Entidades sugeridas (guía, no MER cerrado)
- TipoHabitación
- Habitación
- Huésped
- Reserva
- Acompañante (relación N a M con Reserva)
- Servicio (catálogo)
- ConsumoServicio (servicios consumidos durante una reserva)

> El estudiante debe diseñar el MER completo, identificar atributos, definir cardinalidades y normalizar hasta 3FN.

## 5. Entregables
- Aplicativo funcionando (lenguaje y tipo de UI libres: escritorio o web).
- Diagrama MER completo con PK, FK y cardinalidades.
- Script DDL completo (`.sql`) para MySQL o PostgreSQL.
- Datos de prueba (al menos 20 habitaciones, 15 huéspedes, 20 reservas en distintos estados y varios servicios consumidos).
- Breve README con instrucciones de instalación y ejecución.

## 6. Rúbrica de evaluación docente

| Criterio | Peso |
|---|---|
| MER completo, correcto y normalizado hasta 3FN (con PK, FK, cardinalidades) | 40% |
| Aplicativo funcionando y usando realmente la BD | 35% |
| Cumplimiento de los requisitos funcionales mínimos (RF1–RF7) | 20% |
| Sustentación clara del diseño de BD | 5% |

## 7. Ideas para diferenciar las dos versiones
- Vista calendario de ocupación (Gantt) vs lista de reservas.
- Tarifas por temporada alta/baja.
- Programa de huésped frecuente con descuentos.
- Facturación electrónica simulada (PDF).
- Manejo de habitaciones con estado "mantenimiento" y bloqueo.
