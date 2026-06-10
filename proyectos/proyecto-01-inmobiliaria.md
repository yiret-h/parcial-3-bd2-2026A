# Proyecto N°01 — Inmobiliaria de arrendamientos

## 1. Contexto
Una inmobiliaria pequeña administra inmuebles propios y de terceros (propietarios particulares que dejan su inmueble en administración). Atiende arrendatarios que firman contratos por periodos definidos y deben pagar un canon mensual. Hoy todo se lleva en papel y hojas de cálculo, lo que produce olvidos en el cobro, demoras en el cálculo de mora y dificultad para saber qué inmuebles están desocupados.

## 2. Objetivo
Construir un aplicativo que gestione inmuebles, propietarios, arrendatarios, contratos de arriendo y el cobro mensual de cuotas con su respectiva mora, persistiendo toda la información en una base de datos relacional (MySQL o PostgreSQL).

## 3. Requisitos funcionales mínimos
- **RF1**: Mantener catálogo de inmuebles (dirección, tipo, área, número de habitaciones, canon sugerido, estado: disponible / arrendado / mantenimiento).
- **RF2**: Registrar propietarios cuando el inmueble es de un tercero y vincularlos al inmueble que administran. Un propietario puede tener varios inmuebles.
- **RF3**: Registrar arrendatarios con datos personales, documento de identidad y codeudor opcional.
- **RF4**: Crear contratos de arrendamiento (inmueble, arrendatario, fecha inicio, fecha fin, canon mensual, valor del depósito, día de pago acordado).
- **RF5**: Generar las cuotas mensuales a cobrar para cada contrato vigente.
- **RF6**: Registrar pagos contra cuotas, indicando estado (pagada, parcial, vencida) y calculando interés de mora cuando aplique.
- **RF7**: Reporte de cartera (cuotas pendientes y en mora por arrendatario) y reporte de inmuebles desocupados.

## 4. Entidades sugeridas (guía, no MER cerrado)
- TipoInmueble (casa, apartamento, local, oficina)
- Inmueble
- Propietario (relación 1 a N con Inmueble)
- Arrendatario
- Codeudor (opcional, relación con Arrendatario o con Contrato)
- Contrato
- Cuota
- Pago

> El estudiante debe diseñar el MER completo, identificar atributos, definir cardinalidades y normalizar hasta 3FN.

## 5. Entregables
- Aplicativo funcionando (lenguaje y tipo de UI libres: escritorio o web).
- Diagrama MER completo con PK, FK y cardinalidades.
- Script DDL completo (`.sql`) que cree la BD en MySQL o PostgreSQL.
- Datos de prueba (al menos 15 inmuebles, 8 propietarios, 12 arrendatarios, 10 contratos vigentes y cuotas/pagos de varios meses, incluyendo casos con mora).
- Breve README con instrucciones de instalación y ejecución.

## 6. Rúbrica de evaluación docente

| Criterio | Peso |
|---|---|
| MER completo, correcto y normalizado hasta 3FN (con PK, FK, cardinalidades) | 40% |
| Aplicativo funcionando y usando realmente la BD (no datos simulados en memoria) | 35% |
| Cumplimiento de los requisitos funcionales mínimos (RF1–RF7) | 20% |
| Sustentación clara del diseño de BD | 5% |

## 7. Ideas para diferenciar las dos versiones
- Generación automática de cuotas mensuales (job/scheduler) vs creación manual al inicio del contrato.
- Estados del contrato más finos (vigente, próximo a vencer, finalizado, en cobro jurídico) y transición controlada.
- Renovación automática del contrato al vencer vs cierre y nuevo contrato.
- Liquidación final con devolución de depósito (descontando deudas pendientes).
- Manejo de gastos adicionales (administración, servicios) además del canon.
- Recordatorios visuales para cuotas próximas a vencer o vencidas.
