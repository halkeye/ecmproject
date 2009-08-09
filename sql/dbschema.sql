DROP DATABASE ecms;
CREATE DATABASE ecms;
USE ecms;


DROP TABLE IF EXISTS `conventions`;
-- Table that describes a convention. Start/end times, name and location. Auto-incrementing integer ID.
CREATE TABLE conventions (
   convention_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   name VARCHAR(100) NOT NULL,
   start_date DATETIME NOT NULL,
   end_date DATETIME NOT NULL,
   location VARCHAR(150)
);

DROP TABLE IF EXISTS `passes`;
-- Table that describes the various passes. isPurchasable field is to allow for passes such as Vendors, Staff (an attendee
-- shouldn't be able to get their hands on one. For passes like that, have system grant right for the user to register.
-- One-time use codes?
CREATE TABLE passes(
   pass_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   name VARCHAR(100) NOT NULL,
   price DECIMAL NOT NULL,
   isPurchasable TINYINT NOT NULL,
   ageReq TINYINT UNSIGNED
);

DROP TABLE IF EXISTS `accounts`;
-- Reg form information among other things. Require email at a minimum.
-- Salt column, usergroups storing?
CREATE TABLE accounts(
   account_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   email VARCHAR(55) NOT NULL UNIQUE,
   gname VARCHAR(55) NOT NULL,
   sname VARCHAR(55) NOT NULL,
   badge VARCHAR(55),
   dob DATE NOT NULL,
   phone VARCHAR(15) NOT NULL,
   cell VARCHAR(15) NOT NULL,
   address TEXT,
   econtact VARCHAR(55) NOT NULL,
   ephone VARCHAR(15) NOT NULL,
   password VARCHAR(40) NOT NULL,
   salt VARCHAR(10) NOT NULL,
   reg_status TINYINT NOT NULL,
   created INT NOT NULL,
   login INT
);

DROP TABLE IF EXISTS `usergroups`;
-- Expand on permissions later.
CREATE TABLE usergroups(
   usergroup_id int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   name VARCHAR(55) NOT NULL,
   description TEXT
);

DROP TABLE IF EXISTS `registrations`;
CREATE TABLE registrations(
   register_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   convention_id INT UNSIGNED NOT NULL,
   pass_id INT UNSIGNED NOT NULL,
   account_id INT UNSIGNED, -- Took out NOT NULL requirement for SET NULL trigger to work.
   FOREIGN KEY (convention_id) REFERENCES conventions(convention_id) ON DELETE RESTRICT, -- Conventions shouldn't be deleted if in use already.
   FOREIGN KEY (pass_id) REFERENCES passes(pass_id) ON DELETE RESTRICT, -- Passes shouldn't be deleted if in use already.
   FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE SET NULL -- Even if an account is deleted, leave registrations for stat purposes.
);

DROP TABLE IF EXISTS `payments`;
-- Skip specific field details for now.
-- Just store a string stating payment type?
CREATE TABLE payments(
   payment_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   register_id INT UNSIGNED NOT NULL,
   type VARCHAR(55) NOT NULL,
   FOREIGN KEY (register_id) REFERENCES registrations(register_id) ON DELETE RESTRICT -- Registrations with payment information shouldn't be deleted.
);

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE permissions(
   permission_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   pkey VARCHAR(100) NOT NULL,
   description TEXT
);

DROP TABLE IF EXISTS `usergroups_permissions`;
CREATE TABLE usergroups_permissions(
   usergroup_id INT UNSIGNED,
   permission_id INT UNSIGNED,
   FOREIGN KEY (usergroup_id) REFERENCES usergroups(usergroup_id) ON DELETE CASCADE,
   FOREIGN KEY (permission_id) REFERENCES permissions(permission_id) ON DELETE CASCADE
);

DROP TABLE IF EXISTS `accounts_usergroups`;
CREATE TABLE accounts_usergroups(
   usergroup_id INT UNSIGNED,
   account_id INT UNSIGNED,
   FOREIGN KEY (usergroup_id) REFERENCES usergroups(usergroup_id) ON DELETE CASCADE, -- Users who were part of the delete group are removed from group.
   FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE CASCADE -- If account was deleted, makes sense to clear this.
);