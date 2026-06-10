# Proyecto N°06 — Control de parqueadero

## 1. Contexto
Un parqueadero cubierto con aproximadamente 100 espacios atiende clientes ocasionales (cobro por hora) y clientes mensuales (con cupo asignado y tarifa fija). Actualmente todo se anota a mano, lo que ha causado errores de cobro, pérdida de tickets y dificultad para conocer la ocupación en tiempo real.

## 2. Objetivo
Construir un aplicativo que controle entradas y salidas de vehículos, calcule el cobro por tiempo de permanencia, gestione clientes mensuales con cupo y produzca reportes de ingresos, con persistencia en una BD relacional.

## 3. Requisitos funcionales mínimos
- **RF1**: Registrar la entrada de un vehículo (placa, tipo, fecha-hora de ingreso).
- **RF2**: Registrar la salida y calcular automáticamente la tarifa por tiempo de permanencia.
- **RF3**: Configurar tarifas por tipo de vehículo (carro, moto, bicicleta, etc.).
- **RF4**: Gestionar clientes mensuales con cupo asignado, fecha de inicio y fecha de fin de la mensualidad.
- **RF5**: Validar disponibilidad de espacios antes de aceptar un nuevo ingreso.
- **RF6**: Reporte de ingresos por día y por mes, separando ocasionales y mensuales.
- **RF7**: Consultar en cualquier momento qué vehículos están actualmente dentro del parqueadero.

## 4. Entidades sugeridas (guía, no MER cerrado)
- TipoVehículo
- Tarifa
- Vehículo
- Ingreso (registro de entrada/salida)
- ClienteMensual
- Espacio (opcional, si modela espacios numerados)

> El estudiante debe diseñar el MER completo, identificar atributos, definir cardinalidades y normalizar hasta 3FN.

## 5. Entregables
- Aplicativo funcionando (lenguaje y tipo de UI libres: escritorio o web).
- Diagrama MER completo con PK, FK y cardinalidades.
- Script DDL completo (`.sql`) para MySQL o PostgreSQL.
- Datos de prueba (al menos 4 tipos de vehículo, 10 clientes mensuales activos, 50 ingresos registrados con sus salidas).
- Breve README con instrucciones de instalación y ejecución.

## 6. Rúbrica de evaluación docente

| Criterio | Peso |
|---|---|
| MER completo, correcto y normalizado hasta 3FN (con PK, FK, cardinalidades) | 40% |
| Aplicativo funcionando y usando realmente la BD | 35% |
| Cumplimiento de los requisitos funcionales mínimos (RF1–RF7) | 20% |
| Sustentación clara del diseño de BD | 5% |

## 7. Ideas para diferenciar las dos versiones
- Cupos numerados (espacio específico) vs cupo abierto.
- Tarifas escalonadas (primera hora cara, siguientes más baratas).
- Tarjeta/QR de identificación para clientes mensuales.
- Alertas visuales de capacidad llena.
- Tarifa nocturna o tarifa de día festivo.
