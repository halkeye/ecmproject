<?php

define('ACCOUNT_STATUS_ACTIVE', 1);

class Account_Model extends ORM 
{
    // Account specific Stuff
    public $saltLength = 10;
    //public $groups = array();
    //public $permissions = array();

    // Current relationships
    public $has_and_belongs_to_many = array('accounts_usergroups' => 'usergroups');

    // Table primary key and value
    protected $primary_key = 'id';

    // Model table information
    protected $table_columns = array (
            'id'          => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, 'sequenced' => true, ),
            'email'       => array ( 'type' => 'string', 'length' => '55' ),
            'gname'       => array ( 'type' => 'string', 'length' => '55' ),
            'sname'       => array ( 'type' => 'string', 'length' => '55' ),
            'badge'       => array ( 'type' => 'string', 'length' => '55', 'null' => true, ),
            'dob'         => array ( 'type' => 'string', 'format' => '0000-00-00' ),
            'phone'       => array ( 'type' => 'string', 'length' => '15',  ),
            'cell'        => array ( 'type' => 'string', 'length' => '15',  ),
            'address'     => array ( 'type' => 'string', 'null' => true,  ),
            'econtact'    => array ( 'type' => 'string', 'length' => '55',  ),
            'ephone'      => array ( 'type' => 'string', 'length' => '15',  ),
            'password'    => array ( 'type' => 'string', 'length' => '40',  ),
            'salt'        => array ( 'type' => 'string',    'length' => '10',  ),
            'reg_status'  => array ( 'type' => 'int',    'max' => 127,    'unsigned' => false,  ),
            'created'     => array ( 'type' => 'int',    'max' => 2147483647,    'unsigned' => false,  ),
            'login'       => array ( 'type' => 'int', 'max' => 2147483647, 'unsigned' => false, 'null' => true, ),
    );
    protected $ignored_columns = array('confirm_password', 'groups', 'permissions');

    var $validation = array(
        array(
            'field' => 'dob',
            'label' => 'Date Of Birth',
            'rules' => array('xss_clean', 'required', 'trim', 'valid_date'),
        ),
        array(
            'field' => 'cell',
            'label' => 'Cell Phone Number',
            'rules' => array('xss_clean', 'trim', /*'valid_phone_number'*/),
        ),
        array(
            'field' => 'address',
            'label' => 'Address',
            'rules' => array('xss_clean', 'trim'),
        ),
        array(
            'field' => 'econtact',
            'label' => 'Emergency Contact',
            'rules' => array('xss_clean', 'required', 'trim', 'max_length' => 55, 'alpha_dash_dot'),
        ),
        array(
            'field' => 'ephone',
            'label' => 'Emergency Contact Phone',
            'rules' => array('xss_clean', 'trim', /*'valid_phone_number'*/),
        ),
        /*
   badge VARCHAR(55),
   reg_status TINYINT NOT NULL,
   */
    );

    function __construct()
	{
        $ret = parent::__construct();
        $this->created = time();
        $this->salt = substr(md5(uniqid(rand(), true)), 0, $this->saltLength);

        return $ret;
    }
	
    public function __set($key, $value)
	{
		if ($key === 'password')
		{
			// Use Auth to hash the password
            //
            $value = $this->_encryptValue($value);
		}

		parent::__set($key, $value);
	}

    function _encryptValue($value)
    {
        if (empty($value)) return;

        // Generate a random salt if empty
        if (empty($this->salt))
        {
            $this->salt = substr(md5(uniqid(rand(), true)), 0, $this->saltLength);
        }
        return sha1($this->salt . $value);
    }

    function isActive()
    {
        return $this->reg_status == ACCOUNT_STATUS_ACTIVE;
    }

    function userPassRehash($timestamp) { return md5($timestamp . $this->password . $this->login); }
    function sendValidateEmail()
    {
        $timestamp = time();
        $emailVars = array(
                'email'                    => $this->email,
                'validationUrl'            => sprintf('/user/validate/%d/%d/%s', $this->primary_key_value, $timestamp, $this->userPassRehash($timestamp)),
                'convention_name'          => Kohana::lang('ecmproject.convention_name'),
                'convention_name_short'    => Kohana::lang('ecmproject.convention_name_short'),
                'convention_forum_url'     => Kohana::lang('ecmproject.convention_forum_url'),
                'convention_contact_email' => Kohana::lang('ecmproject.convention_contact_email'),
                'convention_url'           => Kohana::lang('ecmproject.convention_url'),
        );

        $to      = $emailVars['email'];
        $from    = Kohana::lang('ecmproject.outgoing_email_name');
        $subject = Kohana::lang('ecmproject.registration_subject');
 
        $view = new View('user/register_email', $emailVars);
        $message = $view->render(FALSE);

        email::send($to, $from, $subject, $message, TRUE);
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
        $array->pre_filter('trim');

        // Add Rules
        $array->add_rules('email', 'required', array('valid','email'));
        $array->add_rules('password', 'required');

        $array->add_rules('confirm_password', 'required');
        $array->add_rules('confirm_password',  'matches[password]');

        $array->add_rules('gname', 'required');
        $array->add_rules('sname', 'required');

        $array->add_rules('phone', 'required');
        $array->add_rules('phone', array('valid', 'phone'));

        // Email unique validation
        $array->add_callbacks('email', array($this, '_unique_email'));
        //$array->add_rules('name', 'required', array($this, '_name_exists'));

 
		return parent::validate($array, $save);
	}
 
	public function unique_key($id = NULL) 
    {
        if (empty($id))
            return $this->primary_key;

        if (is_string($id) && !ctype_digit($id))
            return 'email';
        
        if (is_numeric($id))
            return $this->primary_key;

        return parent::unique_key($id);
    }
    
    /*
     * Callback method that checks for uniqueness of email
     *
     * @param  Validation  $array   Validation object
     * @param  string      $field   name of field being validated
     */
    public function _unique_email(Validation $array, $field)
    {
        $fields = array();
        $fields['email'] = $array[$field];
        if ($this->loaded)
            $fields[$this->primary_key.' !='] = $this->primary_key_value;

        // check the database for existing records
        $email_exists = (bool) ORM::factory('account')->where($fields)->count_all();

        if ($email_exists)
        {
            // add error to validation object
            $array->add_error($field, 'email_exists');
        }
    }
}

/* End of file user.php */
/* Location: ./application/models/user.php */ 
