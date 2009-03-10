<?php
class User extends Module_Base 
{
    function install() { echo "Module installed."; }

    /* Default Action */
    function index() 
    {
        $this->registry->template->heading = 'User Section'; # FIXME
        $this->registry->template->subheading = 'index';

        $this->registry->template->display('user-index');
    }
    
    /* Default Action */
    function register() 
    {
        $this->registry->template->heading = 'User Section'; # FIXME
        $this->registry->template->subheading = 'index';

        $this->registry->template->display('user-register');
    }

    /* 
     * Return a list of menu options as provided by this module 
     * Add a weight field if it matters (default is 0)
     */
    function getMenuOptions()
    {
        return array(
                array( 'module'=>'user', 'action'=>'register', 'weight'=>-100 ),
                /* FIXME - only show login and register if not logged in */
                array( 'module'=>'user', 'action'=>'login', 'weight'=>-100 ),
        );
    }

    function permissions() {}

    function auth($action) 
    {
        return true;
    }
}
?>
