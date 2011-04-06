<?php

class Model_Convention extends ORM
{
    /* On unserialize never check the db */
    protected $_reload_on_wakeup = false;

    protected $_has_many = array(
        'registration' => array()
    );

    protected $_table_columns = array (
            'id'            => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, 'sequenced' => true, ),
            'name'          => array ( 'type' => 'string', 'length' => '255' ),
            'location'      => array ( 'type' => 'string', 'length' => '255' ),
    );

    public $default_fields = array(
            'name' 		=> array( 'type' => 'text', 'label' => 'Convention Name', 'required'=>true ),
            'location' 	=> array( 'type' => 'text', 'label' => 'Location', 'required'=>true )
    );
	
	public function rules()
	{
		return array(
			'name'		=> array( 
				array('not_empty'), 
				array('max_length',	array(':value', 255) )
			),
			'location'	=> array(
				array( 'max_length', array(':value', 255) )		
			)
		);	
	}	

    public function filters()
    {
		return array(
			'name'		=>  array( (array('trim')) ),
			'location'	=>	array( (array('trim')) )
		);	
    }   
    public static function getTotalConventions()
    {
        $query = DB::query(Database::SELECT, 'SELECT COUNT(*) as count FROM conventions');
        $row = $query->execute();
        return (int) $row[0]['count'];
    }
    public function validConvention($cid)
    {
        if (!is_numeric($cid) || $cid == -1)
            return false;

        $res = ORM::factory('Convention', $cid)->find();
        if ($res->loaded())
            return true;
        else
            return false;
    }
}
