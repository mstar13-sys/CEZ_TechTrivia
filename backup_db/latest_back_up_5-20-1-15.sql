-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: quiz_game
-- ------------------------------------------------------
-- Server version	8.0.45

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
-- Table structure for table `achievement`
--

DROP TABLE IF EXISTS `achievement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `achievement` (
  `achievement_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL,
  `description` varchar(100) NOT NULL,
  `condition_type` varchar(45) NOT NULL,
  `condition_value` int NOT NULL,
  PRIMARY KEY (`achievement_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `achievement`
--

LOCK TABLES `achievement` WRITE;
/*!40000 ALTER TABLE `achievement` DISABLE KEYS */;
INSERT INTO `achievement` VALUES (1,'First Compile','Finish your first quiz.','quiz_count',1),(2,'Warm Start','Finish 5 quizzes.','quiz_count',5),(3,'Daily Driver','Finish 10 quizzes.','quiz_count',10),(4,'Quiz Grinder','Finish 25 quizzes.','quiz_count',25),(5,'Century Club','Reach 100 total XP.','total_xp',100),(6,'XP Collector','Reach 500 total XP.','total_xp',500),(7,'XP Hoarder','Reach 1000 total XP.','total_xp',1000),(8,'Flawless Round','Score 10 in one quiz.','best_score',10),(9,'Perfect Habit','Complete 3 perfect quizzes.','perfect_quiz_count',3),(10,'Perfect Machine','Complete 10 perfect quizzes.','perfect_quiz_count',10),(11,'Back Tomorrow','Build a 2 day streak.','current_streak',2),(12,'Consistent Coder','Build a 5 day streak.','max_streak',5),(13,'No Days Off','Build a 10 day streak.','max_streak',10),(14,'Easy Mode Explorer','Complete 5 easy quizzes.','easy_quiz_count',5),(15,'Easy Mode Veteran','Complete 20 easy quizzes.','easy_quiz_count',20),(16,'Medium Climber','Complete 5 medium quizzes.','medium_quiz_count',5),(17,'Medium Veteran','Complete 20 medium quizzes.','medium_quiz_count',20),(18,'Hard Mode Brave','Complete 3 hard quizzes.','hard_quiz_count',3),(19,'Hard Mode Veteran','Complete 15 hard quizzes.','hard_quiz_count',15),(20,'Boss Level Brain','Complete 30 hard quizzes.','hard_quiz_count',30);
/*!40000 ALTER TABLE `achievement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `choice`
--

DROP TABLE IF EXISTS `choice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `choice` (
  `choice_id` int NOT NULL AUTO_INCREMENT,
  `question_id` int NOT NULL,
  `choice_text` varchar(100) NOT NULL,
  `is_correct` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`choice_id`),
  KEY `idx_choice_question` (`question_id`),
  CONSTRAINT `fk_choice_question` FOREIGN KEY (`question_id`) REFERENCES `question` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1024 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `choice`
--

LOCK TABLES `choice` WRITE;
/*!40000 ALTER TABLE `choice` DISABLE KEYS */;
INSERT INTO `choice` VALUES (1,1,'HTML element',0),(2,1,'Email protocol',0),(3,1,'CSS property',0),(4,1,'Stack',1),(5,2,'Queue',1),(6,2,'HTML element',0),(7,2,'Email protocol',0),(8,2,'CSS property',0),(9,3,'HTML element',0),(10,3,'Linear search',1),(11,3,'Email protocol',0),(12,3,'CSS property',0),(13,4,'HTML element',0),(14,4,'Email protocol',0),(15,4,'Bubble sort',1),(16,4,'CSS property',0),(17,5,'HTML element',0),(18,5,'Email protocol',0),(19,5,'CSS property',0),(20,5,'Hash table',1),(21,6,'Linked list',1),(22,6,'HTML element',0),(23,6,'Email protocol',0),(24,6,'CSS property',0),(25,7,'HTML element',0),(26,7,'Tree',1),(27,7,'Email protocol',0),(28,7,'CSS property',0),(29,8,'HTML element',0),(30,8,'Email protocol',0),(31,8,'Big O',1),(32,8,'CSS property',0),(33,9,'HTML element',0),(34,9,'Email protocol',0),(35,9,'CSS property',0),(36,9,'Binary search',1),(37,10,'Breadth-first search',1),(38,10,'HTML element',0),(39,10,'Email protocol',0),(40,10,'CSS property',0),(41,11,'Database transaction',0),(42,11,'O(log n)',1),(43,11,'HTTP header',0),(44,11,'HTML attribute',0),(45,12,'Database transaction',0),(46,12,'HTTP header',0),(47,12,'O(n log n)',1),(48,12,'HTML attribute',0),(49,13,'Database transaction',0),(50,13,'HTTP header',0),(51,13,'HTML attribute',0),(52,13,'Depth-first search',1),(53,14,'Quick sort',1),(54,14,'Database transaction',0),(55,14,'HTTP header',0),(56,14,'HTML attribute',0),(57,15,'Database transaction',0),(58,15,'Dynamic programming',1),(59,15,'HTTP header',0),(60,15,'HTML attribute',0),(61,16,'Database transaction',0),(62,16,'HTTP header',0),(63,16,'Heap',1),(64,16,'HTML attribute',0),(65,17,'Database transaction',0),(66,17,'HTTP header',0),(67,17,'HTML attribute',0),(68,17,'Dijkstra algorithm',1),(69,18,'Trie',1),(70,18,'Database transaction',0),(71,18,'HTTP header',0),(72,18,'HTML attribute',0),(73,19,'Database transaction',0),(74,19,'Two keys mapping to the same bucket',1),(75,19,'HTTP header',0),(76,19,'HTML attribute',0),(77,20,'Database transaction',0),(78,20,'HTTP header',0),(79,20,'In-order traversal',1),(80,20,'HTML attribute',0),(81,21,'CSS pseudo-class',0),(82,21,'DNS record',0),(83,21,'SQL join type',0),(84,21,'O(n^2)',1),(85,22,'Kruskal algorithm',1),(86,22,'CSS pseudo-class',0),(87,22,'DNS record',0),(88,22,'SQL join type',0),(89,23,'CSS pseudo-class',0),(90,23,'Bellman-Ford algorithm',1),(91,23,'DNS record',0),(92,23,'SQL join type',0),(93,24,'CSS pseudo-class',0),(94,24,'DNS record',0),(95,24,'Backtracking',1),(96,24,'SQL join type',0),(97,25,'CSS pseudo-class',0),(98,25,'DNS record',0),(99,25,'SQL join type',0),(100,25,'NP',1),(101,26,'Ordering directed acyclic graph dependencies',1),(102,26,'CSS pseudo-class',0),(103,26,'DNS record',0),(104,26,'SQL join type',0),(105,27,'CSS pseudo-class',0),(106,27,'Floyd-Warshall algorithm',1),(107,27,'DNS record',0),(108,27,'SQL join type',0),(109,28,'CSS pseudo-class',0),(110,28,'DNS record',0),(111,28,'AVL tree',1),(112,28,'SQL join type',0),(113,29,'CSS pseudo-class',0),(114,29,'DNS record',0),(115,29,'SQL join type',0),(116,29,'Caching function results',1),(117,30,'Exchange argument',1),(118,30,'CSS pseudo-class',0),(119,30,'DNS record',0),(120,30,'SQL join type',0),(121,31,'CSS declaration',0),(122,31,'Structured Query Language',1),(123,31,'JavaScript event',0),(124,31,'Network packet',0),(125,32,'CSS declaration',0),(126,32,'JavaScript event',0),(127,32,'SELECT',1),(128,32,'Network packet',0),(129,33,'CSS declaration',0),(130,33,'JavaScript event',0),(131,33,'Network packet',0),(132,33,'INSERT',1),(133,34,'UPDATE',1),(134,34,'CSS declaration',0),(135,34,'JavaScript event',0),(136,34,'Network packet',0),(137,35,'CSS declaration',0),(138,35,'DELETE',1),(139,35,'JavaScript event',0),(140,35,'Network packet',0),(141,36,'CSS declaration',0),(142,36,'JavaScript event',0),(143,36,'Primary key',1),(144,36,'Network packet',0),(145,37,'CSS declaration',0),(146,37,'JavaScript event',0),(147,37,'Network packet',0),(148,37,'Foreign key',1),(149,38,'WHERE',1),(150,38,'CSS declaration',0),(151,38,'JavaScript event',0),(152,38,'Network packet',0),(153,39,'CSS declaration',0),(154,39,'ORDER BY',1),(155,39,'JavaScript event',0),(156,39,'Network packet',0),(157,40,'CSS declaration',0),(158,40,'JavaScript event',0),(159,40,'CREATE TABLE',1),(160,40,'Network packet',0),(161,41,'HTML heading',0),(162,41,'Graph traversal',0),(163,41,'TLS certificate',0),(164,41,'INNER JOIN',1),(165,42,'LEFT JOIN',1),(166,42,'HTML heading',0),(167,42,'Graph traversal',0),(168,42,'TLS certificate',0),(169,43,'HTML heading',0),(170,43,'COUNT()',1),(171,43,'Graph traversal',0),(172,43,'TLS certificate',0),(173,44,'HTML heading',0),(174,44,'Graph traversal',0),(175,44,'HAVING',1),(176,44,'TLS certificate',0),(177,45,'HTML heading',0),(178,45,'Graph traversal',0),(179,45,'TLS certificate',0),(180,45,'Data duplication and update anomalies',1),(181,46,'Atomicity',1),(182,46,'HTML heading',0),(183,46,'Graph traversal',0),(184,46,'TLS certificate',0),(185,47,'HTML heading',0),(186,47,'Index',1),(187,47,'Graph traversal',0),(188,47,'TLS certificate',0),(189,48,'HTML heading',0),(190,48,'Graph traversal',0),(191,48,'UNIQUE',1),(192,48,'TLS certificate',0),(193,49,'HTML heading',0),(194,49,'Graph traversal',0),(195,49,'TLS certificate',0),(196,49,'DROP TABLE',1),(197,50,'DISTINCT',1),(198,50,'HTML heading',0),(199,50,'Graph traversal',0),(200,50,'TLS certificate',0),(201,51,'CSS selector',0),(202,51,'Atomicity, Consistency, Isolation, Durability',1),(203,51,'Array method',0),(204,51,'Router protocol',0),(205,52,'CSS selector',0),(206,52,'Array method',0),(207,52,'Dirty read',1),(208,52,'Router protocol',0),(209,53,'CSS selector',0),(210,53,'Array method',0),(211,53,'Router protocol',0),(212,53,'Read committed',1),(213,54,'Transactions waiting on each other forever',1),(214,54,'CSS selector',0),(215,54,'Array method',0),(216,54,'Router protocol',0),(217,55,'CSS selector',0),(218,55,'Third normal form',1),(219,55,'Array method',0),(220,55,'Router protocol',0),(221,56,'CSS selector',0),(222,56,'Array method',0),(223,56,'The query execution plan',1),(224,56,'Router protocol',0),(225,57,'CSS selector',0),(226,57,'Array method',0),(227,57,'Router protocol',0),(228,57,'B-tree index',1),(229,58,'An index on multiple columns',1),(230,58,'CSS selector',0),(231,58,'Array method',0),(232,58,'Router protocol',0),(233,59,'CSS selector',0),(234,59,'Saved SQL logic executed by the database',1),(235,59,'Array method',0),(236,59,'Router protocol',0),(237,60,'CSS selector',0),(238,60,'Array method',0),(239,60,'Primary-replica replication',1),(240,60,'Router protocol',0),(241,61,'Java compiler',0),(242,61,'HyperText Markup Language',1),(243,61,'Database engine',0),(244,61,'Network router',0),(245,62,'Java compiler',0),(246,62,'Database engine',0),(247,62,'<a>',1),(248,62,'Network router',0),(249,63,'Java compiler',0),(250,63,'Database engine',0),(251,63,'Network router',0),(252,63,'<br>',1),(253,64,'color',1),(254,64,'Java compiler',0),(255,64,'Database engine',0),(256,64,'Network router',0),(257,65,'Java compiler',0),(258,65,'background-color',1),(259,65,'Database engine',0),(260,65,'Network router',0),(261,66,'Java compiler',0),(262,66,'Database engine',0),(263,66,'alt',1),(264,66,'Network router',0),(265,67,'Java compiler',0),(266,67,'Database engine',0),(267,67,'Network router',0),(268,67,'<h1>',1),(269,68,'font-size',1),(270,68,'Java compiler',0),(271,68,'Database engine',0),(272,68,'Network router',0),(273,69,'Java compiler',0),(274,69,'<ol>',1),(275,69,'Database engine',0),(276,69,'Network router',0),(277,70,'Java compiler',0),(278,70,'Database engine',0),(279,70,'.css',1),(280,70,'Network router',0),(281,71,'Inline database query',0),(282,71,'Server firewall rule',0),(283,71,'Command-line package',0),(284,71,'#main',1),(285,72,'.card',1),(286,72,'Inline database query',0),(287,72,'Server firewall rule',0),(288,72,'Command-line package',0),(289,73,'Inline database query',0),(290,73,'Content, padding, border, margin',1),(291,73,'Server firewall rule',0),(292,73,'Command-line package',0),(293,74,'Inline database query',0),(294,74,'Server firewall rule',0),(295,74,'display: flex',1),(296,74,'Command-line package',0),(297,75,'Inline database query',0),(298,75,'Server firewall rule',0),(299,75,'Command-line package',0),(300,75,'display: grid',1),(301,76,'gap',1),(302,76,'Inline database query',0),(303,76,'Server firewall rule',0),(304,76,'Command-line package',0),(305,77,'Inline database query',0),(306,77,'rem',1),(307,77,'Server firewall rule',0),(308,77,'Command-line package',0),(309,78,'Inline database query',0),(310,78,'Server firewall rule',0),(311,78,'`:hover`',1),(312,78,'Command-line package',0),(313,79,'Inline database query',0),(314,79,'Server firewall rule',0),(315,79,'Command-line package',0),(316,79,'object-fit',1),(317,80,'<nav>',1),(318,80,'Inline database query',0),(319,80,'Server firewall rule',0),(320,80,'Command-line package',0),(321,81,'HTTP status code',0),(322,81,'@media',1),(323,81,'SQL isolation level',0),(324,81,'Binary search step',0),(325,82,'HTTP status code',0),(326,82,'SQL isolation level',0),(327,82,'Container queries',1),(328,82,'Binary search step',0),(329,83,'HTTP status code',0),(330,83,'SQL isolation level',0),(331,83,'Binary search step',0),(332,83,'ID selector',1),(333,84,'Stacking order',1),(334,84,'HTTP status code',0),(335,84,'SQL isolation level',0),(336,84,'Binary search step',0),(337,85,'HTTP status code',0),(338,85,'absolute',1),(339,85,'SQL isolation level',0),(340,85,'Binary search step',0),(341,86,'HTTP status code',0),(342,86,'SQL isolation level',0),(343,86,'clamp()',1),(344,86,'Binary search step',0),(345,87,'HTTP status code',0),(346,87,'SQL isolation level',0),(347,87,'Binary search step',0),(348,87,'aria-label',1),(349,88,'transition',1),(350,88,'HTTP status code',0),(351,88,'SQL isolation level',0),(352,88,'Binary search step',0),(353,89,'HTTP status code',0),(354,89,'lazy',1),(355,89,'SQL isolation level',0),(356,89,'Binary search step',0),(357,90,'HTTP status code',0),(358,90,'SQL isolation level',0),(359,90,'1 / -1',1),(360,90,'Binary search step',0),(361,91,'SELECT statement',0),(362,91,'CSS selector only',0),(363,91,'Network protocol',0),(364,91,'let',1),(365,92,'const',1),(366,92,'SELECT statement',0),(367,92,'CSS selector only',0),(368,92,'Network protocol',0),(369,93,'SELECT statement',0),(370,93,'push()',1),(371,93,'CSS selector only',0),(372,93,'Network protocol',0),(373,94,'SELECT statement',0),(374,94,'CSS selector only',0),(375,94,'pop()',1),(376,94,'Network protocol',0),(377,95,'SELECT statement',0),(378,95,'CSS selector only',0),(379,95,'Network protocol',0),(380,95,'object',1),(381,96,'===',1),(382,96,'SELECT statement',0),(383,96,'CSS selector only',0),(384,96,'Network protocol',0),(385,97,'SELECT statement',0),(386,97,'console.log()',1),(387,97,'CSS selector only',0),(388,97,'Network protocol',0),(389,98,'SELECT statement',0),(390,98,'CSS selector only',0),(391,98,'querySelector()',1),(392,98,'Network protocol',0),(393,99,'SELECT statement',0),(394,99,'CSS selector only',0),(395,99,'Network protocol',0),(396,99,'// comment',1),(397,100,'JSON.stringify()',1),(398,100,'SELECT statement',0),(399,100,'CSS selector only',0),(400,100,'Network protocol',0),(401,101,'HTML document type',0),(402,101,'A function passed to another function',1),(403,101,'Database primary key',0),(404,101,'IP subnet mask',0),(405,102,'HTML document type',0),(406,102,'Database primary key',0),(407,102,'All promises to fulfill',1),(408,102,'IP subnet mask',0),(409,103,'HTML document type',0),(410,103,'Database primary key',0),(411,103,'IP subnet mask',0),(412,103,'map()',1),(413,104,'filter()',1),(414,104,'HTML document type',0),(415,104,'Database primary key',0),(416,104,'IP subnet mask',0),(417,105,'HTML document type',0),(418,105,'await',1),(419,105,'Database primary key',0),(420,105,'IP subnet mask',0),(421,106,'HTML document type',0),(422,106,'Database primary key',0),(423,106,'Handling child events from a parent listener',1),(424,106,'IP subnet mask',0),(425,107,'HTML document type',0),(426,107,'Database primary key',0),(427,107,'IP subnet mask',0),(428,107,'addEventListener()',1),(429,108,'...',1),(430,108,'HTML document type',0),(431,108,'Database primary key',0),(432,108,'IP subnet mask',0),(433,109,'HTML document type',0),(434,109,'try...catch',1),(435,109,'Database primary key',0),(436,109,'IP subnet mask',0),(437,110,'HTML document type',0),(438,110,'Database primary key',0),(439,110,'Lexical this',1),(440,110,'IP subnet mask',0),(441,111,'CSS grid track',0),(442,111,'SQL transaction lock',0),(443,111,'Router forwarding table',0),(444,111,'A function retaining access to outer scope',1),(445,112,'Microtask queue',1),(446,112,'CSS grid track',0),(447,112,'SQL transaction lock',0),(448,112,'Router forwarding table',0),(449,113,'CSS grid track',0),(450,113,'How often a function runs after rapid events',1),(451,113,'SQL transaction lock',0),(452,113,'Router forwarding table',0),(453,114,'CSS grid track',0),(454,114,'SQL transaction lock',0),(455,114,'How often a function runs over time',1),(456,114,'Router forwarding table',0),(457,115,'CSS grid track',0),(458,115,'SQL transaction lock',0),(459,115,'Router forwarding table',0),(460,115,'IntersectionObserver',1),(461,116,'cancelAnimationFrame()',1),(462,116,'CSS grid track',0),(463,116,'SQL transaction lock',0),(464,116,'Router forwarding table',0),(465,117,'CSS grid track',0),(466,117,'Declarations being processed before execution',1),(467,117,'SQL transaction lock',0),(468,117,'Router forwarding table',0),(469,118,'CSS grid track',0),(470,118,'SQL transaction lock',0),(471,118,'AbortController',1),(472,118,'Router forwarding table',0),(473,119,'CSS grid track',0),(474,119,'SQL transaction lock',0),(475,119,'Router forwarding table',0),(476,119,'Errors from reading nullish properties',1),(477,120,'import { name } from \"module\"',1),(478,120,'CSS grid track',0),(479,120,'SQL transaction lock',0),(480,120,'Router forwarding table',0),(481,121,'JavaScript variable',0),(482,121,'Internet Protocol',1),(483,121,'CSS property',0),(484,121,'SQL aggregate',0),(485,122,'JavaScript variable',0),(486,122,'CSS property',0),(487,122,'Domain Name System',1),(488,122,'SQL aggregate',0),(489,123,'JavaScript variable',0),(490,123,'CSS property',0),(491,123,'SQL aggregate',0),(492,123,'HTTP',1),(493,124,'HTTPS',1),(494,124,'JavaScript variable',0),(495,124,'CSS property',0),(496,124,'SQL aggregate',0),(497,125,'JavaScript variable',0),(498,125,'ping',1),(499,125,'CSS property',0),(500,125,'SQL aggregate',0),(501,126,'JavaScript variable',0),(502,126,'CSS property',0),(503,126,'Router',1),(504,126,'SQL aggregate',0),(505,127,'JavaScript variable',0),(506,127,'CSS property',0),(507,127,'SQL aggregate',0),(508,127,'Switch',1),(509,128,'SMTP',1),(510,128,'JavaScript variable',0),(511,128,'CSS property',0),(512,128,'SQL aggregate',0),(513,129,'JavaScript variable',0),(514,129,'MAC address',1),(515,129,'CSS property',0),(516,129,'SQL aggregate',0),(517,130,'JavaScript variable',0),(518,130,'CSS property',0),(519,130,'DHCP',1),(520,130,'SQL aggregate',0),(521,131,'HTML form tag',0),(522,131,'Array sorting method',0),(523,131,'Database index',0),(524,131,'TCP',1),(525,132,'UDP',1),(526,132,'HTML form tag',0),(527,132,'Array sorting method',0),(528,132,'Database index',0),(529,133,'HTML form tag',0),(530,133,'80',1),(531,133,'Array sorting method',0),(532,133,'Database index',0),(533,134,'HTML form tag',0),(534,134,'Array sorting method',0),(535,134,'443',1),(536,134,'Database index',0),(537,135,'HTML form tag',0),(538,135,'Array sorting method',0),(539,135,'Database index',0),(540,135,'Translates private addresses to public addresses',1),(541,136,'Dividing a network into smaller networks',1),(542,136,'HTML form tag',0),(543,136,'Array sorting method',0),(544,136,'Database index',0),(545,137,'HTML form tag',0),(546,137,'A record',1),(547,137,'Array sorting method',0),(548,137,'Database index',0),(549,138,'HTML form tag',0),(550,138,'Array sorting method',0),(551,138,'AAAA record',1),(552,138,'Database index',0),(553,139,'HTML form tag',0),(554,139,'Array sorting method',0),(555,139,'Database index',0),(556,139,'ARP',1),(557,140,'traceroute',1),(558,140,'HTML form tag',0),(559,140,'Array sorting method',0),(560,140,'Database index',0),(561,141,'DOM event listener',0),(562,141,'7',1),(563,141,'CSS media rule',0),(564,141,'Primary key constraint',0),(565,142,'DOM event listener',0),(566,142,'CSS media rule',0),(567,142,'Network layer',1),(568,142,'Primary key constraint',0),(569,143,'DOM event listener',0),(570,143,'CSS media rule',0),(571,143,'Primary key constraint',0),(572,143,'Transport layer',1),(573,144,'Encrypted and authenticated communication',1),(574,144,'DOM event listener',0),(575,144,'CSS media rule',0),(576,144,'Primary key constraint',0),(577,145,'DOM event listener',0),(578,145,'Stateful firewall',1),(579,145,'CSS media rule',0),(580,145,'Primary key constraint',0),(581,146,'DOM event listener',0),(582,146,'CSS media rule',0),(583,146,'Representing IP network prefixes',1),(584,146,'Primary key constraint',0),(585,147,'DOM event listener',0),(586,147,'CSS media rule',0),(587,147,'Primary key constraint',0),(588,147,'10.0.0.0/8',1),(589,148,'Backend servers',1),(590,148,'DOM event listener',0),(591,148,'CSS media rule',0),(592,148,'Primary key constraint',0),(593,149,'DOM event listener',0),(594,149,'MX record',1),(595,149,'CSS media rule',0),(596,149,'Primary key constraint',0),(597,150,'DOM event listener',0),(598,150,'CSS media rule',0),(599,150,'Distributing traffic across servers',1),(600,150,'Primary key constraint',0);
/*!40000 ALTER TABLE `choice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gamesession`
--

DROP TABLE IF EXISTS `gamesession`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gamesession` (
  `session_id` int NOT NULL AUTO_INCREMENT,
  `player_id` int DEFAULT NULL,
  `date_played` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_score` int DEFAULT '0',
  `mode` varchar(45) DEFAULT NULL,
  `xp_earned` int DEFAULT '0',
  `category` varchar(60) DEFAULT 'General',
  `difficulty` enum('easy','medium','hard') DEFAULT 'easy',
  PRIMARY KEY (`session_id`),
  KEY `fk_gamesession_player` (`player_id`),
  CONSTRAINT `fk_gamesession_player` FOREIGN KEY (`player_id`) REFERENCES `player` (`player_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamesession`
--

LOCK TABLES `gamesession` WRITE;
/*!40000 ALTER TABLE `gamesession` DISABLE KEYS */;
/*!40000 ALTER TABLE `gamesession` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `player`
--

DROP TABLE IF EXISTS `player`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `player` (
  `player_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('player','admin') DEFAULT 'player',
  `total_xp` int DEFAULT '0',
  `level` int DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`player_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `player`
--

LOCK TABLES `player` WRITE;
/*!40000 ALTER TABLE `player` DISABLE KEYS */;
INSERT INTO `player` VALUES (1,'admin','admin@ceztechreviewer.com','$2y$12$yr8X08uTiBNpRl1rJokoZewlJFWxRdIOUKtStNtRfLS/UYi9JIEAa','admin',0,1,'2026-05-20 12:29:34'),(2,'Kent','zabalakentlester@gmail.com','$2y$12$oAgjfPingCtcDWpiYD2dzuFVlAYi2YCn0Bq0ghJS1XRaOBvV5Z.ra','player',0,1,'2026-05-20 12:50:17'),(3,'lester','lester@gmail.com','$2y$12$zoroSObjCPT5s5onIAHIm.yOn.vfMI6AtJX/d2mLtXGYC3ROkGVcK','player',0,1,'2026-05-20 12:53:53');
/*!40000 ALTER TABLE `player` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playerachievement`
--

DROP TABLE IF EXISTS `playerachievement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `playerachievement` (
  `player_achievement_id` int NOT NULL AUTO_INCREMENT,
  `player_id` int NOT NULL,
  `achievement_id` int NOT NULL,
  `date_unlocked` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`player_achievement_id`),
  UNIQUE KEY `unique_player_achievement` (`player_id`,`achievement_id`),
  KEY `fk_playerachievement_achievement` (`achievement_id`),
  CONSTRAINT `fk_playerachievement_achievement` FOREIGN KEY (`achievement_id`) REFERENCES `achievement` (`achievement_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_playerachievement_player` FOREIGN KEY (`player_id`) REFERENCES `player` (`player_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playerachievement`
--

LOCK TABLES `playerachievement` WRITE;
/*!40000 ALTER TABLE `playerachievement` DISABLE KEYS */;
/*!40000 ALTER TABLE `playerachievement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playeranswer`
--

DROP TABLE IF EXISTS `playeranswer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `playeranswer` (
  `answer_id` int NOT NULL AUTO_INCREMENT,
  `session_id` int DEFAULT NULL,
  `question_id` int DEFAULT NULL,
  `choice_id` int DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`answer_id`),
  KEY `fk_playeranswer_session` (`session_id`),
  KEY `fk_playeranswer_question` (`question_id`),
  KEY `fk_playeranswer_choice` (`choice_id`),
  CONSTRAINT `fk_playeranswer_choice` FOREIGN KEY (`choice_id`) REFERENCES `choice` (`choice_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_playeranswer_question` FOREIGN KEY (`question_id`) REFERENCES `question` (`question_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_playeranswer_session` FOREIGN KEY (`session_id`) REFERENCES `gamesession` (`session_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playeranswer`
--

LOCK TABLES `playeranswer` WRITE;
/*!40000 ALTER TABLE `playeranswer` DISABLE KEYS */;
/*!40000 ALTER TABLE `playeranswer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `question`
--

DROP TABLE IF EXISTS `question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `question` (
  `question_id` int NOT NULL AUTO_INCREMENT,
  `question_text` varchar(255) NOT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'easy',
  `category` varchar(60) DEFAULT 'General',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`question_id`),
  KEY `idx_question_category_difficulty` (`category`,`difficulty`)
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question`
--

LOCK TABLES `question` WRITE;
/*!40000 ALTER TABLE `question` DISABLE KEYS */;
INSERT INTO `question` VALUES (1,'Which data structure uses LIFO order?','easy','Algorithms','2026-05-20 12:29:34'),(2,'Which data structure uses FIFO order?','easy','Algorithms','2026-05-20 12:29:34'),(3,'Which search works by checking every item one by one?','easy','Algorithms','2026-05-20 12:29:34'),(4,'Which algorithm repeatedly swaps adjacent out-of-order items?','easy','Algorithms','2026-05-20 12:29:34'),(5,'Which data structure stores key-value pairs?','easy','Algorithms','2026-05-20 12:29:34'),(6,'Which structure has nodes connected by references?','easy','Algorithms','2026-05-20 12:29:34'),(7,'Which structure has a root and child nodes?','easy','Algorithms','2026-05-20 12:29:34'),(8,'Which notation describes algorithm growth as input grows?','easy','Algorithms','2026-05-20 12:29:34'),(9,'Which algorithm needs sorted input to split the search range?','easy','Algorithms','2026-05-20 12:29:34'),(10,'Which graph traversal uses a queue?','easy','Algorithms','2026-05-20 12:29:34'),(11,'What is the time complexity of binary search?','medium','Algorithms','2026-05-20 12:29:34'),(12,'What is the average time complexity of merge sort?','medium','Algorithms','2026-05-20 12:29:34'),(13,'Which graph traversal usually uses a stack or recursion?','medium','Algorithms','2026-05-20 12:29:34'),(14,'Which sorting algorithm picks a pivot and partitions items?','medium','Algorithms','2026-05-20 12:29:34'),(15,'Which technique stores subproblem results to avoid recomputation?','medium','Algorithms','2026-05-20 12:29:34'),(16,'Which structure supports fast min or max retrieval?','medium','Algorithms','2026-05-20 12:29:34'),(17,'Which algorithm finds shortest paths with nonnegative weights?','medium','Algorithms','2026-05-20 12:29:34'),(18,'Which data structure is often used for prefix searching?','medium','Algorithms','2026-05-20 12:29:34'),(19,'What is a collision in a hash table?','medium','Algorithms','2026-05-20 12:29:34'),(20,'Which traversal visits left, root, then right in a binary tree?','medium','Algorithms','2026-05-20 12:29:34'),(21,'What is the worst-case time complexity of quick sort?','hard','Algorithms','2026-05-20 12:29:34'),(22,'Which algorithm finds a minimum spanning tree using edges by weight?','hard','Algorithms','2026-05-20 12:29:34'),(23,'Which algorithm detects shortest paths with negative edges?','hard','Algorithms','2026-05-20 12:29:34'),(24,'Which technique explores possible solutions and undoes choices?','hard','Algorithms','2026-05-20 12:29:34'),(25,'Which class contains problems verifiable in polynomial time?','hard','Algorithms','2026-05-20 12:29:34'),(26,'What is topological sorting used for?','hard','Algorithms','2026-05-20 12:29:34'),(27,'Which algorithm computes all-pairs shortest paths?','hard','Algorithms','2026-05-20 12:29:34'),(28,'Which tree keeps itself balanced using rotations?','hard','Algorithms','2026-05-20 12:29:34'),(29,'What is memoization?','hard','Algorithms','2026-05-20 12:29:34'),(30,'Which technique proves greedy choices are safe?','hard','Algorithms','2026-05-20 12:29:34'),(31,'What does SQL stand for?','easy','Databases','2026-05-20 12:29:34'),(32,'Which SQL command retrieves data?','easy','Databases','2026-05-20 12:29:34'),(33,'Which SQL command adds new rows?','easy','Databases','2026-05-20 12:29:34'),(34,'Which SQL command changes existing rows?','easy','Databases','2026-05-20 12:29:34'),(35,'Which SQL command removes rows?','easy','Databases','2026-05-20 12:29:34'),(36,'Which key uniquely identifies a table row?','easy','Databases','2026-05-20 12:29:34'),(37,'Which key points to a row in another table?','easy','Databases','2026-05-20 12:29:34'),(38,'Which clause filters rows?','easy','Databases','2026-05-20 12:29:34'),(39,'Which clause sorts query results?','easy','Databases','2026-05-20 12:29:34'),(40,'Which SQL command creates a table?','easy','Databases','2026-05-20 12:29:34'),(41,'Which join returns matching rows from both tables?','medium','Databases','2026-05-20 12:29:34'),(42,'Which join returns all left table rows and matching right rows?','medium','Databases','2026-05-20 12:29:34'),(43,'Which aggregate counts rows?','medium','Databases','2026-05-20 12:29:34'),(44,'Which clause filters grouped results?','medium','Databases','2026-05-20 12:29:34'),(45,'What does normalization reduce?','medium','Databases','2026-05-20 12:29:34'),(46,'Which transaction property means all changes succeed or none do?','medium','Databases','2026-05-20 12:29:34'),(47,'Which SQL feature speeds lookups on columns?','medium','Databases','2026-05-20 12:29:34'),(48,'Which constraint prevents duplicate values?','medium','Databases','2026-05-20 12:29:34'),(49,'Which command removes a table and its data?','medium','Databases','2026-05-20 12:29:34'),(50,'Which SQL keyword limits duplicate result rows?','medium','Databases','2026-05-20 12:29:34'),(51,'What does ACID stand for in database transactions?','hard','Databases','2026-05-20 12:29:34'),(52,'Which isolation problem reads uncommitted data?','hard','Databases','2026-05-20 12:29:34'),(53,'Which isolation level prevents dirty reads but may allow non-repeatable reads?','hard','Databases','2026-05-20 12:29:34'),(54,'What is a deadlock?','hard','Databases','2026-05-20 12:29:34'),(55,'Which normal form removes transitive dependencies?','hard','Databases','2026-05-20 12:29:34'),(56,'What does EXPLAIN show?','hard','Databases','2026-05-20 12:29:34'),(57,'Which index type is commonly used for equality lookups?','hard','Databases','2026-05-20 12:29:34'),(58,'What is a composite index?','hard','Databases','2026-05-20 12:29:34'),(59,'What is a stored procedure?','hard','Databases','2026-05-20 12:29:34'),(60,'Which replication setup has one writer and read replicas?','hard','Databases','2026-05-20 12:29:34'),(61,'What does HTML stand for?','easy','HTML & CSS','2026-05-20 12:29:34'),(62,'Which tag creates a hyperlink in HTML?','easy','HTML & CSS','2026-05-20 12:29:34'),(63,'Which HTML tag inserts a line break?','easy','HTML & CSS','2026-05-20 12:29:34'),(64,'Which CSS property changes text color?','easy','HTML & CSS','2026-05-20 12:29:34'),(65,'Which CSS property changes the background color?','easy','HTML & CSS','2026-05-20 12:29:34'),(66,'Which attribute provides alternative text for an image?','easy','HTML & CSS','2026-05-20 12:29:34'),(67,'Which tag is used for the largest heading?','easy','HTML & CSS','2026-05-20 12:29:34'),(68,'Which CSS property controls text size?','easy','HTML & CSS','2026-05-20 12:29:34'),(69,'Which HTML tag creates an ordered list?','easy','HTML & CSS','2026-05-20 12:29:34'),(70,'Which file extension is commonly used for CSS files?','easy','HTML & CSS','2026-05-20 12:29:34'),(71,'Which selector targets an element with id main?','medium','HTML & CSS','2026-05-20 12:29:34'),(72,'Which selector targets elements with class card?','medium','HTML & CSS','2026-05-20 12:29:34'),(73,'What are the four parts of the CSS box model?','medium','HTML & CSS','2026-05-20 12:29:34'),(74,'Which display value enables flexbox layout?','medium','HTML & CSS','2026-05-20 12:29:34'),(75,'Which display value enables CSS Grid layout?','medium','HTML & CSS','2026-05-20 12:29:34'),(76,'Which CSS property controls space between grid rows and columns?','medium','HTML & CSS','2026-05-20 12:29:34'),(77,'Which unit is relative to the root element font size?','medium','HTML & CSS','2026-05-20 12:29:34'),(78,'Which pseudo-class styles a link when hovered?','medium','HTML & CSS','2026-05-20 12:29:34'),(79,'Which CSS property sets how an image fits inside its box?','medium','HTML & CSS','2026-05-20 12:29:34'),(80,'Which HTML element groups navigation links?','medium','HTML & CSS','2026-05-20 12:29:34'),(81,'Which CSS rule applies styles only under certain viewport conditions?','hard','HTML & CSS','2026-05-20 12:29:34'),(82,'Which CSS feature lets elements adapt styles based on container size?','hard','HTML & CSS','2026-05-20 12:29:34'),(83,'Which selector has higher specificity than a class selector?','hard','HTML & CSS','2026-05-20 12:29:34'),(84,'What does z-index control?','hard','HTML & CSS','2026-05-20 12:29:34'),(85,'Which positioning value uses the nearest positioned ancestor?','hard','HTML & CSS','2026-05-20 12:29:34'),(86,'Which CSS function chooses a value within min and max limits?','hard','HTML & CSS','2026-05-20 12:29:34'),(87,'Which attribute improves custom control accessibility by naming its purpose?','hard','HTML & CSS','2026-05-20 12:29:34'),(88,'Which CSS property can create smooth state changes?','hard','HTML & CSS','2026-05-20 12:29:34'),(89,'Which HTML loading value delays offscreen image loading?','hard','HTML & CSS','2026-05-20 12:29:34'),(90,'Which CSS keyword allows a grid item to span every column?','hard','HTML & CSS','2026-05-20 12:29:34'),(91,'Which keyword declares a block-scoped variable that can change?','easy','JavaScript','2026-05-20 12:29:34'),(92,'Which keyword declares a block-scoped constant?','easy','JavaScript','2026-05-20 12:29:34'),(93,'Which method adds an item to the end of an array?','easy','JavaScript','2026-05-20 12:29:34'),(94,'Which method removes the last item from an array?','easy','JavaScript','2026-05-20 12:29:34'),(95,'What value does typeof null return?','easy','JavaScript','2026-05-20 12:29:34'),(96,'Which operator checks strict equality?','easy','JavaScript','2026-05-20 12:29:34'),(97,'Which browser method writes a message to the developer console?','easy','JavaScript','2026-05-20 12:29:34'),(98,'Which method selects the first matching CSS selector?','easy','JavaScript','2026-05-20 12:29:34'),(99,'Which syntax creates a single-line comment?','easy','JavaScript','2026-05-20 12:29:34'),(100,'Which JSON method converts an object to a string?','easy','JavaScript','2026-05-20 12:29:34'),(101,'What is a callback function?','medium','JavaScript','2026-05-20 12:29:34'),(102,'What does Promise.all() wait for?','medium','JavaScript','2026-05-20 12:29:34'),(103,'Which array method creates a new array by transforming each item?','medium','JavaScript','2026-05-20 12:29:34'),(104,'Which array method keeps only items that pass a test?','medium','JavaScript','2026-05-20 12:29:34'),(105,'Which keyword pauses inside an async function until a promise settles?','medium','JavaScript','2026-05-20 12:29:34'),(106,'What is event delegation?','medium','JavaScript','2026-05-20 12:29:34'),(107,'Which method attaches an event listener to an element?','medium','JavaScript','2026-05-20 12:29:34'),(108,'What does the spread syntax use?','medium','JavaScript','2026-05-20 12:29:34'),(109,'Which statement handles errors from risky code?','medium','JavaScript','2026-05-20 12:29:34'),(110,'Which scope do arrow functions use for this?','medium','JavaScript','2026-05-20 12:29:34'),(111,'What is a closure?','hard','JavaScript','2026-05-20 12:29:34'),(112,'Which queue runs promise callbacks after the current call stack?','hard','JavaScript','2026-05-20 12:29:34'),(113,'What does debouncing limit?','hard','JavaScript','2026-05-20 12:29:34'),(114,'What does throttling limit?','hard','JavaScript','2026-05-20 12:29:34'),(115,'Which API observes when an element enters the viewport?','hard','JavaScript','2026-05-20 12:29:34'),(116,'Which method cancels a scheduled requestAnimationFrame callback?','hard','JavaScript','2026-05-20 12:29:34'),(117,'What is hoisting?','hard','JavaScript','2026-05-20 12:29:34'),(118,'Which object can cancel fetch requests?','hard','JavaScript','2026-05-20 12:29:34'),(119,'What does optional chaining prevent?','hard','JavaScript','2026-05-20 12:29:34'),(120,'Which module syntax imports a named export?','hard','JavaScript','2026-05-20 12:29:34'),(121,'What does IP stand for?','easy','Networking','2026-05-20 12:29:34'),(122,'What does DNS stand for?','easy','Networking','2026-05-20 12:29:34'),(123,'Which protocol is commonly used to browse websites?','easy','Networking','2026-05-20 12:29:34'),(124,'Which secure version of HTTP uses encryption?','easy','Networking','2026-05-20 12:29:34'),(125,'Which command checks basic reachability to a host?','easy','Networking','2026-05-20 12:29:34'),(126,'Which device forwards traffic between networks?','easy','Networking','2026-05-20 12:29:34'),(127,'Which device connects devices inside a local network?','easy','Networking','2026-05-20 12:29:34'),(128,'Which protocol is used to send email between mail servers?','easy','Networking','2026-05-20 12:29:34'),(129,'Which address is assigned to a network interface by hardware?','easy','Networking','2026-05-20 12:29:34'),(130,'Which protocol automatically assigns IP addresses?','easy','Networking','2026-05-20 12:29:34'),(131,'Which transport protocol provides reliable ordered delivery?','medium','Networking','2026-05-20 12:29:34'),(132,'Which transport protocol is connectionless and low overhead?','medium','Networking','2026-05-20 12:29:34'),(133,'Which port does HTTP use by default?','medium','Networking','2026-05-20 12:29:34'),(134,'Which port does HTTPS use by default?','medium','Networking','2026-05-20 12:29:34'),(135,'What does NAT do?','medium','Networking','2026-05-20 12:29:34'),(136,'What is subnetting?','medium','Networking','2026-05-20 12:29:34'),(137,'Which DNS record maps a name to an IPv4 address?','medium','Networking','2026-05-20 12:29:34'),(138,'Which DNS record maps a name to an IPv6 address?','medium','Networking','2026-05-20 12:29:34'),(139,'Which protocol resolves IP addresses to MAC addresses on a LAN?','medium','Networking','2026-05-20 12:29:34'),(140,'Which tool traces the route packets take to a destination?','medium','Networking','2026-05-20 12:29:34'),(141,'How many layers are in the OSI model?','hard','Networking','2026-05-20 12:29:34'),(142,'Which OSI layer handles routing between networks?','hard','Networking','2026-05-20 12:29:34'),(143,'Which OSI layer handles TCP and UDP?','hard','Networking','2026-05-20 12:29:34'),(144,'What does TLS primarily provide?','hard','Networking','2026-05-20 12:29:34'),(145,'Which firewall model tracks connection state?','hard','Networking','2026-05-20 12:29:34'),(146,'What is CIDR notation used for?','hard','Networking','2026-05-20 12:29:34'),(147,'Which IPv4 private range starts with 10.?','hard','Networking','2026-05-20 12:29:34'),(148,'What does a reverse proxy usually sit in front of?','hard','Networking','2026-05-20 12:29:34'),(149,'Which DNS record identifies mail servers for a domain?','hard','Networking','2026-05-20 12:29:34'),(150,'What problem does load balancing solve?','hard','Networking','2026-05-20 12:29:34');
/*!40000 ALTER TABLE `question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rank`
--

DROP TABLE IF EXISTS `rank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rank` (
  `rank_id` int NOT NULL AUTO_INCREMENT,
  `rank_name` varchar(45) NOT NULL,
  `min_xp` int NOT NULL,
  `max_xp` int DEFAULT NULL,
  `medal` varchar(45) DEFAULT 'Bronze',
  PRIMARY KEY (`rank_id`),
  UNIQUE KEY `rank_name` (`rank_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rank`
--

LOCK TABLES `rank` WRITE;
/*!40000 ALTER TABLE `rank` DISABLE KEYS */;
INSERT INTO `rank` VALUES (1,'Rookie',0,499,'Bronze'),(2,'Debugger',500,1199,'Bronze'),(3,'Code Runner',1200,2499,'Silver'),(4,'Syntax Specialist',2500,4499,'Silver'),(5,'Stack Solver',4500,6999,'Gold'),(6,'Tech Master',7000,9999,'Gold'),(7,'Legendary Architect',10000,NULL,'Platinum');
/*!40000 ALTER TABLE `rank` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `streak`
--

DROP TABLE IF EXISTS `streak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `streak` (
  `streak_id` int NOT NULL AUTO_INCREMENT,
  `player_id` int DEFAULT NULL,
  `current_streak` int DEFAULT '0',
  `max_streak` int DEFAULT '0',
  `last_played_date` date DEFAULT NULL,
  PRIMARY KEY (`streak_id`),
  UNIQUE KEY `player_id` (`player_id`),
  CONSTRAINT `fk_streak_player` FOREIGN KEY (`player_id`) REFERENCES `player` (`player_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `streak`
--

LOCK TABLES `streak` WRITE;
/*!40000 ALTER TABLE `streak` DISABLE KEYS */;
/*!40000 ALTER TABLE `streak` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-20 13:15:28
