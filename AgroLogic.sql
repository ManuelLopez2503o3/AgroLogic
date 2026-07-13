-- MySQL dump 10.13  Distrib 9.7.1, for macos26.4 (arm64)
--
-- Host: localhost    Database: agrologic
-- ------------------------------------------------------
-- Server version	9.7.1

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
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ 'c452b2ec-780b-11f1-b75a-2f10c29fd347:1-316';

--
-- Table structure for table `actuadores`
--

DROP TABLE IF EXISTS `actuadores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `actuadores` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `device_name` varchar(100) NOT NULL,
  `mode` enum('automatico','manual') NOT NULL DEFAULT 'automatico',
  `updated_by` bigint DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `bomba_status` tinyint(1) NOT NULL DEFAULT '0',
  `luces_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `actuadores_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actuadores`
--

LOCK TABLES `actuadores` WRITE;
/*!40000 ALTER TABLE `actuadores` DISABLE KEYS */;
INSERT INTO `actuadores` VALUES (1,'Sistema AgroLogic','manual',NULL,'2026-07-11 19:29:59',0,1,'2026-07-11 14:41:00');
/*!40000 ALTER TABLE `actuadores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comandos`
--

DROP TABLE IF EXISTS `comandos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comandos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `modo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `umbral_alto` double DEFAULT NULL,
  `umbral_bajo` double DEFAULT NULL,
  `enviado` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comandos`
--

LOCK TABLES `comandos` WRITE;
/*!40000 ALTER TABLE `comandos` DISABLE KEYS */;
INSERT INTO `comandos` VALUES (1,'manual','rojo',NULL,NULL,1,'2026-07-08 16:07:35','2026-07-08 16:12:15');
/*!40000 ALTER TABLE `comandos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuraciones`
--

DROP TABLE IF EXISTS `configuraciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuraciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `configuraciones_clave_unique` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuraciones`
--

LOCK TABLES `configuraciones` WRITE;
/*!40000 ALTER TABLE `configuraciones` DISABLE KEYS */;
INSERT INTO `configuraciones` VALUES (1,'temperatura_maxima','30.5','2026-07-09 04:32:03','2026-07-11 14:03:15');
/*!40000 ALTER TABLE `configuraciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incidentes_fuego`
--

DROP TABLE IF EXISTS `incidentes_fuego`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `incidentes_fuego` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha_inicio` timestamp NOT NULL,
  `fecha_fin` timestamp NULL DEFAULT NULL,
  `pico_temperatura` double NOT NULL,
  `humedad_final` double DEFAULT NULL,
  `duracion_segundos` int DEFAULT NULL,
  `reporte_ia` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidentes_fuego`
--

LOCK TABLES `incidentes_fuego` WRITE;
/*!40000 ALTER TABLE `incidentes_fuego` DISABLE KEYS */;
INSERT INTO `incidentes_fuego` VALUES (1,'2026-07-08 22:43:59','2026-07-08 22:44:41',55,60,-42,'**Reporte Técnico: Incidente Modo Fuego - AgroLogic**\n\nFecha: [Fecha del incidente]  \nPico de Temperatura: 55 °C  \nDuración del Episodio: -0.7 minutos  \nHumedad Final Registrada: 60%  \n\nDurante el reciente episodio de Modo Fuego, se registró un pico de temperatura de 55 °C por un breve periodo negativo de -0.7 minutos, lo que indica una reducción rápida de condiciones adversas. La humedad final alcanzó un 60%, sugiriendo que el sistema de riego automático mantuvo un nivel adecuado de humedad, contribuyendo a la contención del evento. \n\nSe concluye que la respuesta del riego automático fue efectiva en la mitigación de este incidente, garantizando la estabilidad del ambiente del invernadero. Se recomienda continuar monitoreando y ajustando los protocolos de riego en futuros episodios.  \n\nAtentamente,  \n[Nombre del Técnico]  \n[Posición]  \nAgroLogic',0,'2026-07-08 22:43:59','2026-07-08 22:44:45'),(2,'2026-07-08 23:18:40','2026-07-08 23:18:56',55,60,16,'**Reporte Técnico: Incidente de Modo Fuego**\n\nFecha: [Fecha del incidente]  \nSistema: AgroLogic  \n\nDurante el reciente episodio de Modo Fuego, se registró un pico de temperatura de 55 °C con una duración de 0.3 minutos. Al finalizar el evento, la humedad final del invernadero se situó en 60%. \n\nEl corto período de alta temperatura sugiere que el fenómeno fue súbito, pero la rápida respuesta del sistema de riego automático parece haber sido efectiva, ya que la humedad se mantuvo en niveles adecuados para mitigar el riesgo de daño a las plantas. Se recomienda mantener monitoreo constante y realizar un análisis de las condiciones que precedieron al episodio para prevenir futuros incidentes similares. \n\nAtentamente,  \n[Tu Nombre]  \n[Cargo]  \nAgroLogic',0,'2026-07-08 23:18:40','2026-07-08 23:19:00'),(3,'2026-07-09 03:43:36','2026-07-09 03:43:58',55,60,22,'Durante el episodio de Modo Fuego, se registró un pico de temperatura de 55 °C con una duración de 0.4 minutos. A pesar de la alta temperatura, la humedad final registrada fue del 60%, lo que sugiere que el sistema de riego automático actuó de manera efectiva para mitigar el calor extremo. La rápida respuesta del riego pudo haber estabilizado las condiciones ambientales, permitiendo que los niveles de humedad se mantuvieran en un rango aceptable. Este comportamiento indica que el sistema de control ambiental está funcionando adecuadamente, protegiendo así la salud de las plantas frente a eventos críticos de temperatura.',0,'2026-07-09 03:43:36','2026-07-09 03:44:00');
/*!40000 ALTER TABLE `incidentes_fuego` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000001_create_cache_table',1),(2,'0001_01_01_000002_create_jobs_table',1),(3,'2026_07_08_094214_create_comandos_table',2),(4,'2026_07_08_162225_create_incidente_fuegos_table',3),(5,'2026_07_08_221026_create_configuracions_table',4),(6,'2026_07_11_083605_add_status_a_actuadores',5),(7,'2026_07_11_083912_add_created_at_a_actuadores',6),(8,'2026_07_11_135202_make_duracion_segundos_nullable_a_reportes_contingencia',7),(9,'2026_07_11_135528_make_columnas_nullable_a_reportes_contingencia',8);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportes_contingencia`
--

DROP TABLE IF EXISTS `reportes_contingencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reportes_contingencia` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `tipo_evento` varchar(100) NOT NULL,
  `temperatura_pico` decimal(5,2) NOT NULL,
  `duracion_segundos` int DEFAULT NULL,
  `diagnostico_ia` text,
  `recomendacion_ia` text,
  `fecha_siniestro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportes_contingencia`
--

LOCK TABLES `reportes_contingencia` WRITE;
/*!40000 ALTER TABLE `reportes_contingencia` DISABLE KEYS */;
INSERT INTO `reportes_contingencia` VALUES (1,'incendio',52.00,NULL,NULL,NULL,'2026-07-11 19:56:08');
/*!40000 ALTER TABLE `reportes_contingencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('1TlObCKqZMJ0smT0FIBGODQVsTqKu5kjBnrVRDg6',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.125.0 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidm5Db0NhOEx6a0s0VlFyNDE1ZjR2NjE2a01Dbkxtb09lNDB6cGZpdSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1781909585),('76qTu1Xad6YjVHe1Wxm9T0XyzfiCnlQ6HyOCvpnp',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.125.1 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTUdwREFEZ1BtUWw0RGtkb0k4T3RDTmtXdVMyNVZEeWFKT3hMM0VQOCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1782244770),('9cQiJIRJOUbFiwLha15zqzLHsQ32OPVYd1kNxhjV',NULL,'127.0.0.1','PostmanRuntime/7.54.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiME5UMnR1UUh1MVpKWG90cHZ1UEtsYzV5Q0xPWTAwWVZkdmhycVc0cSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1781405921),('cfv26Syewlsyl4UtbjnxiSsAZZTgg4SYliYY7CdL',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.127.0 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiM2hqZkdKazF5VTRSYzV0YksxVms5ZDBmV2U1RkZBb1RVdWRBWUJXcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1783215672),('M8HwSLULYL8dITpm868UHzmF9eAJQdaVO0Kp9787',NULL,'127.0.0.1','PostmanRuntime/7.54.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQko5dWgxa2g1aWpiaFIzdEI2NnNaMkhuTDNKUmF3NGNOM2E3aTB4cyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1781406556),('oOuCfcEe7NJPiuPGXEJMPpq2p9haUc3e46qoxsrr',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.127.0 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieXZqV0QxdlFodWZNQ3BCZWZtbVBkampONjlKWEhBVEN3UDdMaWoydSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1783140000),('T9XC4QYh3nqmsWyADi9q7uG1dZJYVxDM9qzdTtE3',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.122.1 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieHBaQXhBWG5IWkd3cWpiRWJTVFptNGphS0Z2aHJiMmZGWTIzR3A0bSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1781407918),('tD70VZZ1UYeJVrbm7nwZXl7OWTV12APOHsjvhUeC',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.124.2 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidFp1eGxEQlY5MUhWcjBZY0xwUnZFd0VEQVNIUk9UdjBYelVYYmF6dSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1781756581),('tWazMKgiIAzSWBJaja9WUPNkzkZfo8JDlCXTTIbP',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.124.2 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoib3NnQVBUaFAxT3pBem5Cd0hQYlRYWmhZQTA1MUtndUNSa1M2NVA0WCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1781796124),('wVSPfq3LsKj9dmeWO90j4cURGjeMG49Ja3IHuN6x',8,'127.0.0.1','PostmanRuntime/7.54.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoibWNJNktHUVk3NEJYaDVsOEtKZHI0a2R4ZGtocFpHeFFhVEZReGh4aiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1783618822);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `key_name` varchar(50) NOT NULL,
  `value_data` varchar(50) NOT NULL,
  `description` text,
  `updated_by` bigint DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_name` (`key_name`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telemetrias`
--

DROP TABLE IF EXISTS `telemetrias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telemetrias` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `temperatura` decimal(5,2) NOT NULL,
  `humedad` decimal(5,2) NOT NULL,
  `estado` enum('normal','alerta','modo_fuego') NOT NULL DEFAULT 'normal',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telemetrias`
--

LOCK TABLES `telemetrias` WRITE;
/*!40000 ALTER TABLE `telemetrias` DISABLE KEYS */;
INSERT INTO `telemetrias` VALUES (1,28.50,64.20,'normal','2026-06-20 05:00:40'),(2,28.50,47.90,'normal','2026-06-28 00:30:50'),(3,23.00,59.30,'normal','2026-07-06 01:05:16'),(4,23.00,59.30,'normal','2026-07-06 01:05:21'),(5,23.00,59.30,'normal','2026-07-06 01:05:26'),(6,23.00,59.20,'normal','2026-07-06 01:05:31'),(7,23.00,59.20,'normal','2026-07-06 01:05:36'),(8,23.00,59.20,'normal','2026-07-06 01:05:41'),(9,23.00,59.20,'normal','2026-07-06 01:05:46'),(10,23.00,59.30,'normal','2026-07-06 01:05:51'),(11,23.00,59.30,'normal','2026-07-06 01:05:56'),(12,23.00,59.30,'normal','2026-07-06 01:06:01'),(13,23.00,59.30,'normal','2026-07-06 01:06:06'),(14,23.00,59.30,'normal','2026-07-06 01:06:11'),(15,23.00,59.30,'normal','2026-07-06 01:06:16'),(16,23.00,59.30,'normal','2026-07-06 01:06:21'),(17,23.00,59.30,'normal','2026-07-06 01:06:26'),(18,23.00,59.30,'normal','2026-07-06 01:06:31'),(19,23.00,59.30,'normal','2026-07-06 01:06:36'),(20,23.00,58.50,'normal','2026-07-06 01:24:24'),(21,23.00,59.00,'normal','2026-07-06 01:25:24'),(22,23.00,59.00,'normal','2026-07-06 01:26:24'),(23,23.00,59.20,'normal','2026-07-06 01:27:24'),(24,23.00,58.50,'normal','2026-07-06 01:28:24'),(25,23.00,58.80,'normal','2026-07-06 01:29:24'),(26,23.00,58.90,'normal','2026-07-06 01:30:24'),(27,23.00,59.00,'normal','2026-07-06 01:31:24'),(28,23.10,58.80,'normal','2026-07-06 01:32:24'),(29,23.00,59.00,'normal','2026-07-06 01:33:24'),(30,23.10,59.00,'normal','2026-07-06 01:34:24'),(31,23.10,58.90,'normal','2026-07-06 01:35:24'),(32,23.10,58.80,'normal','2026-07-06 01:36:24'),(33,22.90,57.30,'normal','2026-07-06 02:22:52'),(34,22.80,57.50,'normal','2026-07-06 02:23:52'),(35,22.80,57.40,'normal','2026-07-06 02:24:52'),(36,22.90,58.10,'normal','2026-07-06 02:25:52'),(37,22.80,58.50,'normal','2026-07-06 02:26:52'),(38,22.90,56.90,'normal','2026-07-05 20:43:53'),(39,22.90,56.90,'normal','2026-07-05 20:44:53'),(40,23.00,56.60,'normal','2026-07-05 20:45:52'),(41,23.00,56.90,'normal','2026-07-05 20:46:53'),(42,22.90,57.00,'normal','2026-07-05 20:47:52'),(43,22.90,56.70,'normal','2026-07-05 20:48:53'),(44,22.90,57.40,'normal','2026-07-05 20:49:52'),(45,22.90,57.30,'normal','2026-07-05 20:50:53'),(46,22.90,56.90,'normal','2026-07-05 20:51:52'),(47,22.90,56.60,'normal','2026-07-05 20:52:53'),(48,22.90,56.60,'normal','2026-07-05 20:53:53'),(49,22.90,56.60,'normal','2026-07-05 20:54:53'),(50,22.90,56.40,'normal','2026-07-05 20:55:53'),(51,23.00,56.60,'normal','2026-07-05 20:56:53'),(52,23.00,56.80,'normal','2026-07-05 20:57:53'),(53,23.00,56.30,'normal','2026-07-05 20:58:53'),(54,23.00,56.70,'normal','2026-07-05 20:59:53'),(55,23.00,56.50,'normal','2026-07-05 21:00:53'),(56,23.00,56.40,'normal','2026-07-05 21:01:53'),(57,23.00,56.50,'normal','2026-07-05 21:02:53'),(58,23.00,57.10,'normal','2026-07-05 21:03:53'),(59,23.00,56.90,'normal','2026-07-05 21:04:53'),(60,23.00,56.90,'normal','2026-07-05 21:05:53'),(61,23.00,56.90,'normal','2026-07-05 21:06:53'),(62,23.00,56.90,'normal','2026-07-05 21:07:53'),(63,22.90,57.20,'normal','2026-07-05 21:13:36'),(64,22.80,58.30,'normal','2026-07-05 21:24:24'),(65,22.80,58.30,'normal','2026-07-05 21:24:53'),(66,22.70,57.80,'normal','2026-07-05 21:33:54'),(67,22.30,63.20,'normal','2026-07-05 22:38:05'),(68,22.50,62.40,'normal','2026-07-05 22:46:53'),(69,22.50,62.50,'normal','2026-07-05 22:48:53'),(70,22.50,62.20,'normal','2026-07-05 22:53:53'),(71,22.50,61.90,'normal','2026-07-05 22:55:53'),(72,22.50,61.90,'normal','2026-07-05 22:56:53'),(73,22.50,61.50,'normal','2026-07-05 22:57:53'),(74,22.50,61.30,'normal','2026-07-05 22:59:53'),(75,22.50,61.20,'normal','2026-07-05 23:00:53'),(76,22.50,61.20,'normal','2026-07-05 23:01:53'),(77,22.60,60.90,'normal','2026-07-05 23:03:53'),(78,22.60,60.80,'normal','2026-07-05 23:06:53'),(79,22.60,60.90,'normal','2026-07-05 23:07:53'),(80,22.60,60.80,'normal','2026-07-05 23:08:53'),(81,22.40,60.70,'normal','2026-07-05 23:10:53'),(82,22.40,61.30,'normal','2026-07-05 23:13:53'),(83,22.40,61.50,'normal','2026-07-05 23:14:53'),(84,22.50,61.20,'normal','2026-07-05 23:15:53'),(85,22.50,61.10,'normal','2026-07-05 23:17:53'),(86,22.60,60.90,'normal','2026-07-05 23:18:53'),(87,22.60,60.80,'normal','2026-07-05 23:19:53'),(88,22.60,60.70,'normal','2026-07-05 23:20:53'),(89,22.50,60.60,'normal','2026-07-05 23:21:53'),(90,22.50,60.70,'normal','2026-07-05 23:22:53'),(91,22.50,60.60,'normal','2026-07-05 23:23:53'),(92,22.60,60.60,'normal','2026-07-05 23:24:53'),(93,22.50,60.40,'normal','2026-07-05 23:26:53'),(94,22.60,60.60,'normal','2026-07-05 23:27:53'),(95,22.60,60.50,'normal','2026-07-05 23:28:53'),(96,22.50,60.40,'normal','2026-07-05 23:29:53'),(97,22.50,60.30,'normal','2026-07-05 23:30:53'),(98,22.60,60.40,'normal','2026-07-05 23:31:53'),(99,22.50,60.50,'normal','2026-07-05 23:32:53'),(100,22.50,60.60,'normal','2026-07-05 23:34:53'),(101,22.50,60.50,'normal','2026-07-05 23:38:53'),(102,22.50,61.20,'normal','2026-07-05 23:41:53'),(103,22.60,61.00,'normal','2026-07-05 23:42:53'),(104,22.70,61.10,'normal','2026-07-05 23:46:53'),(105,22.70,61.20,'normal','2026-07-05 23:47:53'),(106,22.70,61.30,'normal','2026-07-05 23:49:53'),(107,22.70,61.50,'normal','2026-07-05 23:51:53'),(108,22.70,61.40,'normal','2026-07-05 23:53:53'),(109,22.70,61.20,'normal','2026-07-05 23:54:53'),(110,22.70,63.20,'normal','2026-07-06 00:05:44'),(111,22.70,63.50,'normal','2026-07-06 00:06:53'),(112,22.70,63.20,'normal','2026-07-06 00:07:53'),(113,22.70,63.60,'normal','2026-07-06 00:08:53'),(114,22.70,63.70,'normal','2026-07-06 00:09:53'),(115,22.80,63.30,'normal','2026-07-06 00:10:53'),(116,22.80,63.40,'normal','2026-07-06 00:11:53'),(117,22.70,63.60,'normal','2026-07-06 00:12:53'),(118,22.70,64.10,'normal','2026-07-06 00:14:06'),(119,22.60,64.00,'normal','2026-07-06 00:14:53'),(120,22.60,64.00,'normal','2026-07-06 00:15:53'),(121,22.70,63.70,'normal','2026-07-06 00:16:53'),(122,22.70,63.40,'normal','2026-07-06 00:18:36'),(123,22.70,63.20,'normal','2026-07-06 00:18:53'),(124,55.00,40.00,'modo_fuego','2026-07-08 22:31:37'),(125,55.00,40.00,'modo_fuego','2026-07-08 22:32:19'),(126,55.00,40.00,'modo_fuego','2026-07-08 22:32:50'),(127,55.00,40.00,'modo_fuego','2026-07-08 22:38:08'),(128,55.00,40.00,'modo_fuego','2026-07-08 22:39:35'),(129,55.00,40.00,'modo_fuego','2026-07-08 22:43:59'),(130,25.00,60.00,'normal','2026-07-08 22:44:41'),(131,55.00,40.00,'modo_fuego','2026-07-08 23:18:39'),(132,25.00,60.00,'normal','2026-07-08 23:18:56'),(133,55.00,40.00,'modo_fuego','2026-07-09 03:43:36'),(134,25.00,60.00,'normal','2026-07-09 03:43:58'),(135,29.00,50.00,'alerta','2026-07-09 04:33:05');
/*!40000 ALTER TABLE `telemetrias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('administrador','operador') NOT NULL DEFAULT 'operador',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (4,'Alejandro Lopez','alejandro@test.com','$2y$12$9Y927ZMjnhGoH1uGaxD2W.Ih5K3U2dtt61e6NmDI9uz4xS0oCq8HW','operador',NULL,'2026-06-18 00:09:54','2026-07-11 14:13:54'),(5,'Admin AgroLogic','admin@agrologic.com','$2y$12$VtKMhsLsA/XBsDijztwTJuKJ5XoLqPT373fCFuylC/.enFe0yGqRq','administrador',NULL,'2026-06-18 10:28:09','2026-06-18 10:28:09'),(6,'Angelica','angelica@test.com','$2y$12$QccR6SywrpSiqkA5hzuf.uQ4x/kR9NPR9n/GpIec8oPT1NbYC7GDG','operador',NULL,'2026-06-23 22:18:50','2026-06-23 22:18:50'),(7,'Gomez Gallagert','Gomez@test.com','$2y$12$oNnGARhgbpbpvGdWDR3wLOvwM5ANJz8WWhMJ7FA5EqH7Hm0AvT36a','administrador',NULL,'2026-06-23 22:49:17','2026-06-23 16:52:25'),(8,'margarita','margarita@test.com','$2y$12$J/7U65zybtjapX2obrAgqeMLvOu7DciEqlZbvaMnp8FgtvRNQkqyG','administrador',NULL,'2026-06-24 02:15:56','2026-07-08 16:21:52'),(9,'Manuel Lopez','Alejandrogood@gmail.com','$2y$12$Q0MEL9mzSkfc.civ2cQuUOjJadkCha49e7wCvaUGadGXfy7wq2MSy','operador',NULL,'2026-07-04 11:11:19','2026-07-04 11:11:19'),(12,'Jkaren','jkaren@test.com','$2y$12$cnGsLi3EjLimt1o6yHeSc.BRpjIs0C5aAV39iSiSa8pB3BXKPFNee','operador',NULL,'2026-07-09 04:30:25','2026-07-11 13:54:58'),(13,'Prueba','prueba@test.com','$2y$12$XDLBLU5wGaRUCmkPePYCZ.l72Nx0gbO0DYMyuYuFdCbe4hH4mNxea','operador',NULL,'2026-07-09 17:16:07','2026-07-09 17:16:07');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'agrologic'
--
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-13 16:05:20
