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

INSERT INTO `usergroups` VALUES(1, 'Registered', 'All registered users get this group');
INSERT INTO `usergroups` VALUES(2, 'Administrator', 'Admin Access');
INSERT INTO `permissions` VALUES (1,'can_do_stuff',NULL), (2,'admin',NULL);
INSERT INTO `usergroups_permissions` VALUES (NULL,1,1), (NULL, 2, 2);

-- Status of account (unverified, verified, banned, etc)
-- reg_status TINYINT NOT NULL,
INSERT INTO `accounts` SET
    id         = 1,
    email      = 'halkeye@gmail.com',
    password   = 'c1537a66964e2acbb3a8232a20b6d8338cb206c5',
    salt       = '3e215344f1',
    status     = 1,
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
-- Heather from ae reg
INSERT INTO `accounts` SET
    id         = 3,
    email      = 'queens_net@yahoo.ca',
    password   = 'f7a2752cdd4239075c6a8241c8a8ce77c0d3fb8f',
    salt       = '74feb3d02f',
    status     = 1,
    created    = 1257483052,
    login      = 1257484338
;
-- Heather should have admin account
INSERT INTO `accounts_usergroups` VALUES (NULL,1,1), (NULL,2,1), (NULL,2,3);

INSERT INTO `conventions` VALUES(1, 'Anime Evolution 2010', UNIX_TIMESTAMP('2009-07-31'), UNIX_TIMESTAMP('2010-08-31'), 'University of British Columbia');
INSERT INTO `conventions` VALUES(2, 'Anime Evolution 2010-2', UNIX_TIMESTAMP('2009-07-31'), UNIX_TIMESTAMP('2010-08-31'), 'University of British Columbia');

INSERT INTO `passes` VALUES (1,1, '3-day adult pass',         40.00, 1, 19, 255, 1252393200, UNIX_TIMESTAMP('2010-08-01'));
INSERT INTO `passes` VALUES (2,1, '3-day adult pass PRE-REG', 40.00, 1, 19, 255, 1252393200, 1284015600); -- Start date, end (expiry) date.
INSERT INTO `passes` VALUES (3,1, '3-day minor pass PRE-REG', 25.00, 1, 0 , 19 , 1252393200, UNIX_TIMESTAMP('2010-08-01'));

INSERT INTO registrations SET
    convention_id = 1,
    pass_id = 2,
    account_id = 1,
    gname = 'Gavin',
    sname = 'Mogan',
    badge = 'halkeye',
    dob   = '1982-12-18',
    phone = '604-555-5555',
    cell  = '604-555-6666',
    email = 'halkeye@gmail.com',
    econtact = 'ME ME ME ME',
    ephone = '604-555-7777',
    heard_from = 'The best person ever!!!! GAVIN!!!!',
    attendance_reason = 'Um, what goes here?',
    status=0;

/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
/*!40000 ALTER TABLE `usergroups` ENABLE KEYS */;
/*!40000 ALTER TABLE `accounts_usergroups` ENABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
/*!40000 ALTER TABLE `usergroups_permissions` ENABLE KEYS */;
