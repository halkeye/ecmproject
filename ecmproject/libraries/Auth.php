<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class Auth 
{
    var $CI = null;

	function Auth()
	{
        $this->CI =& get_instance();

		$this->CI->load->library('session');
		$this->CI->load->database();
		$this->CI->load->helper('url');
        #$this->CI->load->model('Account_model');
        $this->CI->lang->load('auth');
	}

    function processLogin($email, $password)
    {
        // A few safety checks
        // Our array has to be set
        if(!$email && !$password)
            return FALSE;

        $this->CI->load->model('Account');
        $u = new Account();
        if (!$u->login($email, $password)) 
        { 
            return $this->CI->lang->line('auth_error_invalid_user_pass');
        }

        if (!$u->isActive())
        {
            return $this->CI->lang->line('auth_error_not_active');
        }
        $u->login = time();
        $u->save();
        return $this->loginUser($u);

    }

    function loginUser($account)
    {
        // Our user exists, set session.
        $this->CI->session->set_userdata('logged_user', $account->email);
        $this->CI->session->set_userdata('user_name', $account->gname . ' '. $account->sname);
        return TRUE;
    }

    /**
     *
     * This function restricts users from certain pages.
     * use restrict(TRUE) if a user can't access a page when logged in
     *
     * @access  public
     * @param   boolean wether the page is viewable when logged in
     * @return  void
     */
    function restrict($logged_out = FALSE)
    {
        // If the user is logged in and he's trying to access a page
        // he's not allowed to see when logged in,
        // redirect him to the index!
        if ($logged_out && $this->logged_in())
        {
            redirect('');
        }

        // If the user isn' logged in and he's trying to access a page
        // he's not allowed to see when logged out,
        // redirect him to the login page!
        if ( ! $logged_out && ! $this->logged_in())
        {
            $this->CI->session->set_userdata(
                    'redirected_from',
                    $this->CI->uri->uri_string()
            ); // We'll use this in our redirect method.
            redirect('/user/login');
        }
    }

    /**
     *
     * Checks if a user is logged in
     *
     * @access  public
     * @return  boolean
     */
    function logged_in()
    {
        return $this->CI->session->userdata('logged_user');
    }

    function logout()
    {
        $this->CI->session->destroy();
        return TRUE;
    }

    function name()
    {
        if (!$this->logged_in()) return '';
        return $this->CI->session->userdata('user_name');
    }

}
// End of library class
// Location: system/application/libraries/Auth.php

