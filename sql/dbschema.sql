DROP DATABASE ecms;
CREATE DATABASE ecms;
USE ecms;


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
   phone VARCHAR(15) NOT NULL,
   cell VARCHAR(15), -- Cell is an optional field, so don't require a field value.
   city VARCHAR(85),
   prov VARCHAR(50),
   email VARCHAR(55) NOT NULL, -- Account email can be the same as this one...
   econtact VARCHAR(55) NOT NULL,
   ephone VARCHAR(15) NOT NULL,
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
   receipt_id VARCHAR(19), -- reciept id is in form XXXX-XXXX-XXXX-XXXX (19 characters)
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
   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   account_id INT UNSIGNED NOT NULL,
   code VARCHAR(40) NOT NULL, 
   FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
   UNIQUE (`account_id`),
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