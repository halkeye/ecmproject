<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Ecmproject_base_controller extends Controller 
{
    function Ecmproject_base_controller()
    {
        parent::Controller();

        /*
        $this->load->library('auth');
        $loggedIn = $this->auth->logged_in();
        $this->template->write(
                'isLoggedIn',
                $loggedIn
        );
        if ($loggedIn)
        {
            $this->template->write(
                    'user_name',
                    $this->auth->name()
            );
        }
        */

    }
}
