<?php defined('SYSPATH') OR die('No Direct Script Access');
 
Class Controller_Welcome extends Controller_Template
{
    public $template = 'welcomeTemplate';
    
    function before()
    {
        $ret = parent::before();

        $this->session = Session::instance();
        //$this->requireAuth();

        $this->template->currentPage = "index";
        $this->template->title = "index";
        $this->template->account = new StdClass;
        $this->template->account->displayName = $this->session->get('account_displayName');
        $this->template->account->email = $this->session->get('account_email');
        return $ret;
    }
    public function action_index()
    {
        $this->template->content = "Hello! WORLD!";
    }
}
