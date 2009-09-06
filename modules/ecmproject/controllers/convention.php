<?php

/**
 * User Controller
 * 
 * All user related webpage and functionality.
 * @author Gavin Mogan <ecm@gavinmogan.com>
 * @version 1.0
 * @package ecm
 */


class Convention_Controller extends Controller 
{
    function index()
    {
        $this->requireLogin();
        $regs = Registration_Model::getByAccount($this->auth->get_user()->id);
        if (!count($regs))
        {
            url::redirect('/convention/register');
        }

        $content = '';
        foreach ($regs as $row) 
        {
            $content = '<pre>' . var_export($row, 1) . '</pre>';
        }
        $this->view->content = "This page is 'main page' from the flow diagram. What does it do, I don't know. " . $content;
    }
}
