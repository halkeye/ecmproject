-- DROP DATABASE ecms;
CREATE DATABASE ecms;
USE ecms;


DROP TABLE IF EXISTS `convention`;
-- Table that describes a convention. Start/end times, name and location. Auto-incrementing integer ID.
CREATE TABLE convention(
   cid INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
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
   name VARCHAR(100) NOT NULL PRIMARY KEY,
   price DECIMAL NOT NULL,
   isPurchasable TINYINT NOT NULL,
   ageReq TINYINT UNSIGNED
);

DROP TABLE IF EXISTS `accounts`;
-- Reg form information among other things. Require email at a minimum.
-- Salt column, usergroups storing?
CREATE TABLE accounts(
   id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
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
   salt VARCHAR(10) NOT NULL
);

DROP TABLE IF EXISTS `usergroups`;
-- Expand on permissions later.
CREATE TABLE usergroups(
   guid int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   name VARCHAR(55) NOT NULL
);

DROP TABLE IF EXISTS `register`;
CREATE TABLE register(
   reg_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   cid INT UNSIGNED NOT NULL,
   pass VARCHAR(100) NOT NULL,
   account_id INT UNSIGNED NOT NULL,
   FOREIGN KEY (cid) REFERENCES convention(cid),
   FOREIGN KEY (pass) REFERENCES passes(name),
   FOREIGN KEY (account_id) REFERENCES accounts(id)
);

DROP TABLE IF EXISTS `payment`;
-- Skip specific field details for now.
-- Just store a string stating payment type?
CREATE TABLE payment(
   cid INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   reg_id INT UNSIGNED NOT NULL,
   type VARCHAR(55) NOT NULL,
   FOREIGN KEY (reg_id) REFERENCES register(reg_id)
);