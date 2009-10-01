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
	
		$array->add_rules('name', 'required');
		$array->add_rules('price', 'required'); //Also set
		$array->add_rules('isPurchasable', 'required'); //Will be set on form. 	

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
}

/* End of file pass.php */
/* Location: ./application/models/pass.php */ 
