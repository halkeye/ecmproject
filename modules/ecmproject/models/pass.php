<?php

class Pass_Model extends orm 
{
    var $table_name = 'passes';

    /*
    protected $table_columns = array (
            'id'          => array ( 'type' => 'int',    'max' => 2147483647,    'unsigned' => true,    'sequenced' => true,  ),
            'pkey'        => array ( 'type' => 'string', 'length' => '55',  ),
            'description' => array ( 'type' => 'string', 'null' => true,  ),
    );
    */

    public static function find_all_for_account($account)
    {
        $orm = ORM::factory('pass');
        $orm->orwhere(array(
                'enddate >' => time(),
                'enddate ' => null,
        ));
        $orm->where(array('startdate <' => time()));
        return $orm->find_all();

    }
}

/* End of file pass.php */
/* Location: ./application/models/pass.php */ 
