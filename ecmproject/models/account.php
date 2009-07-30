<?php

class Account extends DataMapper 
{
    var $table = 'accounts';
    var $saltLength = 10;

    var $created_field = 'created';
    var $updated_field = 'login';
    var $has_many = array('book');
/*
    var $has_one = array('country');

*/
    var $validation = array(
        array(
            'field' => 'email',
            'label' => 'Email',
            'rules' => array('required', 'trim', 'unique', 'valid_email', 'min_length' => 3, 'max_length' => 55),
        ),
        array(
            'field' => 'password',
            'label' => 'Password',
            'rules' => array('required', 'trim', 'min_length' => 5, 'encrypt'),
        ),
        array(
            'field' => 'confirm_password',
            'label' => 'Confirm Password',
            'rules' => array('encrypt', 'matches' => 'password'),
        ),
    );

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

    function login()
    {
        // Create a temporary user object
        $u = new Account();

        // Get this users stored record via their username
        $u->where('email', $this->email)->get();

        ## Not a valid user
        if (!isset($u->id))
            return false;

        // Give this user their stored salt
        $this->salt = $u->salt;

        $this->_encrypt('password');

        if ($u->password == $this->password)
        {
            // Login succeeded
            return TRUE;
        }
        return FALSE;
    }

    // Validation prepping function to encrypt passwords
    // If you look at the $validation array, you will see the password field will use this function
    function _encrypt($field)
    {
        // Don't encrypt an empty string
        if (!empty($this->{$field}))
        {
            // Generate a random salt if empty
            if (empty($this->salt))
            {
                $this->salt = substr(md5(uniqid(rand(), true)), 0, $this->saltLength);
            }

            $this->{$field} = sha1($this->salt . $this->{$field});
        }
    }
}

/* End of file user.php */
/* Location: ./application/models/user.php */ 
