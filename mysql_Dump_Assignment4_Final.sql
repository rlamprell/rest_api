-- MySQL dump 10.14  Distrib 5.5.68-MariaDB, for Linux (x86_64)
--
-- Host: studdb.csc.liv.ac.uk    Database: sgrlampr
-- ------------------------------------------------------
-- Server version	8.0.13

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Players`
--

DROP TABLE IF EXISTS `Players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `surname` text NOT NULL,
  `forenames` text NOT NULL,
  `nationality` text NOT NULL,
  `date_of_birth` date NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `fk_teamid_Team` (`team_id`),
  CONSTRAINT `fk_teamid_Team` FOREIGN KEY (`team_id`) REFERENCES `Teams` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Players`
--

LOCK TABLES `Players` WRITE;
/*!40000 ALTER TABLE `Players` DISABLE KEYS */;
INSERT INTO `Players` VALUES (1,'Smith','John','UK','1989-11-11',1),(2,'Teri','Phebe','UK','1966-10-23',1),(3,'Jared','Sybil','UK','2001-09-24',1),(4,'Lynnette','Sharmaine','UK','1989-05-26',2),(5,'Cuthbert','Jenna','UK','1978-04-23',2),(6,'Idella','Sunny','UK','1995-03-12',2),(7,'Kayleah','Carley','UK','1997-02-08',3),(8,'Grier','Nat','UK','1999-01-15',3),(9,'Deven','Ben','UK','2004-12-18',3);
/*!40000 ALTER TABLE `Players` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Teams`
--

DROP TABLE IF EXISTS `Teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `sport` text NOT NULL,
  `average_age` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Teams`
--

LOCK TABLES `Teams` WRITE;
/*!40000 ALTER TABLE `Teams` DISABLE KEYS */;
INSERT INTO `Teams` VALUES (1,'Liverpool','Football',34.6667),(2,'Manchester','Tennis',32.6667),(3,'Liverpool','Rugby',20.3333);
/*!40000 ALTER TABLE `Teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'sgrlampr'
--

--
-- Dumping routines for database 'sgrlampr'
--
/*!50003 DROP PROCEDURE IF EXISTS `insert_initial_data` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`sgrlampr`@`%` PROCEDURE `insert_initial_data`()
BEGIN
	
	INSERT INTO Teams VALUES
		(NULL, 'Liverpool', 'Football', null),
        (NULL, 'Manchester','Tennis',   null),
        (NULL, 'Liverpool', 'Rugby',    null);

	
    INSERT INTO Players VALUES 
		(NULL, 'Smith',     'John',     'UK', '1989-11-11', 1),
		(NULL, 'Teri',      'Phebe',    'UK', '1966-10-23', 1),
        (NULL, 'Jared',     'Sybil',    'UK', '2001-09-24', 1),
        (NULL, 'Lynnette',  'Sharmaine','UK', '1989-05-26', 2),
        (NULL, 'Cuthbert',  'Jenna',    'UK', '1978-04-23', 2),
        (NULL, 'Idella',    'Sunny',    'UK', '1995-03-12', 2),
        (NULL, 'Kayleah',   'Carley',   'UK', '1997-02-08', 3),
        (NULL, 'Grier',     'Nat',      'UK', '1999-01-15', 3),
        (NULL, 'Deven',     'Ben',      'UK', '2004-12-18', 3);

	
    
    
        
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_avg_ages` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`sgrlampr`@`%` PROCEDURE `update_avg_ages`(

    IN teamId INT
)
BEGIN
	
	CREATE TEMPORARY TABLE player_ages
	SELECT
		Players.team_id,
		TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) AS age
    FROM 
        Players
	WHERE
        Players.team_id = teamId;

    
    UPDATE Teams
    SET 
		average_age = 	
			
			(SELECT 
				avg(age) AS avg_age
			FROM player_ages
			LIMIT 1)
    WHERE
        Teams.id = teamId;

	DROP TEMPORARY TABLE IF EXISTS player_ages;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-01-18 10:58:38
