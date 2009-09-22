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

    public function __toString()
    {
        return $this->name . ' - ' . sprintf('$%01.2F', $this->price);
    }
}

/* End of file pass.php */
/* Location: ./application/models/pass.php */ 
