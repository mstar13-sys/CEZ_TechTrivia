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
  `title` varchar(45) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `condition_type` varchar(45) DEFAULT NULL,
  `condition_value` int DEFAULT NULL,
  PRIMARY KEY (`achievement_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `achievement`
--

LOCK TABLES `achievement` WRITE;
/*!40000 ALTER TABLE `achievement` DISABLE KEYS */;
INSERT INTO `achievement` VALUES (1,'First Compile','Finish your first quiz.','quiz_count',1),(2,'Warm Start','Finish 5 quizzes.','quiz_count',5),(3,'Daily Driver','Finish 10 quizzes.','quiz_count',10),(4,'Quiz Grinder','Finish 25 quizzes.','quiz_count',25),(5,'Century Club','Reach 100 total XP.','total_xp',100),(6,'XP Collector','Reach 500 total XP.','total_xp',500),(7,'XP Hoarder','Reach 1000 total XP.','total_xp',1000),(8,'Flawless Round','Score 10 in one quiz.','best_score',10),(9,'Perfect Habit','Complete 3 perfect quizzes.','perfect_quiz_count',3),(10,'Perfect Machine','Complete 10 perfect quizzes.','perfect_quiz_count',10),(11,'Back Tomorrow','Build a 2 day streak.','current_streak',2),(12,'Consistent Coder','Build a 5 day streak.','max_streak',5),(13,'No Days Off','Build a 10 day streak.','max_streak',10),(14,'Easy Mode Explorer','Complete 5 easy quizzes.','easy_quiz_count',5),(15,'Easy Mode Veteran','Complete 20 easy quizzes.','easy_quiz_count',20),(16,'Medium Climber','Complete 5 medium quizzes.','medium_quiz_count',5),(17,'Medium Veteran','Complete 20 medium quizzes.','medium_quiz_count',20),(18,'Hard Mode Brave','Complete 3 hard quizzes.','hard_quiz_count',3),(19,'Hard Mode Veteran','Complete 15 hard quizzes.','hard_quiz_count',15),(20,'Boss Level Brain','Complete 30 hard quizzes.','hard_quiz_count',30),(21,'Mr. Programmer','Reach 200 Total exp','hard_quiz_count',200);
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
  `question_id` int DEFAULT NULL,
  `choice_text` varchar(100) NOT NULL,
  `is_correct` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`choice_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `choice_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `question` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=512 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `choice`
--

LOCK TABLES `choice` WRITE;
/*!40000 ALTER TABLE `choice` DISABLE KEYS */;
INSERT INTO `choice` VALUES (1,1,'HyperText Markup Language',1),(2,1,'HighText Machine Language',0),(3,1,'Hyperlink and Text Markup Language',0),(4,1,'Home Tool Markup Language',0),(5,2,'CSS',1),(6,2,'JavaScript',0),(7,2,'Python',0),(8,2,'XML',0),(9,3,'\"object\"',1),(10,3,'\"null\"',0),(11,3,'\"undefined\"',0),(12,3,'\"string\"',0),(13,4,'Merge Sort',1),(14,4,'Bubble Sort',0),(15,4,'Insertion Sort',0),(16,4,'Selection Sort',0),(17,5,'Domain Name System',1),(18,5,'Digital Network Service',0),(19,5,'Data Node Service',0),(20,5,'Domain Node System',0),(21,6,'Guko',0),(22,6,'Sanny_Tisoy',0),(23,6,'Arvin_Gwapo',0),(24,6,'Bito AI',1),(25,7,'<url>',0),(26,7,'<href>',0),(27,7,'<link>',0),(28,7,'<a>',1),(32,8,'fill',0),(33,8,'bg-color',0),(34,8,'color',0),(35,8,'background-color',1),(39,9,'Defines a button',0),(40,9,'Creates a border',0),(41,9,'Bold text',0),(42,9,'Inserts a line break',1),(46,10,'letter-spacing',0),(47,10,'font-weight',0),(48,10,'text-size',0),(49,10,'font-size',1),(53,11,'Colorful Style Sheets',0),(54,11,'Creative Style System',0),(55,11,'Computer Style Syntax',0),(56,11,'Cascading Style Sheets',1),(60,12,'label',0),(61,12,'src',0),(62,12,'title',0),(63,12,'alt',1),(67,13,'A method for animating elements',0),(68,13,'A grid layout system',0),(69,13,'A CSS 3D transform property',0),(70,13,'Content, padding, border, and margin around an element',1),(74,14,'@classname',0),(75,14,'*classname',0),(76,14,'#classname',0),(77,14,'.classname',1),(81,15,'There is no difference',0),(82,15,'Block elements cannot have children',0),(83,15,'Inline elements are larger',0),(84,15,'Block elements start on a new line; inline do not',1),(88,16,'position: grid',0),(89,16,'layout: grid',0),(90,16,'display: flex',0),(91,16,'display: grid',1),(95,17,'ID > Inline > Element > Class',0),(96,17,'Class > ID > Inline > Element',0),(97,17,'Element > Class > ID > Inline',0),(98,17,'Inline > ID > Class > Element',1),(102,18,'define',0),(103,18,'dim',0),(104,18,'var',0),(105,18,'let / const',1),(109,19,'Reference equality',0),(110,19,'Type only',0),(111,19,'Value only',0),(112,19,'Strict equality (value and type)',1),(116,20,'unshift()',0),(117,20,'shift()',0),(118,20,'pop()',0),(119,20,'push()',1),(123,21,'A function that only runs once',0),(124,21,'A function that returns another function',0),(125,21,'A function that calls itself',0),(126,21,'A function passed as an argument to another function',1),(130,22,'Returns the first resolved promise',0),(131,22,'Cancels all pending promises',0),(132,22,'Runs promises one by one sequentially',0),(133,22,'Runs multiple promises in parallel and waits for all',1),(137,23,'null is a number type',0),(138,23,'undefined means the variable does not exist',0),(139,23,'They are identical',0),(140,23,'null is explicitly set; undefined means not yet assigned',1),(144,24,'Blocking event propagation with stopPropagation',0),(145,24,'Firing custom events manually',0),(146,24,'Removing events from child elements',0),(147,24,'Attaching a single listener to a parent to handle child events',1),(151,25,'undefined always',0),(152,25,'The global window object always',0),(153,25,'The arrow function itself',0),(154,25,'The enclosing lexical (outer) scope',1),(158,26,'SSH',0),(159,26,'HTTP',0),(160,26,'FTP',0),(161,26,'SMTP',1),(165,27,'A password for network access',0),(166,27,'The URL of a website',0),(167,27,'A physical address burned into hardware',0),(168,27,'A unique numerical label assigned to each device on a network',1),(172,28,'HyperText Terminal Protocol',0),(173,28,'HyperText Transport Process',0),(174,28,'HighText Transfer Protocol',0),(175,28,'HyperText Transfer Protocol',1),(179,29,'They are identical',0),(180,29,'TCP is used only for video streaming',0),(181,29,'UDP is slower but more reliable',0),(182,29,'TCP is reliable and ordered; UDP is fast but connectionless',1),(186,30,'Boosting Wi-Fi signal strength',0),(187,30,'Connecting two different internet providers',0),(188,30,'Encrypting network traffic',0),(189,30,'Dividing a network into smaller sub-networks',1),(193,31,'Acts as an antivirus',0),(194,31,'Converts analog signals to digital',0),(195,31,'Stores website files',0),(196,31,'Forwards data packets between different networks',1),(200,32,'A software firewall address',0),(201,32,'A type of wireless protocol',0),(202,32,'An IP address for Apple computers',0),(203,32,'A hardware identifier assigned to a network interface card',1),(207,33,'A routing model with 3 layers',0),(208,33,'A data storage model with 4 layers',0),(209,33,'A security model with 5 layers',0),(210,33,'A networking framework with 7 layers',1),(214,34,'An email security standard',0),(215,34,'A protocol only used for file transfers',0),(216,34,'A faster version of HTTP with no encryption',0),(217,34,'HTTP over encrypted SSL/TLS connection using certificates',1),(221,35,'O(1)',0),(222,35,'O(n²)',0),(223,35,'O(n)',0),(224,35,'O(log n)',1),(228,36,'Priority-based ordering',0),(229,36,'Random access',0),(230,36,'FIFO (First In, First Out)',0),(231,36,'LIFO (Last In, First Out)',1),(235,37,'A hash table implementation',0),(236,37,'A tree structure with two children per node',0),(237,37,'An array with a fixed size',0),(238,37,'A sequence of nodes where each node points to the next',1),(242,38,'DFS cannot handle cyclic graphs',0),(243,38,'They produce identical results always',0),(244,38,'BFS uses a stack; DFS uses a queue',0),(245,38,'BFS explores level by level; DFS goes deep first',1),(249,39,'Only useful for sorting algorithms',0),(250,39,'A way to write code at runtime',0),(251,39,'A programming language paradigm',0),(252,39,'Breaking problems into subproblems and storing results to avoid re-computation',1),(256,40,'O(n)',0),(257,40,'O(log n)',0),(258,40,'O(n log n)',0),(259,40,'O(n²)',1),(263,41,'A circular linked list',0),(264,41,'A type of binary tree',0),(265,41,'A sorted array of key-value pairs',0),(266,41,'A data structure that maps keys to values using a hash function',1),(267,42,'<url>',0),(268,42,'<href>',0),(269,42,'<link>',0),(270,42,'<a>',1),(274,43,'fill',0),(275,43,'bg-color',0),(276,43,'color',0),(277,43,'background-color',1),(281,44,'Defines a button',0),(282,44,'Creates a border',0),(283,44,'Bold text',0),(284,44,'Inserts a line break',1),(288,45,'letter-spacing',0),(289,45,'font-weight',0),(290,45,'text-size',0),(291,45,'font-size',1),(295,46,'Colorful Style Sheets',0),(296,46,'Creative Style System',0),(297,46,'Computer Style Syntax',0),(298,46,'Cascading Style Sheets',1),(302,47,'label',0),(303,47,'src',0),(304,47,'title',0),(305,47,'alt',1),(309,48,'A method for animating elements',0),(310,48,'A grid layout system',0),(311,48,'A CSS 3D transform property',0),(312,48,'Content, padding, border, and margin around an element',1),(316,49,'@classname',0),(317,49,'*classname',0),(318,49,'#classname',0),(319,49,'.classname',1),(323,50,'There is no difference',0),(324,50,'Block elements cannot have children',0),(325,50,'Inline elements are larger',0),(326,50,'Block elements start on a new line; inline do not',1),(330,51,'position: grid',0),(331,51,'layout: grid',0),(332,51,'display: flex',0),(333,51,'display: grid',1),(337,52,'ID > Inline > Element > Class',0),(338,52,'Class > ID > Inline > Element',0),(339,52,'Element > Class > ID > Inline',0),(340,52,'Inline > ID > Class > Element',1),(344,53,'define',0),(345,53,'dim',0),(346,53,'var',0),(347,53,'let / const',1),(351,54,'Reference equality',0),(352,54,'Type only',0),(353,54,'Value only',0),(354,54,'Strict equality (value and type)',1),(358,55,'unshift()',0),(359,55,'shift()',0),(360,55,'pop()',0),(361,55,'push()',1),(365,56,'A function that only runs once',0),(366,56,'A function that returns another function',0),(367,56,'A function that calls itself',0),(368,56,'A function passed as an argument to another function',1),(372,57,'Returns the first resolved promise',0),(373,57,'Cancels all pending promises',0),(374,57,'Runs promises one by one sequentially',0),(375,57,'Runs multiple promises in parallel and waits for all',1),(379,58,'null is a number type',0),(380,58,'undefined means the variable does not exist',0),(381,58,'They are identical',0),(382,58,'null is explicitly set; undefined means not yet assigned',1),(386,59,'Blocking event propagation with stopPropagation',0),(387,59,'Firing custom events manually',0),(388,59,'Removing events from child elements',0),(389,59,'Attaching a single listener to a parent to handle child events',1),(393,60,'undefined always',0),(394,60,'The global window object always',0),(395,60,'The arrow function itself',0),(396,60,'The enclosing lexical (outer) scope',1),(400,61,'SSH',0),(401,61,'HTTP',0),(402,61,'FTP',0),(403,61,'SMTP',1),(407,62,'A password for network access',0),(408,62,'The URL of a website',0),(409,62,'A physical address burned into hardware',0),(410,62,'A unique numerical label assigned to each device on a network',1),(414,63,'HyperText Terminal Protocol',0),(415,63,'HyperText Transport Process',0),(416,63,'HighText Transfer Protocol',0),(417,63,'HyperText Transfer Protocol',1),(421,64,'They are identical',0),(422,64,'TCP is used only for video streaming',0),(423,64,'UDP is slower but more reliable',0),(424,64,'TCP is reliable and ordered; UDP is fast but connectionless',1),(428,65,'Boosting Wi-Fi signal strength',0),(429,65,'Connecting two different internet providers',0),(430,65,'Encrypting network traffic',0),(431,65,'Dividing a network into smaller sub-networks',1),(435,66,'Acts as an antivirus',0),(436,66,'Converts analog signals to digital',0),(437,66,'Stores website files',0),(438,66,'Forwards data packets between different networks',1),(442,67,'A software firewall address',0),(443,67,'A type of wireless protocol',0),(444,67,'An IP address for Apple computers',0),(445,67,'A hardware identifier assigned to a network interface card',1),(449,68,'A routing model with 3 layers',0),(450,68,'A data storage model with 4 layers',0),(451,68,'A security model with 5 layers',0),(452,68,'A networking framework with 7 layers',1),(456,69,'An email security standard',0),(457,69,'A protocol only used for file transfers',0),(458,69,'A faster version of HTTP with no encryption',0),(459,69,'HTTP over encrypted SSL/TLS connection using certificates',1),(463,70,'O(1)',0),(464,70,'O(n²)',0),(465,70,'O(n)',0),(466,70,'O(log n)',1),(470,71,'Priority-based ordering',0),(471,71,'Random access',0),(472,71,'FIFO (First In, First Out)',0),(473,71,'LIFO (Last In, First Out)',1),(477,72,'A hash table implementation',0),(478,72,'A tree structure with two children per node',0),(479,72,'An array with a fixed size',0),(480,72,'A sequence of nodes where each node points to the next',1),(484,73,'DFS cannot handle cyclic graphs',0),(485,73,'They produce identical results always',0),(486,73,'BFS uses a stack; DFS uses a queue',0),(487,73,'BFS explores level by level; DFS goes deep first',1),(491,74,'Only useful for sorting algorithms',0),(492,74,'A way to write code at runtime',0),(493,74,'A programming language paradigm',0),(494,74,'Breaking problems into subproblems and storing results to avoid re-computation',1),(498,75,'O(n)',0),(499,75,'O(log n)',0),(500,75,'O(n log n)',0),(501,75,'O(n²)',1),(505,76,'A circular linked list',0),(506,76,'A type of binary tree',0),(507,76,'A sorted array of key-value pairs',0),(508,76,'A data structure that maps keys to values using a hash function',1);
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
  KEY `player_id` (`player_id`),
  CONSTRAINT `gamesession_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `player` (`player_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamesession`
--

LOCK TABLES `gamesession` WRITE;
/*!40000 ALTER TABLE `gamesession` DISABLE KEYS */;
INSERT INTO `gamesession` VALUES (1,2,'2026-05-02 14:41:16',2,NULL,20,'General','easy'),(2,2,'2026-05-02 14:41:43',0,NULL,0,'General','easy'),(3,4,'2026-05-02 15:16:13',4,NULL,40,'General','easy'),(4,4,'2026-05-02 15:17:08',4,NULL,40,'General','easy'),(5,4,'2026-05-02 15:17:31',5,NULL,50,'General','easy'),(6,2,'2026-05-02 18:11:38',1,NULL,10,'Programming','easy'),(7,2,'2026-05-02 18:13:53',2,NULL,20,'Web Development','easy'),(8,2,'2026-05-02 21:15:01',1,NULL,10,'Algorithms','hard'),(9,2,'2026-05-05 14:34:44',0,NULL,0,'Algorithms','hard'),(10,2,'2026-05-06 16:24:22',1,NULL,10,'JavaScript','hard'),(11,2,'2026-05-06 17:47:52',1,NULL,10,'HTML & CSS','hard'),(12,2,'2026-05-10 16:19:03',0,NULL,0,'Algorithms','hard'),(13,2,'2026-05-10 16:21:33',1,NULL,10,'JavaScript','medium'),(14,2,'2026-05-10 16:25:53',4,NULL,40,'HTML & CSS','easy'),(15,2,'2026-05-10 16:52:24',1,NULL,10,'JavaScript','hard'),(16,5,'2026-05-14 08:54:47',1,NULL,10,'JavaScript','hard'),(17,2,'2026-05-20 06:00:39',1,NULL,10,'Algorithms','medium'),(18,2,'2026-05-20 06:01:34',2,NULL,20,'Web Development','easy'),(19,2,'2026-05-20 12:05:36',3,NULL,30,'Algorithms','hard');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `player`
--

LOCK TABLES `player` WRITE;
/*!40000 ALTER TABLE `player` DISABLE KEYS */;
INSERT INTO `player` VALUES (1,'admin','admin@ceztechreviewer.com','$2y$12$x1ePNZYHAbrSrD8X47tZle4JIfkQ0CgxSR/lbpwkViijpRZo7Or.O','admin',0,1,'2026-05-02 09:54:57'),(2,'Kent','zabalakentlester@gmail.com','$2y$12$n1437SOq3VHkS9A6YPglEeq1HWipUVfDky6577Bert77AGNU3e/Na','player',200,3,'2026-05-02 10:08:09'),(3,'Dan','dan@gmail.com','$2y$12$P.VGhcuVLWjs.WuEcav7SuSpwKJjaXaj.IC4SZr.D6MiLZ/q0LTDu','player',0,1,'2026-05-02 10:09:03'),(4,'nix','nics@gmail.com','$2y$12$EfkkCp7guLLC.SsuVRaStOLdeJ/He8L6wfmrWKepLKnlw27ULhEEe','player',130,2,'2026-05-02 10:37:43'),(5,'guko','guko@gmail.com','$2y$12$Cx20aimjUeCr2ujfmDsxKeThsNdbeZXJFF5M86l0w2k9C5dcLLyWG','player',10,1,'2026-05-02 12:39:07'),(6,'qwwe','kentlester@gmail.com','$2y$12$NbJi.f93J7ZLU63j7mj9auU.tb8qg4lzR5AghctNDJ74tZiD/WnPu','player',0,1,'2026-05-02 14:10:34'),(7,'Philophobia','phobia@gmail.com','$2y$12$ast3NSCQ6QkeuuRepkEKYu7ONGPt320uLZYUjRoRmGkudK4QenurC','player',0,1,'2026-05-10 16:31:19'),(8,'zab','zab@gmail.com','$2y$12$hl.yz.XaNq0tv4n7TGdpMuHUGHG6fjUBDnA3pDciPQVZPaEcc5swW','player',0,1,'2026-05-10 16:40:16'),(9,'pel','pel@gmail.com','$2y$12$35HR0tdGxBSgS7EkRxT./.lIzZYPjKeNV3j6TszuJP7vdyxRS/j6.','player',0,1,'2026-05-10 17:05:55');
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
  `player_id` int DEFAULT NULL,
  `achievement_id` int DEFAULT NULL,
  `date_unlocked` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`player_achievement_id`),
  KEY `player_id` (`player_id`),
  KEY `achievement_id` (`achievement_id`),
  CONSTRAINT `playerachievement_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `player` (`player_id`),
  CONSTRAINT `playerachievement_ibfk_2` FOREIGN KEY (`achievement_id`) REFERENCES `achievement` (`achievement_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playerachievement`
--

LOCK TABLES `playerachievement` WRITE;
/*!40000 ALTER TABLE `playerachievement` DISABLE KEYS */;
INSERT INTO `playerachievement` VALUES (1,2,14,'2026-05-20 12:00:49'),(2,2,18,'2026-05-20 12:00:49'),(3,2,1,'2026-05-20 12:00:49'),(4,2,2,'2026-05-20 12:00:49'),(5,2,3,'2026-05-20 12:00:49'),(6,2,5,'2026-05-20 12:00:49'),(7,4,1,'2026-05-20 12:15:01'),(8,4,5,'2026-05-20 12:15:01'),(9,5,1,'2026-05-20 12:15:01');
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
  KEY `session_id` (`session_id`),
  CONSTRAINT `playeranswer_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `gamesession` (`session_id`)
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
  PRIMARY KEY (`question_id`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question`
--

LOCK TABLES `question` WRITE;
/*!40000 ALTER TABLE `question` DISABLE KEYS */;
INSERT INTO `question` VALUES (1,'What does HTML stand for?','easy','Web Development','2026-05-02 09:55:01'),(2,'Which language is used for styling web pages?','easy','Web Development','2026-05-02 09:55:01'),(3,'What is the output of: console.log(typeof null)?','medium','JavaScript','2026-05-02 09:55:01'),(4,'Which sorting algorithm has O(n log n) average time complexity?','medium','Algorithms','2026-05-02 09:55:01'),(5,'What does DNS stand for?','easy','Networking','2026-05-02 09:55:01'),(6,'Who is the Creator of this system?','easy','Programming','2026-05-02 15:11:36'),(7,'Which HTML tag is used to create a hyperlink?','easy','HTML & CSS','2026-05-02 18:08:32'),(8,'Which property sets the background color in CSS?','easy','HTML & CSS','2026-05-02 18:08:32'),(9,'What does the <br> tag do in HTML?','easy','HTML & CSS','2026-05-02 18:08:32'),(10,'Which CSS property controls the text size?','easy','HTML & CSS','2026-05-02 18:08:32'),(11,'What does CSS stand for?','easy','HTML & CSS','2026-05-02 18:08:32'),(12,'Which HTML attribute specifies an alternate text for an image?','medium','HTML & CSS','2026-05-02 18:08:32'),(13,'What is the CSS box model?','medium','HTML & CSS','2026-05-02 18:08:32'),(14,'Which CSS selector targets elements with a specific class?','medium','HTML & CSS','2026-05-02 18:08:32'),(15,'What is the difference between inline and block elements?','medium','HTML & CSS','2026-05-02 18:08:32'),(16,'Which CSS property is used to create a grid layout?','hard','HTML & CSS','2026-05-02 18:08:32'),(17,'What is the CSS specificity order (highest to lowest)?','hard','HTML & CSS','2026-05-02 18:08:32'),(18,'Which keyword declares a variable in modern JavaScript?','easy','JavaScript','2026-05-02 18:08:32'),(19,'What does === check in JavaScript?','easy','JavaScript','2026-05-02 18:08:32'),(20,'Which method adds an element to the end of an array?','easy','JavaScript','2026-05-02 18:08:32'),(21,'What is a callback function?','medium','JavaScript','2026-05-02 18:08:32'),(22,'What does Promise.all() do?','medium','JavaScript','2026-05-02 18:08:32'),(23,'What is the difference between null and undefined?','medium','JavaScript','2026-05-02 18:08:32'),(24,'What is event delegation?','hard','JavaScript','2026-05-02 18:08:32'),(25,'What does the \"this\" keyword refer to in an arrow function?','hard','JavaScript','2026-05-02 18:08:32'),(26,'Which protocol is used to send emails?','easy','Networking','2026-05-02 18:08:32'),(27,'What is an IP address?','easy','Networking','2026-05-02 18:08:32'),(28,'What does HTTP stand for?','easy','Networking','2026-05-02 18:08:32'),(29,'What is the difference between TCP and UDP?','medium','Networking','2026-05-02 18:08:32'),(30,'What is subnetting?','medium','Networking','2026-05-02 18:08:32'),(31,'What does a router do?','easy','Networking','2026-05-02 18:08:32'),(32,'What is a MAC address?','medium','Networking','2026-05-02 18:08:32'),(33,'What is the OSI model and how many layers does it have?','hard','Networking','2026-05-02 18:08:32'),(34,'What is HTTPS and how does SSL/TLS work?','hard','Networking','2026-05-02 18:08:32'),(35,'What is the time complexity of binary search?','medium','Algorithms','2026-05-02 18:08:32'),(36,'What data structure does a stack use?','easy','Algorithms','2026-05-02 18:08:32'),(37,'What is a linked list?','easy','Algorithms','2026-05-02 18:08:32'),(38,'What is the difference between BFS and DFS?','medium','Algorithms','2026-05-02 18:08:32'),(39,'What is dynamic programming?','hard','Algorithms','2026-05-02 18:08:32'),(40,'What is the worst-case complexity of QuickSort?','hard','Algorithms','2026-05-02 18:08:32'),(41,'What is a hash table?','medium','Algorithms','2026-05-02 18:08:32'),(42,'Which HTML tag is used to create a hyperlink?','easy','HTML & CSS','2026-05-20 11:00:47'),(43,'Which property sets the background color in CSS?','easy','HTML & CSS','2026-05-20 11:00:47'),(44,'What does the <br> tag do in HTML?','easy','HTML & CSS','2026-05-20 11:00:47'),(45,'Which CSS property controls the text size?','easy','HTML & CSS','2026-05-20 11:00:47'),(46,'What does CSS stand for?','easy','HTML & CSS','2026-05-20 11:00:47'),(47,'Which HTML attribute specifies an alternate text for an image?','medium','HTML & CSS','2026-05-20 11:00:47'),(48,'What is the CSS box model?','medium','HTML & CSS','2026-05-20 11:00:47'),(49,'Which CSS selector targets elements with a specific class?','medium','HTML & CSS','2026-05-20 11:00:47'),(50,'What is the difference between inline and block elements?','medium','HTML & CSS','2026-05-20 11:00:47'),(51,'Which CSS property is used to create a grid layout?','hard','HTML & CSS','2026-05-20 11:00:47'),(52,'What is the CSS specificity order (highest to lowest)?','hard','HTML & CSS','2026-05-20 11:00:47'),(53,'Which keyword declares a variable in modern JavaScript?','easy','JavaScript','2026-05-20 11:00:47'),(54,'What does === check in JavaScript?','easy','JavaScript','2026-05-20 11:00:47'),(55,'Which method adds an element to the end of an array?','easy','JavaScript','2026-05-20 11:00:47'),(56,'What is a callback function?','medium','JavaScript','2026-05-20 11:00:47'),(57,'What does Promise.all() do?','medium','JavaScript','2026-05-20 11:00:47'),(58,'What is the difference between null and undefined?','medium','JavaScript','2026-05-20 11:00:47'),(59,'What is event delegation?','hard','JavaScript','2026-05-20 11:00:47'),(60,'What does the \"this\" keyword refer to in an arrow function?','hard','JavaScript','2026-05-20 11:00:47'),(61,'Which protocol is used to send emails?','easy','Networking','2026-05-20 11:00:47'),(62,'What is an IP address?','easy','Networking','2026-05-20 11:00:47'),(63,'What does HTTP stand for?','easy','Networking','2026-05-20 11:00:47'),(64,'What is the difference between TCP and UDP?','medium','Networking','2026-05-20 11:00:47'),(65,'What is subnetting?','medium','Networking','2026-05-20 11:00:47'),(66,'What does a router do?','easy','Networking','2026-05-20 11:00:47'),(67,'What is a MAC address?','medium','Networking','2026-05-20 11:00:47'),(68,'What is the OSI model and how many layers does it have?','hard','Networking','2026-05-20 11:00:47'),(69,'What is HTTPS and how does SSL/TLS work?','hard','Networking','2026-05-20 11:00:47'),(70,'What is the time complexity of binary search?','medium','Algorithms','2026-05-20 11:00:47'),(71,'What data structure does a stack use?','easy','Algorithms','2026-05-20 11:00:47'),(72,'What is a linked list?','easy','Algorithms','2026-05-20 11:00:47'),(73,'What is the difference between BFS and DFS?','medium','Algorithms','2026-05-20 11:00:47'),(74,'What is dynamic programming?','hard','Algorithms','2026-05-20 11:00:47'),(75,'What is the worst-case complexity of QuickSort?','hard','Algorithms','2026-05-20 11:00:47'),(76,'What is a hash table?','medium','Algorithms','2026-05-20 11:00:47');
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
  CONSTRAINT `streak_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `player` (`player_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `streak`
--

LOCK TABLES `streak` WRITE;
/*!40000 ALTER TABLE `streak` DISABLE KEYS */;
INSERT INTO `streak` VALUES (1,2,1,1,'2026-05-20');
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

-- Dump completed on 2026-05-20 12:24:50
