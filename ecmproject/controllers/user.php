<?php

/**
 * User Controller
 * 
 * All user related webpage and functionality.
 * @author Gavin Mogan <ecm@gavinmogan.com>
 * @version 1.0
 * @package ecm
 */


class User_Controller extends Controller 
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        $data['todo'] = array();
        $accounts = ORM::factory('account')->find_all();

        foreach ($accounts as $account) 
        {
            $data['todo'][] = $account->gname . ' ' . $account->sname . ' -- ' . $account->email;
        }

        $this->view->content = new View('user/user_view', $data);

        return;

        $form = new Forge(NULL, 'User Login');

        $form->input('username')->label(TRUE)->rules('required|length[4,32]');
        $form->password('password')->label(TRUE)->rules('required|length[5,40]');
        $form->submit('Attempt Login')->label(FALSE);
		

        if ($form->validate())
        {
        }

        $this->view->content = $form->render();

        /*
        $a = new Account();
        $a->get();
        foreach ($a->all as $account) 
        {
            $this->data['todo'][] = $account->gname . ' ' . $account->sname . ' -- ' . $account->email;
        }
        */
        $this->view->menu += array(
                array('title'=>'Register', 'url'=>'user/register'),
                array('title'=>'Login',    'url'=>'user/login'),
        );
        return;

        $this->load->vars($this->data);
        $this->template->write('pageTitle', 'My Index Title', TRUE);
        $this->template->write('heading', 'User', TRUE);
        $this->template->write('subheading', 'Main Page', TRUE);
        $this->template->write_view('content', 'user/user_view', $this->data, TRUE);
        $this->template->render();
    }

    function login()
    {
        $this->view->title      = 'My Index Title';
        $this->view->subHeading = "Login Page";

        if ($this->auth->is_logged_in()) 
        {
            $_SESSION['messages'][] = 'Already logged in';
            url::redirect('');
            return;
        }

        // Load the user
        $user = ORM::factory('account')->where('email', $this->input->post('user'))->find();
        if (!$user) 
        {
            $this->addError('Invalid Email');
            return;
        }

        $authRet = $this->auth->login($user, $this->input->post('pass'));
        if ($authRet === TRUE) 
        {
            $this->addMessageFlash('Login Success!');
            //$this->addMessageFlash(Kohana::debug($user->roles));
            url::redirect('');
            return;
        }
        $this->addError('Invalid username or password.');
        $this->addError($authRet);
        $this->view->content = new View('user/login_error');
        return;
    }

    function logout()
    {
        if (!$this->auth->is_logged_in()) 
        {
            $_SESSION['messages'][] = 'Not logged in yet.';
            url::redirect('');
            return;
        }

        if($this->auth->logout())
        {
            $this->_redirect('');
            return;
        }
    }

    function register()
    {
        // using the factory enables method chaining
        $form = array(
                'email'     => '',
                'password'  => '',
                'confirm_password'  => '',
                'gname'     => '',
                'sname'     => '',
                'phone'     => '',
        );
        
        $errors = $form;

        if ($post = $this->input->post())
        {
            $account = ORM::factory('Account');
            if ($account->validate($post))
            {
                $account->save();
                $account->sendValidateEmail();
                $this->addMessageFlash(Kohana::lang('ecmproject.registration_success_message'));
                $this->_redirect('');
                return;
            }
            else
            {
                // repopulate the form fields
                $form = arr::overwrite($form, $post->as_array());

                // populate the error fields, if any
                // We need to already have created an error message file, for Kohana to use
                // Pass the error message file name to the errors() method
                $errors = arr::overwrite($errors, $post->errors('form_error_messages'));
            }
        
            /*

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
                $this->_redirect('');
                return;
            }
            */
        }
        $this->view->content = new View('user/register', array('form'=>$form, 'errors'=>$errors));
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
        if ($this->session->get('redirected_from') == FALSE)
        {
            url::redirect($where);
            return;
        } 
        url::redirect($this->session->get('redirected_from'));
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
        $a = ORM::factory('account');
        $a->where('email','halkeye@gmail.com')->find();
        $a->sendValidateEmail();
        $this->index();
    }

    public function _pwd_check(Validation $post)
    {
        // If add->rules validation found any errors, get me out of here!
        if (array_key_exists('password', $post->errors()))
            return;

        // only valid password is '123'
        if ($post->password != '123')
        {
            // Add a validation error, this will cause $post->validate() to return FALSE
            $post->add_error( 'password', 'pwd_check');
        }
    }

}

