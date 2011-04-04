<?php

class Model_Registration extends ORM 
{
    const STATUS_UNPROCESSED = 0; // Payment has not been sent yet (or recieved if mail-in)
    const STATUS_PROCESSING  = 1; // Waiting for Paypal to respond, mail-in/in-person payment is in limbo.
	const STATUS_NOT_ENOUGH	 = 2; // Payment recieved is not enough to pay cost of pass.
	const STATUS_FAILED		 = 98; //Registration no longer valid (cancelled, refunded, etc).
    const STATUS_PAID        = 99; // Fully working and paid
    
    protected $_ignored_columns = array('agree_toc', 'unique_badge');

    /* On unserialize never check the db */
    protected $_reload_on_wakeup = false;

    protected $_belongs_to = array(
        'convention' => array (
            'model' => 'convention', 
            'foreign_key' => 'convention_id'
        )
    );

    protected $_has_one = array(
        'pass' => array(
            'model' => 'pass', 
            'foreign_key' => 'id',
        ),
        'account' => array(
            'model' => 'account', 
            'foreign_key' => 'id',
        ),
    );

//    protected $load_with = array('convention','pass', 'account');

    // Table primary key and value
    protected $_primary_key = 'id';

    // Model table information	
    protected $_table_columns = array (
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
            'gname' => array( 'type'  => 'text', 'label' => 'Given Name', 'required'=>true, 'adminRequired'=>true ),
            'sname' => array( 'type'  => 'text', 'label' => 'Surname', 'required'=>true, 'adminRequired'=>true    ),
            'badge' => array( 'type'  => 'text', 'label' => 'Badge', 'required'=>true    ),
            'pass_id' => array( 'type'  => 'select', 'label' => 'Pass', 'required'=>true, 'adminRequired'=>true    ),
            'dob'   => array( 'type'  => 'date', 'label' => 'Date of Birth', 'required'=>true ),
            'email' => array( 'type'  => 'text', 'label' => 'Email', 'required'=>true ),
            'phone' => array( 'type'  => 'text', 'label' => 'Phone', 'required' => true),
            'cell'  => array( 'type'  => 'text', 'label' => 'Cell Phone', 'required' => false),
            'city'  => array( 'type'  => 'text', 'label' => 'City', 'required' => true),
            'prov'  => array( 'type'  => 'text', 'label' => 'Province', 'required' => true),
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
                $form->add_rules($field, 'required');
            else
                $form->add_rules($field, 'length[0,255]');
        }

