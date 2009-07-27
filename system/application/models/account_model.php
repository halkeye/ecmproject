<?php
define ('ACCOUNT_MODEL_TABLE', 'accounts');
define ('ACCOUNT_MODEL_SALT_LENGTH', 9);
class Account_model extends Model 
{
    var $title   = '';

    function Account_model()
    {
        // Call the Model constructor
        parent::Model();
    }


    function getUserByLogin($email, $password)
    {
        $this->db->from(ACCOUNT_MODEL_TABLE);
        $this->db->where('email', $email);
        $this->db->limit(1);
        $query = $this->db->get();
        if (!$query)
            die('there was an error'); //FIXME - learn CI errors
        if (!$query->num_rows())
        {
            $query->free_result();
            return false;
        }
        $user = $query->row();
        $query->free_result();
        if ($user->password != sha1($user->salt.$password))
        {
            return false;
        }
        return $user;
    }

    function createUser($email, $password)
    {
        $salt = substr(md5(uniqid(rand(), true)), 0, ACCOUNT_MODEL_SALT_LENGTH);
    }
    
}
