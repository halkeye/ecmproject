<?php

class Convention_Model extends ORM 
{

    /* On unserialize never check the db */
    protected $reload_on_wakeup = false;

    protected $has_many = array('registration');

    // Table primary key and value
    protected $primary_key = 'id';

    // Model table information
    protected $table_columns = array (
            'id'            => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, 'sequenced' => true, ),
            'name'          => array ( 'type' => 'string', 'length' => '100' ),
            'name'          => array ( 'type' => 'string', 'length' => '150' ),
            'start_date'    => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, ),
            'end_date  '    => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, ),
    );

    public function getCurrentConvention()
    {
        return $this->where(time().' BETWEEN start_date AND end_date')->find();
    }

}
