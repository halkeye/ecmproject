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
		$account = $this->auth->getAccount();
		$reg = Model_Registration::getAllRegistrationsByConvention($account->id);		
		
		$this->template->heading      = __('Account:') . ' ' . html::chars($account->gname) . ' ' . html::chars($account->sname);
        $this->template->subheading = "Account settings as well as current and past ticket purchases.";
		
        $this->template->content = new View('user/index', array('registrations'=>$reg));
    }

    function action_login()
    {
        $this->template->title      = 'My Index Title';
        $this->template->subHeading = "Login Page";

        if ($this->auth->is_logged_in()) 
        {
            $this->addMessage(__('You are already logged in!'));
            $this->_redirect('');
            return;
        }

        // Load the user
		$data = array();
        $user = ORM::factory('account')->where('email', '=', $this->request->post('email'))->find();
        if ($this->auth->login($user, $this->request->post('password')))
        {
            $this->addMessage(__('Welcome, ') . ' ' . html::chars($user->gname) . ' ' . html::chars($user->sname) . '!');
            $this->_redirect('');
            return;
        }
		else 
		{
			$data['email'] = $this->request->post('email');
		}

        foreach ($this->auth->errors() as $err) 
            $this->addError($err);
        $this->auth->clearErrors();

        return $this->action_loginOrRegister($data);
    }

    function action_logout()
    {
        $this->requireLogin();
        if (!$this->auth->is_logged_in()) 
        {
            $this->addMessage(__('You are not logged in.'));
            $this->request->redirect('');
            return;
        }

        $this->auth->logout();
		$this->addMessage(__('You have been logged out.'));
        $this->_redirect('');       
        return;
    }

    function action_register()
    {
        // using the factory enables method chaining
        $form = array(
                'email'     => '',
                'sname'     => '',
				'gname'		=> '',
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

                $vcode = $account->generateVerifyCode(Model_Verificationcode::TYPE_VALIDATE_EMAIL, '');
                $account->sendValidateEmail($vcode->original_code);

                $this->auth->complete_login($account);
                $this->addMessage(__('A verification email has been sent to your account. Please click the link in the email to activate your account.'));
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
				$error_list = $e->errors('');
                $errors = arr::overwrite($errors, $error_list);
				
				if ( !empty($error_list['_external']['password']) ) {
					$errors['confirm_password'] = $error_list['_external']['password']; //This is a hack!
				}
				
            }
            catch (Verification_Exceeds_Exception $e) 
            {
                $this->addError(__('Too many verification codes requested! Check your junk mail.'));
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
        $this->addMessage(__('Email address successfully validated! You can use your account now.'));
        $this->_redirect('');
    }
        
    function _badVerify()
    {
        $this->addError(__('The verification link used is not valid. Please double check the link or request a new validation code.')); 
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
        $this->template->title = 		__('Verification Required');
        $this->template->heading =		__('Verification Required');
        $this->template->subheading = 	__('The email address associated to this account must be verified first.');
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
            $this->addError(__('Too many verification codes have been sent already! Please check your inbox again (or junk mail)'));
            $this->template->content = "";
            return;
        }

        /* Send email */
        $account->sendValidateEmail($vcode->original_code);

        $this->addMessage(__('A verification email has been sent to your email address.', array('mail'=>$account->email))); 
        return $this->request->redirect('/user/verifyMenu');
    }

    function action_changeEmail()
    {
        /* Set page title */
        $this->template->title = 		__('Change Email');
        $this->template->heading =  	__('Change your Email Address');
        $this->template->subheading = 	__('Change your email address. You will need to revalidate.');
		
        /* Require login */
        $this->requireLogin();

        /* Get logged in account */
        $account = $this->auth->getAccount();
        
        $fields = array('email' => $account->default_fields['email']);
        $form = array('email' => '');
        $errors = $form;

        if ($post = $this->request->post())
        {
			$account->values($post); 
			
			try {
				$account->status = Model_Account::ACCOUNT_STATUS_UNVERIFIED;
				$account->save();
				
				$vcode = $account->generateVerifyCode(Model_Verificationcode::TYPE_EMAIL_CHANGE, $post['email']);
				$account->sendValidateEmail($vcode->original_code, 'emailChange');
				$this->addMessage("Email changed successfully. An email has been sent to your new email address.");
				$this->request->redirect('user/index');
			}
			catch (ORM_Validation_Exception $e)
			{		
				// repopulate the form fields
				$form = arr::overwrite($form, $post);

				// populate the error fields, if any
				// We need to already have created an error message file, for Kohana to use
				// Pass the error message file name to the errors() method
				$error_list = $e->errors('form_error_messages');				
				$errors = arr::overwrite($errors, $error_list);
				
				if ( !empty($error_list['_external']['password']) ) {
					$errors['confirm_password'] = $error_list['_external']['password']; //This is a hack!
				}
				
			}	
			catch (Verification_Exceeds_Exception $e) 
			{
				$this->addError(__('Too many verification codes have been sent already! Please check your inbox again (or junk mail)'));
				$this->template->content = "";
				return;
			}			
			catch (Exception $e)
			{
				$this->addError("Oops. Something went wrong and it's not your fault. Contact the system maintainer please!");
			}
        }
        $this->template->content = new View('user/emailChange', array('form'=>$form, 'errors'=>$errors, 'fields'=>$fields));
    }
    
    function action_changePassword()  {
        /* Set page title */
        $this->template->title = 		__('Change Password');
        $this->template->heading = 		__('Change Your Password');
        $this->template->subheading = 	__('Change your account password.');
		
        /* Require login */
        $this->requireLogin();

        /* Get logged in account */
        $account = $this->auth->getAccount();
        
        $fields = $account->default_fields;
		
        $form = array(
                'password' => '',
                'confirm_password' => '',
        );
		
        $errors = $form;
		if ($post = $this->request->post()) {
			$extra_validation = Validation::Factory($post);
			$account->values($post);
			
			try {				
				$extra_validation->rule('password', 'matches', array(':validation', 'password', 'confirm_password'));
				$account->save($extra_validation);
				$this->addMessage('Successfully changed the password for ' . $account->email);
				
				/* Complete login, update hash, update timestamps, etc */
				$account->salt = null;
				$account->password = $post['password'];
				$this->auth->complete_login($account);					
				$this->request->redirect('user/index');	
				return;
			}
			catch (ORM_Validation_Exception $e)
			{		
				// repopulate the form fields
				$form = arr::overwrite($form, $post);

				// populate the error fields, if any
				// We need to already have created an error message file, for Kohana to use
				// Pass the error message file name to the errors() method
				$error_list = $e->errors('form_error_messages');				
				$errors = arr::overwrite($errors, $error_list);
				
				if ( !empty($error_list['_external']['password']) ) {
					$errors['confirm_password'] = $error_list['_external']['password']; //This is a hack!
				}
				
			}			
			catch (Exception $e)
			{
				$this->addError("Oops. Something went wrong and it's not your fault. Contact the system maintainer please!");
			}
			
		}
		
        $this->template->content = new View('user/changePassword', array('form'=>$form, 'errors'=>$errors, 'fields'=>$fields));
    }
	function action_changeName() {
		/* Set page title */
        $this->template->title = 		__('Change Your Name');
        $this->template->heading = 		__('Change Your Name');
        $this->template->subheading = 	__('Change the name associated to this account.');
		
        /* Require login */
        $this->requireLogin();

        /* Get logged in account */
        $account = $this->auth->getAccount();
        
        $fields = $account->default_fields;
		
        $form = array(
                'gname' => '',
                'sname' => '',
        );
		
		$errors = $form;
		if ($post = $this->request->post()) { 
			$account->values($post);
			
			try {				
				$account->save();
				$this->addMessage('Successfully changed your name to ' . $account->gname . ' ' . $account->sname);		
				$this->request->redirect('user/index');	
				return;
			}
			catch (ORM_Validation_Exception $e)
			{		
				// repopulate the form fields
				$form = arr::overwrite($form, $post);

				// populate the error fields, if any
				// We need to already have created an error message file, for Kohana to use
				// Pass the error message file name to the errors() method
				$error_list = $e->errors('form_error_messages');				
				$errors = arr::overwrite($errors, $error_list);
				
				if ( !empty($error_list['_external']['password']) ) {
					$errors['confirm_password'] = $error_list['_external']['password']; //This is a hack!
				}
				
			}			
		}
		else {
			$form['gname'] = $account->gname;
			$form['sname'] = $account->sname;
		}
	
		$this->template->content = new View('user/changeName', array('form'=>$form, 'errors'=>$errors, 'fields'=>$fields));
	}
	
    function action_lostPassword()
    {
        /* Set page title */
        $this->template->title = 		__('Password Recovery');
        $this->template->heading = 		__('Password Recovery');
        $this->template->subheading = 	__('Send a password recovery email to yourself...');

        /* Get logged in account - is there a point in doing this? It's just going to get overwritten below. */
        //$account = $this->auth->getAccount();
        
        $fields = ORM::Factory('Account')->default_fields;
        $form = array('email' => '');
        $errors = array();

        if ( $post = $this->request->post() )
        {			
			//Query builder escapes parameters. 
            $account = ORM::Factory('Account')
				->where( 'email', '=', trim ( $post['email'] ))
				->find();

			if ( $account->loaded() ) 	
			{
				/* Generate new code */
                try {
                    $vcode           = $account->generateVerifyCode(Model_Verificationcode::TYPE_LOST_PASSWORD, $post['email']);
                }
                catch (Verification_Exceeds_Exception $e) 
                {
                    $this->addError(__('Too many verification codes have been sent already! Please check your inbox again (or junk mail)'));
                    $this->template->content = "";
                    return;
                }

                /* Send out verification Email */
                $account->sendValidateEmail($vcode->original_code, 'lostPassword');
                /* Tell the user what happened */
                $this->addMessage(__('auth.lostPasswordSuccess'));				
				/* Redirect user to root page */
                $this->request->redirect('user');
                return;			
			}
			else
			{
				//Do generic error here.
				$this->addError('Sorry, but there is no account associated with the email you provided.');
			}
            
            $form = arr::overwrite($form, $post);
            //$errors = $post->errors('form_error_messages');
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

