﻿USE ecms;

/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `usergroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounts_usergroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `usergroups_permissions` DISABLE KEYS */;
TRUNCATE `conventions`;
TRUNCATE `passes`;
TRUNCATE `accounts`;
TRUNCATE `usergroups`;
TRUNCATE `accounts_usergroups`;
TRUNCATE `permissions`;
TRUNCATE `usergroups_permissions`;
   
-- Status of account (unverified, verified, banned, etc)
-- reg_status TINYINT NOT NULL,
INSERT INTO `accounts` SET 
    id         = 1,
    email      = 'halkeye@gmail.com',
    password   = 'c1537a66964e2acbb3a8232a20b6d8338cb206c5',
    salt       = '3e215344f1',
    status     = 0,
    created    = 1249191871,
    login      = 1249793436
;
INSERT INTO `accounts` SET 
    id         = 2,
    email      = 'test@test.com',
    password   = 'e210db43253621986ec02d035fb80e5308298cae',
    salt       = '132d32a79d',
    status     = 0,
    created    = 1249888283,
    login      = 1249888291
;

-- INSERT INTO `accounts` VALUES (1,'halkeye@gmail.com','Gavin','Mogan','0','1982-12-18','(604) 505-8034','','705-6622 Southoaks Cres','blah','blah','c1537a66964e2acbb3a8232a20b6d8338cb206c5','3e215344f1',1,1249191871,1249793436);
-- INSERT INTO `accounts` VALUES (2,'test@test.com','Gavin','Mogan','0','1982-12-18','(604) 505-8034','','705-6622 Southoaks Cres','blah','blah','e210db43253621986ec02d035fb80e5308298cae','132d32a79d',1,1249888283,1249888291);
INSERT INTO `usergroups` VALUES(1, 'Registered', 'All registered users get this group');
INSERT INTO `accounts_usergroups` VALUES (NULL,1,1);
INSERT INTO `permissions` VALUES (1,'can_do_stuff',NULL);
INSERT INTO `usergroups_permissions` VALUES (NULL,1,1);

INSERT INTO `conventions` VALUES(1, 'Anime Evolution 2010', 1284966000, 1285138800, 'University of British Columbia');

INSERT INTO `passes` VALUES (1,1, '3-day adult pass', 40.00, 1, 19, 1252393200, NULL); -- Effective forever after start date.
INSERT INTO `passes` VALUES (2,1, '3-day adult pass PRE-REG', 40.00, 1, 19, 1252393200, 1284015600); -- Start date, end (expiry) date.

/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
/*!40000 ALTER TABLE `usergroups` ENABLE KEYS */;
/*!40000 ALTER TABLE `accounts_usergroups` ENABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
/*!40000 ALTER TABLE `usergroups_permissions` ENABLE KEYS */;
