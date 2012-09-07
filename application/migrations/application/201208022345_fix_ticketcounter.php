<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Sample OAuth2 Client
 */
class Migration_Application_201208022345 extends Minion_Migration_Base {

	/**
	 * Run queries needed to apply this migration
	 *
	 * @param Kohana_Database Database connection
	 */
	public function up(Kohana_Database $db)
	{
        /* Migrate */
        $db->query(NULL, 'INSERT IGNORE INTO locations SELECT NULL, SUBSTR(r.reg_id, 1, 3) AS location_prefix, "Auto Imported from db upgrade" AS location FROM registrations r GROUP BY location_prefix');
        $db->query(NULL, "ALTER TABLE registrations ADD location_id INT AFTER id");
        $db->query(NULL, "UPDATE registrations AS r LEFT JOIN locations as l ON (l.prefix = SUBSTR(r.reg_id,1,3)) SET r.location_id=l.id");
        $db->query(NULL, "ALTER TABLE registrations CHANGE location_id location_id INT NOT NULL");
        $db->query(NULL, "ALTER TABLE registrations ADD temp INT AFTER convention_id");
        $db->query(NULL, "UPDATE registrations SET temp=SUBSTR(reg_id, 8)");
        $db->query(NULL, "ALTER TABLE registrations DROP reg_id");
        $db->query(NULL, "ALTER TABLE registrations CHANGE temp reg_id INT NOT NULL");
        # should make the insert statement super fast
        $db->query(NULL, "ALTER TABLE registrations ADD UNIQUE `reg_ident` (`location_id`, `convention_id`,`reg_id`)");

        # Max registrations per pass
        $db->query(NULL, 'ALTER TABLE passes add max_allowed int DEFAULT NULL');
        # feeling lazy, so sub select and copy the value over
        $db->query(NULL, 'UPDATE passes p SET p.max_allowed=(SELECT tickets_total FROM ticketcounters t WHERE t.pass_id=p.id)');

        # Remove ticket counte rtable
        $db->query(NULL, 'DROP TABLE ticketcounters');
	}

	/**
	 * Run queries needed to remove this migration
	 *
	 * @param Kohana_Database Database connection
	 */
	public function down(Kohana_Database $db)
	{
	}
}
