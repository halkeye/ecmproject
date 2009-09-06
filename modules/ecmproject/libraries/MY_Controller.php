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
        /* Are you logged in? */
        $this->view->isLoggedIn = $this->auth->is_logged_in();
        $this->view->user = new StdClass;
        if ($this->view->isLoggedIn)
        {
            $this->view->user = $this->auth->get_user();
        }

        /* Messages to show */
        $this->view->messages = array();

        /* Errors to show */
        $this->view->errors = array();

        $this->view->profiler = '';
        
        if ($this->auth->is_logged_in())
        {
            $user = $this->auth->get_user();
            if ($user->status != Account_Model::ACCOUNT_STATUS_VERIFIED)
            {
                /* check the database not the cached version for determining if they are cached or not
                 * Maybe we can get away with not doing this 
                 */
                $user = ORM::factory('Account', array('id'=>$user->id));
                if ($user->status != Account_Model::ACCOUNT_STATUS_VERIFIED)
                    $this->view->errors[] = Kohana::lang('ecmproject.not_validated');
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

}
