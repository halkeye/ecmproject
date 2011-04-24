<?php

/**
 * User Controller
 * 
 * All user related webpage and functionality.
 * @author Gavin Mogan <ecm@gavinmogan.com>
 * @version 1.0
 * @package ecm
 */


class Controller_User extends Base_MainTemplate 
{
    function action_index()
    {
        $this->requireLogin();		
		$this->addMenuItem(
                array('title'=>'Add Registration', 'url'=>Controller_Convention::STEP1)
        );
		$reg = Model_Registration::getAllRegistrationsByConvention($this->auth->getAccount()->id);		
				
        $this->template->content = new View('user/index', array('registrations'=>$reg));
    }

    function action_login()
    {
        $this->template->title      = 'My Index Title';
        $this->template->subHeading = "Login Page";

        if ($this->auth->is_logged_in()) 
        {
            $this->addMessage(__('auth.already_logged_in'));
            $this->_redirect('');
            return;
        }

        // Load the user
        $user = ORM::factory('account')->where('email', '=', $this->request->post('email'))->find();
        if ($this->auth->login($user, $this->request->post('password')))
        {
            $this->addMessage(__('Welcome, ') . ' ' . html::chars($user->gname) . ' ' . html::chars($user->sname) . '!');
            $this->_redirect('');
            return;
        }

        foreach ($this->auth->errors() as $err) 
            $this->addError($err);
        $this->auth->clearErrors();

        return $this->action_loginOrRegister();
    }

