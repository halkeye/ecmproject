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
            'name' 		=> array( 'type' => 'text', 'label' => 'Event Name', 'required'=>true ),
            'location' 	=> array( 'type' => 'text', 'label' => 'Location' )
    );
		
	public function labels()
	{
		return array(
			'name' => 'Event Name',
			'location' => 'Location',
		);
	}

	
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
		$filters = parent::filters();
		$filters[TRUE] = array ( array('trim') );
		return $filters;
    }   
    public static function getTotalConventions()
    {
        $query = DB::query(Database::SELECT, 'SELECT COUNT(*) as count FROM conventions');
        $row = $query->execute();
        return (int) $row[0]['count'];
    }

    public static function validConvention($cid)
    {
        if (!is_numeric($cid) || $cid == -1)
            return false;

        $res = ORM::factory('Convention', $cid);
        return $res->loaded();
    }
}
