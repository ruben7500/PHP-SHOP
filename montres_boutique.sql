-- MySQL dump 10.13  Distrib 9.2.0, for Win64 (x86_64)
--
-- Host: localhost    Database: montres_boutique
-- ------------------------------------------------------
-- Server version	9.2.0

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
-- Table structure for table `invoice`
--

DROP TABLE IF EXISTS `invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `date_transaction` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `montant` decimal(10,2) NOT NULL,
  `adresse_facturation` varchar(255) NOT NULL,
  `ville` varchar(100) NOT NULL,
  `code_postal` varchar(10) NOT NULL,
  `statut` enum('en_attente','payee','annulee') DEFAULT 'en_attente',
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice`
--

LOCK TABLES `invoice` WRITE;
/*!40000 ALTER TABLE `invoice` DISABLE KEYS */;
INSERT INTO `invoice` VALUES (1,3,'2025-03-03 14:27:49',5900.00,'23 Avenue Des Champs Elys├®es','Paris','75008','en_attente'),(2,3,'2025-03-03 14:29:02',3450.00,'23 Avenue Des Champs Elys├®es','Paris','75008','en_attente'),(3,3,'2025-03-03 14:30:00',2950.00,'23 Avenue Des Champs Elys├®es','Paris','75008','en_attente'),(5,3,'2025-03-03 14:50:25',2950.00,'23 Avenue Des Champs Elys├®es','Paris','75008','en_attente'),(6,3,'2025-03-03 14:51:00',3450.00,'23 Avenue Des Champs Elys├®es','Paris','75008','en_attente');
/*!40000 ALTER TABLE `invoice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `date_publication` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `marque` varchar(100) DEFAULT NULL,
  `categorie` varchar(100) DEFAULT NULL,
  `caracteristiques` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (1,'Chronographe ├ël├®gance','S├®rie limit├®e avec bracelet en cuir italien. Une montre ├®l├®gante pour toutes les occasions.',2950.00,'assets/images/products/67c5d68929798.jpeg','2025-03-02 15:35:38','Eleganza','Chronographe','Mouvement automatique, Verre saphir, ├ëtanche 50m'),(2,'Aqua Profondeur','├ëtanche ├á 300m, id├®ale pour les plongeurs professionnels.',3450.00,'assets/images/products/67c5b950156d0.jpeg','2025-03-02 15:35:38','Diver Pro','Sport','├ëtanche 300m, Lunette rotative, Luminescence'),(3,'Cosmic Heritage','Mouvement automatique et cadran en nacre. Une pi├¿ce d&#039;exception.',5900.00,'assets/images/products/67c5b95f660a8.jpeg','2025-03-02 15:35:38','Prestige','Luxe','Cadran nacre, Calibre manufacture, R├®serve 72h');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_item` int NOT NULL,
  `quantite` int NOT NULL DEFAULT '1',
  `date_commande` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_item` (`id_item`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`id_item`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,3,3,1,'2025-03-03 14:27:49'),(2,3,2,1,'2025-03-03 14:29:02'),(3,3,1,1,'2025-03-03 14:30:00'),(4,3,1,1,'2025-03-03 14:50:25'),(5,3,2,1,'2025-03-03 14:51:00');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock`
--

DROP TABLE IF EXISTS `stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_item` int NOT NULL,
  `quantite` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_item` (`id_item`),
  CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`id_item`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock`
--

LOCK TABLES `stock` WRITE;
/*!40000 ALTER TABLE `stock` DISABLE KEYS */;
INSERT INTO `stock` VALUES (1,1,13),(2,2,6),(3,3,4);
/*!40000 ALTER TABLE `stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('client','admin') DEFAULT 'client',
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','User','admin@example.com','$2y$10$Ow1RrQQZ6QVX5Vz9Y5LoIOLfBRiRWPuDjDvXzJYJxDYq4/pOHLJAe','client','2025-03-02 15:35:31'),(2,'Chrono','Maxime','a.a@a.com','$2y$12$Q8UoxCmAIIwuIioF.X2bleT4plM.jptRWc1rD57v8YcKBNZR5DQhe','client','2025-03-03 13:28:43'),(3,'b','b','b.b@gmail.com','$2y$12$u5qkI0CYPwsuyraOnNSOFO2ZK3s8zoKTfat1rpxZ2m2tEaeMZvSty','admin','2025-03-03 13:40:49');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-03 17:30:00
