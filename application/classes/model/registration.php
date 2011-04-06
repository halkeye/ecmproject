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
        ),
        'pass' => array(
            'model' => 'pass', 
            'foreign_key' => 'pass_id',
        ),
        'account' => array(
            'model' => 'account', 
            'foreign_key' => 'account_id',
        ),
    );

    protected $_has_one = array(
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
    
    public function rules()
	{
        $rules = array();
        $rules['agree_toc'] = array(array('range', array(':value',0,1)));
        $rules['email'] = array(array('email'));
        $rules['phone'] = array(array('phone'));
        $rules['ephone'] = array(array('phone'));
        $rules['cell'] = array(array('phone'));
        $rules['dob'] = array(
            array(array($this, '_valid_date')),
            array(array($this, '_valid_birthdate'))
        );
        //$rules['pass_id'] = array(array(array($this, '_valid_pass_for_account'), array(':validation', ':value')));
        //$rules['unique_badge'] = array(array(array($this, '_unique_badge'), array(':validation')));
        foreach ($this->formo_defaults as $field => $fieldData)
        {
            if (!isset($rules[$field])) $rules[$field] = array();
            if (isset($fieldData['required']) && $fieldData['required'])
            {
                array_push($rules[$field], array('not_empty'));
            }
            else
            {
                array_push($rules[$field], array('min_length', array(':value', 0)));
            }
            array_push($rules[$field], array('max_length', array(':value', 255)));
        }
        return arr::merge(parent::rules(), $rules);
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
        $filters['pass_id'] = array(
            array(array($this, '_update_convention_from_pass')),
        );
        return $filters;
    }   

    public function __toString()
    {
        return $this->pass . ' - ' . $this->badge;
    }

    public function _update_convention_from_pass($value)
    {
        $pass = ORM::Factory('Pass')->where('passes.id','=',$value)->find();
        $this->convention_id = $pass->convention_id;
        return $value;
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
        //$form->add_rules('dob', 'date');
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
	
    public function _valid_pass_for_account(Validation $array, $value)
    {
        $ageTime = strtotime($array['dob']);

        $pass = ORM::Factory('Pass')->with('convention')->where('passes.id','=',$value)->find();
        $conventionStartTime = $pass->convention->start_date;
        # Code from http://forums.webmasterhub.net/viewtopic.php?f=23&t=1831 - Option 4
        $yearsOld = abs(substr(date('Ymd', $conventionStartTime) - date('Ymd', $ageTime), 0, -4));

        $query = $this->getPossiblePassesQuery();
        $query->where('minAge','<=', $yearsOld);
        $query->where('maxAge','>=', $yearsOld);
        $query->where('id','=', $value);
        return !(bool)$query->count_all();
    }
	
	/* Takes in a date in the format: YYYY-MM-DD (ISO_8601) */
	public function _valid_date($value)
	{
		$date = strtotime($value);
		
		/* If date validation failed (not a date string) or date does not match expected... */
		if (!$date || date("Y-m-d", $date) != $value)
            return FALSE;
        return TRUE;
    }
	public function _valid_birthdate($value)
    {
		$date = strtotime($value);
        /* if someone isn't born yet, they can't have a badge (mostly because they don't have a birthday) */
        if ($date > time())
            return FALSE;
        return TRUE;
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
            //$conventionWhere = ':startTime BETWEEN c.start_date AND c.end_date';
			$conventionWhere = '1=1';
            //$vars[':startTime'] = time();
        }

        $query = DB::query(Database::SELECT, "
                SELECT 
                    r.*,
                    c.name as convention_name,
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
        $originalChanged = $this->_changed;
		
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
	
	public static function getTotalRegistrations($cid)
	{
        return ORM::Factory('Registration')->where('convention_id','=',$cid)->count_all();
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
    
    public function _unique_badge(Validation $array)
    {
        $query = ORM::Factory('registration');
        // TODO: use config
        // TODO: switch this to be a config. name bool, and badge bool, so name and badge can be enforced unique
        $query->where('gname', '=',$array['gname']);
        $query->where('sname', '=',$array['sname']);
        $query->where('account_id', '=',$array['account_id']);
        if ($this->loaded) 
            $query->where('id','!=', $this->id);

        return ((bool)$query->count_all());
    }
}

/* End of file user.php */
/* Location: ./application/models/registration.php */ 
