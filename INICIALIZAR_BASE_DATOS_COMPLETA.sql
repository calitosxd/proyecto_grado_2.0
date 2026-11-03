-- ==============================================================================
-- SCRIPT DE INICIALIZACIÓN COMPLETA - SISTEMA INVERNADERO COTECNOVA + TINYML
-- Compatible para ejecutar directamente en MySQL Workbench
-- ==============================================================================

-- 1. CREACIÓN DE LA BASE DE DATOS
CREATE DATABASE IF NOT EXISTS `Base_Datos_Proyecto` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `Base_Datos_Proyecto`;

-- ------------------------------------------------------------------------------
-- 2. CREACIÓN Y PERMISOS DEL USUARIO DE LA APLICACIÓN
-- ------------------------------------------------------------------------------
CREATE USER IF NOT EXISTS 'victorhugohp'@'localhost' IDENTIFIED BY '@1982vhHP2026@';
CREATE USER IF NOT EXISTS 'victorhugohp'@'127.0.0.1' IDENTIFIED BY '@1982vhHP2026@';
GRANT ALL PRIVILEGES ON `Base_Datos_Proyecto`.* TO 'victorhugohp'@'localhost';
GRANT ALL PRIVILEGES ON `Base_Datos_Proyecto`.* TO 'victorhugohp'@'127.0.0.1';
FLUSH PRIVILEGES;

-- ------------------------------------------------------------------------------
-- 3. TABLA: rol
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `usuario`;
DROP TABLE IF EXISTS `rol`;
CREATE TABLE `rol` (
  `id_pk_rol` tinyint(1) NOT NULL,
  `nombre_rol` varchar(100) NOT NULL,
  PRIMARY KEY (`id_pk_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `rol` (`id_pk_rol`, `nombre_rol`) VALUES 
(0, 'super admin'),
(1, 'administrativo'),
(2, 'docente'),
(3, 'estudiante'),
(4, 'invitado');

-- ------------------------------------------------------------------------------
-- 4. TABLA: usuario
-- ------------------------------------------------------------------------------
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
  `universidad_usuario` varchar(100) NOT NULL,
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

-- Insertar usuarios iniciales:
-- Contraseña encriptada corresponde al acceso de prueba del sistema
INSERT INTO `usuario` VALUES 
(9874548, 's7muHlnJS1HfdvlTtlqWow==', 'Victor Hugo', 'Hernández Peláez', 33, 'Masculino', '3113893040', 'Calle 11 # 2-69', 'Cartago', 'COTECNOVA', 'Ingeniería de Sistemas', 'victorhugohernandezpelaez@gmail.com', '2024-01-01', 19, 1, 0),
(1007549874, 'KImQdE9di3w27WMRSMuA3g==', 'Ana Sofía', 'Castillo Moya', 20, 'Femenino', '3124952870', 'Calle 15a # 10-10', 'Cartago', 'COTECNOVA', 'Ingeniería de Sistemas', 'anacastillo@cotecnova.edu.co', '2024-04-25', 19, 1, 2);

-- ------------------------------------------------------------------------------
-- 5. TABLA: sensores
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `sensores`;
CREATE TABLE `sensores` (
  `sensores_id_pk` int NOT NULL AUTO_INCREMENT,
  `fecha_sensores` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `luxIntLdr_sensores` float DEFAULT NULL,
  `luxExtLdr_sensores` float DEFAULT NULL,
  `temIntDHT20_sensores` float DEFAULT NULL COMMENT 'Sensor Temperatura DHT20 Interior (°C)',
  `humIntDHT20_sensores` float DEFAULT NULL COMMENT 'Sensor Humedad DHT20 Interior (%)',
  `temExtDHT20_sensores` float DEFAULT NULL COMMENT 'Sensor Temperatura DHT20 Exterior (°C)',
  `humExtDHT20_sensores` float DEFAULT NULL COMMENT 'Sensor Humedad DHT20 Exterior (%)',
  `humSue100` varchar(100) DEFAULT NULL COMMENT 'Humedad Suelo Surco 100%',
  `humSue75` varchar(100) DEFAULT NULL COMMENT 'Humedad Suelo Surco 75%',
  `humSue50` varchar(100) DEFAULT NULL COMMENT 'Humedad Suelo Surco 50%',
  PRIMARY KEY (`sensores_id_pk`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Registro de lecturas de sensores ambientales';

-- Insertar datos de prueba para sensores
INSERT INTO `sensores` (`fecha_sensores`, `luxIntLdr_sensores`, `luxExtLdr_sensores`, `temIntDHT20_sensores`, `humIntDHT20_sensores`, `temExtDHT20_sensores`, `humExtDHT20_sensores`, `humSue100`, `humSue75`, `humSue50`) VALUES
(NOW() - INTERVAL 4 HOUR, 450.5, 980.2, 24.2, 78.5, 25.1, 74.2, '88.2', '71.5', '46.3'),
(NOW() - INTERVAL 3 HOUR, 510.0, 1120.4, 25.8, 72.1, 26.9, 69.8, '84.0', '68.0', '42.1'),
(NOW() - INTERVAL 2 HOUR, 620.8, 1350.0, 27.4, 68.3, 28.5, 65.0, '79.5', '63.2', '37.8'),
(NOW() - INTERVAL 1 HOUR, 580.2, 1200.7, 26.5, 70.0, 27.2, 67.5, '75.1', '59.0', '33.4'),
(NOW(), 490.0, 1050.3, 25.0, 74.6, 26.0, 71.2, '92.0', '74.8', '51.2');

-- ------------------------------------------------------------------------------
-- 6. TABLA: ia (BITÁCORA DE DECISIONES TINYML)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `ia`;
CREATE TABLE `ia` (
  `ia_id_pk` int NOT NULL AUTO_INCREMENT,
  `fecha_ia` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `registro_ia` text NOT NULL COMMENT 'Diagnóstico o motivo generado por el modelo TinyML',
  `valvula_ia` tinyint NOT NULL COMMENT 'Código binario de electroválvulas (0 a 7)',
  PRIMARY KEY (`ia_id_pk`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Bitácora de decisiones TinyML y activación de electroválvulas';

-- Insertar registros de prueba para la Bitácora de TinyML
INSERT INTO `ia` (`fecha_ia`, `registro_ia`, `valvula_ia`) VALUES
(NOW() - INTERVAL 4 HOUR, 'Condiciones hídricas estables en los 3 surcos. Temperatura y humedad en rango de confort.', 0),
(NOW() - INTERVAL 3 HOUR, 'Humedad en surco 50% descendió a 42.1% (Umbral mínimo 45%). Activando riego localizado.', 1),
(NOW() - INTERVAL 2 HOUR, 'Estrés hídrico detectado en surco 50% (37.8%) y surco 75% (63.2%) por alta radiación térmica.', 3),
(NOW() - INTERVAL 1 HOUR, 'Disminución crítica de humedad en surco 50% (33.4%). Riego focalizado de emergencia activado.', 1),
(NOW(), 'Niveles hídricos restaurados satisfactoriamente tras ciclo de micro-aspersión. Sistema en reposo.', 0);

-- ==============================================================================
-- FIN DEL SCRIPT DE INICIALIZACIÓN
-- ==============================================================================
