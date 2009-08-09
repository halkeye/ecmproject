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

    protected $account     = null;
    protected $groups       = array();
    protected $permissions = array();

	/**
	 * Return a static instance of Auth.
	 *
	 * @return  object
	 */
	public static function instance()
	{
		static $instance;

		// Load the Auth instance
		empty($instance) and $instance = new Auth();

		return $instance;
	}

	/**
	 * Loads Session and user options.
	 */
	public function __construct()
	{
		// Load libraries
		$this->session     = Session::instance();
        /* User Stuff */
        if ($this->is_logged_in())
        {
            $this->account     = $this->session->get('account');
            $this->groups       = $this->session->get('account_groups');
            $this->permissions = $this->session->get('account_perms');
        }
        else
        {
            $this->account    =  new StdClass;
        }

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
        if (!$account->isActive())
            return FALSE;

		// Create a hashed password using the salt from the stored password
        $password = sha1($account->salt . $password);
        if ($account->password !== $password) return FALSE;
			
        $this->complete_login($account);

        return TRUE;
	}

	/**
	 * Log out a account by removing the related session variables.
	 *
	 * @return  bool
	 */
	public function logout()
	{
        $this->session->destroy();
		return TRUE;
	}

	/**
	 * Complete the login for a account by incrementing the logins and setting
	 * session data: account_id, accountname, groups
	 *
	 * @param   object   account model object
	 * @return  void
	 */
	public function complete_login(Account_Model $account)
	{
		// Update the number of logins
		$account->login = time();

		// Save the account
		$account->save();

        $this->groups = array();
        $this->permissions = array();
        foreach ($account->usergroups as $group)
        {
            $this->groups[$group->name] = 1;
            foreach ($group->permissions as $p)
            {
                $this->permissions[$p->pkey] = 1;
            }
        }

        /* FIXME: Make a constant or something out of here */
        /* Load up registered group always */
        foreach (ORM::factory('usergroup', 'registered')->permissions as $p)
        {
            $this->permissions[$p->pkey] = 1;
        }

		// Store session data
		$this->session->set(array
		(
			'account_id'    => $account->id,
			'account_name'  => $account->gname . ' ' . $account->sname,
            'account'       => (Object) $account->as_array(),
			'account_groups' => $this->groups, 
			'account_perms' => $this->permissions, 
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
        return $this->account;
    }

    public function has_perm($permission) { return isset($this->permissions[$permission]); }
    public function has_group($group) { return isset($this->groups[$permission]); }

} // End Auth
