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
        $account = ORM::factory('account')->find('halkeye@gmail.com');
        $account = ORM::factory('account')->find(1);


        $accounts = ORM::factory('account')->find_all();

        foreach ($accounts as $account) 
        {
            $data['todo'][] = $account->gname . ' ' . $account->sname . ' -- ' . $account->email;
        }

        foreach ($account->usergroups as $group)
        {
            $data['todo'][] = $group->name;
            foreach ($group->permissions as $p)
            {
                $data['todo'][] = $group->name . ' - ' . $p->pkey;
            }
        }
        
        $group = ORM::factory('usergroup', 1 );
        $data['todo'][] = var_export($account->has($group),1);

        $group = ORM::factory('usergroup');
        $group->name = "Administrators";
        $group->save();
        
        //$group = ORM::factory('usergroup', 'Administrators');
        $account->add($group);
        $account->save();


        $this->view->content = new View('user/user_view', $data);
        $this->view->menu += array(
                array('title'=>'Register', 'url'=>'user/register'),
                array('title'=>'Login',    'url'=>'user/login'),
        );
        return;
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

        if ($this->auth->login($user, $this->input->post('pass')))
        {
            $this->addMessageFlash(Kohana::lang('auth.login_success'));
            //$this->addMessageFlash(Kohana::debug($user->roles));
            url::redirect('');
            return;
        }
        $this->addError('Invalid username or password.');
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
        }
        $this->view->content = new View('user/register', array('form'=>$form, 'errors'=>$errors));
    }

    function validate($uid = 0, $timestamp = 0, $key = '')
    {
        $timestamp = intval($timestamp);

        $account = ORM::factory('account')->where(Account_Model::where_key(), $uid)->find();

        if ($this->auth->is_logged_in())
        {
            $this->addError(Kohana::lang('auth.expired_validate_link'));
            return;
        }

        /* Get timeout value, defaults at 86400 */
        $timeout = Kohana::config('ecmproject.validate_link_timeout', FALSE, FALSE);
        if (!$timeout) { $timeout = 86400; }

        $current = time();
        
        if (!$account->loaded)
        {
            $this->addError(Kohana::lang('auth.bad_link'));
            return;
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
            $this->addError(Kohana::lang('auth.bad_link'));
            return;
        }
        $account->reg_status = ACCOUNT_STATUS_ACTIVE;
        $account->save();

        $this->addMessageFlash(Kohana::lang('auth.login_success'));
        $this->auth->complete_login($account);
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


    function validationTest()
    {
        $a = ORM::factory('account');
        $a->where('email','halkeye@gmail.com')->find();
        $a->sendValidateEmail();
        $this->index();
    }

}

