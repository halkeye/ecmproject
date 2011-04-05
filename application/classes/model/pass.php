<?php

class Model_Pass extends ORM
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
    
    protected $_has_one = array(
        'convention' => array(
            'model' => 'Convention',
            'foreign_key' => 'convention_id',
        )
    );

	public function rules()
	{
		return array(
			'name'		=> array( 
				array('not_empty'), 
			),
			'isPurchasable' => array( 
                array('range', array(':value',0,1))
			),
			'convention_id' => array( 
                /* FIXME - should be a valid convention too */
                /* Convention_id's should be numeric. */
				array('not_empty'), 
                array('is_numeric'),
			),
			'price' => array( 
				array('not_empty'), 
                array('numeric'),
			),
			'startDate' => array( 
				array('not_empty'), 
                array(array($this, '__validateISODate')),
			),
			'endDate' => array( 
				array('not_empty'), 
                array(array($this, '__validateISODate')),
			),
		);	
	}	
    
    public function filters()
    {
        $filters = parent::filters();
        $filters[TRUE] = array(
            array('trim')
        );
        return $filters;
    }   

	public function save(Validation $validation = null)
	{
		/* Fill in optional fields.*/
		if (!isset($this->minAge) || empty($this->minAge))
			$this->minAge = 0;
			
		if (!isset($this->maxAge) || empty($this->maxAge))
			$this->maxAge = 255;
			
		if (!isset($this->isPurchasable) || empty($this->isPurchasable))
			$this->isPurchasable = 0;
			
		parent::save($validation);	
	}
	
	/* Only admin will modify passes anyways.*/
    public function __toString()
    {
        return $this->name . ' - ' . sprintf('$%01.2F', $this->price);
    }
	
	public function __validateISODate($value)
	{
		// previous to PHP 5.1.0 you would compare with -1, instead of false
		if (($time = strtotime($value)) === false) {
            return 0;
		}
        return 1;
	}
	
	public static function getTotalPasses($cid)
	{
        return ORM::Factory('Pass')->where('convention_id','=',$cid)->count_all();
	}
	
	//Get all passes related to the convention regardless of status.
	public function getAllPasses($cid)
	{
        return ORM::Factory('Pass')->where('convention_id','=',$cid)->find_all();
	}
}

/* End of file pass.php */
/* Location: ./application/models/pass.php */ 
