<?php

class user extends Controller 
{
    function __construct()
    {
        parent::__construct();
        $this->data['subheading'] = "User Information";
    }

    function index()
    {
        $this->data['menu'] = array(
                array('title'=>'Register', 'url'=>array('user','register')),
                array('title'=>'Login',    'url'=>array('user','login')),
        );
        $this->data['title'] = 'My Index Title';
        $this->data['todo'] = array(
            'meow',
            'meow2',
            'meow3',
        );
        $this->load->vars($this->data);
    }

}
