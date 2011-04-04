﻿/*
* Renamed conventions and passes to events and tickets.
* Stripped out start/end fields for events.
* Stripped out age fields for tickets.
* Took out creation/login fields for accounts (didn't really need them?)
* Removed log table.
* Removed all information fields from registrations.
* Modified payments table: addition of payment_type field.
*
* Notes: For now, leave the issue of extra fields outside the picture.
* Use ECM prefix for online reg. Issue of block purchases?
*/
DROP TABLE IF EXISTS `accounts`;
-- Reg form information among other things. Require email at a minimum.
-- Salt column, usergroups storing?
CREATE TABLE accounts (
   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   email VARCHAR(55) UNIQUE,
   phone VARCHAR(25), 
   password CHAR(40) NOT NULL, -- You save one WHOLE byte by using CHAR instead of VARCHAR! lol.
   salt CHAR(10) NOT NULL, 		-- etc...
   status TINYINT NOT NULL 	-- Status of account (unverified, verified, banned, etc)
) ENGINE=Innodb DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `events`;
-- Describes events. 
CREATE TABLE events (
   id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   name VARCHAR(255) NOT NULL,
   location VARCHAR(255)
) ENGINE=Innodb DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `tickets`;
-- Table that describes the various passes. 
-- isPurchasable indicates whether a ticket can be purchased (or if it has to be given by an admin)
CREATE TABLE tickets (
   id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   event_id INT UNSIGNED NOT NULL,
   name VARCHAR(255) NOT NULL,
   price DECIMAL(10,2) NOT NULL,
   startDate INT,
   endDate INT,
   isPurchasable TINYINT NOT NULL,
   FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE -- Cascade deletion of passes. Will (should) still fail if registrations have started.
) ENGINE=Innodb DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `registrations`;
CREATE TABLE registrations (
   id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   event_id INT UNSIGNED NOT NULL,
   ticket_id INT UNSIGNED NOT NULL,
   account_id INT UNSIGNED,
   reg_id CHAR(25) NOT NULL, -- [Event ID]_[Sale Prefix]_[ID #] corresponds to 10_5_10 -> 25 characters where length 5 sale prefix is a chosen number.
   status TINYINT NOT NULL, -- Status of registration?
   FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE RESTRICT, -- Events shouldn't be deleted if in use already.
   FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE RESTRICT, -- Ticket types shouldn't be deleted if in use already.
   FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL -- Even if an account is deleted, leave registrations for stat purposes. (necessary?)
) ENGINE=Innodb DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `payments`;
-- Store payment processor data.
CREATE TABLE payments (
   id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   reg_id INT UNSIGNED NOT NULL,
   payment_type VARCHAR(20) NOT NULL, -- Type of payment (paypal, etc).   
   -- Payment processor specific fields to follow? That or link to another table matching type of payment (processor used)
   type VARCHAR(55) NOT NULL,
   mc_gross DECIMAL(10,2) NOT NULL,
   payer_id VARCHAR(13),
   payment_date INT NOT NULL,
   payment_status VARCHAR(17) NOT NULL,
   txn_id VARCHAR(17), -- txn_id is 17 characters alphanumeric.
   receipt_id VARCHAR(19), -- reciept id is in form XXXX-XXXX-XXXX-XXXX (19 characters)
   mod_time INT,
   FOREIGN KEY (reg_id) REFERENCES registrations(id) ON DELETE RESTRICT -- Registrations with payment information shouldn't be deleted.
) ENGINE=Innodb DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `usergroups`;
-- Expand on permissions later.
CREATE TABLE usergroups (
   id int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   name VARCHAR(55) NOT NULL,
   description TEXT
) ENGINE=Innodb DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE permissions (
   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   pkey VARCHAR(100) NOT NULL,
   description TEXT
) ENGINE=Innodb DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `usergroups_permissions`;
CREATE TABLE usergroups_permissions (
   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   usergroup_id INT UNSIGNED,
   permission_id INT UNSIGNED,
   FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE,
   FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=Innodb DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `accounts_usergroups`;
CREATE TABLE accounts_usergroups (
   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   usergroup_id INT UNSIGNED,
   account_id INT UNSIGNED,
   FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE, -- Users who were part of the delete group are removed from group.
   FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE -- If account was deleted, makes sense to clear this.
) ENGINE=Innodb DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `verificationcodes`;
CREATE TABLE `verificationcodes` (
   `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   `account_id` INT UNSIGNED NOT NULL,
   `type` INT UNSIGNED NOT NULL,
   `code` VARCHAR(40) NOT NULL, 
   `value` VARCHAR(255) NOT NULL,
   FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
   UNIQUE (`code`)
);

/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `usergroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounts_usergroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `usergroups_permissions` DISABLE KEYS */;

INSERT INTO `usergroups` VALUES(1, 'Registered', 'All registered users get this group');
INSERT INTO `usergroups` VALUES(2, 'SuperAdmin', 'Super Admin Access');
INSERT INTO `usergroups` VALUES(3, 'Administrator', 'Admin Access');

INSERT INTO `permissions` VALUES (1,'admin',NULL), (2, 'superAdmin', NULL) ;
-- INSERT INTO `usergroups_permissions` SET usergroup_id = 2, permission_id = 1;
INSERT INTO `usergroups_permissions` SET usergroup_id = 2, permission_id = 2;
INSERT INTO `usergroups_permissions` SET usergroup_id = 3, permission_id = 1;

-- Status of account (unverified, verified, banned, etc)
-- reg_status TINYINT NOT NULL,
INSERT INTO `accounts` SET
    id         = 1,
    email      = 'halkeye@gmail.com',
    password   = 'c1537a66964e2acbb3a8232a20b6d8338cb206c5',
    salt       = '3e215344f1',
    status     = 1
    -- created    = 1249191871,
    -- login      = 1249793436
;
INSERT INTO `accounts` SET
    id         = 2,
    email      = 'stt@sfu.ca',
    password   = '59e9c0e9d8e1f1b26b7f867a58ee6edf93becb33',
    salt       = '9f8d6875ac',
    status     = 1
    -- created    = 1257639234,
    -- login      = 1262579714
;
-- Heather from ae reg
INSERT INTO `accounts` SET
    id         = 3,
    email      = 'queens_net@yahoo.ca',
    password   = 'f7a2752cdd4239075c6a8241c8a8ce77c0d3fb8f',
    salt       = '74feb3d02f',
    status     = 1
    -- created    = 1257483052,
    -- login      = 1257484338
;

INSERT INTO `accounts_usergroups` SET usergroup_id = 2, account_id = 1;
INSERT INTO `accounts_usergroups` SET usergroup_id = 2, account_id = 2;
INSERT INTO `accounts_usergroups` SET usergroup_id = 1, account_id = 3;

/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
/*!40000 ALTER TABLE `usergroups` ENABLE KEYS */;
/*!40000 ALTER TABLE `accounts_usergroups` ENABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
/*!40000 ALTER TABLE `usergroups_permissions` ENABLE KEYS */;