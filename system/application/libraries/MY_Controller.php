<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Ecmproject_base_controller extends Controller 
{
    function Ecmproject_base_controller()
    {
        parent::Controller();
        $this->load->library('auth');
        $this->template->write(
                'isLoggedIn',
                $this->auth->logged_in()
        );
        if ($this->session->userdata('isLoggedIn'))
        {
            $this->template->write(
                    'user_name',
                    $this->auth->name()
            );
        }

    }
}
