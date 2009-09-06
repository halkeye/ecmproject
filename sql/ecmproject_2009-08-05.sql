# Sequel Pro dump
# Version 663
# http://code.google.com/p/sequel-pro
#
# Host: localhost (MySQL 5.1.36)
# Database: ecmproject
# Generation Time: 2009-08-05 23:09:47 -0700
# ************************************************************

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table accounts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `accounts`;

CREATE TABLE `accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(55) NOT NULL,
  `gname` varchar(55) NOT NULL,
  `sname` varchar(55) NOT NULL,
  `badge` varchar(55) DEFAULT NULL,
  `dob` date NOT NULL,
  `phone` varchar(15) NOT NULL,
  `cell` varchar(15) NOT NULL,
  `address` text,
  `econtact` varchar(55) NOT NULL,
  `ephone` varchar(15) NOT NULL,
  `password` varchar(40) NOT NULL,
  `salt` varchar(10) NOT NULL,
  `reg_status` tinyint(4) NOT NULL,
  `created` int(11) NOT NULL,
  `login` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
INSERT INTO `accounts` (`id`,`email`,`gname`,`sname`,`badge`,`dob`,`phone`,`cell`,`address`,`econtact`,`ephone`,`password`,`salt`,`reg_status`,`created`,`login`)
VALUES (1,'halkeye@gmail.com','Gavin','Mogan','0','1982-12-18','(604) 505-8034','','705-6622 Southoaks Cres','blah','blah','c1537a66964e2acbb3a8232a20b6d8338cb206c5','3e215344f1',1,1249191871,1249191871);

/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table convention
# ------------------------------------------------------------

DROP TABLE IF EXISTS `convention`;

CREATE TABLE `convention` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table passes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `passes`;

CREATE TABLE `passes` (
  `name` varchar(100) NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `isPurchasable` tinyint(4) NOT NULL,
  `ageReq` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table payment
# ------------------------------------------------------------

DROP TABLE IF EXISTS `payment`;

CREATE TABLE `payment` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reg_id` int(10) unsigned NOT NULL,
  `type` varchar(55) NOT NULL,
  PRIMARY KEY (`cid`),
  KEY `reg_id` (`reg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table register
# ------------------------------------------------------------

DROP TABLE IF EXISTS `register`;

CREATE TABLE `register` (
  `reg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(10) unsigned NOT NULL,
  `pass` varchar(100) NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`reg_id`),
  KEY `cid` (`cid`),
  KEY `pass` (`pass`),
  KEY `account_id` (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table usergroups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `usergroups`;

CREATE TABLE `usergroups` (
  `guid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(55) NOT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;






/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
