<?php

class Model_Pass extends ORM
{
	public $default_fields = array(
            'name' 				=> array( 'type'  => 'text', 	'label' => 'Pass Name', 	'required'=>true ),
            'price' 			=> array( 'type'  => 'text', 	'label' => 'Price', 		'required'=>true ),
            'convention_id' 	=> array( 'type'  => 'select', 	'label' => 'Convention', 	'required'=>true ),
            'startDate' 		=> array( 'type'  => 'date', 	'label' => 'Start Date', 	'required'=>true ),
            'endDate'   		=> array( 'type'  => 'date', 	'label' => 'End Date', 		'required'=>true ),
            'isPurchasable'  	=> array( 'type'  => 'boolean', 'label' => 'Purchasable', 	'required'=>false),
    );
    
    protected $_has_one = array(
        'convention' => array(
            'model' => 'Convention',
            'foreign_key' => 'id',
        ),
		'ticketcounter' => array(
			'model' => 'TicketCounter',
			'foreign_key' => 'pass_id',
		),
    );
    protected $_has_many = array(
        'passes' => array(
            'model' => 'Pass',
            'foreign_key' => 'pass_id',
        )
    );

    protected $_table_columns = array(
        'id' 				=> array('type' => 'int', 'min' => '0', 'max' => '4294967295', 'column_name' => 'id', 'column_default' => NULL, 'data_type' => 'int unsigned', 'is_nullable' => false, 'ordinal_position' => 1, 'display' => '10', 'comment' => '', 'extra' => 'auto_increment', 'key' => 'PRI', 'privileges' => 'select,insert,update,references',),
        'convention_id' 	=> array('type' => 'int', 'min' => '0', 'max' => '4294967295', 'column_name' => 'convention_id', 'column_default' => NULL, 'data_type' => 'int unsigned', 'is_nullable' => false, 'ordinal_position' => 2, 'display' => '10', 'comment' => '', 'extra' => '', 'key' => 'MUL', 'privileges' => 'select,insert,update,references',), 
        'name' 				=> array('type' => 'string', 'column_name' => 'name', 'column_default' => NULL, 'data_type' => 'varchar', 'is_nullable' => false, 'ordinal_position' => 3, 'character_maximum_length' => '100', 'collation_name' => 'utf8_general_ci', 'comment' => '', 'extra' => '', 'key' => '', 'privileges' => 'select,insert,update,references',), 
        'price' 			=> array('type' => 'float', 'exact' => true, 'column_name' => 'price', 'column_default' => NULL, 'data_type' => 'decimal', 'is_nullable' => false, 'ordinal_position' => 4, 'numeric_scale' => '2', 'numeric_precision' => '10', 'comment' => '', 'extra' => '', 'key' => '', 'privileges' => 'select,insert,update,references',), 
        'isPurchasable' 	=> array('type' => 'int', 'min' => '-128', 'max' => '127', 'column_name' => 'isPurchasable', 'column_default' => NULL, 'data_type' => 'tinyint', 'is_nullable' => false, 'ordinal_position' => 5, 'display' => '4', 'comment' => '', 'extra' => '', 'key' => '', 'privileges' => 'select,insert,update,references',), 
        'startDate' 		=> array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647', 'column_name' => 'startDate', 'column_default' => NULL, 'data_type' => 'int', 'is_nullable' => true, 'ordinal_position' => 6, 'display' => '11', 'comment' => '', 'extra' => '', 'key' => '', 'privileges' => 'select,insert,update,references',), 
        'endDate' 			=> array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647', 'column_name' => 'endDate', 'column_default' => NULL, 'data_type' => 'int', 'is_nullable' => true, 'ordinal_position' => 7, 'display' => '11', 'comment' => '', 'extra' => '', 'key' => '', 'privileges' => 'select,insert,update,references',)
    );

	public $tickets_total = -1;
	
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
                /* TODO - should be a valid convention too */
                /* Convention_id's should be numeric. */
				array('not_empty'), 
                array('is_numeric'),
				array(array($this, '__validConventionID')),
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
				array(array($this, 'valid_range'), array(':validation', ':field')),
			),
		);	
	}	
    
    public function filters()
    {
        $filters = parent::filters();
        $filters[TRUE] = array(
            array('trim')
        );
        $filters['startDate'] = array(
            array('strtotime')
        );
        $filters['endDate'] = array(
            array('strtotime'),
        );
        return $filters;
    }   

	public function save(Validation $validation = null)
	{
		$loaded = $this->loaded();
		/* Fill in optional fields.*/			
		if (!isset($this->isPurchasable) || empty($this->isPurchasable))
			$this->isPurchasable = 0;
			
		$ret = parent::save($validation);	
		
		/* Create ticket counter on CREATE. */
		$this->setTicketCounter();
		return $ret;
	}
	
	private function setTicketCounter() 
	{	
		//Mrawr.
		if ($this->tickets_total < -1 || empty($this->tickets_total) ) {
			$this->tickets_total = -1;
		}
	
		$tc = $this->ticketcounter;
		if ( $tc->loaded() )
		{
			if ($tc->tickets_total !== $this->tickets_total )
			{
				$tc->tickets_total = $this->tickets_total;
				$tc->save();
			}
		}
		else 
		{
			$tc->pass_id = $this->id;
			$tc->tickets_assigned = 0;
			$tc->tickets_total = $this->tickets_total;
			$tc->next_id = 1;
			$tc->save();
		}		
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
	public function __validConventionID($value)
	{
		if (is_numeric($value) && $value > 0) {
			return (bool) ORM::Factory('Convention', $value);
		}
		
		return false;
	}
	
	/* This is cheap (since I hardcoded stuff) But it works :) */
	public function valid_range(Validation $validation, $field)
	{				
		$start = $validation['startDate'];
		$end = $validation['endDate'];
		
		if (!$start || !$end || $start >= $end) {			
			$validation->error($field, 'valid_range', array($validation[$field]));
		}
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
