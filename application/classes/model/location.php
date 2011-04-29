<?php

class Model_Location extends ORM 
{
	/* Prevent the retrieval of this location (internally reserved) */
	const RESERVED_LOCATION = 'ECM';

	protected $_primary_key = 'id';
	protected $_table_columns = array(
		'id'            => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, 'sequenced' => true, ),
		'prefix'		=> array ( 'type' => 'string', 'length' => 5												 ),
		'location'		=> array ( 'type' => 'string', 'length' => 255												 ),
	);

	public $formo_defaults = array(
        'prefix' 	=> array( 'type'  => 'text', 	'label' => 'Prefix', 			'required'	=> true, 'adminRequired'=>true    ),            
        'location' 	=> array( 'type'  => 'text', 	'label' => 'Location', 			'required'	=> true, 'adminRequired'=>true 	  ),
	);

	public function labels()
	{
		return array(
			'prefix'  		=> 'Prefix',
			'location' 		=> 'Location',
		);
	}
	
	public function find() 
	{
		$this->where('prefix', '!=', Model_Location::RESERVED_LOCATION);
		return parent::find();
	}
	
	public function find_all() 
	{
		$this->where('prefix', '!=', Model_Location::RESERVED_LOCATION);
		return parent::find_all();
	}
	
	public function count_all()
	{
		return parent::count_all() - 1;
	}
	
	 public function filters()
    {
        $filters = parent::filters();
        $filters[TRUE] = array(
            array('trim')
        );
		
		return $filters;
	}
	
	public function rules()
	{
        $rules = parent::rules();
		$rules['prefix'] = array(
			array('not_empty'), 
			array('min_length', array(':value', 0)),
			array('max_length', array(':value', 5)),
			array(array($this, 'notReserved')),
			array(array($this, 'uniquePrefix')),
		);
		$rules['location'] = array(
			array('not_empty'), 
			array('min_length', array(':value', 0)),
			array('max_length', array(':value', 255)),
		);
		
		return $rules;
	}
	public function notReserved($value) {
		return $value !== Model_Location::RESERVED_LOCATION;
	}
	public function uniquePrefix($value) {
		return (bool) !ORM::Factory('Location')->where('prefix', '=', $value)->find()->loaded();
	}
	public static function getTotalLocations()
	{
		return ORM::Factory('Location')->count_all();
	}
}

?>