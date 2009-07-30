<?php

class user extends Ecmproject_Base_Controller 
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('auth');
        $this->template->write('heading', 'User', TRUE);
        $this->template->write('subheading', 'User Information', TRUE);
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
        $this->template->write('pageTitle', 'My Index Title', TRUE);
        $this->template->write('heading', 'User', TRUE);
        $this->template->write('subheading', 'Main Page', TRUE);
        $this->template->write_view('content', 'user/user_view', $this->data, TRUE);
        $this->template->render();
    }

    function login()
    {
        $this->template->write('pageTitle', 'My Index Title', TRUE);
        $this->template->write('heading', 'User', TRUE);
        $this->template->write('subheading', 'Main Page', TRUE);

        $this->auth->restrict(TRUE);

        $this->load->library('auth');
        $user = $this->input->post('user');
        $pass = $this->input->post('pass');
        if($this->auth->process_login($user,$pass))
        {
            $this->session->set_flashdata('messages', array('Logged in sucessfully'));
            // Login successful, let's redirect.
            $this->redirect('/user/index');
            return;
        }
        $data['error'] = 'Login failed, please try again';
        $this->load->vars($data);

        $this->template->write_view('content', 'user/login_error', array(), TRUE);
        return $this->template->render();
    }

    function logout()
    {
        $this->auth->restrict();

        if($this->auth->logout())
        {
            $this->redirect('');
            return;
        }
    }

    function register()
    {
        $this->load->library('recaptcha');
        $this->load->library('form_validation');
        $this->lang->load('recaptcha');
        $this->load->helper(array('form', 'url'));

        #$this->form_validation->set_rules('recaptcha_response_field',  'lang:recaptcha_field_name', 'required|callback__check_captcha');

		$this->output->enable_profiler(TRUE);
        #if ($this->form_validation->run() == TRUE)
        if (1)
        {
            //$this->load->model('Account_model');
            $a = new Account();
            $a->email            = $this->input->post('email');
            $a->password         = $this->input->post('password');
            $a->config_password  = $this->input->post('passconf');
            if (!$a->save())
            {
                $this->session->set_flashdata('errors', $a->error->all);
            }
        }

        $this->template->write_view('content', 'user/register', array(), TRUE);
        return $this->template->render();
    }

    function _check_duplicated_email($email)
    {
        die('here');
        $this->load->model('Account_model');
        $user = $this->Account_model->findByEmail($email);
        if ($user)
        {
            $this->form_validation->set_message('_check_duplicated_email', 'Email address is unavailable');
            return FALSE;
        }

        return TRUE;
    }


    function _check_captcha($val) 
    {
        if ($this->recaptcha->check_answer($this->input->ip_address(),$this->input->post('recaptcha_challenge_field'),$val)) {
            return TRUE;
        } else {
            $this->form_validation->set_message('_check_captcha',$this->lang->line('recaptcha_incorrect_response'));
            return FALSE;
        }
    }

    function redirect($where = '/user')
    {
        if ($this->session->userdata('redirected_from') == FALSE)
        {
            redirect($where);
        } 
        else 
        {
            redirect($this->session->userdata('redirected_from'));
        }
    }

}

