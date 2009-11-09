<?php defined('SYSPATH') or die('No direct script access.');


class Controller extends Controller_Core
{
    protected $view = null;
    protected $auth = null;
	// Session instance
	protected $session;

    function __construct()
    {
        parent::__construct();
        Event::add('system.post_controller', array($this, 'renderTemplate'));
        /* Session Instance */
        $this->session = Session::instance();

        /* Auth to handle */
        $this->auth = Auth::instance();

        /* Base Template Stuff */
        $this->view = new View('template');
        /* Default Content */
        $this->view->content = "";
        /* Default Title */
        $this->view->title = "";
        /* Menu options */
        $this->view->menu    = array();

        /* Messages to show */
        $this->view->messages = array();

        /* Errors to show */
        $this->view->errors = array();

        $this->view->profiler = '';
        
        if ($this->auth->is_logged_in())
        {
            $this->isVerifiedAccount = TRUE;
            $user = $this->auth->get_user();
            if ($user->status != Account_Model::ACCOUNT_STATUS_VERIFIED)
            {
                /* check the database not the cached version for determining if they are cached or not
                 * Maybe we can get away with not doing this 
                 */
                $user = ORM::factory('Account', array('id'=>$user->id));
                if ($user->status != Account_Model::ACCOUNT_STATUS_VERIFIED)
                {
                    $this->isVerifiedAccount = FALSE;
                    $this->view->errors[] = Kohana::lang('ecmproject.not_validated');
                    $this->addMenuItem(array('title'=>'Verify Account', 'url'=>'/user/verifyMenu'));
                }
                else
                {
                    /* Store the updated account if we've been verified */
                    $this->auth->storeAccount($user);
                }
            }

            if ($this->auth->hasPermission('admin'))
            {
                $this->addMenuItem(array('title'=>'Administration', 'url'=>'admin'));
                $this->addMenuItem(array('seperator'=>1));
            }
        }
    }

    public function renderTemplate()
    {
        if (!$this->view)
            return; // Don't do anything now

        if (!isset($this->view->heading))
            $this->view->heading = ucfirst(Router::$controller);
        if (!isset($this->view->subheading))
            $this->view->subheading = ucfirst(Router::$method);

        $session_messages = $this->session->get_once('messages');
        if ($session_messages) 
            $this->view->messages = array_merge($session_messages, $this->view->messages);
        $session_errors = $this->session->get_once('errors');
        if ($session_errors) 
            $this->view->errors = array_merge($session_errors, $this->view->errors);

        /* Are you logged in? */
        $this->view->set_global('isLoggedIn', $this->auth->is_logged_in());
        $this->view->set_global('isVerifiedAccount', isset($this->isVerifiedAccount) ? $this->isVerifiedAccount : FALSE);
        /* store the user */
        $this->view->set_global('account', $this->auth->getAccount());
        
        if ($this->view->isLoggedIn)
        {
            $this->addMenuItem(array('url'=>'convention/checkout', 'title'=>'Checkout'));
            $this->addMenuItem(array('seperator'=>1));


            $this->addMenuItem(array('url'=>'user/logout', 'title'=>'Logout'));
        }

        // Displays the view
        $this->view->render(TRUE);
    }

    protected function addMessage($message)
    {
        $messages = $this->session->get('messages') or array();
        $messages[] = $message;
        $this->session->set_flash('messages',  $messages);
    }
    
    protected function addError($error)
    {
        $errors = $this->session->get('errors') or array();
        $errors[] = $error;
        $this->session->set_flash('errors',  $errors);
    }

    /**
     * Redirect a user to a location, unless a session variable is set
     * @param string $where Where to redirect the user if nothing else is set
     */
    function _redirect($where = '')
    {
        $location = $this->session->get_once('redirected_from');
        if (!$location) $location = $where;
        url::redirect($location);
        return;
    }

    protected function requireLogin()
    {
        if (!$this->auth->is_logged_in()) 
        {
            $this->session->set('redirected_from', url::current());
            url::redirect('/user/loginOrRegister');
            return;
        }
    }

    protected function requireVerified()
    {
        if ($this->auth->is_logged_in() && !$this->isVerifiedAccount ) 
        {
            /* FIXME: Add lang file */
            /* You can't go any furthur until email address is verified. */
            #$this->addError(Kohana::lang('auth.not_validated')); 
            $this->session->set('redirected_from', url::current());
            url::redirect('/user/verifyMenu');
            return;
        }
    }

    protected function requirePermission($permission) 
    {
        if (!$this->auth->isLoggedIn() && !$this->auth->hasPermission($permission))
        {
            return $this->accessDenied();
        }
    }
    protected function requireGroup($group) {}

    protected function addMenuItem($item)
    {
        $items = $this->view->menu;
        $items[] = $item;
        $this->view->menu = $items;
    }

    public function accessDenied()
    {
        $this->view->title = Kohana::lang('auth.accessDenied_title');
        $this->view->heading = Kohana::lang('auth.accessDenied_heading');
        $this->view->subheading = Kohana::lang('auth.accessDenied_subheading');
        $this->view->content = new View('global/accessDenied');
    }

}
