-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: wowmart
-- ------------------------------------------------------
-- Server version	8.4.3

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
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `set_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_foreign` (`user_id`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attribute_values`
--

DROP TABLE IF EXISTS `attribute_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attribute_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` bigint unsigned NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attribute_values_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `attribute_values_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attribute_values`
--

LOCK TABLES `attribute_values` WRITE;
/*!40000 ALTER TABLE `attribute_values` DISABLE KEYS */;
INSERT INTO `attribute_values` VALUES (1,1,'Small','2026-10-17 18:48:22','2026-10-17 18:48:22'),(2,1,'Medium','2026-10-17 18:48:22','2026-10-17 18:48:22'),(3,1,'Large','2026-10-17 18:48:23','2026-10-17 18:48:23'),(4,2,'1m','2026-10-17 18:48:23','2026-10-17 18:48:23'),(5,2,'2m','2026-10-17 18:48:23','2026-10-17 18:48:23'),(6,2,'5m','2026-10-17 18:48:23','2026-10-17 18:48:23'),(7,3,'32GB','2026-10-17 18:48:23','2026-10-17 18:48:23'),(8,3,'64GB','2026-10-17 18:48:24','2026-10-17 18:48:24'),(9,3,'128GB','2026-10-17 18:48:24','2026-10-17 18:48:24');
/*!40000 ALTER TABLE `attribute_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attributes`
--

DROP TABLE IF EXISTS `attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attributes`
--

LOCK TABLES `attributes` WRITE;
/*!40000 ALTER TABLE `attributes` DISABLE KEYS */;
INSERT INTO `attributes` VALUES (1,'Size','2026-10-17 18:48:22','2026-10-17 18:48:22'),(2,'Length','2026-10-17 18:48:23','2026-10-17 18:48:23'),(3,'Capacity','2026-10-17 18:48:23','2026-10-17 18:48:23');
/*!40000 ALTER TABLE `attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` bigint unsigned DEFAULT NULL,
  `top` tinyint(1) NOT NULL DEFAULT '0',
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_slug_unique` (`slug`),
  KEY `brands_logo_foreign` (`logo`),
  CONSTRAINT `brands_logo_foreign` FOREIGN KEY (`logo`) REFERENCES `uploads` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES (1,'Arduino','arduino',NULL,0,'Arduino',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(2,'Raspberry Pi','raspberry-pi',NULL,0,'Raspberry Pi',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(3,'LEGO','lego',NULL,0,'LEGO',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(4,'Kingston','kingston',NULL,0,'Kingston',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(5,'Apple','apple',NULL,0,'Apple',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(6,'Audi','audi',NULL,0,'Audi',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(7,'Baby & me','baby-me',NULL,0,'Baby & me',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(8,'Baby Care','baby-care',NULL,0,'Baby Care',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(9,'Baby TV','baby-tv',NULL,0,'Baby TV',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(10,'BMW','bmw',NULL,0,'BMW',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(11,'Canon','canon',NULL,0,'Canon',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(12,'Dell','dell',NULL,0,'Dell',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(13,'Demo brand','demo-brand',NULL,0,'Demo brand',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(14,'Demo brand1','demo-brand1',NULL,0,'Demo brand1',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(15,'Denim','denim',NULL,0,'Denim',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(16,'Dior','dior',NULL,0,'Dior',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(17,'Ford','ford',NULL,0,'Ford',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(18,'Honda','honda',NULL,0,'Honda',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(19,'HP','hp',NULL,0,'HP',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(20,'Huwaei','huwaei',NULL,0,'Huwaei',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(21,'Hyundai','hyundai',NULL,0,'Hyundai',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(22,'Label & Barcode Sticker','label-barcode-sticker',NULL,0,'Label & Barcode Sticker',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(23,'lucky Brand','lucky-brand',NULL,0,'lucky Brand',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(24,'Nike','nike',NULL,0,'Nike',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(25,'Nissan','nissan',NULL,0,'Nissan',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(26,'One Plus','one-plus',NULL,0,'One Plus',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(27,'Pampers','pampers',NULL,0,'Pampers',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(28,'Pos Roll','pos-roll',NULL,0,'Pos Roll',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(29,'Tanishq','tanishq',NULL,0,'Tanishq',NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19');
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_settings`
--

DROP TABLE IF EXISTS `business_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `business_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci,
  `lang` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `business_settings_type_lang_unique` (`type`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_settings`
--

LOCK TABLES `business_settings` WRITE;
/*!40000 ALTER TABLE `business_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `business_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `temp_user_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` bigint unsigned NOT NULL,
  `variation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `price` decimal(20,2) NOT NULL DEFAULT '0.00',
  `quantity` int unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carts_user_id_foreign` (`user_id`),
  KEY `carts_product_id_foreign` (`product_id`),
  KEY `carts_temp_user_id_index` (`temp_user_id`),
  CONSTRAINT `carts_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `level` tinyint unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_level` int unsigned NOT NULL DEFAULT '0',
  `banner` bigint unsigned DEFAULT NULL,
  `icon` bigint unsigned DEFAULT NULL,
  `cover_image` bigint unsigned DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `digital` tinyint(1) NOT NULL DEFAULT '0',
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_banner_foreign` (`banner`),
  KEY `categories_icon_foreign` (`icon`),
  KEY `categories_cover_image_foreign` (`cover_image`),
  KEY `categories_parent_id_order_level_index` (`parent_id`,`order_level`),
  CONSTRAINT `categories_banner_foreign` FOREIGN KEY (`banner`) REFERENCES `uploads` (`id`) ON DELETE SET NULL,
  CONSTRAINT `categories_cover_image_foreign` FOREIGN KEY (`cover_image`) REFERENCES `uploads` (`id`) ON DELETE SET NULL,
  CONSTRAINT `categories_icon_foreign` FOREIGN KEY (`icon`) REFERENCES `uploads` (`id`) ON DELETE SET NULL,
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,NULL,0,'Robotics Kits & Boards','robotics-kits-boards',0,NULL,NULL,NULL,1,0,NULL,NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(2,NULL,0,'3D Printing','3d-printing',1,NULL,NULL,NULL,1,0,NULL,NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(3,NULL,0,'Drones & Drone Accessories','drones-drone-accessories',2,NULL,NULL,NULL,1,0,NULL,NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(4,NULL,0,'Electronic Components & Sensors','electronic-components-sensors',3,NULL,NULL,NULL,1,0,NULL,NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(5,NULL,0,'Tools & Lab Equipment','tools-lab-equipment',4,NULL,NULL,NULL,1,0,NULL,NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(6,NULL,0,'Rubik’s Cubes & Speedcubing','rubiks-cubes-speedcubing',5,NULL,NULL,NULL,1,0,NULL,NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(7,NULL,0,'Robotics Accessories','robotics-accessories',6,NULL,NULL,NULL,0,0,NULL,NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(8,NULL,0,'computer','computer',7,NULL,NULL,NULL,0,0,NULL,NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(9,NULL,0,'Home Decor','home-decor',8,NULL,NULL,NULL,0,0,NULL,NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(10,NULL,0,'Pos Roll','pos-roll',9,NULL,NULL,NULL,0,0,NULL,NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(11,NULL,0,'Thermal Sticker','thermal-sticker',10,NULL,NULL,NULL,0,0,NULL,NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `colors`
--

DROP TABLE IF EXISTS `colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `colors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `colors`
--

LOCK TABLES `colors` WRITE;
/*!40000 ALTER TABLE `colors` DISABLE KEYS */;
INSERT INTO `colors` VALUES (1,'Black','#1B1B1B','2026-10-17 18:48:24','2026-10-17 18:48:24'),(2,'White','#FFFFFF','2026-10-17 18:48:24','2026-10-17 18:48:24'),(3,'Red','#D7263D','2026-10-17 18:48:24','2026-10-17 18:48:24'),(4,'Blue','#1E5AA8','2026-10-17 18:48:25','2026-10-17 18:48:25'),(5,'Green','#2E8B57','2026-10-17 18:48:25','2026-10-17 18:48:25');
/*!40000 ALTER TABLE `colors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commission_logs`
--

DROP TABLE IF EXISTS `commission_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `order_code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seller_id` bigint unsigned NOT NULL,
  `admin_commission` decimal(20,2) NOT NULL DEFAULT '0.00',
  `seller_earning` decimal(20,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `commission_logs_order_id_seller_id_unique` (`order_id`,`seller_id`),
  KEY `commission_logs_seller_id_foreign` (`seller_id`),
  CONSTRAINT `commission_logs_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `commission_logs_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commission_logs`
--

LOCK TABLES `commission_logs` WRITE;
/*!40000 ALTER TABLE `commission_logs` DISABLE KEYS */;
INSERT INTO `commission_logs` VALUES (1,7,'20261013-16150006',3,2.80,53.19,'2026-10-17 18:48:35','2026-10-17 18:48:35'),(2,7,'20261013-16150006',4,0.30,5.70,'2026-10-17 18:48:35','2026-10-17 18:48:35');
/*!40000 ALTER TABLE `commission_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `query` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reply` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_messages_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
INSERT INTO `contact_messages` VALUES (1,'Farhan Ahmed','farhan@example.test','+8801711000011','Do you ship wholesale quantities outside Dhaka?','Yes, wholesale cartons ship nationwide with our carrier partners.','answered','2026-10-05 18:48:36','2026-10-17 18:48:36'),(2,'Nusrat Jahan','nusrat@example.test','+8801811000022','I want to become a seller on WowMart. What documents are needed?','Register a seller account and upload your NID plus a trade licence; approval takes two working days.','answered','2026-10-07 18:48:36','2026-10-17 18:48:36'),(3,'Imran Kabir','imran@example.test',NULL,'Is cash on delivery available for Chattogram?',NULL,'open','2026-10-09 18:48:36','2026-10-17 18:48:36'),(4,'Sadia Islam','sadia@example.test',NULL,'Please remove my account and all personal data.',NULL,'open','2026-10-11 18:48:36','2026-10-17 18:48:36');
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_19_000001_extend_users_for_marketplace',1),(5,'2026_09_19_000002_create_uploads_table',1),(6,'2026_09_19_000003_create_catalog_reference_tables',1),(7,'2026_09_19_000004_create_shops_table',1),(8,'2026_09_19_000005_create_products_tables',1),(9,'2026_09_19_000007_create_orders_tables',1),(10,'2026_09_19_000008_create_customer_tables',1),(11,'2026_09_19_000009_create_business_settings_table',1),(12,'2026_09_19_020447_create_personal_access_tokens_table',1),(13,'2026_09_25_000001_restrict_order_customer_delete',1),(14,'2026_09_26_000001_create_roles_tables',1),(15,'2026_09_27_000011_create_report_tables',1),(16,'2026_09_28_000013_create_support_tables',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes`
--

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;
INSERT INTO `notes` VALUES (1,'refund','Unused items in their original packaging can be returned within 7 days of delivery.','2026-10-17 18:48:22','2026-10-17 18:48:22'),(2,'warranty','Covers manufacturing defects only. Physical damage, water damage and burnt components are not covered.','2026-10-17 18:48:22','2026-10-17 18:48:22');
/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_details`
--

DROP TABLE IF EXISTS `order_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `seller_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `variation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `price` decimal(20,2) NOT NULL,
  `tax` decimal(20,2) NOT NULL DEFAULT '0.00',
  `shipping_cost` decimal(20,2) NOT NULL DEFAULT '0.00',
  `quantity` int unsigned NOT NULL,
  `shipping_type` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'home_delivery',
  `delivery_status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_details_seller_id_foreign` (`seller_id`),
  KEY `order_details_product_id_foreign` (`product_id`),
  KEY `order_details_order_id_seller_id_index` (`order_id`,`seller_id`),
  CONSTRAINT `order_details_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_details_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_details`
--

LOCK TABLES `order_details` WRITE;
/*!40000 ALTER TABLE `order_details` DISABLE KEYS */;
INSERT INTO `order_details` VALUES (1,1,NULL,1,'',30.00,0.00,60.00,2,'home_delivery','delivered','paid','2026-10-17 18:48:26','2026-10-17 18:48:26'),(2,1,NULL,5,'',18.00,0.00,0.00,1,'home_delivery','delivered','paid','2026-10-17 18:48:26','2026-10-17 18:48:26'),(3,2,NULL,3,'',150.00,0.00,60.00,5,'home_delivery','delivered','paid','2026-10-17 18:48:26','2026-10-17 18:48:26'),(4,3,NULL,9,'',24.00,0.00,60.00,2,'home_delivery','delivered','paid','2026-10-17 18:48:27','2026-10-17 18:48:27'),(5,3,NULL,11,'',29.00,0.00,0.00,3,'home_delivery','delivered','paid','2026-10-17 18:48:27','2026-10-17 18:48:27'),(6,4,NULL,14,'',3.80,0.00,60.00,6,'home_delivery','delivered','paid','2026-10-17 18:48:27','2026-10-17 18:48:27'),(7,5,NULL,17,'',1.80,0.00,60.00,2,'home_delivery','delivered','paid','2026-10-17 18:48:27','2026-10-17 18:48:27'),(8,5,NULL,21,'',2.90,0.00,0.00,1,'home_delivery','delivered','paid','2026-10-17 18:48:27','2026-10-17 18:48:27'),(9,6,NULL,2,'',60.00,0.00,60.00,4,'home_delivery','delivered','paid','2026-10-17 18:48:27','2026-10-17 18:48:27'),(10,7,3,8,'',55.99,0.00,60.00,1,'home_delivery','on_the_way','paid','2026-10-17 18:48:27','2026-10-17 18:48:27'),(11,7,4,12,'',3.00,0.00,0.00,2,'home_delivery','on_the_way','paid','2026-10-17 18:48:28','2026-10-17 18:48:28'),(12,8,NULL,23,'',75.00,0.00,60.00,3,'home_delivery','on_the_way','paid','2026-10-17 18:48:28','2026-10-17 18:48:28'),(13,9,2,4,'',5500.00,0.00,60.00,1,'home_delivery','picked_up','unpaid','2026-10-17 18:48:28','2026-10-17 18:48:28'),(14,10,NULL,10,'',29.00,0.00,60.00,1,'home_delivery','confirmed','unpaid','2026-10-17 18:48:28','2026-10-17 18:48:28'),(15,10,NULL,19,'',9.50,0.00,0.00,2,'home_delivery','confirmed','unpaid','2026-10-17 18:48:28','2026-10-17 18:48:28'),(16,11,NULL,2,'',60.00,0.00,60.00,3,'home_delivery','pending','unpaid','2026-10-17 18:48:28','2026-10-17 18:48:28'),(17,11,NULL,7,'',1.00,0.00,0.00,1,'home_delivery','pending','unpaid','2026-10-17 18:48:28','2026-10-17 18:48:28'),(18,12,NULL,13,'',2.50,0.00,60.00,4,'home_delivery','pending','unpaid','2026-10-17 18:48:29','2026-10-17 18:48:29'),(19,13,NULL,6,'',68.00,0.00,60.00,1,'home_delivery','cancelled','unpaid','2026-10-17 18:48:29','2026-10-17 18:48:29');
/*!40000 ALTER TABLE `order_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `seller_id` bigint unsigned DEFAULT NULL,
  `shipping_address` json NOT NULL,
  `delivery_status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash_on_delivery',
  `payment_status` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `grand_total` decimal(20,2) NOT NULL DEFAULT '0.00',
  `coupon_discount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `coupon_code` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_code_unique` (`code`),
  KEY `orders_seller_id_foreign` (`seller_id`),
  KEY `orders_delivery_status_id_index` (`delivery_status`,`id`),
  KEY `orders_delivery_status_index` (`delivery_status`),
  KEY `orders_payment_status_index` (`payment_status`),
  KEY `orders_user_id_foreign` (`user_id`),
  CONSTRAINT `orders_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'20261005-10150000',7,NULL,'{\"city\": \"Chattogram\", \"name\": \"Rafi Ahmed\", \"phone\": \"+8801700000000\", \"address\": \"House 12, Road 4, GEC Circle\", \"country\": \"Bangladesh\"}','delivered','cash_on_delivery','paid',138.00,0.00,NULL,'2026-10-05 04:15:00','2026-10-17 18:48:26'),(2,'20261006-11150001',8,NULL,'{\"city\": \"Dhaka\", \"name\": \"Nusrat Jahan\", \"phone\": \"+8801700000001\", \"address\": \"Flat 3B, Block C, Mirpur 10\", \"country\": \"Bangladesh\"}','delivered','cash_on_delivery','paid',810.00,0.00,NULL,'2026-10-06 05:15:00','2026-10-17 18:48:26'),(3,'20261007-12150002',9,NULL,'{\"city\": \"Sylhet\", \"name\": \"Tanvir Hasan\", \"phone\": \"+8801700000002\", \"address\": \"Station Road, Zindabazar\", \"country\": \"Bangladesh\"}','delivered','cash_on_delivery','paid',195.00,0.00,NULL,'2026-10-07 06:15:00','2026-10-17 18:48:27'),(4,'20261008-13150003',7,NULL,'{\"city\": \"Chattogram\", \"name\": \"Rafi Ahmed\", \"phone\": \"+8801700000003\", \"address\": \"House 12, Road 4, GEC Circle\", \"country\": \"Bangladesh\"}','delivered','cash_on_delivery','paid',82.80,0.00,NULL,'2026-10-08 07:15:00','2026-10-17 18:48:27'),(5,'20261010-14150004',8,NULL,'{\"city\": \"Dhaka\", \"name\": \"Nusrat Jahan\", \"phone\": \"+8801700000004\", \"address\": \"Flat 3B, Block C, Mirpur 10\", \"country\": \"Bangladesh\"}','delivered','cash_on_delivery','paid',66.50,0.00,NULL,'2026-10-10 08:15:00','2026-10-17 18:48:27'),(6,'20261011-15150005',9,NULL,'{\"city\": \"Sylhet\", \"name\": \"Tanvir Hasan\", \"phone\": \"+8801700000005\", \"address\": \"Station Road, Zindabazar\", \"country\": \"Bangladesh\"}','delivered','cash_on_delivery','paid',300.00,0.00,NULL,'2026-10-11 09:15:00','2026-10-17 18:48:27'),(7,'20261013-16150006',7,NULL,'{\"city\": \"Chattogram\", \"name\": \"Rafi Ahmed\", \"phone\": \"+8801700000006\", \"address\": \"House 12, Road 4, GEC Circle\", \"country\": \"Bangladesh\"}','on_the_way','cash_on_delivery','paid',121.99,0.00,NULL,'2026-10-13 10:15:00','2026-10-17 18:48:28'),(8,'20261014-17150007',8,NULL,'{\"city\": \"Dhaka\", \"name\": \"Nusrat Jahan\", \"phone\": \"+8801700000007\", \"address\": \"Flat 3B, Block C, Mirpur 10\", \"country\": \"Bangladesh\"}','on_the_way','cash_on_delivery','paid',285.00,0.00,NULL,'2026-10-14 11:15:00','2026-10-17 18:48:28'),(9,'20261015-18150008',9,NULL,'{\"city\": \"Sylhet\", \"name\": \"Tanvir Hasan\", \"phone\": \"+8801700000008\", \"address\": \"Station Road, Zindabazar\", \"country\": \"Bangladesh\"}','picked_up','cash_on_delivery','unpaid',5560.00,0.00,NULL,'2026-10-15 12:15:00','2026-10-17 18:48:28'),(10,'20261016-19150009',7,NULL,'{\"city\": \"Chattogram\", \"name\": \"Rafi Ahmed\", \"phone\": \"+8801700000009\", \"address\": \"House 12, Road 4, GEC Circle\", \"country\": \"Bangladesh\"}','confirmed','cash_on_delivery','unpaid',108.00,0.00,NULL,'2026-10-16 13:15:00','2026-10-17 18:48:28'),(11,'20261017-20150010',8,NULL,'{\"city\": \"Dhaka\", \"name\": \"Nusrat Jahan\", \"phone\": \"+8801700000010\", \"address\": \"Flat 3B, Block C, Mirpur 10\", \"country\": \"Bangladesh\"}','pending','cash_on_delivery','unpaid',241.00,0.00,NULL,'2026-10-17 14:15:00','2026-10-17 18:48:28'),(12,'20261018-21150011',9,NULL,'{\"city\": \"Sylhet\", \"name\": \"Tanvir Hasan\", \"phone\": \"+8801700000011\", \"address\": \"Station Road, Zindabazar\", \"country\": \"Bangladesh\"}','pending','cash_on_delivery','unpaid',70.00,0.00,NULL,'2026-10-18 15:15:00','2026-10-17 18:48:29'),(13,'20261009-22150012',8,NULL,'{\"city\": \"Dhaka\", \"name\": \"Nusrat Jahan\", \"phone\": \"+8801700000012\", \"address\": \"Flat 3B, Block C, Mirpur 10\", \"country\": \"Bangladesh\"}','cancelled','cash_on_delivery','unpaid',128.00,0.00,NULL,'2026-10-09 16:15:00','2026-10-17 18:48:29');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_categories_product_id_category_id_unique` (`product_id`,`category_id`),
  KEY `product_categories_category_id_foreign` (`category_id`),
  CONSTRAINT `product_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_categories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
INSERT INTO `product_categories` VALUES (1,1,10),(2,2,10),(3,3,10),(4,4,8),(5,5,1),(6,6,1),(7,7,10),(8,8,1),(9,9,1),(10,10,1),(11,11,1),(12,12,4),(13,13,4),(14,14,4),(15,15,4),(16,16,4),(17,17,4),(18,18,4),(19,19,5),(20,20,5),(21,21,5),(22,22,5),(23,23,4),(24,24,4),(25,25,4),(26,26,4),(27,27,4),(28,28,4),(29,29,4),(30,30,4),(31,31,4),(32,32,1),(33,33,4),(34,34,4),(35,35,5),(36,36,5),(37,37,5),(38,38,10),(39,39,10),(40,40,10),(41,41,10),(42,42,10);
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_conversation_messages`
--

DROP TABLE IF EXISTS `product_conversation_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_conversation_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_conversation_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_conversation_messages_product_conversation_id_foreign` (`product_conversation_id`),
  KEY `product_conversation_messages_user_id_foreign` (`user_id`),
  CONSTRAINT `product_conversation_messages_product_conversation_id_foreign` FOREIGN KEY (`product_conversation_id`) REFERENCES `product_conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_conversation_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_conversation_messages`
--

LOCK TABLES `product_conversation_messages` WRITE;
/*!40000 ALTER TABLE `product_conversation_messages` DISABLE KEYS */;
INSERT INTO `product_conversation_messages` VALUES (1,1,9,'Hello, I need 20 units for a school lab. Is there a bulk price?','2026-10-12 18:48:37'),(2,1,2,'Hi! For 20 pieces I can offer 8% off plus free shipping. Shall I reserve them?','2026-10-12 20:48:37'),(3,1,1,'Bulk price agreed with the seller; both sides confirmed in chat.','2026-10-12 21:48:37'),(4,2,7,'Do you have the red variant in stock?','2026-10-14 18:48:37'),(5,2,3,'Red is back in stock as of this morning. Go ahead and order!','2026-10-14 20:48:37');
/*!40000 ALTER TABLE `product_conversation_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_conversations`
--

DROP TABLE IF EXISTS `product_conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_conversations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `seller_id` bigint unsigned NOT NULL,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_conversations_product_id_foreign` (`product_id`),
  KEY `product_conversations_customer_id_foreign` (`customer_id`),
  KEY `product_conversations_seller_id_foreign` (`seller_id`),
  KEY `product_conversations_status_index` (`status`),
  CONSTRAINT `product_conversations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_conversations_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_conversations_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_conversations`
--

LOCK TABLES `product_conversations` WRITE;
/*!40000 ALTER TABLE `product_conversations` DISABLE KEYS */;
INSERT INTO `product_conversations` VALUES (1,4,9,2,'Bulk discount for 20 pieces','open','2026-10-12 18:48:37','2026-10-17 18:48:37'),(2,5,7,3,'Colour option availability','open','2026-10-14 18:48:37','2026-10-17 18:48:37');
/*!40000 ALTER TABLE `product_conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_queries`
--

DROP TABLE IF EXISTS `product_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_queries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reply` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_queries_user_id_foreign` (`user_id`),
  KEY `product_queries_product_id_foreign` (`product_id`),
  KEY `product_queries_status_index` (`status`),
  CONSTRAINT `product_queries_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_queries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_queries`
--

LOCK TABLES `product_queries` WRITE;
/*!40000 ALTER TABLE `product_queries` DISABLE KEYS */;
INSERT INTO `product_queries` VALUES (1,7,1,'Is this kit compatible with Arduino Mega?','Yes — verified with the product team and updated on the listing.','answered','2026-10-09 18:48:36','2026-10-17 18:48:36'),(2,8,2,'What is the battery life under continuous use?','Yes — verified with the product team and updated on the listing.','answered','2026-10-10 18:48:37','2026-10-17 18:48:37'),(3,9,3,'Does the price include VAT?',NULL,'open','2026-10-11 18:48:37','2026-10-17 18:48:37'),(4,7,4,'Can it ship in original packaging as a gift?',NULL,'open','2026-10-12 18:48:37','2026-10-17 18:48:37');
/*!40000 ALTER TABLE `product_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_stocks`
--

DROP TABLE IF EXISTS `product_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_stocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `variant` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sku` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(20,2) NOT NULL DEFAULT '0.00',
  `qty` int NOT NULL DEFAULT '0',
  `image` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_stocks_product_id_variant_unique` (`product_id`,`variant`),
  KEY `product_stocks_image_foreign` (`image`),
  CONSTRAINT `product_stocks_image_foreign` FOREIGN KEY (`image`) REFERENCES `uploads` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_stocks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_stocks`
--

LOCK TABLES `product_stocks` WRITE;
/*!40000 ALTER TABLE `product_stocks` DISABLE KEYS */;
INSERT INTO `product_stocks` VALUES (1,1,'','WM-00001',30.00,12,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(2,2,'','WM-00002',60.00,49,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(3,3,'','WM-00003',150.00,86,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(4,4,'','WM-00004',5500.00,123,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(5,5,'','WM-00005',18.00,160,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(6,6,'','WM-00006',68.00,0,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(7,7,'','WM-00007',1.00,54,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(8,8,'','WM-00008',55.99,91,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(9,9,'','WM-00009',24.00,128,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(10,10,'','WM-00010',29.00,3,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(11,11,'','WM-00011',29.00,22,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(12,12,'','WM-00012',3.00,59,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(13,13,'','WM-00013',2.50,96,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(14,14,'','WM-00014',3.80,133,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(15,15,'','WM-00015',6.50,3,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(16,16,'','WM-00016',4.50,27,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(17,17,'','WM-00017',1.80,64,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(18,18,'','WM-00018',4.50,101,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(19,19,'','WM-00019',9.50,138,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(20,20,'','WM-00020',9.50,175,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(21,21,'','WM-00021',2.90,32,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(22,22,'','WM-00022',4.80,69,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(23,23,'','WM-00023',75.00,106,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(24,24,'','WM-00024',29.00,143,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(25,25,'','WM-00025',57.00,180,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(26,26,'','WM-00026',53.00,37,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(27,27,'','WM-00027',50.00,74,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(28,28,'','WM-00028',45.00,111,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(29,29,'','WM-00029',75.00,148,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(30,30,'','WM-00030',130.00,185,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(31,31,'','WM-00031',35.00,42,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(32,32,'','WM-00032',150.00,79,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(33,33,'','WM-00033',319.00,116,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(34,34,'','WM-00034',260.00,153,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(35,35,'','WM-00035',24.99,190,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(36,36,'','WM-00036',8.99,47,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(37,37,'','WM-00037',19.99,84,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(38,38,'','WM-00038',40.00,121,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(39,39,'','WM-00039',60.00,158,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(40,40,'','WM-00040',26.00,15,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(41,41,'','WM-00041',77.00,52,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(42,42,'','WM-00042',24.00,89,NULL,'2026-10-17 18:48:22','2026-10-17 18:48:22');
/*!40000 ALTER TABLE `product_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_upload`
--

DROP TABLE IF EXISTS `product_upload`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_upload` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `upload_id` bigint unsigned NOT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_upload_product_id_upload_id_unique` (`product_id`,`upload_id`),
  KEY `product_upload_upload_id_foreign` (`upload_id`),
  CONSTRAINT `product_upload_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_upload_upload_id_foreign` FOREIGN KEY (`upload_id`) REFERENCES `uploads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_upload`
--

LOCK TABLES `product_upload` WRITE;
/*!40000 ALTER TABLE `product_upload` DISABLE KEYS */;
INSERT INTO `product_upload` VALUES (1,1,1,0),(2,2,2,0),(3,3,3,0),(4,4,4,0),(5,5,5,0),(6,6,6,0),(7,7,7,0),(8,8,8,0),(9,9,9,0),(10,10,10,0),(11,12,11,0),(12,13,12,0),(13,14,13,0),(14,15,14,0),(15,16,15,0),(16,17,16,0),(17,18,17,0),(18,19,18,0),(19,20,19,0),(20,21,20,0),(21,22,21,0),(22,23,22,0),(23,24,23,0),(24,25,24,0),(25,26,25,0),(26,27,26,0),(27,28,27,0),(28,29,28,0),(29,30,29,0),(30,31,30,0),(31,32,31,0),(32,33,32,0),(33,34,33,0),(34,35,34,0),(35,36,35,0),(36,37,36,0),(37,38,37,0),(38,39,38,0),(39,40,39,0),(40,41,40,0),(41,42,41,0);
/*!40000 ALTER TABLE `product_upload` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `added_by` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `brand_id` bigint unsigned DEFAULT NULL,
  `unit` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weight` decimal(10,3) NOT NULL DEFAULT '0.000',
  `min_qty` int unsigned NOT NULL DEFAULT '1',
  `tags` text COLLATE utf8mb4_unicode_ci,
  `barcode` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quick_overview` text COLLATE utf8mb4_unicode_ci,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `refundable` tinyint(1) NOT NULL DEFAULT '0',
  `refund_note_id` bigint unsigned DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `todays_deal` tinyint(1) NOT NULL DEFAULT '0',
  `thumbnail_img` bigint unsigned DEFAULT NULL,
  `video_provider` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf` bigint unsigned DEFAULT NULL,
  `unit_price` decimal(20,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(20,2) NOT NULL DEFAULT '0.00',
  `discount_type` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'amount',
  `discount_start_date` timestamp NULL DEFAULT NULL,
  `discount_end_date` timestamp NULL DEFAULT NULL,
  `current_stock` int NOT NULL DEFAULT '0',
  `sku` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `low_stock_quantity` int unsigned NOT NULL DEFAULT '1',
  `stock_visibility_state` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'quantity',
  `variant_product` tinyint(1) NOT NULL DEFAULT '0',
  `colors` json DEFAULT NULL,
  `choice_attributes` json DEFAULT NULL,
  `choice_options` json DEFAULT NULL,
  `external_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_link_btn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_img` bigint unsigned DEFAULT NULL,
  `cash_on_delivery` tinyint(1) NOT NULL DEFAULT '1',
  `shipping_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'free',
  `shipping_cost` decimal(20,2) NOT NULL DEFAULT '0.00',
  `is_quantity_multiplied` tinyint(1) NOT NULL DEFAULT '0',
  `est_shipping_days` smallint unsigned DEFAULT NULL,
  `has_warranty` tinyint(1) NOT NULL DEFAULT '0',
  `warranty_id` bigint unsigned DEFAULT NULL,
  `warranty_note_id` bigint unsigned DEFAULT NULL,
  `frequently_bought_selection_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'product',
  `fq_bought_product_category_id` bigint unsigned DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `approved` tinyint(1) NOT NULL DEFAULT '1',
  `wholesale_product` tinyint(1) NOT NULL DEFAULT '0',
  `digital` tinyint(1) NOT NULL DEFAULT '0',
  `num_of_sale` int unsigned NOT NULL DEFAULT '0',
  `rating` decimal(3,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_user_id_foreign` (`user_id`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_brand_id_foreign` (`brand_id`),
  KEY `products_refund_note_id_foreign` (`refund_note_id`),
  KEY `products_thumbnail_img_foreign` (`thumbnail_img`),
  KEY `products_pdf_foreign` (`pdf`),
  KEY `products_meta_img_foreign` (`meta_img`),
  KEY `products_warranty_id_foreign` (`warranty_id`),
  KEY `products_warranty_note_id_foreign` (`warranty_note_id`),
  KEY `products_fq_bought_product_category_id_foreign` (`fq_bought_product_category_id`),
  KEY `products_published_approved_index` (`published`,`approved`),
  KEY `products_num_of_sale_index` (`num_of_sale`),
  KEY `products_unit_price_index` (`unit_price`),
  KEY `products_added_by_index` (`added_by`),
  KEY `products_sku_index` (`sku`),
  CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_fq_bought_product_category_id_foreign` FOREIGN KEY (`fq_bought_product_category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_meta_img_foreign` FOREIGN KEY (`meta_img`) REFERENCES `uploads` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_pdf_foreign` FOREIGN KEY (`pdf`) REFERENCES `uploads` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_refund_note_id_foreign` FOREIGN KEY (`refund_note_id`) REFERENCES `notes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_thumbnail_img_foreign` FOREIGN KEY (`thumbnail_img`) REFERENCES `uploads` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_warranty_id_foreign` FOREIGN KEY (`warranty_id`) REFERENCES `warranties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_warranty_note_id_foreign` FOREIGN KEY (`warranty_note_id`) REFERENCES `notes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'admin',1,'56mm x 38mm (2.2 - 1.4 inch) POS Printer Paper Roll','56mm-x-38mm-22-14-inch-pos-printer-paper-roll',10,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,1,1,1,NULL,NULL,NULL,30.00,0.00,'amount',NULL,NULL,12,'WM-00001',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'56mm x 38mm (2.2 - 1.4 inch) POS Printer Paper Roll',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,82,5.00,'2026-10-17 18:48:21','2026-10-17 18:48:30'),(2,'admin',1,'79mm x 50m POS Printer Paper Roll','pos-roll-2',10,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,1,1,2,NULL,NULL,NULL,60.00,0.00,'amount',NULL,NULL,49,'WM-00002',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'79mm x 50m POS Printer Paper Roll',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,7,3.00,'2026-10-17 18:48:21','2026-10-17 18:48:30'),(3,'admin',1,'80mm x 80mm POS Printer Paper Roll','pos-roll-3',10,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,1,1,3,NULL,NULL,NULL,150.00,0.00,'amount',NULL,NULL,86,'WM-00003',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'80mm x 80mm POS Printer Paper Roll',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,100,5.00,'2026-10-17 18:48:21','2026-10-17 18:48:30'),(4,'seller',2,'A400 SATA 480GB Rev 3.0 Internal SSD','ssd',8,4,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,1,1,4,NULL,NULL,NULL,5500.00,0.00,'amount',NULL,NULL,123,'WM-00004',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'A400 SATA 480GB Rev 3.0 Internal SSD',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,21,0.00,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(5,'admin',1,'Arduino UNO Board','arduino-boards-kits',1,1,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,5,NULL,NULL,NULL,18.00,0.00,'amount',NULL,NULL,160,'WM-00005',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Arduino UNO Board',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,94,4.00,'2026-10-17 18:48:21','2026-10-17 18:48:30'),(6,'admin',1,'Raspberry Pi Zero W','raspberry-pi-zero-w',1,2,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,6,NULL,NULL,NULL,68.00,0.00,'amount',NULL,NULL,0,'WM-00006',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Raspberry Pi Zero W',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,88,0.00,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(7,'admin',1,'78mm x 73m Thermal POS Paper Roll','78mm-x-73m-thermal-pos-paper-roll',10,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,7,NULL,NULL,NULL,1.00,0.00,'amount',NULL,NULL,54,'WM-00007',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'78mm x 73m Thermal POS Paper Roll',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,76,0.00,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(8,'seller',3,'Raspberry Pi 4 Model B – 4GB','raspberry-pi-4-model-b-4gb',1,2,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,8,NULL,NULL,NULL,55.99,0.00,'amount',NULL,NULL,91,'WM-00008',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Raspberry Pi 4 Model B – 4GB',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,70,0.00,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(9,'admin',1,'Arduino Mega 2560 Board','arduino-mega-2560-board',1,1,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,9,NULL,NULL,NULL,24.00,0.00,'amount',NULL,NULL,128,'WM-00009',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Arduino Mega 2560 Board',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,64,3.00,'2026-10-17 18:48:21','2026-10-17 18:48:30'),(10,'admin',1,'LEGO Robotics Kits (Spike Prime, Mindstorms)','lego-robotics-kits-spike-prime-mindstorms',1,3,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,10,NULL,NULL,NULL,29.00,0.00,'amount',NULL,NULL,3,'WM-00010',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'LEGO Robotics Kits (Spike Prime, Mindstorms)',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,13,0.00,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(11,'admin',1,'Robot Chassis (2WD / 4WD)','robot-chassis-2wd-4wd',1,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,NULL,NULL,NULL,NULL,29.00,0.00,'amount',NULL,NULL,22,'WM-00011',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Robot Chassis (2WD / 4WD)',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,20,4.00,'2026-10-17 18:48:21','2026-10-17 18:48:30'),(12,'seller',4,'Breadboards','breadboards',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,11,NULL,NULL,NULL,3.00,0.00,'amount',NULL,NULL,59,'WM-00012',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Breadboards',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,2,0.00,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(13,'admin',1,'LED Packs / Resistors','led-packs-resistors',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,12,NULL,NULL,NULL,2.50,0.00,'amount',NULL,NULL,96,'WM-00013',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'LED Packs / Resistors',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,9,0.00,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(14,'admin',1,'Basic Sensors (light, temperature, ultrasonic)','basic-sensors-light-temperature-ultrasonic',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,13,NULL,NULL,NULL,3.80,0.00,'amount',NULL,NULL,133,'WM-00014',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Basic Sensors (light, temperature, ultrasonic)',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,16,0.00,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(15,'admin',1,'Advanced Sensors (IR, Line Sensors)','advanced-sensors-ir-line-sensors',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,14,NULL,NULL,NULL,6.50,0.00,'amount',NULL,NULL,3,'WM-00015',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Advanced Sensors (IR, Line Sensors)',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,23,0.00,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(16,'seller',5,'Motor Drivers (L298N, L293D, DRV8833)','motor-drivers-l298n-l293d-drv8833',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,15,NULL,NULL,NULL,4.50,0.00,'amount',NULL,NULL,27,'WM-00016',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Motor Drivers (L298N, L293D, DRV8833)',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,5,0.00,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(17,'admin',1,'Jumper Wires','jumper-wires',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,16,NULL,NULL,NULL,1.80,0.00,'amount',NULL,NULL,64,'WM-00017',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Jumper Wires',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,12,5.00,'2026-10-17 18:48:21','2026-10-17 18:48:30'),(18,'admin',1,'L298N Motor Driver Module','l298n-motor-driver-module',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,17,NULL,NULL,NULL,4.50,0.00,'amount',NULL,NULL,101,'WM-00018',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'L298N Motor Driver Module',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,19,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(19,'admin',1,'Multimeters','multimeters',5,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,18,NULL,NULL,NULL,9.50,0.00,'amount',NULL,NULL,138,'WM-00019',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Multimeters',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,1,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(20,'seller',6,'Soldering Iron & Solder Wire','soldering-iron-solder-wire',5,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,19,NULL,NULL,NULL,9.50,0.00,'amount',NULL,NULL,175,'WM-00020',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Soldering Iron & Solder Wire',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,8,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(21,'admin',1,'Safety Goggles','safety-goggles',5,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,20,NULL,NULL,NULL,2.90,0.00,'amount',NULL,NULL,32,'WM-00021',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Safety Goggles',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,15,4.00,'2026-10-17 18:48:22','2026-10-17 18:48:30'),(22,'admin',1,'Slide Calipers','slide-calipers',5,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,21,NULL,NULL,NULL,4.80,0.00,'amount',NULL,NULL,69,'WM-00022',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Slide Calipers',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,22,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(23,'admin',1,'LM2596 DC-DC Buck Converter Module','lm2596-dc-dc-buck-converter-module',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,22,NULL,NULL,NULL,75.00,0.00,'amount',NULL,NULL,106,'WM-00023',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'LM2596 DC-DC Buck Converter Module',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,4,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(24,'seller',2,'TTP223 Capacitive Touch Sensor Module','ttp223-capacitive-touch-sensor-module',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,23,NULL,NULL,NULL,29.00,0.00,'amount',NULL,NULL,143,'WM-00024',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'TTP223 Capacitive Touch Sensor Module',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,11,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(25,'admin',1,'Water Level Detection Sensor Module','water-level-detection-sensor-module',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,24,NULL,NULL,NULL,57.00,0.00,'amount',NULL,NULL,180,'WM-00025',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Water Level Detection Sensor Module',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,18,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(26,'admin',1,'Flame Sensor Fire Detection Module','flame-sensor-fire-detection-module',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,25,NULL,NULL,NULL,53.00,0.00,'amount',NULL,NULL,37,'WM-00026',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Flame Sensor Fire Detection Module',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,0,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(27,'admin',1,'LDR Light Sensor Module','push-button-switch-module',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,26,NULL,NULL,NULL,50.00,0.00,'amount',NULL,NULL,74,'WM-00027',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'LDR Light Sensor Module',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,7,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(28,'seller',3,'Push Button Switch Module','dual-axis-joystick-module-ps2-style',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,27,NULL,NULL,NULL,45.00,0.00,'amount',NULL,NULL,111,'WM-00028',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Push Button Switch Module',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,14,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(29,'admin',1,'Dual-Axis Joystick Module (PS2 Style)','breadboard-830-point-solderless-prototyping-board-2',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,28,NULL,NULL,NULL,75.00,0.00,'amount',NULL,NULL,148,'WM-00029',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Dual-Axis Joystick Module (PS2 Style)',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,21,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(30,'admin',1,'Breadboard 830 Point Solderless Prototyping Board','breadboard-830-point-solderless-prototyping-board',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,29,NULL,NULL,NULL,130.00,0.00,'amount',NULL,NULL,185,'WM-00030',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Breadboard 830 Point Solderless Prototyping Board',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,3,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(31,'admin',1,'TP4056 Lithium Battery Charging Module (Micro USB)','tp4056-lithium-battery-charging-module-micro-usb',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,30,NULL,NULL,NULL,35.00,0.00,'amount',NULL,NULL,42,'WM-00031',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'TP4056 Lithium Battery Charging Module (Micro USB)',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,10,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(32,'seller',4,'2WD Robot Car Chassis Kit','2wd-robot-car-chassis-kit',1,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,31,NULL,NULL,NULL,150.00,0.00,'amount',NULL,NULL,79,'WM-00032',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'2WD Robot Car Chassis Kit',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,17,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(33,'admin',1,'16x2 LCD Display with I2C Adapter Module','16x2-lcd-display-with-i2c-adapter-module',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,32,NULL,NULL,NULL,319.00,0.00,'amount',NULL,NULL,116,'WM-00033',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'16x2 LCD Display with I2C Adapter Module',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,24,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(34,'admin',1,'0.96\" OLED Display Module (I2C, 128x64)','096-oled-display-module-i2c-128x64',4,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,33,NULL,NULL,NULL,260.00,0.00,'amount',NULL,NULL,153,'WM-00034',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'0.96\" OLED Display Module (I2C, 128x64)',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,6,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(35,'admin',1,'Li-ion Battery Charging Station','li-ion-battery-charging-station',5,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,34,NULL,NULL,NULL,24.99,0.00,'amount',NULL,NULL,190,'WM-00035',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Li-ion Battery Charging Station',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,13,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(36,'seller',5,'Glue Gun','glue-gun',5,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,35,NULL,NULL,NULL,8.99,0.00,'amount',NULL,NULL,47,'WM-00036',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Glue Gun',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,20,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(37,'admin',1,'Basic Toolset (pliers, cutters, screwdrivers)','basic-toolset-pliers-cutters-screwdrivers',5,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,36,NULL,NULL,NULL,19.99,0.00,'amount',NULL,NULL,84,'WM-00037',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'Basic Toolset (pliers, cutters, screwdrivers)',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,0,0,2,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(38,'admin',1,'80mm x 60mm Gas Station/Petrol Pump Paper Roll','80mm-x-60mm-gas-stationpetrol-pump-paper-roll',10,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,37,NULL,NULL,NULL,40.00,0.00,'amount',NULL,NULL,121,'WM-00038',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'80mm x 60mm Gas Station/Petrol Pump Paper Roll',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,1,0,9,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(39,'admin',1,'80mm x 90mm Banking Counter Paper Roll','80mm-x-90mm-banking-counter-paper-roll',10,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,38,NULL,NULL,NULL,60.00,0.00,'amount',NULL,NULL,158,'WM-00039',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'80mm x 90mm Banking Counter Paper Roll',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,1,0,16,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(40,'seller',6,'57mm x 35mm Mobile Recharge/Top-up Paper Roll','57mm-x-35mm-mobile-rechargetop-up-paper-roll',10,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,39,NULL,NULL,NULL,26.00,0.00,'amount',NULL,NULL,15,'WM-00040',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'57mm x 35mm Mobile Recharge/Top-up Paper Roll',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,1,0,23,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(41,'admin',1,'80mm x 120mm Large Billing Paper Roll (Supermarket)','80mm-x-120mm-large-billing-paper-roll-supermarket',10,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,40,NULL,NULL,NULL,77.00,0.00,'amount',NULL,NULL,52,'WM-00041',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'80mm x 120mm Large Billing Paper Roll (Supermarket)',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,1,0,5,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(42,'admin',1,'57mm x 25mm Small Receipt Paper Roll (Pharmacy/Retail)','57mm-x-25mm-small-receipt-paper-roll-pharmacyretail',10,NULL,'pc',0.000,1,NULL,NULL,NULL,NULL,0,NULL,0,0,41,NULL,NULL,NULL,24.00,0.00,'amount',NULL,NULL,89,'WM-00042',5,'quantity',0,NULL,NULL,NULL,NULL,NULL,'57mm x 25mm Small Receipt Paper Roll (Pharmacy/Retail)',NULL,NULL,1,'free',0.00,0,NULL,0,NULL,NULL,'product',NULL,1,1,1,0,12,0.00,'2026-10-17 18:48:22','2026-10-17 18:48:22');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reviews_product_id_user_id_unique` (`product_id`,`user_id`),
  KEY `reviews_user_id_foreign` (`user_id`),
  CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,1,7,5,'Arrived well packed and works exactly as described.',1,'2026-10-07 04:15:00','2026-10-07 04:15:00'),(2,5,7,4,'Good quality for the price. Delivery took a day longer than promised.',1,'2026-10-07 04:15:00','2026-10-07 04:15:00'),(3,3,8,5,'Used it in a weekend build, no problems at all. Would buy again.',1,'2026-10-08 05:15:00','2026-10-08 05:15:00'),(4,9,9,3,'Does the job, but the pins needed a little straightening.',1,'2026-10-09 06:15:00','2026-10-09 06:15:00'),(5,11,9,4,'Solid part. The seller answered my wiring question quickly.',1,'2026-10-09 06:15:00','2026-10-09 06:15:00'),(6,14,7,2,'Call 01700-000000 for cheaper wholesale prices!!!',0,'2026-10-10 07:15:00','2026-10-10 07:15:00'),(7,17,8,5,'Exactly what my workshop needed. Genuine part, fast delivery.',1,'2026-10-12 08:15:00','2026-10-12 08:15:00'),(8,21,8,4,'Works fine. Documentation could be better for beginners.',1,'2026-10-12 08:15:00','2026-10-12 08:15:00'),(9,2,9,3,'Average. It works, though the finish is rougher than the photos.',1,'2026-10-13 09:15:00','2026-10-13 09:15:00');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `permission` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permissions_role_id_permission_unique` (`role_id`,`permission`),
  CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_logs`
--

DROP TABLE IF EXISTS `search_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `search_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `query` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `results` int unsigned NOT NULL DEFAULT '0',
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `search_logs_user_id_foreign` (`user_id`),
  KEY `search_logs_query_index` (`query`),
  CONSTRAINT `search_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_logs`
--

LOCK TABLES `search_logs` WRITE;
/*!40000 ALTER TABLE `search_logs` DISABLE KEYS */;
INSERT INTO `search_logs` VALUES (1,'drone kit',3,8,'2026-10-14 09:48:30','2026-10-17 18:48:30'),(2,'drone kit',9,8,'2026-09-26 03:48:30','2026-10-17 18:48:30'),(3,'drone kit',5,9,'2026-09-26 09:48:30','2026-10-17 18:48:30'),(4,'drone kit',0,9,'2026-10-13 04:48:30','2026-10-17 18:48:30'),(5,'drone kit',7,9,'2026-09-19 12:48:30','2026-10-17 18:48:30'),(6,'drone kit',5,9,'2026-10-01 11:48:30','2026-10-17 18:48:30'),(7,'drone kit',7,8,'2026-09-18 20:48:30','2026-10-17 18:48:30'),(8,'drone kit',2,7,'2026-09-20 21:48:30','2026-10-17 18:48:30'),(9,'drone kit',5,9,'2026-10-01 08:48:30','2026-10-17 18:48:30'),(10,'drone kit',1,9,'2026-09-22 09:48:30','2026-10-17 18:48:30'),(11,'drone kit',11,7,'2026-10-16 00:48:30','2026-10-17 18:48:30'),(12,'drone kit',2,9,'2026-09-20 22:48:30','2026-10-17 18:48:30'),(13,'drone kit',6,8,'2026-10-12 04:48:31','2026-10-17 18:48:31'),(14,'drone kit',4,9,'2026-10-14 08:48:31','2026-10-17 18:48:31'),(15,'rc car',7,9,'2026-10-02 04:48:31','2026-10-17 18:48:31'),(16,'rc car',4,9,'2026-09-23 00:48:31','2026-10-17 18:48:31'),(17,'rc car',10,8,'2026-10-12 00:48:31','2026-10-17 18:48:31'),(18,'rc car',7,7,'2026-10-12 17:48:31','2026-10-17 18:48:31'),(19,'rc car',1,9,'2026-10-01 21:48:31','2026-10-17 18:48:31'),(20,'rc car',7,8,'2026-10-05 14:48:31','2026-10-17 18:48:31'),(21,'rc car',6,9,'2026-09-25 04:48:31','2026-10-17 18:48:31'),(22,'rc car',8,8,'2026-09-22 05:48:31','2026-10-17 18:48:31'),(23,'rc car',6,9,'2026-10-01 02:48:31','2026-10-17 18:48:31'),(24,'rc car',12,7,'2026-10-14 10:48:31','2026-10-17 18:48:31'),(25,'rc car',1,7,'2026-09-19 14:48:31','2026-10-17 18:48:31'),(26,'servo motor',4,9,'2026-09-30 09:48:31','2026-10-17 18:48:31'),(27,'servo motor',3,8,'2026-09-26 07:48:32','2026-10-17 18:48:32'),(28,'servo motor',0,9,'2026-10-17 11:48:32','2026-10-17 18:48:32'),(29,'servo motor',5,9,'2026-09-28 08:48:32','2026-10-17 18:48:32'),(30,'servo motor',5,9,'2026-10-12 04:48:32','2026-10-17 18:48:32'),(31,'servo motor',4,9,'2026-10-15 10:48:32','2026-10-17 18:48:32'),(32,'servo motor',9,8,'2026-10-13 04:48:32','2026-10-17 18:48:32'),(33,'servo motor',5,7,'2026-10-16 18:48:32','2026-10-17 18:48:32'),(34,'servo motor',3,8,'2026-10-11 00:48:32','2026-10-17 18:48:32'),(35,'arduino uno',9,7,'2026-09-21 05:48:32','2026-10-17 18:48:32'),(36,'arduino uno',0,7,'2026-10-10 19:48:32','2026-10-17 18:48:32'),(37,'arduino uno',1,7,'2026-10-04 04:48:32','2026-10-17 18:48:32'),(38,'arduino uno',0,7,'2026-09-24 12:48:32','2026-10-17 18:48:32'),(39,'arduino uno',0,7,'2026-10-08 18:48:32','2026-10-17 18:48:32'),(40,'arduino uno',1,8,'2026-10-02 07:48:32','2026-10-17 18:48:32'),(41,'arduino uno',5,8,'2026-09-27 12:48:33','2026-10-17 18:48:33'),(42,'arduino uno',9,7,'2026-09-21 19:48:33','2026-10-17 18:48:33'),(43,'raspberry pi',0,7,'2026-09-22 05:48:33','2026-10-17 18:48:33'),(44,'raspberry pi',8,9,'2026-09-20 05:48:33','2026-10-17 18:48:33'),(45,'raspberry pi',12,8,'2026-09-25 13:48:33','2026-10-17 18:48:33'),(46,'raspberry pi',7,8,'2026-10-08 03:48:33','2026-10-17 18:48:33'),(47,'raspberry pi',1,9,'2026-10-12 09:48:33','2026-10-17 18:48:33'),(48,'raspberry pi',3,7,'2026-10-10 03:48:33','2026-10-17 18:48:33'),(49,'raspberry pi',11,9,'2026-10-16 22:48:33','2026-10-17 18:48:33'),(50,'lipo battery',7,8,'2026-10-07 18:48:33','2026-10-17 18:48:33'),(51,'lipo battery',7,8,'2026-10-07 11:48:33','2026-10-17 18:48:33'),(52,'lipo battery',4,7,'2026-09-29 09:48:33','2026-10-17 18:48:33'),(53,'lipo battery',7,9,'2026-09-27 03:48:33','2026-10-17 18:48:33'),(54,'lipo battery',2,7,'2026-09-27 09:48:33','2026-10-17 18:48:33'),(55,'lipo battery',2,8,'2026-10-12 21:48:33','2026-10-17 18:48:33'),(56,'fpv camera',7,8,'2026-09-27 18:48:33','2026-10-17 18:48:33'),(57,'fpv camera',12,9,'2026-10-04 20:48:34','2026-10-17 18:48:34'),(58,'fpv camera',11,9,'2026-09-29 01:48:34','2026-10-17 18:48:34'),(59,'fpv camera',6,7,'2026-09-25 02:48:34','2026-10-17 18:48:34'),(60,'fpv camera',3,9,'2026-09-21 23:48:34','2026-10-17 18:48:34'),(61,'sensor kit',3,7,'2026-10-04 06:48:34','2026-10-17 18:48:34'),(62,'sensor kit',12,9,'2026-09-29 15:48:34','2026-10-17 18:48:34'),(63,'sensor kit',5,9,'2026-10-03 17:48:34','2026-10-17 18:48:34'),(64,'sensor kit',4,8,'2026-10-08 00:48:34','2026-10-17 18:48:34'),(65,'3d printer',12,7,'2026-09-30 09:48:34','2026-10-17 18:48:34'),(66,'3d printer',9,9,'2026-10-03 06:48:34','2026-10-17 18:48:34'),(67,'3d printer',0,8,'2026-10-01 06:48:34','2026-10-17 18:48:34'),(68,'soldering iron',5,9,'2026-09-26 06:48:34','2026-10-17 18:48:34'),(69,'soldering iron',12,7,'2026-10-10 03:48:34','2026-10-17 18:48:34'),(70,'led strip',2,7,'2026-09-26 15:48:34','2026-10-17 18:48:34'),(71,'led strip',6,8,'2026-09-21 17:48:35','2026-10-17 18:48:35'),(72,'gear motor',11,9,'2026-10-07 13:48:35','2026-10-17 18:48:35');
/*!40000 ALTER TABLE `search_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shops`
--

DROP TABLE IF EXISTS `shops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shops` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` bigint unsigned DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification_status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `rating` decimal(3,2) NOT NULL DEFAULT '0.00',
  `num_of_reviews` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shops_user_id_unique` (`user_id`),
  UNIQUE KEY `shops_slug_unique` (`slug`),
  KEY `shops_logo_foreign` (`logo`),
  KEY `shops_verification_status_index` (`verification_status`),
  CONSTRAINT `shops_logo_foreign` FOREIGN KEY (`logo`) REFERENCES `uploads` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shops_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shops`
--

LOCK TABLES `shops` WRITE;
/*!40000 ALTER TABLE `shops` DISABLE KEYS */;
INSERT INTO `shops` VALUES (1,2,'Circuit House','circuit-house',NULL,NULL,NULL,'approved',0.00,0,'2026-10-17 18:48:20','2026-10-17 18:48:20'),(2,3,'Sensor Bazaar','sensor-bazaar',NULL,NULL,NULL,'approved',0.00,0,'2026-10-17 18:48:20','2026-10-17 18:48:20'),(3,4,'Receipt Roll Co','receipt-roll-co',NULL,NULL,NULL,'approved',0.00,0,'2026-10-17 18:48:20','2026-10-17 18:48:20'),(4,5,'Maker Den','maker-den',NULL,NULL,NULL,'approved',0.00,0,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(5,6,'Volt Street','volt-street',NULL,NULL,NULL,'pending',0.00,0,'2026-10-17 18:48:21','2026-10-17 18:48:21');
/*!40000 ALTER TABLE `shops` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_ticket_replies`
--

DROP TABLE IF EXISTS `support_ticket_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_ticket_replies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `support_ticket_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `support_ticket_replies_support_ticket_id_foreign` (`support_ticket_id`),
  KEY `support_ticket_replies_user_id_foreign` (`user_id`),
  CONSTRAINT `support_ticket_replies_support_ticket_id_foreign` FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_ticket_replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_ticket_replies`
--

LOCK TABLES `support_ticket_replies` WRITE;
/*!40000 ALTER TABLE `support_ticket_replies` DISABLE KEYS */;
INSERT INTO `support_ticket_replies` VALUES (1,1,7,'One of the drone motors arrived cracked. Can I get a replacement shipped?','2026-10-08 18:48:35'),(2,1,1,'Sorry about that! We have dispatched a replacement motor free of charge; tracking will reach your email tonight.','2026-10-08 22:48:35'),(3,2,8,'The WELCOME10 coupon says invalid at checkout but it should be valid this month.','2026-10-09 15:48:35'),(4,2,1,'The coupon requires a minimum order of 500 taka. Adding one more item will make it apply.','2026-10-09 19:48:35'),(5,3,9,'I paid with bKash two days ago and have no tracking number yet.','2026-10-10 12:48:36'),(6,3,1,'Your parcel left the warehouse this morning. The tracking code is now visible under My Orders.','2026-10-10 16:48:36'),(7,4,7,'The box was crushed but the product seems fine. Should I still report it?','2026-10-11 09:48:36'),(8,5,8,'I need a company invoice for order ORD-000104.','2026-10-12 06:48:36');
/*!40000 ALTER TABLE `support_ticket_replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `subject` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `support_tickets_code_unique` (`code`),
  KEY `support_tickets_user_id_foreign` (`user_id`),
  KEY `support_tickets_status_index` (`status`),
  CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_tickets`
--

LOCK TABLES `support_tickets` WRITE;
/*!40000 ALTER TABLE `support_tickets` DISABLE KEYS */;
INSERT INTO `support_tickets` VALUES (1,'TKT-000001',7,'My order arrived with a broken part','open','2026-10-08 18:48:35','2026-10-17 18:48:35'),(2,'TKT-000002',8,'Coupon code not applying','open','2026-10-09 15:48:35','2026-10-17 18:48:35'),(3,'TKT-000003',9,'How do I track my parcel?','open','2026-10-10 12:48:36','2026-10-17 18:48:36'),(4,'TKT-000004',7,'Return window for damaged box','open','2026-10-11 09:48:36','2026-10-17 18:48:36'),(5,'TKT-000005',8,'Invoice request','open','2026-10-12 06:48:36','2026-10-17 18:48:36');
/*!40000 ALTER TABLE `support_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uploads`
--

DROP TABLE IF EXISTS `uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `uploads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `file_original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `file_size` bigint unsigned NOT NULL DEFAULT '0',
  `extension` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'image',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uploads_user_id_foreign` (`user_id`),
  KEY `uploads_file_size_index` (`file_size`),
  KEY `uploads_file_original_name_index` (`file_original_name`),
  CONSTRAINT `uploads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uploads`
--

LOCK TABLES `uploads` WRITE;
/*!40000 ALTER TABLE `uploads` DISABLE KEYS */;
INSERT INTO `uploads` VALUES (1,'56mm-x-38mm-22-14-inch-pos-printer-paper-roll.jpeg','uploads/catalog/e86aeb1bbfb600f6.jpeg',1,18950,'jpeg','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(2,'79mm-x-50m-pos-printer-paper-roll.png','uploads/catalog/f30e5dbd5017b402.png',1,909369,'png','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(3,'80mm-x-80mm-pos-printer-paper-roll.png','uploads/catalog/d33f52e9504c1e6d.png',1,713609,'png','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(4,'a400-sata-480gb-rev-30-internal-ssd.png','uploads/catalog/7530083cc22d7397.png',1,225037,'png','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(5,'arduino-uno-board.webp','uploads/catalog/4e91a4fcd202c193.webp',1,90824,'webp','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(6,'raspberry-pi-zero-w.png','uploads/catalog/53342eec6e7664b4.png',1,255008,'png','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(7,'78mm-x-73m-thermal-pos-paper-roll.png','uploads/catalog/851dcf8bcf0c781c.png',1,908025,'png','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(8,'raspberry-pi-4-model-b-4gb.png','uploads/catalog/19f1e2e0b2c6b7e0.png',1,1493320,'png','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(9,'arduino-mega-2560-board.png','uploads/catalog/e885124f657f1faa.png',1,644349,'png','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(10,'lego-robotics-kits-spike-prime-mindstorms.png','uploads/catalog/81d321ebf49a476e.png',1,540292,'png','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(11,'breadboards.png','uploads/catalog/ebbb4efe20052ac9.png',1,740612,'png','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(12,'led-packs-resistors.png','uploads/catalog/95a08a49c20b070f.png',1,182973,'png','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(13,'basic-sensors-light-temperature-ultrasonic.png','uploads/catalog/cfab83853b1118eb.png',1,71904,'png','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(14,'advanced-sensors-ir-line-sensors.png','uploads/catalog/b87baba1f0de3b06.png',1,485885,'png','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(15,'motor-drivers-l298n-l293d-drv8833.png','uploads/catalog/496eec3787ea1ff5.png',1,568182,'png','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(16,'jumper-wires.png','uploads/catalog/8270fc182e8e6964.png',1,500233,'png','image','2026-10-17 18:48:21','2026-10-17 18:48:21'),(17,'l298n-motor-driver-module.png','uploads/catalog/0f90f5c13677c180.png',1,172322,'png','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(18,'multimeters.png','uploads/catalog/cb8804ea0ef0f0dd.png',1,101116,'png','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(19,'soldering-iron-solder-wire.png','uploads/catalog/608c0afd82fbd232.png',1,618487,'png','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(20,'safety-goggles.png','uploads/catalog/27c35717c8de4e0e.png',1,30584,'png','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(21,'slide-calipers.png','uploads/catalog/5745f2c98ffb1336.png',1,24089,'png','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(22,'lm2596-dc-dc-buck-converter-module.jpg','uploads/catalog/2ac6794779b81c79.jpg',1,123683,'jpg','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(23,'ttp223-capacitive-touch-sensor-module.jpg','uploads/catalog/7ee284b020ff1d0d.jpg',1,132101,'jpg','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(24,'water-level-detection-sensor-module.jpg','uploads/catalog/d38abf56f34a97a1.jpg',1,88620,'jpg','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(25,'flame-sensor-fire-detection-module.jpg','uploads/catalog/4aa30b9db18af514.jpg',1,117269,'jpg','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(26,'ldr-light-sensor-module.jpg','uploads/catalog/9f8d9c60d1bd859a.jpg',1,65448,'jpg','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(27,'push-button-switch-module.jpg','uploads/catalog/0c2a272f938c2739.jpg',1,109224,'jpg','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(28,'dual-axis-joystick-module-ps2-style.jpg','uploads/catalog/ca59e2465d0c0d7d.jpg',1,126944,'jpg','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(29,'breadboard-830-point-solderless-prototyping-board.jpg','uploads/catalog/140de4c193974a29.jpg',1,145071,'jpg','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(30,'tp4056-lithium-battery-charging-module-micro-usb.jpg','uploads/catalog/e4b8a04489062e23.jpg',1,67993,'jpg','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(31,'2wd-robot-car-chassis-kit.jpeg','uploads/catalog/f6bf018a4f0bb484.jpeg',1,102053,'jpeg','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(32,'16x2-lcd-display-with-i2c-adapter-module.jpeg','uploads/catalog/054b198bea5a06f5.jpeg',1,203330,'jpeg','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(33,'096-oled-display-module-i2c-128x64.jpeg','uploads/catalog/365b464beb23fe58.jpeg',1,87710,'jpeg','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(34,'li-ion-battery-charging-station.png','uploads/catalog/6384f47a5515c3d5.png',1,883989,'png','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(35,'glue-gun.png','uploads/catalog/f60a45d8c018d906.png',1,208014,'png','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(36,'basic-toolset-pliers-cutters-screwdrivers.png','uploads/catalog/d189a78405cf297c.png',1,836117,'png','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(37,'80mm-x-60mm-gas-stationpetrol-pump-paper-roll.png','uploads/catalog/c47b263e561abf4c.png',1,139166,'png','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(38,'80mm-x-90mm-banking-counter-paper-roll.png','uploads/catalog/df2171c1a2122c9c.png',1,200310,'png','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(39,'57mm-x-35mm-mobile-rechargetop-up-paper-roll.png','uploads/catalog/036b114cc8d0783e.png',1,124051,'png','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(40,'80mm-x-120mm-large-billing-paper-roll-supermarket.png','uploads/catalog/2d62d24e4d0610db.png',1,332459,'png','image','2026-10-17 18:48:22','2026-10-17 18:48:22'),(41,'57mm-x-25mm-small-receipt-paper-roll-pharmacyretail.png','uploads/catalog/b28de75d9a57ac3d.png',1,216059,'png','image','2026-10-17 18:48:22','2026-10-17 18:48:22');
/*!40000 ALTER TABLE `uploads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `role_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  KEY `users_user_type_index` (`user_type`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin',NULL,'WowMart Admin','admin@wowmart.test',NULL,NULL,NULL,'$2y$12$TASULMwWdb9TN7py08xyvulOEcZ9FOQtixphwzcFDAPyQ/tD0jMei',0,NULL,'2026-10-17 18:48:19','2026-10-17 18:48:19'),(2,'seller',NULL,'Circuit House','circuit-house@sellers.wowmart.test',NULL,NULL,NULL,'$2y$12$jpWnGq4COkzH1llNMi1H0OIQJYA/zKW1g01eAljvhJyY4TI2mGYTm',0,NULL,'2026-10-17 18:48:20','2026-10-17 18:48:20'),(3,'seller',NULL,'Sensor Bazaar','sensor-bazaar@sellers.wowmart.test',NULL,NULL,NULL,'$2y$12$QpRUTfrw.EUqM0dNWZoPuOs/GoKmjrOqaEwrfWH7JFQoI.neu1C8y',0,NULL,'2026-10-17 18:48:20','2026-10-17 18:48:20'),(4,'seller',NULL,'Receipt Roll Co','receipt-roll-co@sellers.wowmart.test',NULL,NULL,NULL,'$2y$12$OjUJp0DdL83XVG01EJ4smecKxD2k1B5qyUPPDitp5T9eqao0yP.TS',0,NULL,'2026-10-17 18:48:20','2026-10-17 18:48:20'),(5,'seller',NULL,'Maker Den','maker-den@sellers.wowmart.test',NULL,NULL,NULL,'$2y$12$Itor9TacVy6hHx.BiolGh.AmYwmyYTMwYN/hkZZPBPcUqr9nvkvfG',0,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(6,'seller',NULL,'Volt Street','volt-street@sellers.wowmart.test',NULL,NULL,NULL,'$2y$12$MVhSRHYFSeKeCQHHnxFDneDLD1Qjgvk5S52FkDLXzcWJC.2CiJ6sy',0,NULL,'2026-10-17 18:48:21','2026-10-17 18:48:21'),(7,'customer',NULL,'Rafi Ahmed','rafi@customers.wowmart.test',NULL,NULL,NULL,'$2y$12$Yzafd7rxJPl6TfF9pDfvNu2kwqv5RXB3wdlXns6ReWJHzs5kSbpp.',0,NULL,'2026-10-17 18:48:25','2026-10-17 18:48:25'),(8,'customer',NULL,'Nusrat Jahan','nusrat@customers.wowmart.test',NULL,NULL,NULL,'$2y$12$lVVPGqLapmNPqQ7PBkYBtOK.sPpOQak11ViR/RHNQVlCmjau/9sPq',0,NULL,'2026-10-17 18:48:25','2026-10-17 18:48:25'),(9,'customer',NULL,'Tanvir Hasan','tanvir@customers.wowmart.test',NULL,NULL,NULL,'$2y$12$qyC2DYox8z69oUWiI47UzOtNjQjLhUsEgZUDpcTMvLvBKeaglAC9u',0,NULL,'2026-10-17 18:48:26','2026-10-17 18:48:26');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wallet_recharges`
--

DROP TABLE IF EXISTS `wallet_recharges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wallet_recharges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `amount` decimal(20,2) NOT NULL,
  `payment_method` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approval` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wallet_recharges_user_id_foreign` (`user_id`),
  KEY `wallet_recharges_approval_index` (`approval`),
  CONSTRAINT `wallet_recharges_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallet_recharges`
--

LOCK TABLES `wallet_recharges` WRITE;
/*!40000 ALTER TABLE `wallet_recharges` DISABLE KEYS */;
INSERT INTO `wallet_recharges` VALUES (1,7,100.00,'bank_transfer','TXN-B8D0E2F333','approved','2026-10-16 18:48:35','2026-10-17 18:48:35'),(2,8,50.00,'bkash','TXN-09961A274D','approved','2026-10-15 18:48:35','2026-10-17 18:48:35'),(3,9,200.00,'bank_transfer','TXN-BA771948DC','pending','2026-10-14 18:48:35','2026-10-17 18:48:35'),(4,7,75.00,'rocket','TXN-C2F3DCB344','pending','2026-10-13 18:48:35','2026-10-17 18:48:35'),(5,8,30.00,'bkash','TXN-E553518E67','rejected','2026-10-12 18:48:35','2026-10-17 18:48:35');
/*!40000 ALTER TABLE `wallet_recharges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warranties`
--

DROP TABLE IF EXISTS `warranties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `warranties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warranties`
--

LOCK TABLES `warranties` WRITE;
/*!40000 ALTER TABLE `warranties` DISABLE KEYS */;
INSERT INTO `warranties` VALUES (1,'6 months','2026-10-17 18:48:22','2026-10-17 18:48:22'),(2,'1 year','2026-10-17 18:48:22','2026-10-17 18:48:22'),(3,'2 years','2026-10-17 18:48:22','2026-10-17 18:48:22');
/*!40000 ALTER TABLE `warranties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wholesale_prices`
--

DROP TABLE IF EXISTS `wholesale_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wholesale_prices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_stock_id` bigint unsigned NOT NULL,
  `min_qty` int unsigned NOT NULL,
  `max_qty` int unsigned NOT NULL,
  `price` decimal(20,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wholesale_prices_product_stock_id_min_qty_index` (`product_stock_id`,`min_qty`),
  CONSTRAINT `wholesale_prices_product_stock_id_foreign` FOREIGN KEY (`product_stock_id`) REFERENCES `product_stocks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wholesale_prices`
--

LOCK TABLES `wholesale_prices` WRITE;
/*!40000 ALTER TABLE `wholesale_prices` DISABLE KEYS */;
INSERT INTO `wholesale_prices` VALUES (1,38,10,49,36.80,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(2,38,50,199,34.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(3,38,200,9999,31.20,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(4,39,10,49,55.20,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(5,39,50,199,51.00,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(6,39,200,9999,46.80,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(7,40,10,49,23.92,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(8,40,50,199,22.10,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(9,40,200,9999,20.28,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(10,41,10,49,70.84,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(11,41,50,199,65.45,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(12,41,200,9999,60.06,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(13,42,10,49,22.08,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(14,42,50,199,20.40,'2026-10-17 18:48:22','2026-10-17 18:48:22'),(15,42,200,9999,18.72,'2026-10-17 18:48:22','2026-10-17 18:48:22');
/*!40000 ALTER TABLE `wholesale_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlists`
--

DROP TABLE IF EXISTS `wishlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wishlists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wishlists_user_id_product_id_unique` (`user_id`,`product_id`),
  KEY `wishlists_product_id_foreign` (`product_id`),
  CONSTRAINT `wishlists_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlists`
--

LOCK TABLES `wishlists` WRITE;
/*!40000 ALTER TABLE `wishlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `wishlists` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: wowmart
-- ------------------------------------------------------
-- Server version	8.4.3

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
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
