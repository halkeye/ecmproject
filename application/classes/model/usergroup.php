<?php

class Model_Usergroup extends orm
{
    var $_table_name = 'usergroups';
    #var $primary_key = 'usergroups_id';

    protected $_table_columns = array (
            'id'          => array ( 'type' => 'int',    'max' => 2147483647,    'unsigned' => true,    'sequenced' => true,  ),
            'name'        => array ( 'type' => 'string', 'length' => '55',  ),
            'description' => array ( 'type' => 'string', 'null' => true,  ),
    );

    public $_has_many = array(
        'Permissions' => array (
            'model' => 'permission',
            'through' => 'usergroups_permissions',
            'foreign_key' => 'usergroup_id',
        ),
		'Accounts' => array (
            'model' => 'account',
            'through' => 'accounts_usergroups',
        )
    );

    public function unique_key($id = NULL)
    {
        if (empty($id))
            return parent::unique_key($id);

        if (is_string($id))
            return 'name';

        return parent::unique_key($id);
    }

    /**
	 * Validates and optionally saves a new user record from an array.
	 *
	 * @param  array    values to check
	 * @param  boolean  save[Optional] the record when validation succeeds
	 * @return boolean
	 */
	public function validate(array & $array, $save = FALSE)
	{
		// Initialise the validation library and setup some rules
		$array = Validation::factory($array);
        // uses PHP trim() to remove whitespace from beginning and end of all fields before validation
        $array->pre_filter('trim');

        // Add Rules
        $array->add_rules('name', 'required', 'unique');

        // Email unique validation
        //$array->add_callbacks('email', array($this, '_unique_name'));
        //$array->add_rules('name', 'required', array($this, '_name_exists'));


		return parent::validate($array, $save);
	}

}

/* End of file usergroup.php */
/* Location: ./application/models/usergroup.php */
