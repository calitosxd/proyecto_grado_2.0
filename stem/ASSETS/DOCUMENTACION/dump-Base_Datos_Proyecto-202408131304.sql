-- MySQL dump 10.13  Distrib 8.0.39, for Linux (x86_64)
--
-- Host: localhost    Database: Base_Datos_Proyecto
-- ------------------------------------------------------
-- Server version	8.0.39-0ubuntu0.20.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `rol`
--

DROP TABLE IF EXISTS `rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol` (
  `id_pk_rol` tinyint(1) NOT NULL,
  `nombre_rol` varchar(100) NOT NULL,
  PRIMARY KEY (`id_pk_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol`
--

LOCK TABLES `rol` WRITE;
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
INSERT INTO `rol` VALUES (0,'super admin'),(1,'administrativo'),(2,'docente'),(3,'estudiante'),(4,'invitado');
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sensores`
--

DROP TABLE IF EXISTS `sensores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sensores` (
  `sensores_id_pk` int NOT NULL AUTO_INCREMENT,
  `fecha_sensores` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `luxIntLdr_sensores` float DEFAULT NULL,
  `luxExtLdr_sensores` float DEFAULT NULL,
  `temIntDHT20_sensores` float DEFAULT NULL COMMENT 'Sensor de Temperatura DHT 20 Interior.',
  `humIntDHT20_sensores` float DEFAULT NULL COMMENT 'Sensor DHT20 interior de humedad.',
  `temExtDHT20_sensores` float DEFAULT NULL COMMENT 'Sensor DHT20 exterior de temperatura.',
  `humExtDHT20_sensores` float DEFAULT NULL COMMENT 'Sensor DHT20 exterior de humedad.',
  `humSue100` varchar(100) DEFAULT NULL,
  `humSue75` varchar(100) DEFAULT NULL,
  `humSue50` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`sensores_id_pk`)
) ENGINE=InnoDB AUTO_INCREMENT=280 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tabla donde se almacena los datos entregados por los sensores.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sensores`
--

LOCK TABLES `sensores` WRITE;
/*!40000 ALTER TABLE `sensores` DISABLE KEYS */;
/*!40000 ALTER TABLE `sensores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id_pk_usuario` bigint NOT NULL,
  `pass_usuario` varchar(100) NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `apellido_usuario` varchar(100) NOT NULL,
  `edad_usuario` int NOT NULL,
  `genero_usuario` varchar(100) NOT NULL,
  `telefono_usuario` varchar(100) NOT NULL,
  `direccion_usuario` varchar(100) NOT NULL,
  `ciudad_usuario` varchar(100) NOT NULL,
  `universidad_usuario` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `programa_usuario` varchar(100) NOT NULL,
  `correo_usuario` varchar(100) NOT NULL,
  `fecha_registro_usuario` date NOT NULL,
  `permiso_usuario_1` int NOT NULL,
  `estado_usuario` tinyint(1) NOT NULL,
  `id_pk_rol_fk` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_pk_usuario`),
  KEY `id_pk_rol_fk` (`id_pk_rol_fk`),
  CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_pk_rol_fk`) REFERENCES `rol` (`id_pk_rol`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (9874548,'s7muHlnJS1HfdvlTtlqWow==','victor hugo','hernandez pelaez',33,'masculino','3113893040','calle 11 # 2-69','cartago','Cotecnova','Ingenieria de Sistemas','victorhugohernandezpelaez@gmail.com','2022-12-31',19,1,0),(1007549874,'KImQdE9di3w27WMRSMuA3g==','Ana Sofía','Castillo Moya',20,'Femenino','3124952870','Calle 15a # 10-10','Cartago','Cotecnova','Ingenía de Sistemas','anacastillo@cotecnova.edu.co','2024-04-25',19,1,2);
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'Base_Datos_Proyecto'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-08-13 13:04:07