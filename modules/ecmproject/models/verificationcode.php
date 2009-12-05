<?php

class Verificationcode_Model extends ORM 
{
    const TYPE_EMAIL_CHANGE   = 1;
    const TYPE_VALIDATE_EMAIL = 2;
    const TYPE_LOST_PASSWORD  = 3;

    public $original_code;
    protected $ignored_columns = array('original_code');

    /* On unserialize never check the db */
    protected $reload_on_wakeup = false;

    // Current relationships
    public $belongs_to = array('account');

    // Table primary key and value
    protected $primary_key = 'id';

    // Model table information
    protected $table_columns = array (
            'id'          => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, 'sequenced' => true, ),
            'account_id'  => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true                       ),
            'type'        => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true                       ),
            'code'        => array ( 'type' => 'string', 'length' => '40'                                              ),
            'value'       => array ( 'type' => 'string', 'length' => '255'                                             ),
    );
	
    public function unique_key($id = NULL) 
    {
        if (empty($id))
            return $this->primary_key;

        /*
        if (is_string($id) && !ctype_digit($id))
            return 'code';
        
        if (is_numeric($id))
            return $this->primary_key;
        */

        return parent::unique_key($id);
    }
}
