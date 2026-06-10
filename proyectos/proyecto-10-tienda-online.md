# Proyecto N°10 — Tienda online básica

## 1. Contexto
Una tienda de productos artesanales quiere empezar a vender en línea. Necesita un catálogo navegable, un carrito por cliente, registro de pedidos y un seguimiento básico del estado del pedido. El administrador atiende los pedidos desde su panel interno.

## 2. Objetivo
Construir un aplicativo que permita a un cliente registrar su cuenta, navegar productos, armar un carrito y generar pedidos; y que permita al administrador gestionar el catálogo y el estado de los pedidos, con persistencia en una BD relacional.

## 3. Requisitos funcionales mínimos
- **RF1**: Mantener catálogo de productos con categorías, precio, stock y descripción.
- **RF2**: Registrar clientes con datos básicos y al menos una dirección de envío.
- **RF3**: Carrito de compras por cliente (agregar, quitar y modificar cantidad de productos).
- **RF4**: Generar un pedido a partir del carrito, descontando stock al confirmar.
- **RF5**: Cambiar el estado del pedido en el tiempo (recibido, en preparación, enviado, entregado, cancelado).
- **RF6**: Mostrar el historial de pedidos por cliente.
- **RF7**: Reporte de productos más vendidos y de ventas por categoría.

## 4. Entidades sugeridas (guía, no MER cerrado)
- Producto
- Categoría
- Cliente
- Dirección (relación 1 a N con Cliente)
- Carrito
- ItemCarrito
- Pedido y DetallePedido
- EstadoPedido

> El estudiante debe diseñar el MER completo, identificar atributos, definir cardinalidades y normalizar hasta 3FN.

## 5. Entregables
- Aplicativo funcionando (lenguaje y tipo de UI libres: escritorio o web).
- Diagrama MER completo con PK, FK y cardinalidades.
- Script DDL completo (`.sql`) para MySQL o PostgreSQL.
- Datos de prueba (al menos 20 productos, 6 categorías, 10 clientes, 15 pedidos en distintos estados).
- Breve README con instrucciones de instalación y ejecución.

## 6. Rúbrica de evaluación docente

| Criterio | Peso |
|---|---|
| MER completo, correcto y normalizado hasta 3FN (con PK, FK, cardinalidades) | 40% |
| Aplicativo funcionando y usando realmente la BD | 35% |
| Cumplimiento de los requisitos funcionales mínimos (RF1–RF7) | 20% |
| Sustentación clara del diseño de BD | 5% |

## 7. Ideas para diferenciar las dos versiones
- UI web vistosa (CSS pulido) vs UI funcional minimalista.
- Múltiples direcciones de envío con dirección por defecto.
- Cupones o descuentos por monto mínimo.
- Pago simulado integrado vs solo registro del pedido.
- Calificaciones/reseñas de productos por clientes.
