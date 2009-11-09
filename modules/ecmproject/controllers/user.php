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

        return $this->loginOrRegister();
    }

    function logout()
    {
        $this->requireLogin();
        if (!$this->auth->is_logged_in()) 
        {
            $this->addMessage(Kohana::lang('auth.not_logged_in'));
            url::redirect('');
            return;
        }

        $this->view->content = new View('user/logout');

        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $this->auth->logout();
            $this->_redirect('');
        }
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

                $code = $account->generateVerifyCode();
                $account->sendValidateEmail($code);

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

    function validate($uid = 0, $key = '')
    {
        $this->view->title = "Verification";

        if (!$uid) 
        {
            $this->requireLogin();
            $account = $this->auth->getAccount();
            if ($account) { $uid = $account->id; }
        }
        else 
        {
            $account = ORM::factory('account')->find($uid); 
        }
        if (!$key) { $key = $this->input->post('verifyCode'); }

        /* Validate incomingness */
        if (!$account)
            $this->_badVerify();
        if (!$key) 
            $this->_badVerify();
        $code = ORM::Factory('verificationcode', sha1($account->salt.$key));

        /* We have no account */
        if (!$account->loaded) 
            $this->_badVerify();
        
        /* We don't have a valid code */
        if (!$code || !$code->loaded || 
             $code->account_id != $account->id)
            $this->_badVerify();

        /* Verify the account */
        $account->status = Account_Model::ACCOUNT_STATUS_VERIFIED;
        /* Complete login saves the account when it updates the login time */
        $this->auth->complete_login($account);
        /* Delete the code */
        $code->delete();

        /* Show a message that they logged in, and goto the default page */
        /* FIXME */
        $this->addMessage(Kohana::lang('auth.verification_success'));
        $this->_redirect('');
    }
        
    function _badVerify()
    {
        $this->addError(Kohana::lang('auth.bad_link')); 
        return url::redirect('/user/verifyMenu');
    }
    
    function loginOrRegister()
    {
        $data = array();

        $this->view->content = new View('user/loginOrRegister', $data);
        return;
    }

    function verifyMenu()
    {
        $this->view->title = "Require Verification";
        $this->view->heading = Kohana::lang('auth.verifyMenu_heading');
        $this->view->subheading = Kohana::lang('auth.verifyMenu_subheading');
        $this->requireLogin();
        $data = array();

        $this->view->content = new View('user/verifyMenu', $data);
        return;
    }

    function resendVerification()
    {
        /* Set page title */
        $this->view->title = "Require Verification";
        /* Require login */
        $this->requireLogin();

        /* Get logged in account */
        $account = $this->auth->getAccount();
        /* Generate new code */
        $code = $account->generateVerifyCode();
        /* Send email */
        $account->sendValidateEmail($code);

        /* FIXME: Send Verification Message */
        $this->addMessage(Kohana::lang('auth.sendVerificationMessage', $account->email)); 
        return url::redirect('/user/verifyMenu');
    }

    function changeEmail()
    {
        /* Set page title */
        $this->view->title = "Change Email";
        $this->view->heading = Kohana::lang('auth.changeEmail_heading');
        $this->view->subheading = Kohana::lang('auth.changeEmail_subheading');
        /* Require login */
        $this->requireLogin();

        /* Get logged in account */
        $account = $this->auth->getAccount();
        
        $fields = array(
                'email' => array('type'=>'text', 'required'=> true),
        );
        $form = array(
                'email' => ''
        );
        $errors = array();

        if ($post = $this->input->post())
        {
            $post = new Validation($this->input->post());
            // uses PHP trim() to remove whitespace from beginning and end of all fields before validation
            $post->pre_filter('trim');

            $post->add_rules('email', 'required', array('valid', 'email'));
            $post->add_callbacks('email', array($account,'_unique_email_validation'));

            if ( $post->validate())
            {
                $account->email  = $post['email'];

                /* FIXME: Log email change */
                /* FIXME: if we have a working one, keep the working one until the new one is verified? */

                /* Reset verification status */
                $account->status = Account_Model::ACCOUNT_STATUS_UNVERIFIED;
                /* Generate new code */
                $code = $account->generateVerifyCode();
                /* Send out verification Email */
                $account->sendValidateEmail($code);
                /* Update cached session object */
                $this->auth->complete_login($account);
                /* Tell the user what happened */
                $this->addMessage(Kohana::lang('auth.emailChangeSuccess'));
                /* Redirect to main page */
                $this->_redirect('');
            }
            else
            {
                $errors = $post->errors();
            }
            
            $form = arr::overwrite($form, $post->as_array());
            $errors = $post->errors('form_error_messages');
        }
        $this->view->content = new View('user/changeEmail', array('form'=>$form, 'errors'=>$errors, 'fields'=>$fields));
    }

}

