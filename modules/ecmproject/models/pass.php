<?php

class Pass_Model extends orm 
{
    var $table_name = 'passes';
    
	/*
    protected $table_columns = array (
            'id'          		=> array ( 'type' => 'int',    'max' => 2147483647,    'unsigned' => true,    'sequenced' => true,  ),
			'convention_id' 	=> array ( 'type' => 'int',    'max' => 2147483647,    'unsigned' => true),            
            'name'        		=> array ( 'type' => 'string', 'length' => '55',  ),
            'price'				=> array ( 'type' => 'string', 'null' => true,  ),
			'isPurchasable'     => array ( 'type' => 'string', 'length' => '55',  ),
            'minAge'			=> array ( 'type' => 'string', 'null' => true,  ),
			'maxAge'			=> array ( 'type' => 'string', 'null' => true,  ),
			'startDate'  	    => array ( 'type' => 'string', 'length' => '55',  ),
            'endDate'			=> array ( 'type' => 'string', 'null' => true,  )			
    );
		*/
		
	public function __construct($id = NULL)
	{
        parent::__construct($id);
    }
	
	public function __set($key, $value)
	{
		parent::__set($key, $value);
	}

	/* Only admin will modify passes anyways.*/
	public function validate_admin(array & $array, $save = FALSE) 
	{
		$array = Validation::factory($array);
        $array->pre_filter('trim');
	
		$array->add_rules('convention_id', 'required');
		$array->add_rules('name', 'required');
		$array->add_rules('price', 'required'); //Also set
		$array->add_callbacks('startDate', array($this, '__validateISODate')); //Non-set start date will be set to today
		$array->add_callbacks('endDate', array($this, '__validateISODate')); //Non-set end date will be set to convention end.
		
		if (!isset($isPurchasable))
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
		$regex = '/(\d{2})-(\d{2})-(\d{4})/';		
		
		// PHP regex matching seems so damn picky relative to Java. Can't even match an escaped backslash -_-;
		$string = str_replace("/", "-", $array->$field);
		
		//If the field is not set or empty
		if (!isset($array->$field) || empty($array->$field))
		{
			//Do nothing. 
		}		
		else if ( ! preg_match($regex, $string) ) {
			$array->add_error($field, 'invalid_date');
		}
	}
}

/* End of file pass.php */
/* Location: ./application/models/pass.php */ 
