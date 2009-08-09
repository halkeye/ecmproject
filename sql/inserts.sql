USE ecms;


/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `usergroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounts_usergroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `usergroups_permissions` DISABLE KEYS */;
TRUNCATE `accounts`;
TRUNCATE `usergroups`;
TRUNCATE `accounts_usergroups`;
TRUNCATE `permissions`;
TRUNCATE `usergroups_permissions`;

INSERT INTO `accounts` VALUES (1,'halkeye@gmail.com','Gavin','Mogan','0','1982-12-18','(604) 505-8034','','705-6622 Southoaks Cres','blah','blah','c1537a66964e2acbb3a8232a20b6d8338cb206c5','3e215344f1',1,1249191871,1249793436);
INSERT INTO `usergroups` VALUES (1,'registered',NULL);
INSERT INTO `accounts_usergroups` VALUES (NULL,1,1);
INSERT INTO `permissions` VALUES (1,'can_do_stuff',NULL);
INSERT INTO `usergroups_permissions` VALUES (NULL,1,1);
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
/*!40000 ALTER TABLE `usergroups` ENABLE KEYS */;
/*!40000 ALTER TABLE `accounts_usergroups` ENABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
/*!40000 ALTER TABLE `usergroups_permissions` ENABLE KEYS */;
