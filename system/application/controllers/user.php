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
        $user = $this->Account_model->findByEmail('halkeye@gmail.com');
        var_dump($user->gname);
        
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

        $this->form_validation->set_rules('user','Username', "trim|xss_clean|required|min_length[3]|valid_email");
        $this->form_validation->set_rules('pass','Password', "trim|xss_clean|required|min_length[5]");

        if ($this->form_validation->run() !== FALSE)
        {
            $user = $this->input->post('user');
            $pass = $this->input->post('pass');
            if($this->auth->process_login($user,$pass))
            {
                // Login successful, let's redirect.
                redirect('/user/index');
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
            redirect();
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
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('recaptcha_response_field',  'lang:recaptcha_field_name', 'required|callback__check_captcha');
        if ($this->form_validation->run() == TRUE)
        {
            $this->load->model('Account_model');
            $user = $this->Account_model->register(
                    $this->input->post('email'),
                    $this->input->post('pass')
            );
            if ($user !== FALSE)
            {
                $this->_login($user);
                $this->load->helper('url');
                return redirect('/user/index');
            }
        }

        $this->template->write_view('content', 'user/register', array(), TRUE);
        return $this->template->render();
    }

    function _login_check($email, $passwordField)
    {
        $this->load->model('Account_model');
        $user = $this->Account_model->getUserByLogin(
                $this->input->post($email),
                $this->input->post($passwordField)
        );
        if (!$user)
        {
            $this->form_validation->set_message('_login_check', 'Wrong %s or %s');
            return false;
        }

        //Destroy old session
        $this->session->sess_destroy();
        
        //Create a fresh, brand new session
        $this->session->sess_create();
        
        // Indicate we are logged in
        $this->session->set_userdata('isLoggedIn', TRUE);
        // Set all the session vars about a user
        $this->session->set_userdata($user);

        return true;
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

    function redirect()
    {
        if ($this->CI->session->userdata('redirected_from') == FALSE)
        {
            redirect('/admin');
        } else {
            redirect($this->CI->session->userdata('redirected_from'));
        }
    }

}

