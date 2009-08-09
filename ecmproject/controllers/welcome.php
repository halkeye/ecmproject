<?php  if ( ! defined('BASEPATH') && !defined('SYSPATH')) exit('No direct script access allowed');

Event::add('system.post_controller', array('Welcome_Controller', 'blah'));

class Welcome_Controller extends Controller
{
    protected $view = null;

	function index()
	{
        // Load the view and set the $title variable
        $view = new View('template', array('title' => 'User Details'));
         
        // Sets the $username variable in the view
        $view->username = 'JohnDoe';
         
        // Sets the $visits variable to another view
        $view->content = new View('welcome/welcome_view', array('user_id' => 3));
         
        $this->view = $view;
	}

    function blah()
    {
        $data = Event::$data;
         
        // Debug the data
        //echo Kohana::debug(Kohana::instance());
        
        $view =  Kohana::instance()->view;
        if (!$view->heading)
            $view->heading = Router::$method;

        // Displays the view
        $view->render(TRUE);
    }
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */
