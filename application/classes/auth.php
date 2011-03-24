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
class Auth {

    // Session instance
    protected $session;

    protected $account     = null;
    protected $groups      = array();
    protected $permissions = array();

    protected $errors      = array();

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
        $this->account    =  new StdClass;
        if ($this->isLoggedIn() && $this->session->get('account'))
        {
            $this->account     = is_string($this->session->get('account')) ? 
                unserialize($this->session->get('account')) : 
                $this->session->get('account');
            $this->groups      = $this->session->get('account_groups');
            $this->permissions = $this->session->get('account_perms');
        }

        $this->clearErrors();

        Kohana::$log->add(Log::DEBUG, 'Auth Library loaded');
    }

    /**
     * Attempt to log in a account by using an ORM object and plain-text password.
     *
     * @param   object  account model object
     * @param   string  plain-text password to check against
     * @return  bool
     */
    public function login($account, $password)
    {
        if (!$account || !$account->isLoaded())
        {
            $this->addError(Kohana::lang('auth.invalid_user_pass'));
            return FALSE;
        }

        if (empty($password))
        {
            $this->addError(Kohana::lang('auth.invalid_user_pass'));
            return FALSE;
        }

        if ($account->isBanned())
        {
            $this->addError(Kohana::lang('auth.banned'));
            return FALSE;
        }

        // Create a hashed password using the salt from the stored password
        $password = sha1($account->salt . $password);
        if ($account->password !== $password)
        {
            $this->addError(Kohana::lang('auth.invalid_user_pass'));
            return FALSE;
        }
            
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
    public function complete_login(Model_Account $account)
    {
        // Update the number of logins
        $account->login = time();

        // Save the account
        $account->save();

        $this->groups = array();
        $this->permissions = array();
        if ($account->has('usergroups', ORM::factory('Usergroup'), TRUE))
        {
            foreach ($account->usergroups as $group)
            {
                if ($group->has(ORM::factory('Permission'), TRUE))
                {
                    $this->groups[$group->name] = 1;
                    foreach ($group->permissions as $p)
                        $this->permissions[$p->pkey] = 1;
                }
            }
        }

        /* FIXME: Make a constant or something out of here */
        /* Load up registered group always */
        $regGroup = ORM::factory('usergroup')->find('Registered');
        foreach ($regGroup->Permissions->find_all() as $p)
        {
            ####### GAVIN----
            $this->permissions[$p->pkey()] = 1;
        }

        // extra safety to prevent session fixation - http://en.wikipedia.org/wiki/Session_fixation
        $this->session->regenerate();

        $this->account = serialize($account);
        $this->storeAccount($account);

    }

    function storeAccount($account)
    {
        // Store session data
        $this->session->set('account_id'    , $account->id);
        $this->session->set('account'       , $this->account);
        $this->session->set('account_groups', $this->groups); 
        $this->session->set('account_perms' , $this->permissions); 
    }

    /**
     * Returns weither or not the account is logged in
     *
     * @return  boolean
     */

    public function isLoggedIn() { return $this->is_logged_in(); }
    public function is_logged_in()
    {
        if (Session::instance()->get('account_id'))
            return TRUE;
        return FALSE;
    }

    public function getAccount() { return $this->account; }
    public function get_user() { return $this->getAccount(); }

    public function hasPermission($permission) 
    {
        if ($permission != 'superAdmin' && $this->hasPermission('superAdmin'))
            return true;
        return isset($this->permissions[$permission]); 
    }
    public function hasUserGroup($group)
    {
        if ($group != 'SuperAdmin' && $this->hasPermission('superAdmin'))
            return true;
        return isset($this->groups[$permission]); 
    }

    /* Error functions */
    public function clearErrors() { $this->errors = array(); }
    public function addError($err) { $this->errors[] = $err; }
    public function errors() { return $this->errors; }


} // End Auth
