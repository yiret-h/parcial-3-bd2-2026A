# Proyecto N°05 — Venta de boletos de cine

## 1. Contexto
Un cine local con varias salas vende entradas en taquilla. Quiere controlar mejor la programación de funciones, evitar sobreventa de butacas y entender cuáles películas y horarios generan más ingresos.

## 2. Objetivo
Construir un aplicativo que permita gestionar películas, salas, funciones, butacas y la venta de boletos asignando butacas específicas a cada cliente, con persistencia en una BD relacional.

## 3. Requisitos funcionales mínimos
- **RF1**: Mantener catálogo de películas con género, duración y clasificación.
- **RF2**: Mantener salas con su número de butacas (asignadas por fila y número).
- **RF3**: Programar funciones (película + sala + fecha + hora + tarifa).
- **RF4**: Mostrar la disponibilidad de butacas para una función específica.
- **RF5**: Vender boletos seleccionando una o varias butacas para una función; impedir vender una butaca ya vendida.
- **RF6**: Registrar clientes (opcional, para programas de descuento o fidelidad).
- **RF7**: Reporte de funciones más vendidas y recaudación por día.

## 4. Entidades sugeridas (guía, no MER cerrado)
- Película
- Género
- Sala
- Butaca
- Función
- Boleto
- Cliente (opcional)

> El estudiante debe diseñar el MER completo, identificar atributos, definir cardinalidades y normalizar hasta 3FN.

## 5. Entregables
- Aplicativo funcionando (lenguaje y tipo de UI libres: escritorio o web).
- Diagrama MER completo con PK, FK y cardinalidades.
- Script DDL completo (`.sql`) para MySQL o PostgreSQL.
- Datos de prueba (al menos 6 películas, 3 salas, 10 funciones programadas y 30 boletos vendidos).
- Breve README con instrucciones de instalación y ejecución.

## 6. Rúbrica de evaluación docente

| Criterio | Peso |
|---|---|
| MER completo, correcto y normalizado hasta 3FN (con PK, FK, cardinalidades) | 40% |
| Aplicativo funcionando y usando realmente la BD | 35% |
| Cumplimiento de los requisitos funcionales mínimos (RF1–RF7) | 20% |
| Sustentación clara del diseño de BD | 5% |

## 7. Ideas para diferenciar las dos versiones
- Selector visual de butacas (mapa de la sala) vs lista de butacas disponibles.
- Combos (boleto + snack) como módulo extra.
- Promociones por día (martes 2x1, miércoles infantil, etc.).
- Tarifa diferencial por edad (niño, adulto, adulto mayor).
- Programa de cliente frecuente con puntos.
