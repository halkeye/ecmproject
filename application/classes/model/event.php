<?php
class Model_Event extends ORM
{
    /* On unserialize never check the db */
    protected $_reload_on_wakeup = false;

    protected $_has_many = array(
        'registration' => array()
    );

    // Table primary key and value
    protected $_primary_key = 'id';

    protected $_table_columns = array (
        'id'            => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, 'sequenced' => true, ),
        'name'          => array ( 'type' => 'string', 'length' => '255' ),
        'location'      => array ( 'type' => 'string', 'length' => '255' ),
    );

    public $default_fields = array(
        'name' 			=> array( 'type'  => 'text', 'label' => 'Convention Name', 'required'=>true ),
        'location' 		=> array( 'type'  => 'text', 'label' => 'Location', 	   'required'=>true )
    );

	protected $_rules = array(
		'name'		=> array(
			'not_empty'		=> true,
			'max_length'	=> array(255)
		),
		'location'	=> array(
			'max_length'	=> array(255)
		)
	);

    public function filters()
    {
        $filters = parent::filters();
        $filters['*'] = array('trim');
        return $filters;
    }

    public static function getTotalEvents()
    {
        $query = DB::query(Database::SELECT, 'SELECT COUNT(*) as count FROM events');
        $row = $query->execute();
        return (int) $row[0]['count'];
    }
}
