<?php

class UserGroup extends DataMapper 
{
    var $table = 'usergroups';
    var $pk_key = 'guid';

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
