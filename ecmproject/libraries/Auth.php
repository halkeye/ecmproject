<?php defined('SYSPATH') or die('No direct script access.');
/**
 * User authorization library. Handles account login and logout, as well as secure
 * password hashing.
 *
 * @package    User Management
 * @depends    ORM
 * @author     Kohana Team
 * @copyright  (c) 2007 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Auth_Core {

	// Session instance
	protected $session;

	// Configuration
	protected $config;

	/**
	 * Create an instance of Auth.
	 *
	 * @return  object
	 */
	public static function factory($config = array())
	{
		return new Auth($config);
	}

	/**
	 * Return a static instance of Auth.
	 *
	 * @return  object
	 */
	public static function instance($config = array())
	{
		static $instance;

		// Load the Auth instance
		empty($instance) and $instance = new Auth($config);

		return $instance;
	}

	/**
	 * Loads Session and configuration options.
	 */
	public function __construct($config = array())
	{
		// Load libraries
		$this->session = Session::instance();

		Kohana::log('debug', 'Auth Library loaded');
	}

	/**
	 * Attempt to log in a account by using an ORM object and plain-text password.
	 *
	 * @param   object  account model object
	 * @param   string  plain-text password to check against
	 * @param   bool    to allow auto-login, or "remember me" feature
	 * @return  bool
	 */
	public function login(Account_Model $account, $password, $remember = FALSE)
	{
		if (empty($password))
			return FALSE;

		// Create a hashed password using the salt from the stored password
        $password = sha1($account->salt . $password);
        if ($account->password !== $password) return FALSE;
			
        $this->complete_login($account);

        return TRUE;

		// If the account has the "login" role and the passwords match, perform a login
		if ($account->has_role('login') AND $account->password === $password)
		{
			// Finish the login
			$this->complete_login($account);

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Attempt to automatically log a account in by using tokens.
	 *
	 * @return  bool
	 */
	public function auto_login()
	{
		if ($token = cookie::get('autologin'))
		{
			// Load the token and account
			$token = new Account_Token_Model($token);
			$account = new Account_Model($token->account_id);

			if ($token->id != 0 AND $account->id != 0)
			{
				if ($token->account_agent === sha1(Kohana::$account_agent))
				{
					// Save the token to create a new unique token
					$token->save();

					// Set the new token
					cookie::set('autologin', $token->token, $token->expires - time());

					// Complete the login with the found data
					$this->complete_login($account);

					// Automatic login was successful
					return TRUE;
				}

				// Token is invalid
				$token->delete();
			}
		}

		return FALSE;
	}

	/**
	 * Log out a account by removing the related session variables.
	 *
	 * @param   bool   completely destroy the session
	 * @return  bool
	 */
	public function logout($destroy = FALSE)
	{
		// Delete the autologin cookie if it exists
		cookie::get('autologin') and cookie::delete('autologin');

		if ($destroy == TRUE)
		{
			$this->session->destroy();
		}
		else
		{
			$this->session->delete(
                    'account_id', 
                    'account_name', 
                    'account',
                    'roles'
            );
		}

		return TRUE;
	}

	/**
	 * Complete the login for a account by incrementing the logins and setting
	 * session data: account_id, accountname, roles
	 *
	 * @param   object   account model object
	 * @return  void
	 */
	protected function complete_login(Account_Model $account)
	{
		// Update the number of logins
		$account->login = time();

		// Save the account
		$account->save();

		// Store session data
		$this->session->set(array
		(
			'account_id'   => $account->id,
			'account_name' => $account->gname . ' ' . $account->sname,
            'account'      => (Object) $account->as_array(),
			#'roles'        => $account->roles
		));
	}

	/**
     * Returns weither or not the account is logged in
     *
	 * @return  boolean
	 */

    public function is_logged_in()
    {
        if (Session::instance()->get('account_id'))
            return TRUE;
        return FALSE;
    }

    public function get_user()
    {
        if (!$this->is_logged_in())
            return new StdClass;
        return $this->session->get('account');
    }

} // End Auth
