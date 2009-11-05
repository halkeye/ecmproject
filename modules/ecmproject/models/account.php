<?php

class Account_Model extends ORM 
{
    const ACCOUNT_STATUS_UNVERIFIED =  0;
    const ACCOUNT_STATUS_VERIFIED   =  1;
    const ACCOUNT_STATUS_BANNED     = 99;

    /* On unserialize never check the db */
    protected $reload_on_wakeup = false;

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
            'email'       => array ( 'type' => 'string', 'length' => '55'                                              ),
            'password'    => array ( 'type' => 'string', 'length' => '40'                                              ),
            'salt'        => array ( 'type' => 'string', 'length' => '10',                                             ),
            'status'      => array ( 'type' => 'int',    'max' => 127,        'unsigned' => false,                     ),
            'created'     => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => false,                     ),
            'login'       => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => false, 'null' => true,     ),
    );
	
	public $default_fields = array(
            'email' => array( 'type'  => 'text', 'label' => 'Email', 'required'=>true 								),
            'password' => array( 'type'  => 'text', 'label' => 'Password', 'required'=>true     					),
			'confirm_password' => array( 'type'  => 'text', 'label' => 'Confirm Password', 'required'=>true    		),
            'status' => array( 'type'  => 'select', 'label' => 'Status', 'required'=>false    						)
    );
	
    protected $ignored_columns = array('confirm_password', 'groups', 'permissions');

	public function __construct($id = NULL)
	{
        parent::__construct($id);
        if (!$this->loaded)
        {
            $this->created = time();
        /* Set a default status on new user creation */
            $this->status = Account_Model::ACCOUNT_STATUS_UNVERIFIED;
        }
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
	
    function isBanned()   { return $this->status == Account_model::ACCOUNT_STATUS_BANNED; }
    function isVerified() { return $this->status == Account_model::ACCOUNT_STATUS_VERIFIED; }

    function sendValidateEmail($code)
    {
        $timestamp = time();
        $emailVars = array(
                'email'                    => $this->email,
                'validationUrl'            => sprintf('/user/validate/%d/%s', $this->id, $code),
                'validationCode'           => $code,
                'convention_name'          => Kohana::lang('ecmproject.convention_name'),
                'convention_name_short'    => Kohana::lang('ecmproject.convention_name_short'),
                'convention_forum_url'     => Kohana::lang('ecmproject.convention_forum_url'),
                'convention_contact_email' => Kohana::lang('ecmproject.convention_contact_email'),
                'convention_url'           => Kohana::lang('ecmproject.convention_url'),
        );

        $to      = $emailVars['email'];
        $from    = Kohana::lang('ecmproject.outgoing_email_name') . ' <' . Kohana::lang('ecmproject.outgoing_email_address') . '>';
        $subject = Kohana::lang('ecmproject.registration_subject');
 
        $view = new View('user/register_email', $emailVars);
        $message = $view->render(FALSE);

/*        if (php_uname('n') == 'barkdog')
            file_put_contents("/var/www/emails.html", "<pre>To: $to\nFrom: $from\nSubject: Subject\n\n$message\n=======================================================================\n\n", FILE_APPEND);
        else*/
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

        /*
        $array->add_rules('gname', 'required');
        $array->add_rules('sname', 'required');

        $array->add_rules('phone', 'required');
        $array->add_rules('phone', array('valid', 'phone'));
        */

        // Email unique validation
        $array->add_callbacks('email', array($this, '_unique_email_validation'));
        //$array->add_rules('name', 'required', array($this, '_name_exists'));

 
		return parent::validate($array, $save);
	}
	
	public function validate_admin(array & $array, $save = FALSE, $passRequired = FALSE)
	{
		// Initialise the validation library and setup some rules
		$array = Validation::factory($array);
        // uses PHP trim() to remove whitespace from beginning and end of all fields before validation
        $array->pre_filter('trim');
		
		$array->add_rules('email', 'required', array('valid','email')); //Email is always required. 
		
		/* If password is filled in, set additional rules. */
		if (isset($array->password) && isset($array->confirm_password) 
				&& (!empty($array->password) || !empty($array->password) || $passRequired))
		{
			$array->add_rules('password', 'required');			
			$array->add_rules('confirm_password', 'required');
			$array->add_rules('confirm_password',  'matches[password]');
		}
		
		$array->add_rules('status', 'required');
		
		/* Password is not required, but if it is...the new passwords should match. */		
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
    public function _unique_email_validation(Validation $array, $field)
    {
        if (!$this->_unique_email($array[$field]))
        {
            // add error to validation object
            $array->add_error($field, 'email_exists');
        }
    }

    public function _unique_email_formo($email)
    {
        return $this->_unique_email($email);
    }

    private function _unique_email($email)
    {
        $fields = array();
        $fields['email'] = $email;
        if ($this->loaded)
            $fields[$this->primary_key.' !='] = $this->primary_key_value;

        // check the database for existing records
        $email_exists = (bool) ORM::factory('account')->where($fields)->count_all();
        return !$email_exists;

    }


    public function generateVerifyCode()
    {
        $vcode = ORM::Factory('verificationcode')->where('account_id', $this->id)->delete_all();
        while (true)
        {
            try 
            {
                $code = substr(md5(uniqid(rand(), true)), 0, 10);
                $vcode = ORM::Factory('verificationcode');
                $vcode->account_id = $this->id;
                $vcode->code = sha1($this->salt. $code);
                $vcode->save();
                return $code;
            }
            catch (Kohana_Database_Exception $e) {}
        }
    }
	
	public function statusToString() {
		if ($this->status == Account_Model::ACCOUNT_STATUS_UNVERIFIED)
			return 'UNVERIFIED';
		else if ($this->status == Account_Model::ACCOUNT_STATUS_VERIFIED)
			return 'VERIFIED';
		else if ($this->status == Account_Model::ACCOUNT_STATUS_BANNED)
			return 'BANNED';
		else
			return 'UNKNOWN STATUS';
	}
	
	public function stringToStatus($status) {
		if (strcmp($status, 'UNVERIFIED') == 0)
			return Account_Model::ACCOUNT_STATUS_UNVERIFIED;
		else if (strcmp($status, 'VERIFIED') == 0)
			return Account_Model::ACCOUNT_STATUS_VERIFIED;
		else
			return Account_Model::ACCOUNT_STATUS_BANNED;	
	
	}	
	
	public function getVerifySelectList() {
		return array('0' => 'Unverified', '1' => 'Confirmed', '99' => 'Banned');
	}
	
	public function getTotalAccounts()
	{
		$db = new Database();
		$result = $db->query('SELECT COUNT(*) as count FROM accounts');
		
		return (int) $result[0]->count;
	}
}

/* End of file user.php */
/* Location: ./application/models/user.php */ 
