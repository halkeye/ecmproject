<?php

define('ACCOUNT_STATUS_ACTIVE', 1);

class Account extends DataMapper 
{
    var $table = 'accounts';
    var $saltLength = 10;

    var $created_field = '';
    var $updated_field = '';
    #var $has_many = array('book');
/*
    var $has_one = array('country');

*/
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

    var $CI = null;

    function Account()
	{
        $this->CI =& get_instance();
        $ret = parent::DataMapper();
        $this->created = time();
        return $ret;
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
                'account' => $this,
                'email'=>$this->email,
                'validationUrl'=>sprintf('/user/validate/%d/%d/%s', $this->id, $timestamp, $this->userPassRehash($timestamp)),
        );

        $this->CI->load->library('email');

        $this->CI->email->from(
                $this->CI->config->item('convention_outgoing_email_email'),
                $this->CI->config->item('convention_outgoing_email_name') // lang?
        );
        $this->CI->email->to($this->email); 
        $this->CI->email->subject('LANG: registration email subject');
        $this->CI->email->message(
                $this->CI->load->view('user/register.email', $emailVars, TRUE)
        );
        $this->CI->email->send();
#        echo $this->CI->email->print_debugger();
    }
}

/* End of file user.php */
/* Location: ./application/models/user.php */ 
