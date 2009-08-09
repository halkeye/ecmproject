DROP DATABASE ecms;
CREATE DATABASE ecms;
USE ecms;


DROP TABLE IF EXISTS `convention`;
-- Table that describes a convention. Start/end times, name and location. Auto-incrementing integer ID.
CREATE TABLE convention(
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
   passes_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   name VARCHAR(100) NOT NULL,
   price DECIMAL NOT NULL,
   isPurchasable TINYINT NOT NULL,
   ageReq TINYINT UNSIGNED
);

DROP TABLE IF EXISTS `accounts`;
-- Reg form information among other things. Require email at a minimum.
-- Salt column, usergroups storing?
CREATE TABLE accounts(
   accounts_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
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
   usergroups_id int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   name VARCHAR(55) NOT NULL,
   description TEXT
);

DROP TABLE IF EXISTS `account_usergroups`;
-- Expand on permissions later.
CREATE TABLE account_usergroups(
   account_id INT UNSIGNED NOT NULL,
   usergroup_id INT UNSIGNED NOT NULL,
   PRIMARY KEY (`account_id`,`usergroup_id`),
   FOREIGN KEY (account_id) REFERENCES account(id),
   FOREIGN KEY (usergroup_id) REFERENCES usergroup(id)
);

DROP TABLE IF EXISTS `register`;
CREATE TABLE register(
   register_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   convention_id INT UNSIGNED NOT NULL,
   passes_id INT UNSIGNED NOT NULL,
   accounts_id INT UNSIGNED NOT NULL,
   FOREIGN KEY (convention_id) REFERENCES convention(convention_id),
   FOREIGN KEY (passes_id) REFERENCES passes(passes_id),
   FOREIGN KEY (accounts_id) REFERENCES accounts(accounts_id)
);

DROP TABLE IF EXISTS `payment`;
-- Skip specific field details for now.
-- Just store a string stating payment type?
CREATE TABLE payment(
   payment_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   register_id INT UNSIGNED NOT NULL,
   type VARCHAR(55) NOT NULL,
   FOREIGN KEY (register_id) REFERENCES register(register_id)
);

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE permissions(
   permission_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   pkey VARCHAR(100) NOT NULL,
   description TEXT
);

DROP TABLE IF EXISTS `usergroups_permissions`;
CREATE TABLE usergroups_permissions(
   usergroups_id INT UNSIGNED,
   permission_id INT UNSIGNED,
   FOREIGN KEY (usergroups_id) REFERENCES usergroups(usergroups_id),
   FOREIGN KEY (permission_id) REFERENCES permissions(permission_id)
);

DROP TABLE IF EXISTS `accounts_usergroups`;
CREATE TABLE accounts_usergroups(
   usergroups_id INT UNSIGNED,
   accounts_id INT UNSIGNED,
   FOREIGN KEY (usergroups_id) REFERENCES usergroups(usergroups_id),
   FOREIGN KEY (accounts_id) REFERENCES accounts(accounts_id)
);
