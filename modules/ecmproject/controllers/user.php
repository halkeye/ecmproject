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
		$reg = Registration_Model::getAllRegistrationsByConvention($this->auth->getAccount()->id);		
				
        $this->view->content = new View('user/index', array('registrations'=>$reg));
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

                try {
                    $vcode = $account->generateVerifyCode(Verificationcode_Model::TYPE_VALIDATE_EMAIL);
                }
                catch (Verification_Exceeds_Exception $e) 
                {
                    $this->addError(Kohana::lang('auth.too_many_verification'));
                    $this->view->content = "";
                    return;
                }
                $account->sendValidateEmail($vcode->original_code);

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
        $vcode = ORM::Factory('verificationcode')->where('code',sha1($account->salt.$key))->find();

        /* We have no account */
        if (!$account->loaded) 
            $this->_badVerify();
        
        /* We don't have a valid code */
        if (!$vcode || !$vcode->loaded || 
             $vcode->account_id != $account->id)
        {
            $this->_badVerify();
        }

        /* Verify the account */
        if ($vcode->type == Verificationcode_Model::TYPE_EMAIL_CHANGE)
        {
            $account->email = $vcode->value;
        }
        $account->validateAccount();
        /* Complete login saves the account when it updates the login time */
        $this->auth->complete_login($account);

        /* Show a message that they logged in, and goto the default page */
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
        try {
            $vcode = $account->generateVerifyCode(Verificationcode_Model::TYPE_VALIDATE_EMAIL);
        }
        catch (Verification_Exceeds_Exception $e) 
        {
            $this->addError(Kohana::lang('auth.too_many_verification'));
            $this->view->content = "";
            return;
        }

        /* Send email */
        $account->sendValidateEmail($vcode->original_code);

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
        
        $fields = array('email' => array('type'=>'text', 'required'=> true));
        $form = array('email' => '');
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
                /* Generate new code */
                try {
                    $vcode           = $account->generateVerifyCode(Verificationcode_Model::TYPE_EMAIL_CHANGE, $post['email']);
                }
                catch (Verification_Exceeds_Exception $e) 
                {
                    $this->addError(Kohana::lang('auth.too_many_verification'));
                    $this->view->content = "";
                    return;
                }

                /* Send out verification Email */
                $account->sendValidateEmail($vcode->original_code, 'emailChange');
                /* Tell the user what happened */
                $this->addMessage(Kohana::lang('auth.emailChangeSuccess'));
                /* Redirect to main page */
                url::redirect('user/index');
            }
            else
            {
                $errors = $post->errors();
            }
            
            $form = arr::overwrite($form, $post->as_array());
            $errors = $post->errors('form_error_messages');
        }
        $this->view->content = new View('user/emailChange', array('form'=>$form, 'errors'=>$errors, 'fields'=>$fields));
    }
    
    function changePassword()
    {
        /* Set page title */
        $this->view->title = "Change Password";
        $this->view->heading = Kohana::lang('auth.changePassword_heading');
        $this->view->subheading = Kohana::lang('auth.changePassword_subheading');
        /* Require login */
        $this->requireLogin();

        /* Get logged in account */
        $account = $this->auth->getAccount();
        
        $fields = array(
                'password'         => array('type'=>'password', 'required'=> true),
                'confirm_password' => array('type'=>'password', 'required'=> true),
        );
        $form = array(
                'password' => '',
                'confirm_password' => '',
        );
        $errors = array();

        if ($post = $this->input->post())
        {
            $post = new Validation($this->input->post());
            // uses PHP trim() to remove whitespace from beginning and end of all fields before validation
            $post->pre_filter('trim');
            $post->add_rules('password', 'required');
            $post->add_rules('password', 'length[6,255]');

            $post->add_rules('confirm_password', 'required');
            $post->add_rules('confirm_password',  'matches[password]');

            if ( $post->validate())
            {
                $account->salt = null;
                $account->password = $post['password'];
                /* Complete login, update hash, update timestamps, etc */
                $this->auth->complete_login($account);
                /* Tell the user what happened */
                $this->addMessage(Kohana::lang('auth.passwordChangeSuccess'));
                /* Redirect to main page */
                url::redirect('user/index');
            }
            else
            {
                $errors = $post->errors();
            }
            
            $form = arr::overwrite($form, $post->as_array());
            $errors = $post->errors('form_error_messages');
        }
        $this->view->content = new View('user/changePassword', array('form'=>$form, 'errors'=>$errors, 'fields'=>$fields));
    }

    function _findAccount(Validation $array, $field)
    {
        $exists = (bool)ORM::Factory('Account')
            ->where('email', $array[$field])
            ->count_all();

        if (!$exists)
            $array->add_error($field, 'email_not_exists' );
    }

    function lostPassword()
    {
        /* Set page title */
        $this->view->title = "Change Email";
        $this->view->heading = Kohana::lang('auth.lostPassword_heading');
        $this->view->subheading = Kohana::lang('auth.lostPassword_subheading');

        /* Get logged in account */
        $account = $this->auth->getAccount();
        
        $fields = array('email' => array('type'=>'text', 'required'=> true));
        $form = array('email' => '');
        $errors = array();

        if ($post = $this->input->post())
        {
            $post = new Validation($this->input->post());
            // uses PHP trim() to remove whitespace from beginning and end of all fields before validation
            $post->pre_filter('trim');

            $post->add_rules('email', 'required', array('valid', 'email'));
            $post->add_callbacks('email', array($this, '_findAccount'));

            if ( $post->validate())
            {
                $account = ORM::Factory('Account')
                    ->where('email', $post['email'])
                    ->find();

                /* Generate new code */
                try {
                    $vcode           = $account->generateVerifyCode(Verificationcode_Model::TYPE_LOST_PASSWORD, $post['email']);
                }
                catch (Verification_Exceeds_Exception $e) 
                {
                    $this->addError(Kohana::lang('auth.too_many_verification'));
                    $this->view->content = "";
                    return;
                }

                /* Send out verification Email */
                $account->sendValidateEmail($vcode->original_code, 'lostPassword');
                /* Tell the user what happened */
                $this->addMessage(Kohana::lang('auth.lostPasswordSuccess'));
                /* Show empty page */
                $this->view->content = "";
                return;
            }
            else
            {
                $errors = $post->errors();
            }
            
            $form = arr::overwrite($form, $post->as_array());
            $errors = $post->errors('form_error_messages');
        }
        $this->view->content = new View('user/lostPassword', array('form'=>$form, 'errors'=>$errors, 'fields'=>$fields));
    }
}

