<?php defined('SYSPATH') or die('No direct script access.');
/**
 * ECM Test/DefaultData
 */
class Migration_Application_20120122183501 extends Minion_Migration_Base {

	/**
	 * Run queries needed to apply this migration
	 *
	 * @param Kohana_Database Database connection
	 */
	public function up(Kohana_Database $db)
	{
        $rows = $db->query(Database::SELECT,"select count(*) as c from `usergroups`");
        # migrating existing db
        if ($rows->get('c') > 0) { return; }

        $db->query(NULL,"ALTER TABLE `accounts` DISABLE KEYS");
        $db->query(NULL,"ALTER TABLE `accounts` DISABLE KEYS");
        $db->query(NULL,"ALTER TABLE `usergroups` DISABLE KEYS");
        $db->query(NULL,"ALTER TABLE `accounts_usergroups` DISABLE KEYS");
        $db->query(NULL,"ALTER TABLE `permissions` DISABLE KEYS");
        $db->query(NULL,"ALTER TABLE `usergroups_permissions` DISABLE KEYS");

        $db->query(NULL,"INSERT INTO `usergroups` VALUES(1, 'Registered', 'All registered users get this group')");
        $db->query(NULL,"INSERT INTO `usergroups` VALUES(2, 'SuperAdmin', 'Super Admin Access')");
        $db->query(NULL,"INSERT INTO `usergroups` VALUES(3, 'Administrator', 'Admin Access')");

        $db->query(NULL,"INSERT INTO `permissions` VALUES (1,'admin',NULL), (2, 'superAdmin', NULL) ");
        $db->query(NULL,"INSERT INTO `usergroups_permissions` SET usergroup_id = 2, permission_id = 2");
        $db->query(NULL,"INSERT INTO `usergroups_permissions` SET usergroup_id = 3, permission_id = 1");

        # Status of account (unverified, verified, banned, etc)
        # reg_status TINYINT NOT NULL,
        $db->query(NULL,"INSERT INTO `accounts` SET
            id         = 1,
            email      = 'halkeye@gmail.com',
            gname      = 'Halk',
            sname      = 'eye',
            password   = 'c1537a66964e2acbb3a8232a20b6d8338cb206c5',
            salt       = '3e215344f1',
            status     = 1,
            created    = 1249191871,
            login      = 1249793436
        ");

        $db->query(NULL,"INSERT INTO `accounts` SET
            id         = 2,
            email      = 'stt@sfu.ca',
            gname      = 'Uchi',
            sname      = 'koma',
            password   = '59e9c0e9d8e1f1b26b7f867a58ee6edf93becb33',
            salt       = '9f8d6875ac',
            status     = 1,
            created    = 1257639234,
            login      = 1262579714
        ");

        $db->query(NULL,"INSERT INTO `accounts` SET
            id         = 3,
            email      = 'greg@irlevents.com',
            gname      = 'Gregory',
            sname      = 'Neher',
            password   = '19bf8c3d90c32f96a0766b03919bc03f1d3e612d',
            salt       = '5fd18be8b9',
            status     = 1,
            created    = 1303752682,
            login      = 1303752682
        ");

        $db->query(NULL,"INSERT INTO `conventions` SET
            id         = 1,
            name       = 'Cos & Effect',
            location   = 'UBC'
        ");

        # start = 1304226000 = 05 / 01 / 2011 @ 0:0:0 EST
        # start = 1306904400 = 06 / 01 / 2011 @ 0:0:0 EST
        $db->query(NULL,"INSERT INTO `passes` SET
            id            = 1,
            convention_id = 1,
            name          = 'Weekend Pass',
            price         = '35.00',
            startDate     = 1204226000,
            endDate       = 1306904400,
            isPurchasable = 1
        ");

        $db->query(NULL,"INSERT INTO ticketcounters (pass_id, tickets_assigned, tickets_total, next_id) VALUES (1, 0, 600, 1)");

        $db->query(NULL,"INSERT INTO `accounts_usergroups` SET usergroup_id = 2, account_id = 1");
        $db->query(NULL,"INSERT INTO `accounts_usergroups` SET usergroup_id = 2, account_id = 2");
        $db->query(NULL,"INSERT INTO `accounts_usergroups` SET usergroup_id = 3, account_id = 3");

        $db->query(NULL,"INSERT INTO `locations` SET id = 1, prefix = 'ECM', location = 'Electronic Convention Management System'");

        $db->query(NULL,"ALTER TABLE `accounts` ENABLE KEYS");
        $db->query(NULL,"ALTER TABLE `accounts` ENABLE KEYS");
        $db->query(NULL,"ALTER TABLE `usergroups` ENABLE KEYS");
        $db->query(NULL,"ALTER TABLE `accounts_usergroups` ENABLE KEYS");
        $db->query(NULL,"ALTER TABLE `permissions` ENABLE KEYS");
        $db->query(NULL,"ALTER TABLE `usergroups_permissions` ENABLE KEYS");

    }

    public function down(Kohana_Database $db)
    {
    }
}

