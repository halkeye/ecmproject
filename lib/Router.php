<?php
class Router 
{
    private $registry;
    private $args = array();

    private $module_path;
    private $module_name;
    private $action;

    function __construct($registry) {
        $this->registry = $registry;
    } 

    public function loader() {
        $this->getArguments();

        if (!is_readable($this->module_path))
        {
            $this->registry->template->display('404.tpl');
            return 0;
        }

        include $this->module_path;

        $instance = new $this->module_name($this->registry);
        if (! is_callable(array($instance, $this->action)))
            $action = 'index';
        else
            $action = $this->action;	

        /* Do the menu */
        /*
        $menu = new menu($this->registry);
        $menu->setupMenu($this->module_name, $action);
        */

        /* Check if user has permission to do said action */			
        if ($instance->auth($action))
        {
            $this->registry->template->addTemplateDir(MODL_PATH . '/' . $this->module_name . '/templates/');
            /* Default heading is module name */
            $this->registry->template->heading = $module; 
            /* Default subheading is the action name */
            $this->registry->template->subheading = $action;
            /* Call the action on the module */
            $instance->$action();
            /* Get Menu Data */
            $this->registry->template->menu = $instance->menu();
        }
        else
        {
            $this->registry->template->display('403.tpl');
            return 0;
        }
        /* FIXME: is this the right place for this stuff ?*/
        $this->registry->template->loginUrl = $this->registry->template->getLink('user','login');
        $this->registry->template->isLoggedIn = $_SESSION['user'] ? TRUE : FALSE;
        /* Render The content */
        $this->registry->template->render();
    }

    /* Gets the arguments in the URL specifying the module and the action */
    private function getArguments() 
    {
        if (isset($_GET['mod']))
            $module = $_GET['mod'];
        else
            $module = DEFAULT_MOD;

        if (isset($_GET['do']))
            $this->action = $_GET['do'];
        else
            $this->action = 'index'; 

        $this->module_path = MODL_PATH . '/' . $module . '/' . $module . '.php';
        $this->module_name = $module;						
    }

}
?>
