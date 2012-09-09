<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Initial Schema
 *
 * @package    Migration
 * @category   Migration
 * @author     Gavin Mogan
 * @copyright  (c) 2012 Kode Koan
 */
class Migration_Application_20120122183500 extends Minion_Migration_Base {

	public function up(Kohana_Database $db)
	{
		$db->query(NULL, 'CREATE TABLE IF NOT EXISTS accounts (
           id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
           email VARCHAR(55) UNIQUE,
           gname VARCHAR(55) NOT NULL, -- Given Name
           sname VARCHAR(55) NOT NULL, -- Surname
           phone VARCHAR(25),
           password CHAR(40) NOT NULL, -- You save one WHOLE byte by using CHAR instead of VARCHAR! lol.
           salt CHAR(10) NOT NULL, 		-- etc...
           status TINYINT NOT NULL,	  -- Status of account (unverified, verified, banned, etc)
           created INT NOT NULL, -- Creation date
           login INT -- Last login.
        ) ENGINE=Innodb DEFAULT CHARSET=utf8');

        # Describes events.
		$db->query(NULL, 'CREATE TABLE IF NOT EXISTS conventions (
           id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
           name VARCHAR(255) NOT NULL,
           location VARCHAR(255)
        ) ENGINE=Innodb DEFAULT CHARSET=utf8');

        # Table that describes the various passes.
        # isPurchasable indicates whether a ticket can be purchased (or if it has to be given by an admin)
		$db->query(NULL, 'CREATE TABLE IF NOT EXISTS passes (
               id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
               convention_id INT UNSIGNED NOT NULL,
               name VARCHAR(255) NOT NULL,
               price DECIMAL(10,2) NOT NULL,
               startDate INT,
               endDate INT,
               isPurchasable TINYINT NOT NULL,
               requireDOB TINYINT NOT NULL,
               FOREIGN KEY (convention_id) REFERENCES conventions(id) ON DELETE CASCADE -- Cascade deletion of passes. Will (should) still fail if registrations have started.
            ) ENGINE=Innodb DEFAULT CHARSET=utf8');

		$db->query(NULL, 'CREATE TABLE IF NOT EXISTS registrations (
           id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
           convention_id INT UNSIGNED NOT NULL,
           pass_id INT UNSIGNED NOT NULL,
           account_id INT UNSIGNED,
           gname VARCHAR(55) NOT NULL, -- Given name
           sname VARCHAR(55) NOT NULL, -- Surname
           email VARCHAR(55),
           phone VARCHAR(25),
           dob	 DATE,
           reg_id CHAR(25) NOT NULL UNIQUE, -- [Event ID]_[Sale Prefix]_[ID #] corresponds to 10_5_10 -> 25 characters where length 5 sale prefix is a chosen number.
           status TINYINT NOT NULL, -- Status of registration?
           pickupStatus TINYINT NOT NULL,
           FOREIGN KEY (convention_id) REFERENCES conventions(id) ON DELETE RESTRICT, -- Events shouldnt be deleted if in use already.
           FOREIGN KEY (pass_id) REFERENCES passes(id) ON DELETE RESTRICT, -- Ticket types shouldnt be deleted if in use already.
           FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL -- Even if an account is deleted, leave registrations for stat purposes. (necessary?)
        ) ENGINE=Innodb DEFAULT CHARSET=utf8');

        #FOREIGN KEY(pass_id) REFERENCES passes(id) ON DELETE CASCADE
		$db->query(NULL, 'CREATE TABLE IF NOT EXISTS ticketcounters (
           id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
           location_id INT UNSIGNED NOT NULL,
           convention_id INT UNSIGNED NOT NULL,
           tickets_assigned INT UNSIGNED NOT NULL,
           tickets_total INT NOT NULL,
           next_id INT UNSIGNED NOT NULL,
           UNIQUE (`location_id`, `convention_id`)
        ) ENGINE=Innodb DEFAULT CHARSET=utf8');

        # Store payment processor data.
		$db->query(NULL, 'CREATE TABLE IF NOT EXISTS payments (
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
           FOREIGN KEY (reg_id) REFERENCES registrations(id) ON DELETE RESTRICT -- Registrations with payment information shouldnt be deleted.
        ) ENGINE=Innodb DEFAULT CHARSET=utf8');

        # Expand on permissions later.
		$db->query(NULL, 'CREATE TABLE IF NOT EXISTS usergroups (
           id int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
           name VARCHAR(55) NOT NULL,
           description TEXT
        ) ENGINE=Innodb DEFAULT CHARSET=utf8');

		$db->query(NULL, 'CREATE TABLE IF NOT EXISTS permissions (
           id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
           pkey VARCHAR(100) NOT NULL,
           description TEXT
        ) ENGINE=Innodb DEFAULT CHARSET=utf8');

		$db->query(NULL, 'CREATE TABLE IF NOT EXISTS usergroups_permissions (
           id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
           usergroup_id INT UNSIGNED,
           permission_id INT UNSIGNED,
           FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE,
           FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
        ) ENGINE=Innodb DEFAULT CHARSET=utf8');

		$db->query(NULL, 'CREATE TABLE IF NOT EXISTS accounts_usergroups (
           id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
           usergroup_id INT UNSIGNED,
           account_id INT UNSIGNED,
           FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE, -- Users who were part of the delete group are removed from group.
           FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE, -- If account was deleted, makes sense to clear this.
           UNIQUE (`usergroup_id`, `account_id`)
        ) ENGINE=Innodb DEFAULT CHARSET=utf8');


		$db->query(NULL, 'CREATE TABLE IF NOT EXISTS `verificationcodes` (
           `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
           `account_id` INT UNSIGNED NOT NULL,
           `type` INT UNSIGNED NOT NULL,
           `code` VARCHAR(40) NOT NULL,
           `value` VARCHAR(255) NOT NULL,
           FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
           UNIQUE (`code`)
        )');

		$db->query(NULL, 'CREATE TABLE IF NOT EXISTS locations (
          id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
          prefix CHAR(6) NOT NULL UNIQUE,
          location VARCHAR(255) NOT NULL
        ) ENGINE=Innodb DEFAULT CHARSET=utf8');
    }

    /**
	 * Run queries needed to remove this migration
	 *
	 * @param Kohana_Database Database connection
	 */
	public function down(Kohana_Database $db)
	{
		$db->query(NULL, 'DROP TABLE IF EXISTS accounts');
		$db->query(NULL, 'DROP TABLE IF EXISTS conventions');
		$db->query(NULL, 'DROP TABLE IF EXISTS passes');
		$db->query(NULL, 'DROP TABLE IF EXISTS registrations');
		$db->query(NULL, 'DROP TABLE IF EXISTS ticketcounters');
		$db->query(NULL, 'DROP TABLE IF EXISTS payments');
		$db->query(NULL, 'DROP TABLE IF EXISTS usergroups');
		$db->query(NULL, 'DROP TABLE IF EXISTS permissions');
		$db->query(NULL, 'DROP TABLE IF EXISTS usergroups_permissions');
		$db->query(NULL, 'DROP TABLE IF EXISTS accounts_usergroups');
		$db->query(NULL, 'DROP TABLE IF EXISTS `verificationcodes`');
		$db->query(NULL, 'DROP TABLE IF EXISTS locations');
    }
}