    function action_logout()
    {
        $this->requireLogin();
        if (!$this->auth->is_logged_in()) 
        {
            $this->addMessage(__('auth.not_logged_in'));
            $this->request->redirect('');
            return;
        }

        $this->template->content = new View('user/logout');

        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $this->auth->logout();
            $this->_redirect('');
        }
        return;
    }

    function action_register()
    {
        // using the factory enables method chaining
        $form = array(
                'email'     => '',
                'name'     => '',
                'phone'     => '',
                'password'  => '',
                'confirm_password'  => '',
        );
        
        $errors = $form;

        if ($post = $this->request->post())
        {
            try {
                $account = ORM::factory('Account');
                $account->values($post);

                $extra_validation = Validation::Factory($post);
                $extra_validation->rule('password', 'matches', array(':validation', 'password', 'confirm_password'));
                $account->save($extra_validation);

                $vcode = $account->generateVerifyCode(Model_Verificationcode::TYPE_VALIDATE_EMAIL);
                $account->sendValidateEmail($vcode->original_code);

                $this->auth->complete_login($account);
                $this->addMessage(__('ecmproject.registration_success_message'));
                $this->_redirect('');
                return;
            }
            catch (ORM_Validation_Exception $e)
            {
                // repopulate the form fields
                $form = arr::overwrite($form, $post);

                // populate the error fields, if any
                // We need to already have created an error message file, for Kohana to use
                // Pass the error message file name to the errors() method
                $errors = arr::overwrite($errors, $e->errors('form_error_messages'));
            }
            catch (Verification_Exceeds_Exception $e) 
            {
                $this->addError(__('auth.too_many_verification'));
                $this->template->content = "";
                return;
            }
        }
        return $this->action_loginOrRegister( array('form'=>$form, 'errors'=>$errors) );
    }

    function action_validate($uid = NULL, $key = '')
    {
        $this->template->title = "Verification";

        if ($uid === NULL) 
        {
            $this->requireLogin();
            $account = $this->auth->getAccount();
            if ($account) { $uid = $account->id; }
        }
        else 
        {
            $account = ORM::factory('account')->where('id','=', $uid)->find(); 
        }
        if (!$key) { $key = $this->request->post('verifyCode'); }

        /* Validate incomingness */
        /* We have no account */
        if (!$account || !$account->loaded()) $this->_badVerify();
        if (!$key)     $this->_badVerify();

        $vcode = ORM::Factory('verificationcode')
            ->where('account_id','=', $uid)
            ->where('code','=',sha1($account->salt.$key))
            ->find();
        
        /* We don't have a valid code */
        if (!$vcode->loaded()) $this->_badVerify();

        /* Verify the account */
        if ($vcode->type == Model_Verificationcode::TYPE_EMAIL_CHANGE)
        {
            $account->email = $vcode->value;
        }
        $account->validateAccount();
        /* Complete login saves the account when it updates the login time */
        $this->auth->complete_login($account);

        /* Show a message that they logged in, and goto the default page */
        $this->addMessage(__('auth.verification_success'));
        $this->_redirect('');
    }
        
    function _badVerify()
    {
        $this->addError(__('auth.bad_link')); 
        return $this->request->redirect('/user/verifyMenu');
    }
    
    function action_loginOrRegister($data = array())
    {
        if (!is_array($data)) $data = array();
		$this->template->heading = 		__('Login or Register');
        $this->template->subheading = 	__('Login with an existing account or register for a new one.');

        $this->template->content = new View('user/loginOrRegister', $data);
        return;
    }

    function action_verifyMenu()
    {
        $this->template->title = "Require Verification";
        $this->template->heading = __('auth.verifyMenu_heading');
        $this->template->subheading = __('auth.verifyMenu_subheading');
        $this->requireLogin();
        $data = array();

        $this->template->content = new View('user/verifyMenu', $data);
        return;
    }

    function action_resendVerification()
    {
        /* Set page title */
        $this->template->title = "Require Verification";
        /* Require login */
        $this->requireLogin();

        /* Get logged in account */
        $account = $this->auth->getAccount();
        /* Generate new code */
        try {
            $vcode = $account->generateVerifyCode(Model_Verificationcode::TYPE_VALIDATE_EMAIL);
        }
        catch (Verification_Exceeds_Exception $e) 
        {
            $this->addError(__('auth.too_many_verification'));
            $this->template->content = "";
            return;
        }

        /* Send email */
        $account->sendValidateEmail($vcode->original_code);

        $this->addMessage(__('auth.sendVerificationMessage', array('mail'=>$account->email))); 
        return $this->request->redirect('/user/verifyMenu');
    }

    function action_changeEmail()
    {
        /* Set page title */
        $this->template->title = "Change Email";
        $this->template->heading = __('auth.changeEmail_heading');
        $this->template->subheading = __('auth.changeEmail_subheading');
        /* Require login */
        $this->requireLogin();

        /* Get logged in account */
        $account = $this->auth->getAccount();
        
        $fields = array('email' => array('type'=>'text', 'required'=> true));
        $form = array('email' => '');
        $errors = array();

        if ($post = $this->request->post())
        {
            $post = new Validation($this->request->post());
            // uses PHP trim() to remove whitespace from beginning and end of all fields before validation
            $post->pre_filter('trim');

            $post->add_rules('email', 'required', array('valid', 'email'));
            $post->add_callbacks('email', array($account,'_unique_email_validation'));

            if ( $post->validate())
            {
                /* Generate new code */
                try {
                    $vcode           = $account->generateVerifyCode(Model_Verificationcode::TYPE_EMAIL_CHANGE, $post['email']);
                }
                catch (Verification_Exceeds_Exception $e) 
                {
                    $this->addError(__('auth.too_many_verification'));
                    $this->template->content = "";
                    return;
                }

                /* Send out verification Email */
                $account->sendValidateEmail($vcode->original_code, 'emailChange');
                /* Tell the user what happened */
                $this->addMessage(__('auth.emailChangeSuccess'));
                /* Redirect to main page */
                $this->request->redirect('user/index');
            }
            else
            {
                $errors = $post->errors();
            }
            
            $form = arr::overwrite($form, $post->as_array());
            $errors = $post->errors('form_error_messages');
        }
        $this->template->content = new View('user/emailChange', array('form'=>$form, 'errors'=>$errors, 'fields'=>$fields));
    }
    
    function action_changePassword()
    {
        /* Set page title */
        $this->template->title = "Change Password";
        $this->template->heading = __('auth.changePassword_heading');
        $this->template->subheading = __('auth.changePassword_subheading');
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

        if ($post = $this->request->post())
        {
            $post = new Validation($this->request->post());
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
                $this->addMessage(__('auth.passwordChangeSuccess'));
                /* Redirect to main page */
                $this->request->redirect('user/index');
            }
            else
            {
                $errors = $post->errors();
            }
            
            $form = arr::overwrite($form, $post->as_array());
            $errors = $post->errors('form_error_messages');
        }
        $this->template->content = new View('user/changePassword', array('form'=>$form, 'errors'=>$errors, 'fields'=>$fields));
    }

    function _findAccount(Validation $array, $field)
    {
        $exists = (bool)ORM::Factory('Account')
            ->where('email', $array[$field])
            ->count_all();

        if (!$exists)
            $array->add_error($field, 'email_not_exists' );
    }

    function action_lostPassword()
    {
        /* Set page title */
        $this->template->title = "Change Email";
        $this->template->heading = __('auth.lostPassword_heading');
        $this->template->subheading = __('auth.lostPassword_subheading');

        /* Get logged in account */
        $account = $this->auth->getAccount();
        
        $fields = array('email' => array('type'=>'text', 'required'=> true));
        $form = array('email' => '');
        $errors = array();

        if ($post = $this->request->post())
        {
            $post = new Validation($this->request->post());
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
                    $vcode           = $account->generateVerifyCode(Model_Verificationcode::TYPE_LOST_PASSWORD, $post['email']);
                }
                catch (Verification_Exceeds_Exception $e) 
                {
                    $this->addError(__('auth.too_many_verification'));
                    $this->template->content = "";
                    return;
                }

                /* Send out verification Email */
                $account->sendValidateEmail($vcode->original_code, 'lostPassword');
                /* Tell the user what happened */
                $this->addMessage(__('auth.lostPasswordSuccess'));
                /* Show empty page */
                $this->template->content = "";
                return;
            }
            else
            {
                $errors = $post->errors();
            }
            
            $form = arr::overwrite($form, $post->as_array());
            $errors = $post->errors('form_error_messages');
        }
        $this->template->content = new View('user/lostPassword', array('form'=>$form, 'errors'=>$errors, 'fields'=>$fields));
    }
    function action_testEmail()
    {

        $account = ORM::Factory('account')->where('id','=',55)->find();
        if (!$account->loaded())
            die("not loaded");
        $vcode = $account->generateVerifyCode(Model_Verificationcode::TYPE_VALIDATE_EMAIL);
        $account->sendValidateEmail($vcode->original_code);
    }
}

