<?php

class Pass_Model extends ORM
{
    var $table_name = 'passes';

	public $default_fields = array(
            'name' => array( 'type'  => 'text', 'label' => 'Pass Name', 'required'=>true ),
            'price' => array( 'type'  => 'text', 'label' => 'Price', 'required'=>true    ),
            'convention_id' => array( 'type'  => 'select', 'label' => 'Convention', 'required'=>true    ),
            'startDate' => array( 'type'  => 'date', 'label' => 'Start Date', 'required'=>true    ),
            'endDate'   => array( 'type'  => 'date', 'label' => 'End Date', 'required'=>true ),
            'minAge' => array( 'type'  => 'text', 'label' => 'Minimum Age', 'required'=>true ),
            'maxAge' => array( 'type'  => 'text', 'label' => 'Maximum Age', 'required' => true),
            'isPurchasable'  => array( 'type'  => 'boolean', 'label' => 'Purchasable', 'required' => false),
    );
    
    protected $has_one = array('convention');
		
	public function __construct($id = NULL)
	{
        parent::__construct($id);
    }
	
	public function __set($key, $value)
	{
		parent::__set($key, $value);
	}

	public function save()
	{
		/* Fill in optional fields.*/
		if (!isset($this->minAge) || empty($this->minAge))
			$this->minAge = 0;
			
		if (!isset($this->maxAge) || empty($this->maxAge))
			$this->maxAge = 255;
			
		if (!isset($this->isPurchasable) || empty($this->isPurchasable))
			$this->isPurchasable = 0;
			
		parent::save();	
	}
	
	/* Only admin will modify passes anyways.*/
	public function validate_admin(array & $array, $save = FALSE) 
	{
		$array = Validation::factory($array);
        $array->pre_filter('trim');

		$array->add_rules('isPurchasable', 'is_numeric');
		$array->add_rules('convention_id', 'required');
		$array->add_rules('convention_id', 'is_numeric'); //Convention_id's should be numeric.
		$array->add_rules('name', 'required');
		$array->add_rules('price', 'required'); //Also set
		
		// Some extra work is needed for this.		
		$array->add_rules('startDate', 'required');
		$array->add_rules('endDate', 'required');
		$array->add_callbacks('startDate', array($this, '__validateISODate')); //Non-set start date will be set to today
		$array->add_callbacks('endDate', array($this, '__validateISODate')); //Non-set end date will be set to convention end.

		// Non-selected convention_id input is less than 1.
		if (isset($array['convention_id']) && is_numeric($array['convention_id']) && $array['convention_id'] < 1) {
			$array->add_error('convention_id', 'convention_id_invalid');
		}	
		
		if (!isset($array->isPurchasable))
			$array->isPurchasable = 0;
		
		if (isset($array->minAge) && !empty($array->minAge))
			$array->add_rules('minAge', 'required', 'numeric');
			
		if (isset($array->maxAge) && !empty($array->maxAge))
			$array->add_rules('maxAge', 'required', 'numeric');
				
		return parent::validate($array, $save);
	}
	
    public function __toString()
    {
        return $this->name . ' - ' . sprintf('$%01.2F', $this->price);
    }
	
	public function __validateISODate(Validation $array, $field)
	{
		// previous to PHP 5.1.0 you would compare with -1, instead of false
		if (($time = strtotime($array->$field)) === false) {
			$array->add_error($field, 'invalid_date');
		}
		else
			$this->startDate = $array->$field;
	}
	
	public function getTotalPasses($convention_id)
	{
		$cid = htmlspecialchars($convention_id);
		$db = new Database();
		$result = $db->query('SELECT COUNT(*) as count FROM passes WHERE convention_id = ' . $cid);
		
		return (int) $result[0]->count;
	}
	
	//Get all passes related to the convention regardless of status.
	public function getAllPasses($convention_id)
	{
		$cid = htmlspecialchars($convention_id);
		return ORM::Factory('Pass')->where("convention_id=$convention_id")->find_all();
	}
}

/* End of file pass.php */
/* Location: ./application/models/pass.php */ 
