<?php
define ('ACCOUNT_MODEL_TABLE', 'accounts');
define ('ACCOUNT_MODEL_SALT_LENGTH', 9);
class Account_model extends Model 
{
    function findByLogin($email, $password)
    {
        $user = $this->findByEmail($email);
        if ($user->password != sha1($user->salt.$password))
        {
            return false;
        }
        return $user;
    }

    function register($email, $password)
    {
        $salt = substr(md5(uniqid(rand(), true)), 0, ACCOUNT_MODEL_SALT_LENGTH);
        $password = sha1($salt.$password);

        $data = array(
                'email' => $email,
                'password'  => $password,
                'salt' => $salt,
        );

        $query = $this->db->insert(ACCOUNT_MODEL_TABLE, $data); 
        return $this->findByEmail($email);
    }

    function findByEmail($email)
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
        return $user;
    }

    /* Instance Functions */
    
}
