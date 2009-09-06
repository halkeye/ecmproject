-- MySQL dump 10.11
--
-- Host: localhost    Database: ecms
-- ------------------------------------------------------
-- Server version	5.0.67-0ubuntu6

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
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `accounts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `email` varchar(55) NOT NULL,
  `password` varchar(40) NOT NULL,
  `salt` varchar(10) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created` int(11) NOT NULL,
  `login` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `accounts_usergroups`
--

DROP TABLE IF EXISTS `accounts_usergroups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `accounts_usergroups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `usergroup_id` int(10) unsigned default NULL,
  `account_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `usergroup_id` (`usergroup_id`),
  KEY `account_id` (`account_id`),
  CONSTRAINT `accounts_usergroups_ibfk_1` FOREIGN KEY (`usergroup_id`) REFERENCES `usergroups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounts_usergroups_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `conventions`
--

DROP TABLE IF EXISTS `conventions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `conventions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `start_date` int(10) unsigned NOT NULL,
  `end_date` int(10) unsigned NOT NULL,
  `location` varchar(150) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `passes`
--

DROP TABLE IF EXISTS `passes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `passes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `convention_id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `isPurchasable` tinyint(4) NOT NULL,
  `ageReq` tinyint(3) unsigned default NULL,
  `startDate` int(10) unsigned default NULL,
  `endDate` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `convention_id` (`convention_id`),
  CONSTRAINT `passes_ibfk_1` FOREIGN KEY (`convention_id`) REFERENCES `conventions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `register_id` int(10) unsigned NOT NULL,
  `last_modified` int(10) unsigned default NULL,
  `type` varchar(55) NOT NULL,
  `raw_data` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `register_id` (`register_id`),
  KEY `last_modified` (`last_modified`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`register_id`) REFERENCES `registrations` (`id`),
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`last_modified`) REFERENCES `accounts` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pkey` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `registrations`
--

DROP TABLE IF EXISTS `registrations`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `registrations` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `convention_id` int(10) unsigned NOT NULL,
  `pass_id` int(10) unsigned default NULL,
  `account_id` int(10) unsigned default NULL,
  `gname` varchar(55) NOT NULL,
  `sname` varchar(55) NOT NULL,
  `badge` varchar(55) default NULL,
  `dob` date NOT NULL,
  `phone` varchar(15) NOT NULL,
  `cell` varchar(15) NOT NULL,
  `address` text,
  `email` varchar(55) NOT NULL,
  `econtact` varchar(55) NOT NULL,
  `ephone` varchar(15) NOT NULL,
  `heard_from` text,
  `attendance_reason` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `convention_badge_name` (`convention_id`,`badge`),
  KEY `pass_id` (`pass_id`),
  KEY `account_id` (`account_id`),
  CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`convention_id`) REFERENCES `conventions` (`id`),
  CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`pass_id`) REFERENCES `passes` (`id`),
  CONSTRAINT `registrations_ibfk_3` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `usergroups`
--

DROP TABLE IF EXISTS `usergroups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `usergroups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(55) NOT NULL,
  `description` text,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `usergroups_permissions`
--

DROP TABLE IF EXISTS `usergroups_permissions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `usergroups_permissions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `usergroup_id` int(10) unsigned default NULL,
  `permission_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `usergroup_id` (`usergroup_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `usergroups_permissions_ibfk_1` FOREIGN KEY (`usergroup_id`) REFERENCES `usergroups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `usergroups_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-09-06  7:51:51
