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

        $this->profiler = new Profiler;
        $this->view->profiler = '';
    }

    public function renderTemplate()
    {
        if (!isset($this->view->heading))
            $this->view->heading = ucfirst(Router::$controller);
        if (!isset($this->view->subheading))
            $this->view->subheading = ucfirst(Router::$method);

        if (isset($this->profiler))
        {
            $this->profiler->disable();
            $this->view->profiler = $this->profiler->render(TRUE);
        }
        
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
        $messages = $this->view->errors;
        $messages[] = $message;
        $this->view->messages = $messages;
    }
    
    protected function addMessageFlash($message)
    {
        $messages = $this->session->get('messages') or array();
        $messages[] = $message;
        $this->session->set_flash('messages',  $messages);
    }

    protected function addError($error)
    {
        $errors = $this->view->errors;
        $errors[] = $error;
        $this->view->errors = $errors;
    }
    
    protected function addErrorFlash($error)
    {
        $errors = $this->session->get('errors') or array();
        $errors[] = $error;
        $this->session->set_flash('errors',  $errors);
    }
}
