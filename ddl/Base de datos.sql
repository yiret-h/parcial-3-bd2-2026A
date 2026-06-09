-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: clinica_veterinaria
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS `clinica_veterinaria`;
USE `clinica_veterinaria`;


--
-- Table structure for table `carnet_vacunacion`
--

DROP TABLE IF EXISTS `carnet_vacunacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carnet_vacunacion` (
  `id_carnet` int NOT NULL AUTO_INCREMENT,
  `id_mascota` int NOT NULL,
  `id_vacuna` int NOT NULL,
  `fecha_aplicacion` date NOT NULL,
  `proxima_dosis` datetime DEFAULT NULL,
  PRIMARY KEY (`id_carnet`),
  KEY `id_mascota` (`id_mascota`),
  KEY `id_vacuna` (`id_vacuna`),
  CONSTRAINT `carnet_vacunacion_ibfk_1` FOREIGN KEY (`id_mascota`) REFERENCES `mascota` (`id_mascota`) ON DELETE CASCADE,
  CONSTRAINT `carnet_vacunacion_ibfk_2` FOREIGN KEY (`id_vacuna`) REFERENCES `vacuna` (`id_vacuna`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carnet_vacunacion`
--

LOCK TABLES `carnet_vacunacion` WRITE;
/*!40000 ALTER TABLE `carnet_vacunacion` DISABLE KEYS */;
INSERT INTO `carnet_vacunacion` VALUES (1,1,3,'2026-06-03','2027-06-02 00:00:00'),(2,1,1,'2026-06-04','2026-06-04 00:00:00'),(3,2,2,'2025-05-10','2026-05-10 00:00:00'),(4,3,1,'2025-06-15','2026-06-15 00:00:00'),(5,4,2,'2025-08-20','2026-08-20 00:00:00'),(6,5,3,'2025-09-01','2026-09-01 00:00:00'),(7,6,1,'2025-10-10','2026-10-10 00:00:00'),(8,2,1,'2025-11-11','2026-11-11 00:00:00'),(9,3,3,'2026-01-05','2027-01-05 00:00:00'),(10,4,1,'2026-02-28','2027-02-28 00:00:00');
/*!40000 ALTER TABLE `carnet_vacunacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cita`
--

DROP TABLE IF EXISTS `cita`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cita` (
  `id_cita` int NOT NULL AUTO_INCREMENT,
  `fecha_hora` datetime NOT NULL,
  `motivo_previo` varchar(255) DEFAULT NULL,
  `estado` enum('Pendiente','Completada','Cancelada') DEFAULT 'Pendiente',
  `id_mascota` int NOT NULL,
  `id_veterinario` int NOT NULL,
  PRIMARY KEY (`id_cita`),
  KEY `id_mascota` (`id_mascota`),
  KEY `id_veterinario` (`id_veterinario`),
  CONSTRAINT `cita_ibfk_1` FOREIGN KEY (`id_mascota`) REFERENCES `mascota` (`id_mascota`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cita_ibfk_2` FOREIGN KEY (`id_veterinario`) REFERENCES `veterinario` (`id_veterinario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cita`
--

LOCK TABLES `cita` WRITE;
/*!40000 ALTER TABLE `cita` DISABLE KEYS */;
INSERT INTO `cita` VALUES (2,'2026-06-09 17:30:00','Vacunación','Pendiente',2,1),(3,'2026-06-02 17:02:00','Revision general','Pendiente',2,1),(4,'2026-06-02 20:31:00','Vacunación','Completada',1,1),(5,'2026-06-10 12:36:00','Revisión/Control','Pendiente',3,4),(6,'2026-06-03 12:40:00','Revisión/Control','Completada',4,3),(7,'2026-06-04 10:00:00','Vacunación','Completada',1,1);
/*!40000 ALTER TABLE `cita` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consulta`
--

DROP TABLE IF EXISTS `consulta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consulta` (
  `id_consulta` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `diagnostico` text NOT NULL,
  `observaciones` text,
  `id_mascota` int NOT NULL,
  `id_veterinario` int NOT NULL,
  PRIMARY KEY (`id_consulta`),
  KEY `id_mascota` (`id_mascota`),
  KEY `id_veterinario` (`id_veterinario`),
  CONSTRAINT `consulta_ibfk_1` FOREIGN KEY (`id_mascota`) REFERENCES `mascota` (`id_mascota`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `consulta_ibfk_2` FOREIGN KEY (`id_veterinario`) REFERENCES `veterinario` (`id_veterinario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consulta`
--

LOCK TABLES `consulta` WRITE;
/*!40000 ALTER TABLE `consulta` DISABLE KEYS */;
INSERT INTO `consulta` VALUES (1,'2026-06-09','Vacunación','Refuerzo contra la triple Felina','',3,1),(2,'2026-06-03','Vacunación','Paciente sano , Se le aplica la vacuna triple felina','Guardar reposo y buena hidratacion',1,1),(3,'2026-06-03','Revisión/Control','Paciente en recuperacion','Ejercitarlo con caminatas de 20 minutos cada dia',4,3),(4,'2026-06-04','Vacunación','Control general y vacunacion','Reposo , buena hibratacion',1,1),(5,'2025-05-10','Vacunación','Vacunación Parvovirus','Ninguna',2,1),(6,'2025-06-15','Vacunación','Vacunación Antirrábica','Observar por 24 horas',3,1),(7,'2025-08-20','Revisión/Control','Sano','Ninguna',4,3),(8,'2025-09-01','Enfermedad','Infección leve','Tratamiento de 5 días',5,4),(9,'2025-10-10','Revisión/Control','Control de peso','Dieta estricta',6,2),(10,'2025-11-11','Vacunación','Vacunación Antirrábica','Ninguna',2,1),(11,'2026-01-05','Control','Revisión dental','Limpieza requerida en un mes',3,5),(12,'2026-02-28','Vacunación','Vacunación Antirrábica','Ninguna',4,1),(13,'2026-03-10','Enfermedad','Dermatitis','Aplicar crema',2,2),(14,'2026-03-15','Control','Evolución favorable','Alta médica',5,4),(15,'2026-04-01','Consulta General','Chequeo rutinario','Todo en orden',1,1),(16,'2026-04-12','Problema gástrico','Gastritis','Dieta blanda y medicación',6,4),(17,'2026-05-05','Control de garrapatas','Presencia de ectoparásitos','Aplicación de antiparasitario',3,2),(18,'2026-05-18','Dolor articular','Posible displasia','Rayos X sugeridos',4,3),(19,'2026-05-25','Limpieza dental','Sarro','Profilaxis realizada',5,5),(20,'2026-06-01','Consulta General','Sano','Mantener buenos hábitos',2,1);
/*!40000 ALTER TABLE `consulta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_tratamiento`
--

DROP TABLE IF EXISTS `detalle_tratamiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_tratamiento` (
  `id_tratamiento` int NOT NULL,
  `id_medicamento` int NOT NULL,
  `dosis` varchar(100) NOT NULL,
  `frecuencia` varchar(100) NOT NULL,
  PRIMARY KEY (`id_tratamiento`,`id_medicamento`),
  KEY `id_medicamento` (`id_medicamento`),
  CONSTRAINT `detalle_tratamiento_ibfk_1` FOREIGN KEY (`id_tratamiento`) REFERENCES `tratamiento` (`id_tratamiento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detalle_tratamiento_ibfk_2` FOREIGN KEY (`id_medicamento`) REFERENCES `medicamento` (`id_medicamento`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_tratamiento`
--

LOCK TABLES `detalle_tratamiento` WRITE;
/*!40000 ALTER TABLE `detalle_tratamiento` DISABLE KEYS */;
INSERT INTO `detalle_tratamiento` VALUES (2,1,'1 Tableta','Cada 12 horas'),(3,3,'3 Gotas','Cada 24 horas'),(4,4,'1 Cápsula','Cada 24 horas en ayunas'),(5,2,'1 ml','Cada 24 horas'),(2,5,'1 Tableta','Cada 8 horas');
/*!40000 ALTER TABLE `detalle_tratamiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dueño`
--

DROP TABLE IF EXISTS `dueño`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dueño` (
  `id_dueño` int NOT NULL AUTO_INCREMENT,
  `documento_identidad` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id_dueño`),
  UNIQUE KEY `documento_identidad` (`documento_identidad`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dueño`
--

LOCK TABLES `dueño` WRITE;
/*!40000 ALTER TABLE `dueño` DISABLE KEYS */;
INSERT INTO `dueño` VALUES (1,'1096203233','yiret arenas','3144019616','',''),(2,'37548965','Martha  Forero','325985208','',''),(4,'100652836','Marlly Rodriguez','314159623','','las granjas'),(5,'375896123','Tatiana Forero','3001524596','Tati89756@gmail.com','Diag.54 manz 3 casa 45'),(6,'964582365','Melany Forero','3215489625','britney67@gmail.com','Calle56 #33-89'),(7,'88997766','Juan Perez','3111222333','juanperez@example.com','Calle 123'),(8,'55443322','Maria Gomez','3222333444','mariagomez@example.com','Avenida 456'),(9,'11223344','Carlos Ruiz','3333444555','carlosruiz@example.com','Carrera 789');
/*!40000 ALTER TABLE `dueño` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `especie`
--

DROP TABLE IF EXISTS `especie`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `especie` (
  `id_especie` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id_especie`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `especie`
--

LOCK TABLES `especie` WRITE;
/*!40000 ALTER TABLE `especie` DISABLE KEYS */;
INSERT INTO `especie` VALUES (4,'Conejos'),(2,'Gato'),(5,'Hámsteres'),(3,'Loros'),(1,'Perro'),(6,'Tortugas');
/*!40000 ALTER TABLE `especie` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fotografia_mascota`
--

DROP TABLE IF EXISTS `fotografia_mascota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fotografia_mascota` (
  `id_fotografia` int NOT NULL AUTO_INCREMENT,
  `ruta_archivo` varchar(255) NOT NULL,
  `fecha_subida` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_mascota` int NOT NULL,
  PRIMARY KEY (`id_fotografia`),
  KEY `id_mascota` (`id_mascota`),
  CONSTRAINT `fotografia_mascota_ibfk_1` FOREIGN KEY (`id_mascota`) REFERENCES `mascota` (`id_mascota`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fotografia_mascota`
--

LOCK TABLES `fotografia_mascota` WRITE;
/*!40000 ALTER TABLE `fotografia_mascota` DISABLE KEYS */;
/*!40000 ALTER TABLE `fotografia_mascota` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mascota`
--

DROP TABLE IF EXISTS `mascota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mascota` (
  `id_mascota` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `sexo` enum('Macho','Hembra') NOT NULL,
  `id_dueño` int NOT NULL,
  `id_raza` int NOT NULL,
  PRIMARY KEY (`id_mascota`),
  KEY `id_dueño` (`id_dueño`),
  KEY `id_raza` (`id_raza`),
  CONSTRAINT `mascota_ibfk_1` FOREIGN KEY (`id_dueño`) REFERENCES `dueño` (`id_dueño`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mascota_ibfk_2` FOREIGN KEY (`id_raza`) REFERENCES `raza` (`id_raza`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mascota`
--

LOCK TABLES `mascota` WRITE;
/*!40000 ALTER TABLE `mascota` DISABLE KEYS */;
INSERT INTO `mascota` VALUES (1,'Miringa','2018-07-16','Hembra',1,7),(2,'Lulú','2024-12-11','Hembra',2,21),(3,'Chocolate','2025-02-05','Hembra',1,5),(4,'Edgar','2023-06-25','Macho',4,1),(5,'Fabio','2023-06-12','Macho',5,20),(6,'Sol','2022-04-05','Hembra',6,13),(7,'Luna','2021-01-10','Hembra',7,3),(8,'Rex','2020-03-20','Macho',7,4),(9,'Max','2019-05-15','Macho',8,6),(10,'Bella','2022-08-30','Hembra',8,7),(11,'Rocky','2023-11-12','Macho',9,1),(12,'Coco','2021-07-07','Macho',9,8),(13,'Toby','2018-09-25','Macho',1,12),(14,'Simba','2020-12-01','Macho',2,5),(15,'Nala','2022-02-14','Hembra',4,16);
/*!40000 ALTER TABLE `mascota` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medicamento`
--

DROP TABLE IF EXISTS `medicamento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medicamento` (
  `id_medicamento` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `presentacion` varchar(50) NOT NULL,
  PRIMARY KEY (`id_medicamento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medicamento`
--

LOCK TABLES `medicamento` WRITE;
/*!40000 ALTER TABLE `medicamento` DISABLE KEYS */;
INSERT INTO `medicamento` VALUES (1,'Amoxicilina','Tabletas 500mg'),(2,'Meloxicam','Suspensión 1.5mg/ml'),(3,'Ivermectina','Gotas 1%'),(4,'Omeprazol','Cápsulas 20mg'),(5,'Cefalexina','Tabletas 250mg');
/*!40000 ALTER TABLE `medicamento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raza`
--

DROP TABLE IF EXISTS `raza`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `raza` (
  `id_raza` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `id_especie` int NOT NULL,
  PRIMARY KEY (`id_raza`),
  KEY `id_especie` (`id_especie`),
  CONSTRAINT `raza_ibfk_1` FOREIGN KEY (`id_especie`) REFERENCES `especie` (`id_especie`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raza`
--

LOCK TABLES `raza` WRITE;
/*!40000 ALTER TABLE `raza` DISABLE KEYS */;
INSERT INTO `raza` VALUES (1,'Labrador',1),(2,'Pug',1),(3,'Pastor Alemán',1),(4,'Criollo / Mestizo',1),(5,'Persa',2),(6,'Siamés',2),(7,'Criollo / Mestizo',2),(8,'Guacamaya',3),(9,'Perico Australiano',3),(10,'Cacatúa',3),(11,'Loro Real',3),(12,'Cabeza de León',4),(13,'Angora',4),(14,'Belier',4),(15,'Ruso',5),(16,'Sirio',5),(17,'Roborovski',5),(18,'Morrocoy',6),(19,'De Orejas Rojas',6),(20,'Carey',6),(21,'Guaro',3);
/*!40000 ALTER TABLE `raza` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tratamiento`
--

DROP TABLE IF EXISTS `tratamiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tratamiento` (
  `id_tratamiento` int NOT NULL AUTO_INCREMENT,
  `id_consulta` int NOT NULL,
  `descripcion` text NOT NULL,
  `duracion_dias` int NOT NULL,
  PRIMARY KEY (`id_tratamiento`),
  KEY `id_consulta` (`id_consulta`),
  CONSTRAINT `tratamiento_ibfk_1` FOREIGN KEY (`id_consulta`) REFERENCES `consulta` (`id_consulta`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tratamiento`
--

LOCK TABLES `tratamiento` WRITE;
/*!40000 ALTER TABLE `tratamiento` DISABLE KEYS */;
INSERT INTO `tratamiento` VALUES (1,3,'Realizar actividad fisica 20minutos diarios ,evitar alimentos pesado y mantener buena hidratacion',15),(2,8,'Antibiótico para controlar la infección',7),(3,13,'Crema tópica para tratar la dermatitis',10),(4,16,'Tratamiento protector gástrico para la gastritis',5),(5,18,'Antiinflamatorio para el dolor articular',14);
/*!40000 ALTER TABLE `tratamiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('Administrador','Veterinario','Recepcionista') NOT NULL,
  `id_veterinario` int DEFAULT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `username` (`username`),
  KEY `id_veterinario` (`id_veterinario`),
  CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_veterinario`) REFERENCES `veterinario` (`id_veterinario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'admin','$2y$10$n7c9BG1Lo5U9rxORDQtUo.WEBJT01/0QW1edcsuCrkGFi08EmSs6.','Administrador',NULL,'Activo');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vacuna`
--

DROP TABLE IF EXISTS `vacuna`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vacuna` (
  `id_vacuna` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id_vacuna`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vacuna`
--

LOCK TABLES `vacuna` WRITE;
/*!40000 ALTER TABLE `vacuna` DISABLE KEYS */;
INSERT INTO `vacuna` VALUES (1,'Antirrábica','Protege contra el virus de la rabia. Aplicación anual.'),(2,'Parvovirus','Protección esencial para cachorros contra parvovirosis canina.'),(3,'Triple Felina','Protege contra panleucopenia, herpesvirus y calicivirus.');
/*!40000 ALTER TABLE `vacuna` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `veterinario`
--

DROP TABLE IF EXISTS `veterinario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `veterinario` (
  `id_veterinario` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `especialidad` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_veterinario`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `veterinario`
--

LOCK TABLES `veterinario` WRITE;
/*!40000 ALTER TABLE `veterinario` DISABLE KEYS */;
INSERT INTO `veterinario` VALUES (1,'Dayanna romero','3259862078','Medicina General'),(2,'Adriana Arango','3015478957','Dermatología '),(3,'Juan Galan','3215846952','Fisioterapeuta'),(4,'Alexandra Hernández','3144026125','Etología'),(5,'Lina Camacho','3174849523','odontologia');
/*!40000 ALTER TABLE `veterinario` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-05 17:34:31
