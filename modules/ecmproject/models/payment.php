<?php

class Payment_Model extends ORM 
{

	/* These fields are all that are needed for manual payment entry. Other fields are for PayPal use only */
	public $default_fields = array(
            'type' => array( 'type'  => 'select', 'label' => 'Email', 'required'=>true 								),
			'txn_id' => array ('type' => 'text', 'label' => 'Transaction ID', 'required' => false					),
			'receipt_id' => array ('type' => 'text', 'label' => 'Reciept ID', 'required' => false					),
            'mc_gross' => array( 'type'  => 'text', 'label' => 'Password', 'required'=>true     					),
            'payment_status' => array( 'type'  => 'select', 'label' => 'Status', 'required'=>true    					)
    );
    
    /* On unserialize never check the db */
    protected $reload_on_wakeup = false;

    // Table primary key and value
    protected $primary_key = 'id';

    // Model table information
    protected $table_columns = array (
            'id' => array ( 'type' => 'int','max' => 2147483647,'unsigned' => true,'sequenced' => true, ),
            'register_id'   => array ( 'type' => 'int', 'max' => 2147483647, 'unsigned' => true,  ),
            'last_modified' => array ( 'type' => 'int','max' => 2147483647,'unsigned' => true,'null' => true,  ),
            'type' => array ('type' => 'string','length' => '55',),
            'mc_gross' => array ('type' => 'float','length' => '10,2',),
            'payer_id' => array ('type' => 'string','length' => '13',),
            'payment_date' => array ('type' => 'int', 'max' => 2147483647, 'unsigned' => true,),
            'payment_status' => array ('type' => 'string','length' => '17',),
            'txn_id' => array ('type' => 'string','length' => '17',),
            'receipt_id' => array ('type' => 'string','length' => '19', ),
            'mod_time' => array ('type' => 'int','max' => 2147483647,'unsigned' => true,'null' => true,),
    );
	
	public function save()
	{
		if ($this->id == 0)
			$this->payment_date = time();
			
		$this->last_modified = 1;
		$ret = parent::save();
	}
	
	public function validate_admin(array & $array, $save = FALSE)
	{
		$array = Validation::factory($array);
        $array->pre_filter('trim');
		
		$fields = $this->default_fields;
        foreach ($fields as $field => $fieldData)
        {
            if (isset($fieldData['required']) && $fieldData['required'])
            {
                $array->add_rules($field, 'required');
            }
        }
		
		/* Ensure non-empty dropdown menus */

		if (empty($array->type) || $array->type == -1)
			$array->add_error('type', 'type_default');
			
		if (empty($array->payment_status) || $array->type == -1)
			$array->add_error('payment_status', 'payment_status_default');
		
		return parent::validate($array, $save);
	}

	public function getTotalPayments($reg)
	{
		$db = new Database();
		$result = $db->query('SELECT COUNT(*) as count FROM payments WHERE register_id=' . $reg);
		
		return (int) $result[0]->count;
	}
	
	public function getTotal()
	{
		$db = new Database();
		$result = $db->query('SELECT SUM(mc_gross) as gross FROM payments WHERE register_id=' . $this->register_id);
		
		return (int) $result[0]->gross;		
	}
}
