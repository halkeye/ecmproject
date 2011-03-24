<?php


define('MAX_VERIFICATION_ITEMS', 2);
class Verification_Exceeds_Exception extends Exception {}

class Model_Account extends ORM 
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
    public $_has_many = array(
        'Usergroups' => array ( 
            'model' => 'usergroup',
            'through' => 'accounts_usergroups',
        )
    );

    // Table primary key and value
    protected $_primary_key = 'id';

    // Model table information
    protected $_table_columns = array (
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
        if (!$this->_loaded)
        {
            $this->created = time();
        /* Set a default status on new user creation */
            $this->status = Model_Account::ACCOUNT_STATUS_UNVERIFIED;
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
	
    function isBanned()   { return $this->status == Model_Account::ACCOUNT_STATUS_BANNED; }
    function isVerified() { return $this->status == Model_Account::ACCOUNT_STATUS_VERIFIED; }

    function sendValidateEmail($code, $type = 'registration')
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
        $subject = Kohana::lang('ecmproject.'.$type.'_subject');
 
        $view = new View('user/'.$type.'_email', $emailVars);
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
		$array->add_rules('password', 'length[6,255]');

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
        $data = (array) $array;
		// Initialise the validation library and setup some rules
		$array = Validation::factory($array);
        // uses PHP trim() to remove whitespace from beginning and end of all fields before validation
        $array->pre_filter('trim');
		
		$array->add_rules('email', 'required', array('valid','email')); //Email is always required. 
		
		/* If password is filled in, set additional rules. */
		if (isset($data['password']) && isset($data['confirm_password']) 
				&& (!empty($data['password']) || !empty($data['password']) ||
                    $passRequired))
		{
            $array->add_rules('password', 'length[0-255]');
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
        if ($this->_loaded)
            $fields[$this->primary_key.' !='] = $this->primary_key_value;

        // check the database for existing records
        $email_exists = (bool) ORM::factory('account')->where($fields)->count_all();
        return !$email_exists;

    }


    public function generateVerifyCode($type, $value = NULL)
    {
        $countType = ORM::Factory('verificationcode')
            ->where('account_id', $this->id)
            ->where('type', $type)
            ->count_all();

        if ($countType >= MAX_VERIFICATION_ITEMS)
        {
            throw new Verification_Exceeds_Exception();
        }

        while (true)
        {
            try 
            {
                $code = substr(md5(uniqid(rand(), true)), 0, 10);
                $vcode = ORM::Factory('verificationcode');
                $vcode->original_code = $code;
                $vcode->account_id = $this->id;
                $vcode->code = sha1($this->salt. $code);
                $vcode->type = $type;
                $vcode->value = $value;
                $vcode->save();
                return $vcode;
            }
            catch (Kohana_Database_Exception $e) {
                var_dump($e);
                die($e);
            }
        }
    }

    public function validateAccount()
    {
        $this->status = Model_Account::ACCOUNT_STATUS_VERIFIED;
        /* Delete any outstanding validation codes */
        $vcode = ORM::Factory('verificationcode')->where('account_id', $this->id)->delete_all();
    }
	
	public function statusToString() {
		if ($this->status == Model_Account::ACCOUNT_STATUS_UNVERIFIED)
			return 'UNVERIFIED';
		else if ($this->status == Model_Account::ACCOUNT_STATUS_VERIFIED)
			return 'VERIFIED';
		else if ($this->status == Model_Account::ACCOUNT_STATUS_BANNED)
			return 'BANNED';
		else
			return 'UNKNOWN STATUS';
	}
	
	public function stringToStatus($status) {
		if (strcmp($status, 'UNVERIFIED') == 0)
			return Model_Account::ACCOUNT_STATUS_UNVERIFIED;
		else if (strcmp($status, 'VERIFIED') == 0)
			return Model_Account::ACCOUNT_STATUS_VERIFIED;
		else
			return Model_Account::ACCOUNT_STATUS_BANNED;	
	
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
	
	/**
	* Given an email address, create an account if it does not already exist with the email as the password.
	* Return the id of the account (new or existing).
	*/
	public function createAccount($email)
	{
		$account = ORM::Factory('Account');
		$account->email = $email; //Race condition aside, we know it won't be in the DB.	
		$account->password = $account->_encryptValue($email);
		
		try {
		
			$account->save();
			if ($account->saved)			
				return $account->id;
			
		} catch (Exception $exception)
		{
			//Do nothing. Assume that it already exists (the account).
		}
			
		/* Email column has restraint UNIQUE. We will either get 0 ... 1 entries. */
		$results = ORM::Factory('Account')->where('email',$email)->find_all();
		if (count($results) > 0)
		{
			return $results[0]->id;
		}
		else
		{
			return -1;
		}		
	}

    public function isLoaded() { return $this->_loaded; }
}

/* End of file user.php */
/* Location: ./application/models/user.php */ 
