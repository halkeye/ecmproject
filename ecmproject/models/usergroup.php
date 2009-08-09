<?php

class UserGroup extends Model 
{
    var $table_name = 'usergroups';
    var $primary_key = 'guid';
    
    /**
	 * Allows a model to be loaded by username or email address.
	 */
	protected function where_key($id = NULL)
	{
        return 'id';
	}


    var $validation = array(
        array(
            'field' => 'guid',
            'label' => 'GUID',
            'rules' => array('xss_clean', 'trim', 'unique'),
        ),
        array(
            'field' => 'name',
            'label' => 'Group Name',
            'rules' => array('xss_clean', 'required', 'trim', 'unique'),
        ),
        array(
            'field' => 'description',
            'label' => 'Group Description',
            'rules' => array('xss_clean', 'trim'),
        ),
    );

}

/* End of file usergroup.php */
/* Location: ./application/models/usergroup.php */ 
