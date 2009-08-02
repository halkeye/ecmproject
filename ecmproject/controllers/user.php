<?php

/**
 * User Controller
 * 
 * All user related webpage and functionality.
 * @author Gavin Mogan <ecm@gavinmogan.com>
 * @version 1.0
 * @package ecm
 */


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
$this->output->enable_profiler(TRUE);
        $this->template->write('pageTitle', 'My Index Title', TRUE);
        $this->template->write('heading', 'User', TRUE);
        $this->template->write('subheading', 'Main Page', TRUE);

        $this->auth->restrict(TRUE);

        $this->load->library('auth');
        $user = $this->input->post('user');
        $pass = $this->input->post('pass');

        $authRet = $this->auth->processLogin($user,$pass);
        if($authRet === TRUE)
        {
            $this->session->set_flashdata('messages', array('Logged in sucessfully'));
            // Login successful, let's redirect.
            $this->_redirect('/user/index');
            return;
        }

        $data['errors'] = array($authRet);

        $this->template->write_view('content', 'user/login_error', $data, TRUE);
        return $this->template->render();
    }

    function logout()
    {
        $this->auth->restrict();

        if($this->auth->logout())
        {
            $this->_redirect('');
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
                $a->sendValidateEmail();
                $this->_redirect('');
                return;
            }
        }

        $this->template->write_view('content', 'user/register', array('object'=>$a), TRUE);
        return $this->template->render();
    }

    function validate($uid = 0, $timestamp = 0, $key = '')
    {
        $timestamp = intval($timestamp);

$this->output->enable_profiler(TRUE);
        $account = new Account();
        $account->where('id',$uid)->get();
        //$account->login = 0; //time();
        //$account->save();
        //return;

        if ($this->auth->logged_in())
        {
            $data['errors'][] = $this->lang->line('auth_error_expired_validate_link');
            $this->session->set_flashdata('errors', $data['errors']);
            //return redirect('');
        }

        $account = new Account();

        /* Get timeout value, defaults at 86400 */
        $timeout = $this->config->item('validate_link_timeout');
        if (!$timeout) { $timeout = 86400; }

        $current = time();
        
        if ($uid > 0)
        {
            $account->where('id',$uid)->get();
        }
        if (!isset($account->id))
        {
            $data['errors'][] = $this->lang->line('auth_error_invalid_account');

            $this->session->set_flashdata('errors', $data['errors']);
            return redirect('');
        }
        $invalidLink = 0;
        /*
         * Make sure timestamp is earlier than now
         * Make sure if they've logged in, that the url hasn't expired
         */
        if ($timestamp > $current) { $invalidLink = 1; }
        if ($account->login) 
            if ($current - $timestamp > $timeout || $timestamp < $account->login)
                $invalidLink = 1; 

        if ($invalidLink)
        {
            $data['errors'][] = $this->lang->line('auth_error_expired_validate_link');
            $this->session->set_flashdata('errors', $data['errors']);
            return redirect('');
        }
        /* FIXME: Move to account model */
        $account->reg_status = ACCOUNT_STATUS_ACTIVE;
        $account->login = time();
        $account->save();

        $this->auth->loginUser($account);
        $this->_redirect('');
    }


    /**
     * Redirect a user to a location, unless a session variable is set
     * @param string $where Where to redirect the user if nothing else is set
     */
    function _redirect($where = '/user')
    {
        if ($this->session->userdata('redirected_from') == FALSE)
        {
            redirect($where);
            return;
        } 
        redirect($this->session->userdata('redirected_from'));
        return;
    }


    /**
     * Form Validation callback for checking captcha
     * @param string $val input to validate
     * @return boolean true if the valid is successful 
     */
    function _check_captcha($val) 
    {
        if ($this->recaptcha->check_answer(
                $this->input->ip_address(),
                $this->input->post('recaptcha_challenge_field'),
                $val
            ))
        {
            return TRUE;
        }

        $this->form_validation->set_message('_check_captcha',$this->lang->line('recaptcha_incorrect_response'));
        return FALSE;
    }

    function validationTest()
    {
        $a = new Account();
        $a->where('email','halkeye@gmail.com')->get();
        $a->sendValidateEmail();
        $this->index();
    }

}

