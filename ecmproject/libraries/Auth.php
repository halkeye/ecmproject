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
	}

    function process_login($username, $password)
    {
        // A few safety checks
        // Our array has to be set
        if(!$username && !$password)
            return FALSE;

        $this->CI->load->model('Account');
        $u = new Account();
        $u->email    = $username;
        $u->password = $password;
        if (!$u->login()) { return false; }

        // Our user exists, set session.
        $this->CI->session->set_userdata('logged_user', $u->email);
        $this->CI->session->set_userdata('user_name', $u->gname . ' '. $u->sname);
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

