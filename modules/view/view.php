<?php
class View extends Module_Base 
{

    function install() {
        echo "Module installed.";
    }

    function index() 
    {
        $this->registry->template->assign('heading', $_SESSION['gname'] . ' ' . $_SESSION['sname']);
        $this->registry->template->assign('subheading',  'HR Assigned ID: ' . $_SESSION['id']);

        if ( isset( $_SESSION['ugroups'] ) && $_SESSION['ugroups'] ) 
        {
            $this->registry->template->assign('userGroups', $_SESSION['ugroups']);
        }

        if ( isset( $_SESSION['dept_name'] ) && $_SESSION['dept_name']) 
        {
            $this->registry->template->assign('department', $_SESSION['dept_name']);
            $this->registry->template->assign('position', $_SESSION['pos_name']);

            if (isset($_SESSION['fname']) && !empty($_SESSION['fname']))
                $sub = $sub . '<br />Forum Name: ' . $_SESSION['fname'];
            if (isset($_SESSION['photo']) && !empty($_SESSION['photo']))
                $sub = $sub . '<br />Photo: ' . $_SESSION['photo'];
            if (isset($_SESSION['shirt']))
                $sub = $sub . '<br />Shirt Size: ' . $_SESSION['shirt'];

            $content = $content . $sub . '</p>';
        }
        else if ( isset ( $_SESSION['setup'] ) && !empty ( $_SESSION['setup'] ) ) 
        {
            $sub = sprintf("<p>Setup Hours: %s <br />Day 1 Hours: %s<br />Day 2 Hours: %s<br />Day 3 Hours: %s</p>", 
                    $_SESSION['setup'],
                    $_SESSION['day1'],
                    $_SESSION['day2'],
                    $_SESSION['day3']);

            $content = $content . $sub;
        }

        if ( isset ( $_SESSION['supervisor'] ) && !empty($_SESSION['supervisor']) )
            $content = $content . '<p><span class="emphasis">Supervisors: ' . $_SESSION['supervisor'] . '</span></p>';


        $this->registry->template->content = $content;
        $this->registry->template->display('view-index.tpl');
    }

    function getMenuOption()
    {

    }

    function permissions() {}

    function auth($action) 
    {
        $_SESSION['dept_name'] = 'Vendors';
        return true;
        if (!isset($_SESSION['ugroups']))
            return false;

        //explode(",", $_SESSION['permissions']);				
        return true;
    }
}
?>
