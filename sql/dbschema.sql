DROP TABLE IF EXISTS `conventions`;
-- Table that describes a convention. Start/end times, name and location. Auto-incrementing integer ID.
CREATE TABLE conventions (
   id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   name VARCHAR(100) NOT NULL,
   start_date INT NOT NULL,
   end_date INT NOT NULL,
   location VARCHAR(150)
) ENGINE=Innodb DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `passes`;
-- Table that describes the various passes. isPurchasable field is to allow for passes such as Vendors, Staff (an attendee
-- shouldn't be able to get their hands on one. For passes like that, have system grant right for the user to register.
-- One-time use codes?
CREATE TABLE passes (
   id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   convention_id INT UNSIGNED NOT NULL,
   name VARCHAR(100) NOT NULL,
   price DECIMAL(10,2) NOT NULL,
   isPurchasable TINYINT NOT NULL,
   minAge TINYINT UNSIGNED,
   maxAge TINYINT UNSIGNED,
   startDate INT,
   endDate INT,
   FOREIGN KEY (convention_id) REFERENCES conventions(id) ON DELETE CASCADE -- Cascade deletion of passes. Will (should) still fail if registrations have started.
) ENGINE=Innodb DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `accounts`;
-- Reg form information among other things. Require email at a minimum.
-- Salt column, usergroups storing?
CREATE TABLE accounts (
   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   email VARCHAR(55) NOT NULL UNIQUE,
   password VARCHAR(40) NOT NULL,
   salt VARCHAR(10) NOT NULL,
   status TINYINT NOT NULL, -- Status of account (unverified, verified, banned, etc)
   created INT NOT NULL, -- Creation date
   login INT -- Last login.
) ENGINE=Innodb DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `usergroups`;
-- Expand on permissions later.
CREATE TABLE usergroups (
   id int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   name VARCHAR(55) NOT NULL,
   description TEXT
) ENGINE=Innodb DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `registrations`;
CREATE TABLE registrations (
   id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   convention_id INT UNSIGNED NOT NULL,
   pass_id INT UNSIGNED NOT NULL,
   account_id INT UNSIGNED, -- Took out NOT NULL requirement for SET NULL trigger to work.
   gname VARCHAR(55) NOT NULL, -- Given name
   sname VARCHAR(55) NOT NULL, -- Surname
   badge VARCHAR(55),
   dob DATE NOT NULL,
   phone VARCHAR(25) NOT NULL,
   cell VARCHAR(25), -- Cell is an optional field, so don't require a field value.
   city VARCHAR(85),
   prov VARCHAR(50),
   email VARCHAR(55) NOT NULL, -- Account email can be the same as this one...
   econtact VARCHAR(55) NOT NULL,
   ephone VARCHAR(25) NOT NULL,
   heard_from TEXT, -- We'll leave these two here just in case.
   attendance_reason TEXT,
   status TINYINT NOT NULL, -- Status of account (unprocessed, processing, accepted, etc)
   FOREIGN KEY (convention_id) REFERENCES conventions(id) ON DELETE RESTRICT, -- Conventions shouldn't be deleted if in use already.
   FOREIGN KEY (pass_id) REFERENCES passes(id) ON DELETE RESTRICT, -- Passes shouldn't be deleted if in use already.
   FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL -- Even if an account is deleted, leave registrations for stat purposes.
) ENGINE=Innodb DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `payments`;
-- Skip specific field details for now.
-- Just store a string stating payment type?
-- Aside from paypal details
CREATE TABLE payments (
   id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   register_id INT UNSIGNED NOT NULL,
   last_modified INT UNSIGNED, -- Track the last account to add/edit a payment manually to allow for reg. manager/board to give out a badge, fix payment, etc. NULL if last person who touched it wasn't human. Think of a better solution.
   type VARCHAR(55) NOT NULL,
   mc_gross DECIMAL(10,2) NOT NULL,
   payer_id VARCHAR(13),
   payment_date INT NOT NULL,
   payment_status VARCHAR(17) NOT NULL,
   txn_id VARCHAR(17), -- txn_id is 17 characters alphanumeric.
   receipt_id VARCHAR(19), -- receipt id is in form XXXX-XXXX-XXXX-XXXX (19 characters)
   mod_time INT,
   FOREIGN KEY (register_id) REFERENCES registrations(id) ON DELETE RESTRICT, -- Registrations with payment information shouldn't be deleted.
   FOREIGN KEY (last_modified) REFERENCES accounts(id) ON DELETE RESTRICT
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

DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   modifier_id INT UNSIGNED,
   target_account_id INT UNSIGNED,
   target_registration_id INT UNSIGNED,
   target_badge_id INT UNSIGNED,
   method TEXT,
   description TEXT,
   mod_time INT,
   ip VARCHAR(39),
   FOREIGN KEY (modifier_id) REFERENCES accounts(id) ON DELETE SET NULL,
   FOREIGN KEY (target_account_id) REFERENCES accounts(id) ON DELETE SET NULL,
   FOREIGN KEY (target_registration_id) REFERENCES registrations(id) ON DELETE SET NULL,
   FOREIGN KEY (target_badge_id) REFERENCES passes(id) ON DELETE SET NULL
 ) ENGINE=Innodb DEFAULT CHARSET=utf8;

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
    status     = 1,
    created    = 1249191871,
    login      = 1249793436
;
INSERT INTO `accounts` SET 
    id         = 2,
    email      = 'stt@sfu.ca',
    password   = '59e9c0e9d8e1f1b26b7f867a58ee6edf93becb33',
    salt       = '9f8d6875ac',
    status     = 1,
    created    = 1257639234,
    login      = 1262579714 
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

INSERT INTO `accounts_usergroups` SET usergroup_id = 2, account_id = 1;
INSERT INTO `accounts_usergroups` SET usergroup_id = 2, account_id = 2;
INSERT INTO `accounts_usergroups` SET usergroup_id = 1, account_id = 3;

/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
/*!40000 ALTER TABLE `usergroups` ENABLE KEYS */;
/*!40000 ALTER TABLE `accounts_usergroups` ENABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
/*!40000 ALTER TABLE `usergroups_permissions` ENABLE KEYS */;
