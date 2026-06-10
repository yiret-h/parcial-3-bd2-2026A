# Proyecto N°11 — Taller mecánico

## 1. Contexto
Un taller mecánico atiende vehículos de varios clientes para mantenimiento y reparación. Cada orden de trabajo requiere uno o más mecánicos asignados y consume repuestos del inventario. El dueño quiere saber cuánto se cobró, cuánto se gastó en repuestos y el historial de cada vehículo.

## 2. Objetivo
Construir un aplicativo que gestione clientes y sus vehículos, las órdenes de trabajo, los mecánicos asignados y el consumo de repuestos del inventario, con persistencia en una BD relacional.

## 3. Requisitos funcionales mínimos
- **RF1**: Mantener clientes y los vehículos asociados a cada uno (placa, modelo, año).
- **RF2**: Crear órdenes de trabajo (vehículo, problema reportado, fecha de entrada).
- **RF3**: Asignar uno o varios mecánicos a una orden con su rol o tarea.
- **RF4**: Mantener inventario de repuestos con stock y precio.
- **RF5**: Registrar los repuestos usados en una orden, descontándolos del inventario.
- **RF6**: Cerrar la orden con fecha de salida, mano de obra y costo total calculado.
- **RF7**: Consultar el historial completo de órdenes por vehículo.

## 4. Entidades sugeridas (guía, no MER cerrado)
- Cliente
- Vehículo
- Mecánico
- OrdenTrabajo
- AsignaciónMecánico (relación N a M entre Orden y Mecánico)
- Repuesto
- DetalleRepuesto (repuestos usados en una orden)

> El estudiante debe diseñar el MER completo, identificar atributos, definir cardinalidades y normalizar hasta 3FN.

## 5. Entregables
- Aplicativo funcionando (lenguaje y tipo de UI libres: escritorio o web).
- Diagrama MER completo con PK, FK y cardinalidades.
- Script DDL completo (`.sql`) para MySQL o PostgreSQL.
- Datos de prueba (al menos 10 clientes, 15 vehículos, 5 mecánicos, 20 órdenes y 30 repuestos en inventario).
- Breve README con instrucciones de instalación y ejecución.

## 6. Rúbrica de evaluación docente

| Criterio | Peso |
|---|---|
| MER completo, correcto y normalizado hasta 3FN (con PK, FK, cardinalidades) | 40% |
| Aplicativo funcionando y usando realmente la BD | 35% |
| Cumplimiento de los requisitos funcionales mínimos (RF1–RF7) | 20% |
| Sustentación clara del diseño de BD | 5% |

## 7. Ideas para diferenciar las dos versiones
- Diagnóstico estructurado (lista de chequeo) vs texto libre.
- Cotización previa al cliente antes de iniciar la orden.
- Roles internos (jefe de taller / mecánico junior).
- Estado de la orden visible al cliente (recibido, diagnosticado, en reparación, listo).
- Tiempo estimado vs tiempo real por orden.
