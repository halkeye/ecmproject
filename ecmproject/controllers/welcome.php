<?php  if ( ! defined('BASEPATH') && !defined('SYSPATH')) exit('No direct script access allowed');

class Welcome_Controller extends Controller
{

	function index()
	{
        // Sets the $visits variable to another view
        $this->view->content = new View('welcome/welcome_view', array('user_id' => 3));
	}

}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */
