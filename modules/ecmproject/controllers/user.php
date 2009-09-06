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
    function index()
    {
        $this->requireLogin();
        $this->view->content = "This page is 'main page' from the flow diagram. What does it do, I don't know.";
    }

    function junk()
    {
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
            $this->addMessage(Kohana::lang('auth.already_logged_in'));
            $this->_redirect('');
            return;
        }

        // Load the user
        $user = ORM::factory('account')->where('email', $this->input->post('email'))->find();
        if ($this->auth->login($user, $this->input->post('password')))
        {
            $this->addMessage(Kohana::lang('auth.login_success'));
            $this->_redirect('');
            return;
        }

        foreach ($this->auth->errors() as $err) 
            $this->addError($err);
        $this->auth->clearErrors();

        return;
    }

    function logout()
    {
        if (!$this->auth->is_logged_in()) 
        {
            $this->addMessage(Kohana::lang('auth.not_logged_in'));
            url::redirect('');
            return;
        }

        $this->auth->logout();
        $this->_redirect('');
        return;
    }

    function register()
    {
        // using the factory enables method chaining
        $form = array(
                'email'     => '',
                'password'  => '',
                'confirm_password'  => '',
        );
        
        $errors = $form;

        if ($post = $this->input->post())
        {
            $account = ORM::factory('Account');
            if ($account->validate($post))
            {
                $account->save();
                $account->sendValidateEmail();
                $this->auth->complete_login($account);
                $this->addMessage(Kohana::lang('ecmproject.registration_success_message'));
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

        /*
        if ($this->auth->is_logged_in())
        {
            $this->addError(Kohana::lang('auth.expired_validate_link'));
            return;
        }
        */

        /* Get timeout value, defaults at 86400 */
        $timeout = Kohana::config('ecmproject.validate_link_timeout', FALSE, FALSE);
        if (!$timeout) { $timeout = 86400; }

        $current = time();
        
        $account = ORM::factory('account')->find($uid);
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
        {
            if ($current - $timestamp > $timeout || $timestamp < $account->login)
                $invalidLink = 1; 
        }

        if ($invalidLink)
        {
            $this->addError(Kohana::lang('auth.bad_link'));
            return;
        }
        $account->status = Account_Model::ACCOUNT_STATUS_VERIFIED;

        $this->auth->complete_login($account);
        $this->addMessage(Kohana::lang('auth.login_success'));
        $this->_redirect('');
    }

    function paypalTest()
    {
        if (!$this->auth->is_logged_in()) 
        {
            $this->addMessage('FIXME: make $auth->requre_login()');
            $this->session->set('redirected_from', Router::$current_uri);
            return;
        }

        /* FIXME: Get account / get real account */
        $account = $this->auth->get_user();
        
        $this->view->content = Kohana::lang('ecm.no_passes_available');

        
        $data = array();
        /* FIXME */
        $data['notify_url'] = 'http://barkdog.halkeye.net:6080/ecmproject/index.php/user/paypal_ipn'; //url::site('/user/paypal_ipn');
        $data['return_url'] = url::site('/user/paypal_return');
        $data['cancel_url'] = url::site('/user/paypal_cancel');
        
        $data['passes'] = ORM::factory('pass')->find_all_for_account($account);

        if ($data['passes'])
        {
            $this->view->content = new View('user/passes', $data);
        }
        return;
    }
    
    function paypal_ipn()
    {
        $p = new Paypal();
        if (!$p->validate_ipn()) 
        {
            $this->addError("Unable to validate ipn: " . $p->getLastError());
            return;
        }
        $data = $p->getIpnData();
        $content  =  "An instant payment notification was successfully recieved\n";
        $content .= "from ".$data['payer_email']." on ".date('m/d/Y');
        $content .= " at ".date('g:i A')."\n\nDetails:\n";
        foreach ($data as $key => $value) { $content .= "\n$key: $value"; }

        file_put_contents('/tmp/ipn', $content);
        $this->view = null;
        return;

    }

    function loginOrRegister()
    {
        $data = array();

        $this->view->content = new View('user/loginOrRegister', $data);
        return;
    }
}

