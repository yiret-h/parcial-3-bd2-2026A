# Proyecto N°02 — Farmacia de barrio

## 1. Contexto
Una farmacia de barrio vende medicamentos, productos de aseo y elementos de cuidado personal. La dueña pierde control del inventario porque varios productos llegan en distintos lotes con fechas de vencimiento diferentes. Además, necesita registrar bien las ventas diarias y las compras a proveedores.

## 2. Objetivo
Construir un aplicativo que gestione el catálogo de productos, los lotes con fecha de vencimiento, las ventas y las compras a proveedores, alertando sobre stock bajo y productos próximos a vencer, con persistencia en una BD relacional.

## 3. Requisitos funcionales mínimos
- **RF1**: Mantener catálogo de productos (medicamentos, aseo, etc.) con categoría y presentación.
- **RF2**: Registrar lotes con fecha de vencimiento, cantidad inicial y cantidad disponible.
- **RF3**: Registrar ventas con cabecera y detalle (varios productos por venta), descontando del stock del lote más antiguo (FIFO).
- **RF4**: Registrar compras a proveedores que ingresan nuevos lotes al inventario.
- **RF5**: Alertar productos con stock bajo (umbral configurable) y lotes próximos a vencer (en menos de N días).
- **RF6**: Reporte de ventas por rango de fechas y producto.
- **RF7**: Búsqueda de productos por nombre, categoría o principio activo.

## 4. Entidades sugeridas (guía, no MER cerrado)
- Producto
- Categoría
- Lote
- Proveedor
- Compra (cabecera) y DetalleCompra
- Venta (cabecera) y DetalleVenta
- Cliente (opcional)

> El estudiante debe diseñar el MER completo, identificar atributos, definir cardinalidades y normalizar hasta 3FN.

## 5. Entregables
- Aplicativo funcionando (lenguaje y tipo de UI libres: escritorio o web).
- Diagrama MER completo con PK, FK y cardinalidades.
- Script DDL completo (`.sql`) para MySQL o PostgreSQL.
- Datos de prueba (al menos 15 productos, 5 proveedores, 3 lotes por producto y 20 ventas).
- Breve README con instrucciones de instalación y ejecución.

## 6. Rúbrica de evaluación docente

| Criterio | Peso |
|---|---|
| MER completo, correcto y normalizado hasta 3FN (con PK, FK, cardinalidades) | 40% |
| Aplicativo funcionando y usando realmente la BD | 35% |
| Cumplimiento de los requisitos funcionales mínimos (RF1–RF7) | 20% |
| Sustentación clara del diseño de BD | 5% |

## 7. Ideas para diferenciar las dos versiones
- Manejo estricto FIFO vs selección manual del lote a descontar.
- Semáforo visual de vencimientos (rojo/amarillo/verde) vs alertas en lista.
- Impresión de ticket de venta vs solo registro en pantalla.
- Módulo de devolución de productos.
- Multimoneda o descuentos por promoción.
