<?php

class Registration_Model extends ORM 
{
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
    );

    public $formo_ignores = array('id', 'convention_id', 'pass_id', 'account_id', );

    public $formo_defaults = array(
            'gname' => array( 'type'  => 'text', 'label' => 'Given Name' ),
            'sname' => array( 'type'  => 'text', 'label' => 'Surname'    ),
            'dob'   => array( 'type'  => 'text', 'label' => 'Date of Birth' ),
            'cell'  => array( 'type'  => 'text', 'label' => 'Cell Phone', 'required' => false),
            'address' => array( 'type'  => 'textarea', 'rows'  => 4, 'label' => 'Address', ),
            'heard_from' => array( 'type'  => 'text', 'label' => 'Heard from', 'required'=>false ),
            'attendance_reason' => array( 'type'  => 'textarea', 'rows'  => 10, 'label' => 'Reason For Attendance', 'required'=>false),
    );

    public $formo_rules = array(
            'email'=>array(
                array('email', 'Invalid email')
            ),
            'cell'=>array(
                array('phone', 'Invalid cell number')
            ),
            'phone'=>array(
                array('phone', 'Invalid phone number')
            ),
            'ephone'=>array(
                array('phone', 'Invalid emergency contact phone')
            ),
            'gname'=>array('required', 'Given name is required'),
            'sname'=>array('required', 'Surname is required'),
    );



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

		return parent::validate($array, $save);
	}

    private function addValidationRules($form)
    {
        $form->pre_filter('trim');

        // Add Rules
        $form->add_rules('email', 'required', array('valid','email'));

        $form->add_rules('gname', 'required');
        $form->add_rules('sname', 'required');

        $form->add_rules('phone', 'required');
        $form->add_rules('phone', array('valid', 'phone'));
        
        $form->add_rules('cell', array('valid', 'phone'));

        $form->add_rules('dob', array('valid', 'date'));

        /* Neither ephone or econtact is required */
        $form->add_rules('ephone', array('valid', 'date'));

        $form->add_callbacks('badge', array($this, '_unique_badge'));
    }
    
    public function _unique_badge_form()
    {
        $orm = ORM::Factory('registration');

        $orm->where('convention_id !=', $this->convention_id);
        if ($this->loaded)
        {
            $orm->where('id !=', $this->id);
        }
        $orm->where('badge !=', $this->badge);
        return !(bool) $orm->count_all();
    }

    /*
     * Callback method that checks for uniqueness of email
     *
     * @param  Validation  $array   Validation object
     * @param  string      $field   name of field being validated
     */
    public function _unique_badge(Validation $array, $field)
    {
        $fields = array();
        $fields['badge'] = $array[$field];
        var_dump($array,$field,$this);
        if ($this->loaded)
            $fields[$this->primary_key.' !='] = $this->primary_key_value;

        // check the database for existing records
        $email_exists = (bool) ORM::factory('registration')->where($fields)->count_all();

        if ($email_exists)
        {
            // add error to validation object
            $array->add_error($field, 'badge_exists');
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

}

/* End of file user.php */
/* Location: ./application/models/registration.php */ 
