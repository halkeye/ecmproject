<?php

class Model_Permission extends orm 
{
    var $_table_name = 'permissions';
    #var $primary_key = 'permissions_id';

    protected $_table_columns = array (
            'id'          => array ( 'type' => 'int',    'max' => 2147483647,    'unsigned' => true,    'sequenced' => true,  ),
            'pkey'        => array ( 'type' => 'string', 'length' => '55',  ),
            'description' => array ( 'type' => 'string', 'null' => true,  ),
    );

    public function unique_key($id = NULL) 
    {
        if (empty($id))
            return parent::unique_key($id);

        if (is_string($id))
            return 'pkey';

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
        $array->add_rules('pkey', 'required', 'unique');

        // Email unique validation
        //$array->add_callbacks('email', array($this, '_unique_name'));
        //$array->add_rules('name', 'required', array($this, '_name_exists'));

 
		return parent::validate($array, $save);
	}

}

/* End of file permission.php */
/* Location: ./application/models/permission.php */ 
