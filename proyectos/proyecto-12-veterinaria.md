# Proyecto N°12 — Veterinaria

## 1. Contexto
Una clínica veterinaria atiende mascotas (perros, gatos, aves, etc.) de varios dueños. Brinda consultas médicas, aplica vacunas y prescribe tratamientos. Hoy el carnet de vacunación y el historial se llevan en papel, lo que dificulta el seguimiento y los recordatorios.

## 2. Objetivo
Construir un aplicativo que gestione dueños, mascotas, consultas, aplicación de vacunas y tratamientos prescritos, manteniendo un historial completo por mascota, con persistencia en una BD relacional.

## 3. Requisitos funcionales mínimos
- **RF1**: Registrar dueños y las mascotas asociadas a cada uno.
- **RF2**: Mantener catálogo de especies y razas (raza ligada a su especie).
- **RF3**: Registrar consultas (mascota, veterinario, motivo, diagnóstico, observaciones).
- **RF4**: Registrar vacunas aplicadas (vacuna del catálogo, fecha de aplicación, próxima fecha sugerida).
- **RF5**: Registrar tratamientos con su duración y medicamentos asociados.
- **RF6**: Mantener catálogo de medicamentos disponibles.
- **RF7**: Consultar el historial completo de una mascota (consultas, vacunas, tratamientos).

## 4. Entidades sugeridas (guía, no MER cerrado)
- Dueño
- Mascota
- Especie
- Raza
- Veterinario
- Consulta
- Vacuna (catálogo)
- AplicaciónVacuna
- Tratamiento
- Medicamento (relación N a M con Tratamiento)

> El estudiante debe diseñar el MER completo, identificar atributos, definir cardinalidades y normalizar hasta 3FN.

## 5. Entregables
- Aplicativo funcionando (lenguaje y tipo de UI libres: escritorio o web).
- Diagrama MER completo con PK, FK y cardinalidades.
- Script DDL completo (`.sql`) para MySQL o PostgreSQL.
- Datos de prueba (al menos 8 dueños, 15 mascotas, 4 especies, 10 razas, 20 consultas, 10 vacunas aplicadas y 5 tratamientos).
- Breve README con instrucciones de instalación y ejecución.

## 6. Rúbrica de evaluación docente

| Criterio | Peso |
|---|---|
| MER completo, correcto y normalizado hasta 3FN (con PK, FK, cardinalidades) | 40% |
| Aplicativo funcionando y usando realmente la BD | 35% |
| Cumplimiento de los requisitos funcionales mínimos (RF1–RF7) | 20% |
| Sustentación clara del diseño de BD | 5% |

## 7. Ideas para diferenciar las dos versiones
- Carnet de vacunación imprimible (PDF) vs solo en pantalla.
- Recordatorios automáticos de próximas vacunas.
- Calendario/agenda de citas como módulo extra.
- Galería fotográfica de la mascota.
- Reportes por especie/raza más atendida.
