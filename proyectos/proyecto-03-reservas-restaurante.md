# Proyecto N°03 — Reservas de restaurante

## 1. Contexto
Un restaurante de tamaño mediano con alrededor de 20 mesas atiende clientes por reserva previa y también clientes que llegan directamente. El restaurante tiene problemas de doble reserva, no sabe con claridad la ocupación de cada franja horaria y no tiene control de las cuentas por mesa.

## 2. Objetivo
Construir un aplicativo que permita gestionar reservas de mesas, visualizar la disponibilidad por día y hora, y registrar las órdenes de consumo asociadas a cada mesa o reserva, con persistencia en una BD relacional.

## 3. Requisitos funcionales mínimos
- **RF1**: Mantener catálogo de mesas (número, capacidad, ubicación interna).
- **RF2**: Registrar clientes con datos básicos.
- **RF3**: Crear, modificar y cancelar reservas con fecha, hora, mesa, cliente y número de personas.
- **RF4**: Validar al crear una reserva que no haya conflicto de horario en la misma mesa.
- **RF5**: Mantener carta del restaurante con categorías (entradas, principales, bebidas, etc.) y precios.
- **RF6**: Crear una orden asociada a una mesa (o reserva), agregando platos y bebidas con cantidad.
- **RF7**: Reporte de ocupación por día y de mesas más reservadas.

## 4. Entidades sugeridas (guía, no MER cerrado)
- Mesa
- Cliente
- Reserva
- Mesero (opcional)
- Plato / Producto
- Categoría
- Orden (cabecera) y DetalleOrden

> El estudiante debe diseñar el MER completo, identificar atributos, definir cardinalidades y normalizar hasta 3FN.

## 5. Entregables
- Aplicativo funcionando (lenguaje y tipo de UI libres: escritorio o web).
- Diagrama MER completo con PK, FK y cardinalidades.
- Script DDL completo (`.sql`) para MySQL o PostgreSQL.
- Datos de prueba (al menos 15 mesas, 10 clientes, 20 reservas y 10 órdenes con detalle).
- Breve README con instrucciones de instalación y ejecución.

## 6. Rúbrica de evaluación docente

| Criterio | Peso |
|---|---|
| MER completo, correcto y normalizado hasta 3FN (con PK, FK, cardinalidades) | 40% |
| Aplicativo funcionando y usando realmente la BD | 35% |
| Cumplimiento de los requisitos funcionales mínimos (RF1–RF7) | 20% |
| Sustentación clara del diseño de BD | 5% |

## 7. Ideas para diferenciar las dos versiones
- Vista gráfica con plano del restaurante vs lista textual de mesas.
- Reservas online (formulario público) vs solo internas.
- Cálculo automático de propina sugerida.
- Estados de la orden (recibida, en cocina, servida, pagada).
- Manejo de divisiones de cuenta entre comensales.
