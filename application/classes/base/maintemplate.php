<?php defined('SYSPATH') or die('No direct script access.');


class Base_MainTemplate extends Controller_Template
{
    protected $auth = null;
	// Session instance
	protected $session;
    
    public $template = 'mainTemplate';

    function before()
    {
        $ret = parent::before();
        /* Session Instance */
        $this->session = Session::instance();

        /* Auth to handle */
        $this->auth = Auth::instance();

        /* Default Content */
        $this->template->content = "";
        /* Default Title */
        $this->template->title = "";
        /* Menu options */
        $this->template->menu    = array();

        /* Messages to show */
        $this->template->messages = array();

        /* Errors to show */
        $this->template->errors = array();

        $this->template->profiler = '';

        $this->template->set_global('currentUrl', $this->request->current());
        
        if ($this->auth->is_logged_in())
        {
            $this->isVerifiedAccount = TRUE;
            $user = $this->auth->get_user();
            if ($user->status != Model_Account::ACCOUNT_STATUS_VERIFIED)
            {
                /* check the database not the cached version for determining if they are cached or not
                 * Maybe we can get away with not doing this 
                 */
                $user = ORM::factory('Account', array('id'=>$user->id));
                if ($user->status != Model_Account::ACCOUNT_STATUS_VERIFIED)
                {
                    $this->isVerifiedAccount = FALSE;
                    $this->template->errors[] = __('The email address associated with this account has <strong>NOT</strong> been validated yet.');
                    $this->addMenuItem(array('title'=>'Verify Account', 'url'=>'/user/verifyMenu'));
                }
                else
                {
                    /* Store the updated account if we've been verified */
                    $this->auth->storeAccount($user);
                }
            }
        }
        return $ret;
    }

    public function after()
    {

        if (!isset($this->template->heading))
            $this->template->heading = ucfirst($this->request->controller());
        if (!isset($this->template->subheading))
            $this->template->subheading = ucfirst($this->request->action());

        $session_messages = $this->session->get_once('messages');
        if ($session_messages) 
            $this->template->messages = array_merge($session_messages, $this->template->messages);
        $session_errors = $this->session->get_once('errors');
        if ($session_errors) 
            $this->template->errors = array_merge($session_errors, $this->template->errors);

        /* Are you logged in? */
        $this->template->set_global('isLoggedIn', $this->auth->is_logged_in());
        $this->template->set_global('isVerifiedAccount', isset($this->isVerifiedAccount) ? $this->isVerifiedAccount : FALSE);
        /* store the user */
        $this->template->set_global('account', $this->auth->getAccount());
        
        if ($this->template->isLoggedIn)
        {
			
			$this->addMenuItem(array('url'=>'convention/checkout', 'title'=>'Purchase Tickets'));
			$this->addMenuItem(array('title'=>'My Account', 'url'=>'user'));
			
			if ($this->auth->hasPermission('admin'))
			{
				$this->addMenuItem(array('title'=>'Administration', 'url'=>'admin'));
			}
			
            $this->addMenuItem(array('url'=>'user/logout', 'title'=>'Logout'));
        }
		
        return parent::after();
    }

    protected function addMessage($message)
    {
        $messages = $this->session->get('messages') or array();
        $messages[] = $message;
        $this->session->set('messages',  $messages);
    }
    
    protected function addError($error)
    {
        $errors = $this->session->get('errors') or array();
        $errors[] = $error;
        $this->session->set('errors',  $errors);
    }

    /**
     * Redirect a user to a location, unless a session variable is set
     * @param string $where Where to redirect the user if nothing else is set
     */
    function _redirect($where = '')
    {
        $location = $this->session->get_once('redirected_from');
        if (!$location) $location = $where;
        $this->request->redirect($location);
        return;
    }

    protected function requireLogin()
    {
        if (!$this->auth->is_logged_in()) 
        {
            $this->session->set('redirected_from', $this->request->current());
            $this->request->redirect('/user/loginOrRegister');
            return;
        }
    }

    protected function requireVerified()
    {
        if ($this->auth->is_logged_in() && !$this->isVerifiedAccount ) 
        {
            /* You can't go any furthur until email address is verified. */
            $this->addError(__('auth.not_validated')); 
            $this->session->set('redirected_from',  $this->request->current());
            $this->request->redirect('/user/verifyMenu');
            return;
        }
    }

    protected function requirePermission($permission) 
    {
        $this->requireLogin();
        if (!$this->auth->hasPermission($permission))
        {
            /* Kohana has no forward, so redirect */
            $this->request->redirect('/user/accessDenied');
        }
    }
    protected function requireGroup($group) {}

    protected function addMenuItem($item)
    {
        $items = $this->template->menu;
        $items[] = $item;
        $this->template->menu = $items;
    }

    public function action_accessDenied()
    {
        $this->template->title = 		__('Access Denied');
        $this->template->heading = 		__('Access Denied!');
        $this->template->subheading = 	__('You may not pass.');
        $this->template->content = new View('global/accessDenied');
    }

}
