
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `cargadores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cargadores` (
  `id` varchar(50) NOT NULL,
  `idPuntoCarga` int NOT NULL,
  `potenciaKilowatts` decimal(8,2) DEFAULT '0.00',
  `tipoConector` varchar(100) NOT NULL,
  `tipoCargador` varchar(100) NOT NULL,
  `estadoUso` varchar(50) DEFAULT 'Disponible',
  `estadoOperativo` varchar(100) NOT NULL,
  `precioKwh` decimal(10,2) DEFAULT '0.00',
  `precioHora` decimal(10,2) DEFAULT '0.00',
  `potencia_kw` decimal(5,2) NOT NULL DEFAULT '7.40',
  `estado` enum('libre','ocupado','roto','pausado') DEFAULT 'libre',
  `fila_espera` int NOT NULL DEFAULT '0',
  `libre_estimado` datetime DEFAULT NULL,
  `ultimo_reporte` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_punto_carga` (`idPuntoCarga`),
  CONSTRAINT `fk_punto_carga` FOREIGN KEY (`idPuntoCarga`) REFERENCES `puntos_carga` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `configuracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuracion` (
  `clave` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `puntos_carga`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `puntos_carga` (
  `id` int NOT NULL AUTO_INCREMENT,
  `visible` tinyint(1) DEFAULT '1',
  `tipoUsuario` varchar(100) DEFAULT 'Público',
  `latitud` decimal(10,8) NOT NULL,
  `longitud` decimal(11,8) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `ciudadYDepartamento` varchar(255) NOT NULL,
  `id_usuario` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=512600 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `recibos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recibos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reserva_id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `propietario_id` int NOT NULL,
  `cargador_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `minutos` int NOT NULL,
  `kwh` decimal(8,2) NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `comision_pct` decimal(5,2) NOT NULL,
  `comision` decimal(10,2) NOT NULL,
  `monto_propietario` decimal(10,2) NOT NULL,
  `tarjeta_ult4` char(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_recibo_reserva` (`reserva_id`),
  KEY `idx_rec_cliente` (`cliente_id`),
  KEY `idx_rec_propietario` (`propietario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reportes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cargador_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `usuario_id` int NOT NULL,
  `tipo` enum('operativo','ocupacion','fila') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rep_cargador` (`cargador_id`,`tipo`,`fecha`),
  KEY `idx_rep_usuario` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reportes_cargadores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportes_cargadores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_cargador` int NOT NULL,
  `estado_reportado` varchar(50) NOT NULL,
  `coordenada_lat` decimal(10,8) DEFAULT NULL,
  `coordenada_lng` decimal(11,8) DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reservas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `cargador_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'en_curso',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `vehiculo_id` int DEFAULT NULL,
  `soc_inicial` tinyint unsigned DEFAULT NULL,
  `inicio_real` datetime DEFAULT NULL,
  `fin_real` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_res_cargador` (`cargador_id`,`estado`),
  KEY `idx_res_usuario` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `simulaciones_carga`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `simulaciones_carga` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_cargador` int NOT NULL,
  `id_vehiculo` int NOT NULL,
  `potencia_aplicada_kw` decimal(5,2) NOT NULL,
  `capacidad_bateria_kwh` decimal(6,2) NOT NULL,
  `porcentaje_inicial` decimal(3,2) NOT NULL,
  `porcentaje_final` decimal(3,2) NOT NULL,
  `tiempo_estimado_horas` decimal(6,2) NOT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_usuario` enum('particular','empresa','moderador','administrador') NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `seudonimo` varchar(50) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `estado` varchar(50) DEFAULT 'activo',
  `cedula_identidad` varchar(20) DEFAULT NULL,
  `foto_cedula` varchar(255) DEFAULT NULL,
  `rut` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vehiculos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vehiculos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `marca_modelo` varchar(100) NOT NULL,
  `puerto_carga` varchar(50) NOT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `capacidad_kwh` decimal(6,2) NOT NULL DEFAULT '40.00',
  `potencia_max_kw` decimal(6,2) NOT NULL DEFAULT '50.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

