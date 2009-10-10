<?php

class Registration_Model extends ORM 
{
    const STATUS_UNPROCESSED = 0;
    const STATUS_PROCESSING  = 1; // Waiting for Paypal to respond
    const STATUS_PAID        = 99; // Fully working and paid

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
            'address'       => array ( 'type' => 'string',                   'null' => true                              ),
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
            'address' => array( 'type'  => 'textarea', 'rows'  => 4, 'label' => 'Address', 'required' => true ),
            'econtact'  => array( 'type'  => 'text', 'label' => 'Emergency Contact Name', 'required' => true),
            'ephone'  => array( 'type'  => 'text', 'label' => 'Emergency Contact Phone', 'required' => true),
            'heard_from' => array( 'type'  => 'text', 'label' => 'Heard from', 'required'=>false ),
            'attendance_reason' => array( 'type'  => 'textarea', 'rows'  => 10, 'label' => 'Reason For Attendance', 'required'=>false),
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
        $form->add_rules('email', 'required', array('valid','email'));
        $form->add_rules('phone', array('valid', 'phone'));
        $form->add_rules('cell', array('valid', 'phone'));
        $form->add_rules('ephone', array('valid', 'phone'));
        $form->add_rules('dob', array('valid', 'date'));
        $form->add_callbacks('pass_id', array($this, '_valid_pass_for_account'));
    }

    public function _valid_pass_for_account(Validation $array, $field)
    {
        $ageTime = strftime($array['dob']);
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
            ->where('enddate >',   time())
            ->where('startdate <', time())
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
		$ret = parent::save();

		if ( ! empty($originalChanged))
		{
			foreach ($originalChanged as $column=>$oldValue)
			{
				// Compile changed data
                $log = ORM::Factory('log');
                $log->modifier_id = $this->account_id;
                $log->target_account_id = $this->account_id;
                $log->target_registration_id = $this->id;
                $log->target_badge_id = $this->pass_id;
                $log->method = url::current(TRUE);
                $log->description = sprintf("%s => %s => %s", $column, ($column == $oldValue ? '--unknown--' : $oldValue), $this->$column);
                $log->mod_time = time();
                $log->ip = input::instance()->ip_address();
                $log->save();
			}
        }
    }

}

/* End of file user.php */
/* Location: ./application/models/registration.php */ 
