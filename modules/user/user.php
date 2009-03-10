<?php
class User extends Module_Base 
{
    function install() { echo "Module installed."; }

    /* Default Action */
    function index() 
    {
        $this->setTemplate('index');
    }
    
    function register() 
    {
        $this->setTemplate('register');
    }

    /* 
     * Return a list of menu options as provided by this module 
     * Add a weight field if it matters (default is 0)
     */
    function menu()
    {
        return array(
                array('title'=>'Register', 'url'=>$this->registry->template->getLink('user','register')),
                array('title'=>'Login', 'url'=>$this->registry->template->getLink('user','login')),
        );
    }

    function permissions() {}

    function auth($action) 
    {
        return true;
    }
}
?>
