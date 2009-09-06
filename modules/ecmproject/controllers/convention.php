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
        $this->view->content = "This page is 'main page' from the flow diagram. What does it do, I don't know.";
    }
}
