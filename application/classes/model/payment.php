<?php

class Model_Payment extends ORM 
{
	const STATUS_PENDING = 'Pending'; // Payment approval is pending.
    const STATUS_DENIED  = 'Denied'; // Payment was denied (invalid, insufficient funds, etc).
	const STATUS_COMPLETED	 = 'Completed'; // Payment status is accepted (completed).	

	/* These fields are all that are needed for manual payment entry. Other fields are for PayPal use only */
	public $default_fields = array(
            'type' => array( 'type'  => 'select', 'label' => 'Email', 'required'=>true 								),
			'txn_id' => array ('type' => 'text', 'label' => 'Transaction ID', 'required' => false					),
			'receipt_id' => array ('type' => 'text', 'label' => 'Receipt ID', 'required' => false					),
            'mc_gross' => array( 'type'  => 'text', 'label' => 'Password', 'required'=>true     					),
            'payment_status' => array( 'type'  => 'select', 'label' => 'Status', 'required'=>true    					)
    );
    
    /* On unserialize never check the db */
    protected $_reload_on_wakeup = false;

    // Table primary key and value
    protected $_primary_key = 'id';

    // Model table information
    protected $_table_columns = array (
            'id' => array ( 'type' => 'int','max' => 2147483647,'unsigned' => true,'sequenced' => true, ),
            'reg_id'   => array ( 'type' => 'int', 'max' => 2147483647, 'unsigned' => true,  ),
            'type' => array ('type' => 'string','length' => '55',),
            'mc_gross' => array ('type' => 'float','length' => '10,2',),
            'payer_id' => array ('type' => 'string','length' => '13',),
            'payment_date' => array ('type' => 'int', 'max' => 2147483647, 'unsigned' => true,),
            'payment_status' => array ('type' => 'string','length' => '17',),
            'txn_id' => array ('type' => 'string','length' => '17',),
            'receipt_id' => array ('type' => 'string','length' => '19', ),
            'mod_time' => array ('type' => 'int','max' => 2147483647,'unsigned' => true,'null' => true,),
            'payment_type' => array ('type' => 'string','length' => '20', )
    );

	public function __construct($id = NULL)
	{
        parent::__construct($id);
        if (!$this->loaded())
        {
			$this->payment_date = time();
        }
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
		
		/* For optional fields */
        $array->add_rules('txn_id', 'length[0-255]');
        $array->add_rules('receipt_id', 'length[0-255]');
		
		/* Ensure non-empty dropdown menus */

		if (empty($array->type) || $array->type == -1)
			$array->add_error('type', 'type_default');
			
		if (empty($array->payment_status) || $array->type == -1)
			$array->add_error('payment_status', 'payment_status_default');
		
		return parent::validate($array, $save);
	}
	
	public function getPaymentStatusSelectList() {
		return array(Model_Payment::STATUS_PENDING => 'Pending', Model_Payment::STATUS_DENIED => 'Denied', Model_Payment::STATUS_COMPLETED => 'Completed');
	}

	public function statusToString()
	{
		if ($this->payment_status == Model_Payment::STATUS_PENDING)
			return 'PENDING';
		else if ($this->payment_status == Model_Payment::STATUS_DENIED )
			return 'DENIED';
		else if ($this->payment_status == Model_Payment::STATUS_COMPLETED)
			return 'COMPLETE';
		else
			return $this->payment_status; //If paypal sets status values, it'll probably be in string format. So just return it.
	}
	
	public function getTotalPayments($reg)
	{
		$db = new Database();
		$result = $db->query('SELECT COUNT(*) as count FROM payments WHERE reg_id=?', $db->escape($reg));
		
		return (int) $result[0]->count;
	}
	
	public function lastModifiedName()
	{
        /* FIXME UCHI< YOU ARE MY ONLY HOPE */
        return $this->id;
	}
	
	public function getTotal()
	{
		$db = new Database();				
		$result = $db->query('SELECT SUM(mc_gross) as gross FROM payments WHERE reg_id=? AND payment_status=?',$this->reg_id, Model_Payment::STATUS_COMPLETED);
		
		return (int) $result[0]->gross;		
	}
	
	public function staticGetTotal($reg_id)
	{
		$db = new Database();
		$result = $db->query('SELECT SUM(mc_gross) as gross FROM payments WHERE reg_id=? AND payment_status=?',$reg_id, Model_Payment::STATUS_COMPLETED);
		
		return (int) $result[0]->gross;		
	}
}
