<?php

class user extends Ecmproject_Base_Controller 
{
    function user()
    {
        parent::__construct();
        $this->data['subheading'] = "User Information";
    }

    function index()
    {
        $this->data['todo'] = array(
            'meow',
            'meow2',
            'meow3',
        );

        $this->data['menu'] = array(
                array('title'=>'Register', 'url'=>array('user','register')),
                array('title'=>'Login',    'url'=>array('user','login')),
        );

        $this->load->vars($this->data);
        $this->template->write('pageTitle', 'My Index Title');
        $this->template->write('heading', 'User');
        $this->template->write('subheading', 'Main Page');
        $this->template->write_view('content', 'user/user_view');
        $this->template->render();
    }

    function login()
    {
        $this->template->write('pageTitle', 'My Index Title');
        $this->template->write('heading', 'User');
        $this->template->write('subheading', 'Main Page');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('user','Username', "trim|xss_clean|required|min_length[3]|valid_email");
        $this->form_validation->set_rules('pass','Password', "trim|xss_clean|required|min_length[5]");

        if ($this->form_validation->run() === FALSE)
        {
            $this->template->write_view('content', 'user/login_error');
            return $this->template->render();
        }

        $this->load->model('Account_model');
        $user = $this->Account_model->getUserByLogin(
                $this->input->post('user'),
                $this->input->post('pass')
        );
        if ($user !== FALSE)
        {
            $this->load->helper('url');

            //Destroy old session
            $this->session->sess_destroy();
            
            //Create a fresh, brand new session
            $this->session->sess_create();
            
            // Indicate we are logged in
            $this->session->set_userdata('isLoggedIn', TRUE);
            // Set all the session vars about a user
            $this->session->set_userdata($user);
            return redirect('/user/index');
        }
        $this->form_validation->set_message('username_check', 'Username or password does not match');
        $this->template->write_view('content', 'user/login_error');
        return $this->template->render();
    }

    function logout()
    {
        //Destroy old session
        $this->session->sess_destroy();
        $this->load->helper('url');
        redirect('');
    }

}
