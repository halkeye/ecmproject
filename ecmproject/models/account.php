<?php

define('ACCOUNT_STATUS_ACTIVE', 1);

class Account_Model extends ORM 
{
    var $saltLength = 10;
    var $has_many = array('usergroup');
    protected $ignored_columns = array('confirm_password');

    var $table_columns = array (
            'id'    => array ( 'type' => 'int',    'max' => 2147483647, 'unsigned' => true, 'sequenced' => true, ),
            'email' => array ( 'type' => 'string', 'length' => '55' ),
            'gname' => array ( 'type' => 'string', 'length' => '55' ),
            'sname' => array ( 'type' => 'string', 'length' => '55' ),
            'badge' => array ( 'type' => 'string', 'length' => '55', 'null' => true, ),
            'dob'   => array ( 'type' => 'string', 'format' => '0000-00-00' ),
            'phone' => array (    'type' => 'string',    'length' => '15',  ),
            'cell' =>   array (    'type' => 'string',    'length' => '15',  ),
            'address' =>   array (    'type' => 'string',    'null' => true,  ),
            'econtact' =>   array (    'type' => 'string',    'length' => '55',  ),
            'ephone' =>   array (    'type' => 'string',    'length' => '15',  ),
            'password' =>   array (    'type' => 'string',    'length' => '40',  ),
            'salt' =>   array (    'type' => 'string',    'length' => '10',  ),
            'reg_status' =>   array (    'type' => 'int',    'max' => 127,    'unsigned' => false,  ),
            'created' =>   array (    'type' => 'int',    'max' => 2147483647,    'unsigned' => false,  ),
            'login' => array ( 'type' => 'int', 'max' => 2147483647, 'unsigned' => false, 'null' => true, ),
    );

    var $validation = array(
        array(
            'field' => 'email',
            'label' => 'Email',
            'rules' => array('xss_clean', 'required', 'trim', 'unique', 'valid_email', 'min_length' => 3, 'max_length' => 55),
        ),
        array(
            'field' => 'password',
            'label' => 'Password',
            'rules' => array('xss_clean', 'required', 'trim', 'min_length' => 5, 'encrypt'),
        ),
        array(
            'field' => 'confirm_password',
            'label' => 'Confirm Password',
            'rules' => array('xss_clean', 'required', 'encrypt', 'matches' => 'password'),
        ),
        array(
            'field' => 'gname',
            'label' => 'Given Name',
            'rules' => array('xss_clean', 'required', 'trim', 'max_length' => 55, 'alpha_dash_dot'),
        ),
        array(
            'field' => 'sname',
            'label' => 'Surname',
            'rules' => array('xss_clean', 'required', 'trim', 'max_length' => 55, 'alpha_dash_dot'),
        ),
        array(
            'field' => 'dob',
            'label' => 'Date Of Birth',
            'rules' => array('xss_clean', 'required', 'trim', 'valid_date'),
        ),
        array(
            'field' => 'phone',
            'label' => 'Phone Number',
            'rules' => array('xss_clean', 'required', 'trim', /*'valid_phone_number'*/),
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

    function Account()
	{
        $ret = parent::ORM();
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


    /*
    var $field_data = array(); 

    function Account()
    {
        $this->field_data[] = (object)array( 'name' => 'id', 'type' => 'int', 'default' => '', 'max_length' => 1, 'primary_key' => 1, );
        $this->field_data[] = (object)array( 'name' => 'email', 'type' => 'string', 'default' => '', 'max_length' => 17, 'primary_key' => 0, );
        $this->field_data[] = (object)array( 'name' => 'gname', 'type' => 'string', 'default' => '', 'max_length' => 0, 'primary_key' => 0, );
        $this->field_data[] = (object)array( 'name' => 'sname', 'type' => 'string', 'default' => '', 'max_length' => 0, 'primary_key' => 0, );
        $this->field_data[] = (object)array( 'name' => 'badge', 'type' => 'string', 'default' => '', 'max_length' => 0, 'primary_key' => 0, );
        $this->field_data[] = (object)array( 'name' => 'dob', 'type' => 'date', 'default' => '', 'max_length' => 10, 'primary_key' => 0, );
        $this->field_data[] = (object)array( 'name' => 'phone', 'type' => 'string', 'default' => '', 'max_length' => 0, 'primary_key' => 0, );
        $this->field_data[] = (object)array( 'name' => 'cell', 'type' => 'string', 'default' => '', 'max_length' => 0, 'primary_key' => 0, );
        $this->field_data[] = (object)array( 'name' => 'address', 'type' => 'blob', 'default' => '', 'max_length' => 0, 'primary_key' => 0, );
        $this->field_data[] = (object)array( 'name' => 'econtact', 'type' => 'string', 'default' => '', 'max_length' => 0, 'primary_key' => 0, );
        $this->field_data[] = (object)array( 'name' => 'ephone', 'type' => 'string', 'default' => '', 'max_length' => 0, 'primary_key' => 0, );
        $this->field_data[] = (object)array( 'name' => 'password', 'type' => 'string', 'default' => '', 'max_length' => 40, 'primary_key' => 0, );
        $this->field_data[] = (object)array( 'name' => 'salt', 'type' => 'string', 'default' => '', 'max_length' => 10, 'primary_key' => 0, );
        $this->field_data[] = (object)array( 'name' => 'reg_status', 'type' => 'int', 'default' => '', 'max_length' => 1, 'primary_key' => 0, );
        $this->field_data[] = (object)array( 'name' => 'created', 'type' => 'datetime', 'default' => '', 'max_length' => 19, 'primary_key' => 0, );
        $this->field_data[] = (object)array( 'name' => 'login', 'type' => 'datetime', 'default' => '', 'max_length' => 19, 'primary_key' => 0, );
        parent::DataMapper();
    }
    */

    function login($email, $password)
    {
        // Get this users stored record via their username
        $this->where('email', $email)->get();

        ## Not a valid user
        if (!isset($this->id))
            return false;

        if ($this->password == $this->_encryptValue($password))
        {
            // Login succeeded
            return TRUE;
        }
        return FALSE;
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


    // Validation prepping function to encrypt passwords
    // If you look at the $validation array, you will see the password field will use this function
    function _encrypt($field)
    {
        // Don't encrypt an empty string
        if (!empty($this->{$field}))
        {
            $this->{$field} = $this->_encryptValue($this->{$field});

        }
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
                'validationUrl'            => sprintf('/user/validate/%d/%d/%s', $this->id, $timestamp, $this->userPassRehash($timestamp)),
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
 
    
	
    /**
	 * Allows a model to be loaded by username or email address.
	 */
	public function where_key($id = NULL)  { return 'id'; }
	public function unique_key($id = NULL) { return 'email'; }
    
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
            $fields['id !='] = $this->id;

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
