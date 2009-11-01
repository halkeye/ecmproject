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
            'start_date'    => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, ),
            'end_date'      => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, ),
			'location'          => array ( 'type' => 'string', 'length' => '150' ),
    );
	
	public $default_fields = array(
            'name' => array( 'type'  => 'text', 'label' => 'Convention Name', 'required'=>true ),
            'start_date' => array( 'type'  => 'date', 'label' => 'Start Date', 'required'=>true    ),
            'end_date' => array( 'type'  => 'date', 'label' => 'End Date', 'required'=>true    ),
            'location' => array( 'type'  => 'text', 'label' => 'Location', 'required'=>true    )
    );
	
	public function save()
	{
		parent::save();
	}
	
	
	public function validate_admin(array & $array, $save = FALSE) 
	{
		$array = Validation::factory($array);
        $array->pre_filter('trim');
		
		$array->add_rules('name', 'required');
		$array->add_rules('location', 'required');
		$array->add_callbacks('start_date', array($this, '__validateISODate')); //Non-set start date will be set to today
		$array->add_callbacks('end_date', array($this, '__validateISODate')); //Non-set end date will be set to convention end.
		
		return parent::validate($array, $save);
	}
	
	/* Have some utility library instead of duplicating this across models? */
	public function __validateISODate(Validation $array, $field)
	{
		// previous to PHP 5.1.0 you would compare with -1, instead of false
		if (($time = strtotime($array->$field)) === false) {
			$array->add_error($field, 'invalid_date');
		}
		else
			$this->$field = $array->$field;
	}

    public function getCurrentConvention()
    {
        return $this->where(time().' BETWEEN start_date AND end_date')->find();
    }

	public function getTotalConventions()
	{
		$db = new Database();
		$result = $db->query('SELECT COUNT(*) as count FROM conventions');
		
		return (int) $result[0]->count;
	}
}
