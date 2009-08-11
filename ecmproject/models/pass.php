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

    function paypalButton($notify_url = null, $return_url = null, $cancel_url = null)
    {
        $p = new Paypal();
        $p->itemName = $this->name;
        $p->itemId   = $this->id;
        $p->price    = $this->price;
        if ($notify_url)
            $p->addField('notify_url',    $notify_url);
        if ($return_url)
            $p->addField('return',        $return_url);
        if ($cancel_url)
            $p->addField('cancel_return', $cancel_url);
        return $p->paypalView();
    }

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
