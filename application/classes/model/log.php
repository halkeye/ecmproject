<?php

class Model_Log extends ORM
{
    /* On unserialize never check the db */
    protected $_reload_on_wakeup = false;

    //protected $has_many = array('registration');

    // Table primary key and value
    protected $_primary_key = 'id';

    // Model table information
    /*
    protected $table_columns = array (
            'id'            => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, 'sequenced' => true, ),
            'name'          => array ( 'type' => 'string', 'length' => '100' ),
            'name'          => array ( 'type' => 'string', 'length' => '150' ),
            'start_date'    => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, ),
            'end_date'      => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, ),
    );
    */

    /**
     * Prepares the model database connection and loads the object.
     *
     * @param   mixed  parameter for find or object to load
     * @return  void
     */
    public function __construct($id = NULL)
    {
        $ret = parent::__construct($id);
        if (!$id || !$this->loaded)
        {
            $this->mod_time = time();
            $this->ip = Request::$client_ip;
            $this->method = Request::current()->controller() .'/' . Request::current()->action();
            if (Auth::instance()->is_logged_in())
            {
                $this->modifier_id = Auth::instance()->getAccount()->id;
            }
        }
    }
}
