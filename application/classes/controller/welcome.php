<?php defined('SYSPATH') OR die('No Direct Script Access');
 
Class Controller_Welcome extends Base_MainTemplate
{
    //public $template = 'welcomeTemplate';
    public $template = 'mainTemplate';
    
    public function action_index()
    {
        $this->template->content = "Hello! WORLD!";
    }
}
