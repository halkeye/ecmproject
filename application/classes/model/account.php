<?php
//Not sure what this is?
define('MAX_VERIFICATION_ITEMS', 2);
class Verification_Exceeds_Exception extends Exception {}

// FIXME - on insert
// ORM::factory('usergroup')->where('name', '=', 'Registered')->find()
class Model_Account extends ORM 
{
    const ACCOUNT_STATUS_UNVERIFIED =  0;
    const ACCOUNT_STATUS_VERIFIED   =  1;
    const ACCOUNT_STATUS_BANNED     = 99;

    /* On unserialize never check the db */
    protected $reload_on_wakeup = false;

    // Account specific Stuff
    public $saltLength = 10;

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
    // var_export($model->list_columns());
	
	public $default_fields = array(
            'email' => array( 'type'  => 'text', 'label' => 'Email', 'required'=>true 								),
            'password' => array( 'type'  => 'text', 'label' => 'Password', 'required'=>true     					),
			'confirm_password' => array( 'type'  => 'text', 'label' => 'Confirm Password', 'required'=>true    		),
            'status' => array( 'type'  => 'select', 'label' => 'Status', 'required'=>false    						)
    );
	
    protected $ignored_columns = array('confirm_password', 'groups', 'permissions');
	
    public function filters()
    {
        $filters = parent::filters();
        $filters[TRUE] = array(
            array('trim')
        );
        $filters['password'] = array(
            array(array($this, '_set_password'))
        );
        return $filters;
    }

    public function rules()
    {
        $rules = parent::rules();

        $rules['email'] = array(
            array('not_empty'),
            array('email'),
        );
		$rules['password'] = array(
			array('not_empty'),
			array('min_length', array(':value',6)),
			array('max_length', array(':value',255)),		
		);		
        // Email unique validation
        $rules['email'] = array(
            array(array($this, '_valid_unique_email'))
        );
		$rules['status'] = array(
			array(array($this, '_valid_status_code'))
		);
        return $rules;
    }

	public function __construct($id = NULL)
	{
        parent::__construct($id);
        if (!$this->loaded())
        {
            $this->created = time();
            /* Set a default status on new user creation */
            $this->status = Model_Account::ACCOUNT_STATUS_UNVERIFIED;
        }
    }
	
    /*
    public function __set($key, $value)
	{
		if ($key === 'password')
		{
            if (empty($this->salt))
            {
                $this->salt = substr(md5(uniqid(rand(), true)), 0, $this->saltLength);
                Kohana::$log->add(Log::DEBUG, 'Generated Salt: ' . $this->salt);
                die();
            }
			// Use Auth to hash the password
            $value = $this->_encryptValue($value);
		}

		parent::__set($key, $value);
	}
     */
    public function _set_password($value)
    {
        if (empty($this->salt))
        {
            $this->salt = substr(md5(uniqid(rand(), true)), 0, $this->saltLength);
            Kohana::$log->add(Log::DEBUG, 'Generated Salt: ' . $this->salt);
        }
        // Use Auth to hash the password
        return $this->_encryptValue($value);
    }

    function _encryptValue($value)
    {
        if (empty($value)) return;
        return sha1($this->salt . $value);
    }

    function isBanned()   { return $this->status == Model_Account::ACCOUNT_STATUS_BANNED; }
    function isVerified() { return $this->status == Model_Account::ACCOUNT_STATUS_VERIFIED; }

    function sendValidateEmail($code, $type = 'registration')
    {
        $config = Kohana::config('ecmproject');
        $timestamp = time();
        $emailVars = array(
                'email'                    => $this->email,
                'validationUrl'            => URL::site(sprintf('/user/validate/%d/%s', $this->id, $code),Request::current()),
                'validationCode'           => $code,
                'convention_name'          => $config['convention_name'],
                'convention_name_short'    => $config['convention_name_short'],
                'convention_forum_url'     => $config['convention_forum_url'],
                'convention_contact_email' => $config['convention_contact_email'],
                'convention_url'           => $config['convention_url'],
        );

        $view = new View('user/'.$type.'_email', $emailVars);
        $message = $view->render();

        ### FIXME - MAKE SURE TO ADD non html version too
        $email = Email::factory($config[$type.'_subject']);
        $email->message($message,'text/html');
        $email->to($emailVars['email']);
        $email->from($config['outgoing_email_address'], $config['outgoing_email_name']);
        $email->send();
    }

	public function validate_admin(array & $array, $save = FALSE, $passRequired = FALSE)
	{
        $data = (array) $array;
		// Initialise the validation library and setup some rules
		$array = Validation::factory($array);		
		
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
    
    public function _valid_unique_email($email)
    {
        $query = ORM::factory('Account');
        $query->where('email','=',$email);
        $fields['email'] = $email;
        if ($this->loaded())
            $query->where($this->_primary_key, '!=', $this->pk());

        // check the database for existing records
        $ret = $query->count_all();
        return !$ret;
    }
	public function _valid_status_code($code)
	{
		if ($code == Model_Account::ACCOUNT_STATUS_UNVERIFIED 	|| 
			$code == Model_Account::ACCOUNT_STATUS_VERIFIED 	|| 
			$code == Model_Account::ACCOUNT_STATUS_BANNED) 
		{		
			return true;
		}
	
		return false;
	}

    public function generateVerifyCode($type, $value = NULL)
    {
        $countType = ORM::Factory('verificationcode')
            ->where('account_id', '=', $this->id)
            ->where('type', '=', $type)
            ->count_all();

        if ($countType >= MAX_VERIFICATION_ITEMS)
        {
            throw new Verification_Exceeds_Exception();
        }
        return ORM::Factory('verificationcode')->generate_code($this->id,$this->salt,$type,$value);

    }

    public function validateAccount()
    {
        $this->status = Model_Account::ACCOUNT_STATUS_VERIFIED;
        /* Delete any outstanding validation codes */
        ORM::Factory('verificationcode')->delete_all_for_account($this->id);
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
	
	public static function getVerifySelectList() {
		return array('0' => 'Unverified', '1' => 'Confirmed', '99' => 'Banned');
	}
	
	public static function getTotalAccounts()
	{	
		$query = DB::query(Database::SELECT, 'SELECT COUNT(*) as count FROM accounts');
        $row = $query->execute();
        return (int) $row[0]['count'];
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
}

/* End of file user.php */
/* Location: ./application/models/user.php */ 
