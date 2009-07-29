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
        $this->load->model('Account_model');
        #$user = $this->Account_model->findByEmail('halkeye@gmail.com');
        
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

        $this->load->library('auth');
        $this->auth->restrict(TRUE);

        $this->load->library('form_validation');

        $this->form_validation->set_rules('user','Email', "trim|xss_clean|required|min_length[3]|valid_email");
        $this->form_validation->set_rules('pass','Password', "trim|xss_clean|required|min_length[5]");

        if ($this->form_validation->run() !== FALSE)
        {
            $user = $this->input->post('user');
            $pass = $this->input->post('pass');
            if($this->auth->process_login($user,$pass))
            {
                // Login successful, let's redirect.
                $this->redirect('/user/index');
                return;
            }
            else
            {
                $data['error'] = 'Login failed, please try again';
                $this->load->vars($data);
            }
        }

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

        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[5]|matches[passconf]');
        $this->form_validation->set_rules('passconf', 'Password Confirmation', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|callback__check_duplicated_email');
        $this->form_validation->set_rules('recaptcha_response_field',  'lang:recaptcha_field_name', 'required|callback__check_captcha');

        if ($this->form_validation->run() == TRUE)
        {
            $this->load->model('Account_model');
            $email = $this->input->post('email');
            $pass  = $this->input->post('pass');
            $this->Account_model->register($email, $pass);
            /* FIXME: ADD FLASH MSG ABOUT USER CREATED SUCCESSFULLY */
            if($this->auth->process_login ($email,$pass  ))
            {
                // Login successful, let's redirect.
                $this->redirect('/user/index');
                return;
            }
        }

        $this->template->write_view('content', 'user/register', array(), TRUE);
        return $this->template->render();
    }

    function _check_duplicated_email($email)
    {
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

