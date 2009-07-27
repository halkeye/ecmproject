<?php

class Welcome extends Ecmproject_Base_Controller 
{
	
	function index()
	{
        $this->template->write_view('content', 'welcome/welcome_view');
        $this->template->render();
	}
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */
