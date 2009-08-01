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
        $this->data['todo'] = array();
        $a = new Account();
        $a->get();
        foreach ($a->all as $account) 
        {
            $this->data['todo'][] = $account->gname . ' ' . $account->sname . ' -- ' . $account->email;
        }
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
        $data['messages'] = array('Login failed, please try again');

        $this->template->write_view('content', 'user/login_error', $data, TRUE);
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

        $this->output->enable_profiler(TRUE);
        $a = new Account();
        if ($this->input->post('registerUser'))
        {
            $a->email             = $this->input->post('email');
            $a->password          = $this->input->post('password');
            $a->confirm_password  = $this->input->post('confirm_password');
            foreach ($a->validation as $field) 
            {
                $fieldName = $field['field'];
                if ($fieldName == 'id') continue;

                $a->$fieldName    = $this->input->post($fieldName);
            }

            $validated = 1;
            if ($this->config->item('use_captcha'))
            {
                $validated = 0;
                $this->form_validation->set_rules('recaptcha_response_field',  'lang:recaptcha_field_name', 'required|callback__check_captcha');
                $validated = ($a->form_validation->run() == TRUE);
                if (!$validated) 
                {

                    foreach ($this->form_validation->_error_array as $field=>$val)
                        $a->error_message($field,$val); // FIXME, using form_validation array directly
                }

            }

            if ($validated && $a->save())
            {
                $this->session->set_flashdata('messages', array('LANG: registration sucessful. Check email box blah blah'));
                $emailVars = array(
                    'email'=>$a->email
                );
                $this->load->library('email');

                $this->email->from(
                        $this->config->item('convention_outgoing_email_email'),
                        $this->config->item('convention_outgoing_email_name') // lang?
                );
                $this->email->to($a->email); 
                $this->email->subject('LANG: registration email subject');
                $this->email->message(
                        $this->load->view('user/register.email', $emailVars, TRUE)
                );
                $this->email->send();
                echo $this->email->print_debugger();
                return;
                redirect('');
                return;
            }
        }

        $this->template->write_view('content', 'user/register', array('object'=>$a), TRUE);
        return $this->template->render();
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

