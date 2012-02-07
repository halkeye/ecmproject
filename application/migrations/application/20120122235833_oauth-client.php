<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Sample OAuth2 Client
 */
class Migration_Application_20120122235833 extends Minion_Migration_Base {

	/**
	 * Run queries needed to apply this migration
	 *
	 * @param Kohana_Database Database connection
	 */
	public function up(Kohana_Database $db)
	{
        Kohana_Model_OAuth2_Client::create_client(
            'http://localhost/oauth-iphone-client',
            1
        );
	}

	/**
	 * Run queries needed to remove this migration
	 *
	 * @param Kohana_Database Database connection
	 */
	public function down(Kohana_Database $db)
	{
		$db->query(NULL, "DELETE FROM `oauth2_clients` WHERE 'user_id' = 1;");
	}
}