        // Add Rules
        //$form->add_rules('heard_from', 'standard_text');
        //$form->add_rules('attendance_reason', array($this, '_true'));
        if (isset($form['agree_toc']))
        {
            $form->add_rules('agree_toc', 'required');
        }
        $form->add_rules('email', 'required', array('valid','email'));		
		$form->add_rules('phone', 'phone[7,9,10,11,14,15]');
        $form->add_rules('cell', 'phone[7,9,10,11,14,15]');
        $form->add_rules('ephone', 'phone[7,9,10,11,14,15]');
        //$form->add_rules('dob', 'date');
		$form->add_callbacks('dob', array($this, '_valid_birthdate'));
        $form->add_callbacks('pass_id', array($this, '_valid_pass_for_account'));
        $form->add_callbacks('unique_badge', array($this, '_unique_badge'));
    }
	
	private function addValidationRules_admin($form)
    {
        $form->pre_filter('trim');

        $fields = $this->formo_defaults;
        foreach ($fields as $field => $fieldData)
        {
            if (isset($fieldData['adminRequired']) && $fieldData['adminRequired'])
            {
                $form->add_rules($field, 'required');
            }
        }
		
        // Add Rules
        $form->add_rules('heard_from', 'standard_text');
        $form->add_rules('attendance_reason', array($this, '_true'));
        $form->add_rules('email', 'required', array('valid','email'));
		$form->add_rules('phone', 'phone[7,9,10,11,14,15]');
        $form->add_rules('cell', 'phone[7,9,10,11,14,15]');
        $form->add_rules('ephone', 'phone[7,9,10,11,14,15]');
        //$form->add_rules('dob', 'date');
		$form->add_callbacks('dob', array($this, '_valid_birthdate'));
        //$form->add_callbacks('pass_id', array($this, '_valid_pass_for_account'));
    }
	
    public function _valid_pass_for_account(Validation $array, $field)
    {
        /*
         * If add->rules validation found any errors, get me out of here!
         * Saves us doing any sql lookups before its valid data 
         */
//        if (array_key_exists('pass_id', $array->errors()))
//            return;
        $ageTime = strtotime($array['dob']);

        $pass = ORM::Factory('Pass')->with('convention')->where('id','=',$array['pass_id'])->find();
        $conventionStartTime = $pass->convention->start_date;
        # Code from http://forums.webmasterhub.net/viewtopic.php?f=23&t=1831 - Option 4
        $yearsOld = abs(substr(date('Ymd', $conventionStartTime) - date('Ymd', $ageTime), 0, -4));

        $query = $this->getPossiblePassesQuery();
        $query->where('minAge <=', $yearsOld);
        $query->where('maxAge >=', $yearsOld);
        $query->where('id', $array['pass_id']);
        if (!(bool)$query->count_all())
            $array->add_error($field, 'invalid_pass_age');
    }
	
	/* Takes in a date in the format: YYYY-MM-DD (ISO_8601) */
	public function _valid_birthdate(Validation $array, $field)
	{
		$date = strtotime($array[$field]);
		
		/* If date validation failed (not a date string) or date does not match expected... */
		if (!$date || date("Y-m-d", $date) != $array[$field])
			$array->add_error($field, 'invalid_birthdate');
        /* if someone isn't born yet, they can't have a badge (mostly because they don't have a birthday) */
        if ($date > time())
			$array->add_error($field, 'invalid_birthdate');
	}
	
    /**
     * @param $accountId Account Id
     * @param $conventionId [optional] Convention id, defaults to most recent one
     * @return array of registrations
     */
    public static function getByAccount($accountId, $conventionId = null)
    {
        $vars = array(':account_id' => $accountId);
        if ($conventionId)
        {
            $conventionWhere = 'c.conventionId = :conventionId';
            $vars[':conventionId'] = $conventionId;
        }
        else 
        {
            $conventionWhere = ':startTime BETWEEN c.start_date AND c.end_date';
            $vars[':startTime'] = time();
        }

        $query = DB::query(Database::SELECT, "
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
                    account_id = :account_id AND $conventionWhere
                ");
        $query->parameters($vars);
        return $query->execute();
    }

    public function getPossiblePassesQuery()
    {
        return ORM::Factory('pass')
            ->where('enddate', '>=',   time())
            ->where('startdate', '<=', time())
            ->where('isPurchasable', '=', 1);
            #->where('convention_id', $this->convention_id);
    }

    public function getForAccount($account_id)
    {
        /* FIXME - Maybe limit to this convention also, so any outstanding entries will be ignored */
        return $this 
            ->with('convention')
            ->with('pass')
            ->where('account_id', '=', $account_id)
            ->where('status', 'IN', array(
                        Model_Registration::STATUS_UNPROCESSED, /* Only grab one we havn't heard back from yet */
                        Model_Registration::STATUS_NOT_ENOUGH
                )
            )
            ->find_all();
    }
    
    public function save(Validation $validation = null)
    {
        $this->convention_id = $this->pass->convention_id;
        $originalChanged = $this->changed;
        $this->changed = array_keys($this->changed);
		
		//Only set status to UNPROCESSED if it's a new registration! (Else it'll keep blanking my status updates).
		if ($this->id == 0)
			$this->status = Model_Registration::STATUS_UNPROCESSED;

		if (isset($originalChanged['status']) && $this->status == Model_Registration::STATUS_PAID)
			$this->sendConfirmationEmail();
				
        $ret = parent::save($validation);

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
	
	/*
	* getAllRegistrationsByConvention
	*
	* Returns all conventions for a particular account ordered by convention_id in newest first (DESC order) Used for displaying 
	* the history of registrations for a particular user.
	*/
	public static function getAllRegistrationsByConvention($account_id)	{
		
		return ORM::Factory('Registration')->where('account_id', '=', $account_id)->order_by('convention_id', 'DESC')->find_all();	
	}
	
	public function statusToString()
	{
		return Model_Registration::regStatusToString($this->status);
	}
	
	public function regStatusToString($status) 
    {
		if ($status == Model_Registration::STATUS_UNPROCESSED)
			return 'UNPROCESSED';
		else if ($status == Model_Registration::STATUS_PROCESSING )
			return 'PROCESSING';
		else if ($status == Model_Registration::STATUS_PAID)
			return 'PAID';
		else if ($status == Model_Registration::STATUS_NOT_ENOUGH)
			return 'PARTIAL PAYMENT';
		else if ($status == Model_Registration::STATUS_FAILED)
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
			$columns[$key] = __('convention.registration_field_' . $key);					
		endforeach;
		
		return implode(",", $columns);
	}
	
	//Function spam!
	public function getStatusValues()
	{
		$status_values = array();
		$status_values[Model_Registration::STATUS_UNPROCESSED] = 'UNPROCESSED';
		$status_values[Model_Registration::STATUS_PROCESSING] = 'PROCESSING';
		$status_values[Model_Registration::STATUS_PAID] = 'PAID';
		$status_values[Model_Registration::STATUS_NOT_ENOUGH] = 'PARTIAL PAYMENT';
		$status_values[Model_Registration::STATUS_FAILED] = 'FAILED';
		return $status_values;
	}
	
	private function sendConfirmationEmail()
	{		
		$conv = ORM::Factory('Convention',$this->convention_id)->find();
		$pass = ORM::Factory('Pass',$this->pass_id)->find();
		$acct = ORM::Factory('Account',$this->account_id)->find();
		
		if (!$conv->loaded || !$pass->loaded)
		{
			die('Unexpected error encountered! Press back and try again else contact the system administrators');
		}
		
		/* Prevent spamming the user twice. Ignore upper/lowercase? */
		if ($this->email == $acct->email)
			$email = $this->email;
		else
			$email = $this->email . ',' . $acct->email;
		
        $emailVars = array(
                'email'                    => $email,
                'reg'            		   => $this,
				'conv'					   => $conv,
				'pass'					   => $pass
			);

        $to      = $emailVars['email'];
        $from    = __('ecmproject.outgoing_email_name') . ' <' . __('ecmproject.outgoing_email_address') . '>';
        $subject = __('ecmproject.registration_subject');
 
        $view = new View('user/register_confirmation', $emailVars);
        $message = $view->render();
		
        email::send($to, $from, $subject, $message, TRUE);    
	}
    
    public function _unique_badge(Validation $array, $field)
    {
        $query = ORM::Factory('registration');
        // TODO: use config
        // TODO: switch this to be a config. name bool, and badge bool, so name and badge can be enforced unique
        $query->where('gname', $array['gname']);
        $query->where('sname', $array['sname']);
        $query->where('account_id', $array['account_id']);
        if ($this->loaded) 
            $query->where('id !=', $this->id);

        if ((bool)$query->count_all())
            $array->add_error($field, 'unique');
    }
}

/* End of file user.php */
/* Location: ./application/models/registration.php */ 
