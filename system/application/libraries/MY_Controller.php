<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Ecmproject_base_controller extends Controller 
{
    function Ecmproject_base_controller()
    {
        parent::Controller();
        $this->template->write(
                'isLoggedIn',
                $this->session->userdata('isLoggedIn')
        );
        if ($this->session->userdata('isLoggedIn'))
        {
            $this->template->write(
                    'user_name',
                    $this->session->userdata('gname') . ' ' .
                    $this->session->userdata('lname')
            );
        }

    }
}
