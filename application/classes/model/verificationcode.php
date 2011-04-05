<?php

class Model_Verificationcode extends ORM 
{
    const TYPE_EMAIL_CHANGE   = 1;
    const TYPE_VALIDATE_EMAIL = 2;
    const TYPE_LOST_PASSWORD  = 3;

    public $original_code;
    protected $_ignored_columns = array('original_code');

    /* On unserialize never check the db */
    protected $_reload_on_wakeup = false;

    // Current relationships
    public $_belongs_to = array(
        'account' => array('model'=>'Account'),
    );

    // Table primary key and value
    protected $_primary_key = 'id';

    // Model table information
    protected $_table_columns = array (
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

    public function delete_all_for_account($id)
    {
		DB::delete($this->_table_name)
			->where('account_id', '=', $id)
			->execute($this->_db);
    }
    public function generate_code($account_id,$salt, $type, $value)
    {
        while (true)
        {
            try 
            {
                $code = substr(md5(uniqid(rand(), true)), 0, 10);
                $vcode = ORM::Factory('verificationcode');
                $vcode->original_code = $code;
                $vcode->account_id = $account_id;
                $vcode->code = sha1($salt. $code);
                $vcode->type = $type;
                $vcode->value = $value;
                $vcode->save();
                Kohana::$log->add(Log::DEBUG, "Generated |$type| |" . $vcode->code . "| for |$salt|-|$code|");
                return $vcode;
            }
            catch (Kohana_Database_Exception $e) {
                var_dump($e);
                die($e);
            }
        }
        ## FIXME - throw better exception
        return -1;
    }
}
