<?php

class Registration_Model extends ORM 
{
    const STATUS_UNPROCESSED = 0; // Payment has not been sent yet (or recieved if mail-in)
    const STATUS_PROCESSING  = 1; // Waiting for Paypal to respond, mail-in/in-person payment is in limbo.
	const STATUS_NOT_ENOUGH	 = 2; // Payment recieved is not enough to pay cost of pass.
	const STATUS_FAILED		 = 98; //Registration no longer valid (cancelled, refunded, etc).
    const STATUS_PAID        = 99; // Fully working and paid
    
    protected $ignored_columns = array('agree_toc');

    /* On unserialize never check the db */
    protected $reload_on_wakeup = false;

    protected $belongs_to = array('convention');

    protected $has_one = array('pass', 'account');

//    protected $load_with = array('convention','pass', 'account');

    // Table primary key and value
    protected $primary_key = 'id';

    // Model table information	
    protected $table_columns = array (
            'id'            => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, 'sequenced' => true, ),
            'convention_id' => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true,                      ),
            'pass_id'       => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true,                      ),
            'account_id'    => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true,                      ),
            'gname'         => array ( 'type' => 'string', 'length' => '55'                                              ),
            'sname'         => array ( 'type' => 'string', 'length' => '55'                                              ),
            'badge'         => array ( 'type' => 'string', 'length' => '55', 'null' => true                              ),
            'dob'           => array ( 'type' => 'string', 'format' => '0000-00-00',                                     ),
            'phone'         => array ( 'type' => 'string', 'length' => '15'                                              ),
            'cell'          => array ( 'type' => 'string', 'length' => '15'                                              ),
            'city'          => array ( 'type' => 'string', 'length' => '85'                                              ),
            'prov'          => array ( 'type' => 'string', 'length' => '50'                                              ),
            'email'         => array ( 'type' => 'string', 'length' => '55', 'null' => true                              ),
            'econtact'      => array ( 'type' => 'string', 'length' => '55', 'null' => true                              ),
            'ephone'        => array ( 'type' => 'string', 'length' => '15', 'null' => true                              ),
            'heard_from'    => array ( 'type' => 'text',                     'null' => true,                             ),
            'attendance_reason'    => array ( 'type' => 'text',              'null' => true,                             ), 
            'status'      => array ( 'type' => 'int',    'max' => 127,        'unsigned' => false,                       ),
    );

    public $formo_defaults = array(
            'gname' => array( 'type'  => 'text', 'label' => 'Given Name', 'required'=>true ),
            'sname' => array( 'type'  => 'text', 'label' => 'Surname', 'required'=>true    ),
            'badge' => array( 'type'  => 'text', 'label' => 'Badge', 'required'=>true    ),
            'pass_id' => array( 'type'  => 'select', 'label' => 'Pass', 'required'=>true    ),
            'dob'   => array( 'type'  => 'date', 'label' => 'Date of Birth', 'required'=>true ),
            'email' => array( 'type'  => 'text', 'label' => 'Email', 'required'=>true ),
            'phone' => array( 'type'  => 'text', 'label' => 'Phone', 'required' => true),
            'cell'  => array( 'type'  => 'text', 'label' => 'Cell Phone', 'required' => false),
            'city'  => array( 'type'  => 'text', 'label' => 'City', 'required' => false),
            'prov'  => array( 'type'  => 'text', 'label' => 'Province', 'required' => false),
            'econtact'  => array( 'type'  => 'text', 'label' => 'Emergency Contact Name', 'required' => true),
            'ephone'  => array( 'type'  => 'text', 'label' => 'Emergency Contact Phone', 'required' => true),
            /*'heard_from' => array( 'type'  => 'text', 'label' => 'Heard from', 'required'=>false ),
            'attendance_reason' => array( 'type'  => 'textarea', 'rows'  => 10, 'label' => 'Reason For Attendance', 'required'=>false), */
    );

    public function __toString()
    {
        return $this->pass . ' - ' . $this->badge;
    }

    /**
     * Validates and optionally saves a new user record from an array.
     *
     * @param  array    values to check
     * @param  boolean  save[Optional] the record when validation succeeds
     * @return boolean
     */
    public function validate(array & $array, $save = FALSE)
    {
        // Initialise the validation library and setup some rules
        $array = Validation::factory($array);
        // uses PHP trim() to remove whitespace from beginning and end of all fields before validation
        $this->addValidationRules($array);

        /* Keep track of what really changed so we don't update fields we havn't changed */
        $realChanged = $this->changed;
        foreach ($array->safe_array() as $field=>$value)
        {
            if (isset($this->ignored_columns[$field])) { continue; }
            if ($this->$field != $value) 
            {
                $realChanged[$field] = $this->$field; 
            }
        }
        $ret = parent::validate($array, $save);
        $this->changed = $realChanged;
        return $ret;
    }

	/**
     * Validates and optionally saves a new user record from an array. Same as validate minus pass restriction checking.
     *
     * @param  array    values to check
     * @param  boolean  save[Optional] the record when validation succeeds
     * @return boolean
     */
    public function validate_admin(array & $array, $save = FALSE)
    {
        // Initialise the validation library and setup some rules
        $array = Validation::factory($array);
        // uses PHP trim() to remove whitespace from beginning and end of all fields before validation
        $this->addValidationRules_admin($array);

        /* Keep track of what really changed so we don't update fields we haven't changed */
        $realChanged = $this->changed;
        foreach ($array->safe_array() as $field=>$value)
        {
            if ($this->$field != $value) 
            {
                $realChanged[$field] = $this->$field; 
            }
        }
        $ret = parent::validate($array, $save);
        $this->changed = $realChanged;
        return $ret;
    }
	
    private function addValidationRules($form)
    {
        $form->pre_filter('trim');

        $fields = $this->formo_defaults;
        foreach ($fields as $field => $fieldData)
        {
            if (isset($fieldData['required']) && $fieldData['required'])
            {
                $form->add_rules($field, 'required');
            }
        }

        // Add Rules
        //$form->add_rules('heard_from', 'standard_text');
        //$form->add_rules('attendance_reason', array($this, '_true'));
        if (isset($form['agree_toc']))
        {
            $form->add_rules('agree_toc', 'required');
        }
        $form->add_rules('email', 'required', array('valid','email'));		
		$form->add_rules('phone', 'phone[7,10-20]');
        $form->add_rules('cell', 'phone[7,10-20]');
        $form->add_rules('ephone', 'phone[7,10-20]');
        $form->add_rules('dob', 'date');
        $form->add_callbacks('pass_id', array($this, '_valid_pass_for_account'));
    }
	
	private function addValidationRules_admin($form)
    {
        $form->pre_filter('trim');

        $fields = $this->formo_defaults;
        foreach ($fields as $field => $fieldData)
        {
            if (isset($fieldData['required']) && $fieldData['required'])
            {
                $form->add_rules($field, 'required');
            }
        }
		
        // Add Rules
        $form->add_rules('heard_from', 'standard_text');
        $form->add_rules('attendance_reason', array($this, '_true'));
        $form->add_rules('email', 'required', array('valid','email'));
		$form->add_rules('phone', 'phone[7,10-20]');
        $form->add_rules('cell', 'phone[7,10-20]');
        $form->add_rules('ephone', 'phone[7,10-20]');
        $form->add_rules('dob', 'date');
        //$form->add_callbacks('pass_id', array($this, '_valid_pass_for_account'));
    }
	
    public function _valid_pass_for_account(Validation $array, $field)
    {
        $ageTime = strtotime($array['dob']);
        $t = $this->convention->start_date; // Store current time for consistency
        $age = ($ageTime < 0) ? ( $t + ($ageTime * -1) ) : $t - $ageTime;
        $yearsOld = intval(floor($age / (60 * 60 * 24 * 365)));

        // If add->rules validation found any errors, get me out of here!
//        if (array_key_exists('pass_id', $array->errors()))
//            return;
        $query = $this->getPossiblePassesQuery();
        $query->where('minAge <=', $yearsOld);
        $query->where('maxAge >=', $yearsOld);
        $query->where('id', $array['pass_id']);
        if (!(bool)$query->count_all())
        {
            $array->add_error($field, 'invalid_pass_age');
        }
    }
	
    /**
     * @param $accountId Account Id
     * @param $conventionId [optional] Convention id, defaults to most recent one
     * @return array of registrations
     */
    public static function getByAccount($accountId, $conventionId = null)
    {
        $db = Database::instance();

        $vars = array($accountId);
        if ($conventionId)
        {
            $conventionWhere = 'c.conventionId = ?';
            $vars[] = $conventionId;
        }
        else 
        {
            $conventionWhere = '? BETWEEN c.start_date AND c.end_date';
            $vars[] = time();
        }

        $result = $db->query("
                SELECT 
                    r.*,
                    c.name as convention_name,
                    c.start_date as convention_start_date,
                    c.end_date as convention_end_date,
                    c.location as convention_location,
                    p.name as pass_name
                FROM 
                    registrations r
                LEFT JOIN 
                    conventions c ON (r.convention_id=c.id)
                LEFT JOIN 
                    passes p ON (r.pass_id=p.id)
                WHERE
                    account_id = ? AND $conventionWhere
                ", $vars);
        return $result;
    }

    public function getPossiblePassesQuery()
    {
        return ORM::Factory('pass')
            ->where('enddate >=',   time())
            ->where('startdate <=', time())
            ->where('isPurchasable', 1)
            ->where('convention_id', $this->convention_id);
    }

    public function getForAccount($account_id)
    {
        /* FIXME - Maybe limit to this convention also, so any outstanding entries will be ignored */
        return $this 
            ->with('convention')
            ->with('pass')
            ->where('account_id', $account_id)
            ->where('status', Registration_Model::STATUS_UNPROCESSED) /* Only grab one we havn't heard back from yet */
            ->find_all();
    }
    
    public function save()
    {
        $originalChanged = $this->changed;
        $this->changed = array_keys($this->changed);
		
		//Only set status to UNPROCESSED if it's a new registration! (Else it'll keep blanking my status updates).
		if ($this->id == 0)
			$this->status = Registration_Model::STATUS_UNPROCESSED;
						
        $ret = parent::save();

        if ( ! empty($originalChanged))
        {
            foreach ($originalChanged as $column=>$oldValue)
            {
                // Compile changed data
                $log = ORM::Factory('log');
                $log->target_account_id = $this->account_id;
                $log->target_registration_id = $this->id;
                $log->target_badge_id = $this->pass_id;
                $log->description = sprintf("%s => %s => %s", $column, ($column == $oldValue ? '--unknown--' : $oldValue), $this->$column);
                $log->save();
            }
        }
    }

    /* for validation */
    public function _true() { return TRUE; }
	
	public function getTotalRegistrations($convention_id)
	{
		$cid = htmlspecialchars($convention_id);
		$db = new Database();
		$result = $db->query('SELECT COUNT(*) as count FROM registrations WHERE convention_id = ' . $cid);
		
		return (int) $result[0]->count;
	}
	
	public function statusToString()
	{
		return Registration_Model::regStatusToString($this->status);
	}
	
	public function regStatusToString($status) 
    {
		if ($status == Registration_Model::STATUS_UNPROCESSED)
			return 'UNPROCESSED';
		else if ($status == Registration_Model::STATUS_PROCESSING )
			return 'PROCESSING';
		else if ($status == Registration_Model::STATUS_PAID)
			return 'PAID';
		else if ($status == Registration_Model::STATUS_NOT_ENOUGH)
			return 'PARTIAL PAYMENT';
		else if ($status == Registration_Model::STATUS_FAILED)
			return 'CANCELLED';
		else
			return 'IN LIMBO';
	}
	
	public function getColumns()
	{
		//return implode(",", array_keys($this->table_columns));
		$keys = array_keys($this->table_columns);
		$columns = array();
		
		foreach($keys as $key):
			$columns[$key] = Kohana::lang('convention.registration_field_' . $key);					
		endforeach;
		
		return implode(",", $columns);
	}
	
	//Function spam!
	public function getStatusValues()
	{
		$status_values = array();
		$status_values[Registration_Model::STATUS_UNPROCESSED] = 'UNPROCESSED';
		$status_values[Registration_Model::STATUS_PROCESSING] = 'PROCESSING';
		$status_values[Registration_Model::STATUS_PAID] = 'PAID';
		$status_values[Registration_Model::STATUS_NOT_ENOUGH] = 'PARTIAL PAYMENT';
		$status_values[Registration_Model::STATUS_FAILED] = 'FAILED';
		return $status_values;
	}
}

/* End of file user.php */
/* Location: ./application/models/registration.php */ 
