<?php

/**
 * Admin Controller
 * 
 * All admin functionality is implemented here.
 * @author Stephen Tiu <stt@sfu.ca>
 * @version 1.0
 * @package ecm
 */


class Controller_Admin extends Base_MainTemplate 
{ 
	const ROWS_PER_PAGE = 10;
    const ADMIN_USERGROUP = 'Administrator';

	function before()
	{
		$ret = parent::before();
		$this->requireLogin();
		$this->requirePermission('admin'); //Force requirement of full administrative access minimum.
	
		$this->addMenuItem(array('title'=>'Add Registration', 'url'=>'convention/editReg'));

		return $ret;
	}

	function action_index()
	{
		$this->template->title = __('admin.admin_area');
		$this->template->heading = __('admin.admin_area');
		$this->template->subheading = __('admin.admin_area_desc');
					
		$this->template->content = new View('admin/main');
	}

	function action_manageConventions($page = NULL)
	{
		// Set headers
		$this->template->title = "Administration: Manage Conventions";
		$this->template->heading = "Administration: Manage Conventions";
		$this->template->subheading = "Create, edit and delete conventions";
				
		$total_rows = Model_Convention::getTotalConventions();
								
		// Calculate the offset.
		$start = ( Controller_Admin::getMultiplier($page) * Controller_Admin::ROWS_PER_PAGE );	
		$rows = ORM::factory('Convention')->find_all( Controller_Admin::ROWS_PER_PAGE, $start );
			
		// Extra validation.
		if (count($rows) == 0 && $total_rows > 0)
		{
			$this->addError('Invalid page number.');
		}
		else if ($total_rows == 0)
		{
			$this->addError("You need to setup a convention for starters.");
		}			
		
		// Header entry. (View with no data generates a header)
		$data['entries'][0] = new View('admin/ListItems/ConventionEntry');
		foreach ($rows as $row)
		{
            $data['actions']['edit'] = html::anchor(
                'admin/editConvention/'. $row->id,
                html::image(url::site('/static/img/edit-copy.png', TRUE), array('title'=>_('admin.edit_convention')))
            );
            $data['actions']['delete'] = html::anchor(
                'admin/deleteConvention/' . $row->id,
                html::image(url::site('/static/img/edit-delete.png',TRUE), array('title'=>__('admin.delete_convention')))
            );
            $data['entries'][$row->id] = new View(
                'admin/ListItems/ConventionEntry', 
                array('row' => $row, 'actions' => $data['actions'])
            );
		}	
		
		// Set callback path for form submit (change convention, jump to page)
		$this->template->content = new View('admin/list', array(
				'entity' => 'Convention',
				'callback' => 'admin/manageConventions', 
				'createText' => 'Create new Convention',
				'createLink' => 'admin/createConvention', 
				'rows' => $data['entries'], 
				'page' => $page,
				'total_rows' => $total_rows)
			);	
	}
	
	function manageAccounts($page = NULL)
	{
		// Set headers
		$this->template->title = "Administration: Manage Accounts";
		$this->template->heading = "Administration: Manage Accounts";
		$this->template->subheading = "Create, edit and delete accounts";
				
		$total_rows = Model_Account::getTotalAccounts();
								
		// Calculate the offset.
		$start = ( Controller_Admin::getMultiplier($page) * Controller_Admin::ROWS_PER_PAGE );	
		$rows = ORM::factory('Account')->find_all( Controller_Admin::ROWS_PER_PAGE, $start );
			
		// Extra validation.
		if (count($rows) == 0 && $total_rows > 0)
		{
			$this->addError('Invalid page number.');
		}
		else if ($total_rows == 0)
		{
			//TODO: Add string to the language file.
			$this->addError("D: No one's used our system yet! (You shouldn't be getting this - if you are, something's wrong) D:");
		}			
		
		// Header entry. (View with no data generates a header)
		$data['entries'][0] = new View('admin/ListItems/AccountEntry');
		foreach ($rows as $row)
		{
			$data['actions']['edit'] = html::anchor('admin/editAccount/'. $row->id, html::image(url::site('/static/img/edit-copy.png'), __('admin.edit_account')));
			$data['actions']['delete'] = html::anchor('admin/deleteAccount/' . $row->id, html::image(url::site('/static/img/edit-delete.png'), __('admin.delete_account')));			
			$data['entries'][$row->id] = new View('admin/ListItems/AccountEntry', array('row' => $row, 'actions' => $data['actions']));				
		}	
		
		// Set callback path for form submit (change convention, jump to page)
	
		$this->template->content = new View('admin/list', array(
				'entity' => 'Account',
				'callback' => 'admin/manageAccounts', 
				'createText' => 'Create new Account',
				'createLink' => 'admin/createAccount', 
				'rows' => $data['entries'], 
				'page' => $page,
				'total_rows' => $total_rows)
			);
	}
	
	function managePasses($convention_id = NULL, $page = NULL) 
	{
		// Set headers
		$this->template->title = "Administration: Manage Passes";
		$this->template->heading = "Administration: Manage Passes";
		$this->template->subheading = "Create, edit and delete passes related to any convention";
			
							
		// Get all conventions, determine and validate convention_id, and get total number of passes for the particular convention_id.
		$crows = ORM::factory('Convention')->find_all();	
		$convention_id = Controller_Admin::getConventionId($convention_id, $crows);						
		$crows = $crows->select_list('id', 'name');					
		$total_rows = Model_Pass::getTotalPasses($convention_id);
							
							
		// Calculate the offset.
		$start = ( Controller_Admin::getMultiplier($page) * Controller_Admin::ROWS_PER_PAGE );	
		$rows = ORM::factory('Pass')->where("convention_id = $convention_id")->find_all( Controller_Admin::ROWS_PER_PAGE, $start );
			
		// Extra validation.
		if (count($rows) == 0 && $total_rows > 0)
		{
			$this->addError('Invalid page number.');
		}
		else if ($total_rows == 0)
		{
			$this->addError("This convention has no passes setup!");
		}			
		
		// Header entry. (View with no data generates a header)
		$data['entries'][0] = new View('admin/ListItems/PassEntry');
		foreach ($rows as $row)
		{
			$data['actions']['edit'] = html::anchor('admin/editPass/'. $row->id, html::image(url::site('/static/img/edit-copy.png'), __('admin.edit_account')));
			$data['actions']['delete'] = html::anchor('admin/deletePass/' . $row->id, html::image(url::site('/static/img/edit-delete.png'), __('admin.delete_account')));			
			$data['entries'][$row->id] = new View('admin/ListItems/PassEntry', array('row' => $row, 'actions' => $data['actions']));				
		}	
		
		// Set callback path for form submit (change convention, jump to page)
		$this->template->content = new View('admin/list', array(
				'entity' => 'Pass',
				'crows' => $crows, 
				'callback' => 'admin/managePasses', 
				'createText' => 'Create new Pass',
				'createLink' => 'admin/createPass',
				'rows' => $data['entries'], 
				'convention_id' => $convention_id,
				'page' => $page,
				'total_rows' => $total_rows)
			);
	}
	
	function manageRegistrations($convention_id = NULL, $page = NULL)
	{
		// Set headers
		$this->template->title = "Administration: Manage Registrations";
		$this->template->heading = "Administration: Manage Registrations";
		$this->template->subheading = "Create, edit and delete registrations related to a convention";
		
		
		// Get all conventions, determine and validate convention_id, and get total number of passes for the particular convention_id.
		$crows = ORM::factory('Convention')->find_all();	
		$convention_id = Controller_Admin::getConventionId($convention_id, $crows);						
		$crows = $crows->select_list('id', 'name');					
		$total_rows = Model_Registration::getTotalRegistrations($convention_id);
		
		// Calculate the offset.
		$start = ( Controller_Admin::getMultiplier($page) * Controller_Admin::ROWS_PER_PAGE );	
		$rows = ORM::factory('Registration')->where("convention_id = $convention_id")->find_all( Controller_Admin::ROWS_PER_PAGE, $start );
			
		// Extra validation.
		if (count($rows) == 0 && $total_rows > 0)
		{
			$this->addError('Invalid page number.');
		}
		else if ($total_rows == 0)
		{
			$this->addError("This convention has no registrations.");
		}			
		
		// Header entry. (View with no data generates a header)
		$data['entries'][0] = new View('admin/ListItems/RegistrationEntry');
		foreach ($rows as $row)
		{
			$data['actions']['edit'] = html::anchor('admin/editRegistration/'. $row->id, html::image(url::site('/static/img/edit-copy.png'), __('admin.edit_account')));
			$data['actions']['delete'] = html::anchor('admin/deleteRegistration/' . $row->id, html::image(url::site('/static/img/edit-delete.png'), __('admin.delete_account')));			
			$data['entries'][$row->id] = new View('admin/ListItems/RegistrationEntry', array('row' => $row, 'actions' => $data['actions']));				
		}	
		
		// Set callback path for form submit (change convention, jump to page)
		$this->template->content = new View('admin/list', array(
				'entity' => 'Registration',
				'crows' => $crows, 
				'callback' => 'admin/manageRegistrations', 
				'createText' => 'New Registration',
				'createLink' => 'admin/createRegistration',
				'rows' => $data['entries'], 
				'convention_id' => $convention_id,
				'page' => $page,
				'total_rows' => $total_rows)
			);	
	}
	
	function managePayments($rid = NULL)
	{
		if (!isset($rid) || !is_numeric($rid))
			die('Get out of here!');
			
		//Get registration and then get associated payment entries (if any)	
		$reg = ORM::Factory('Registration')->find($rid);
		if (! $reg->loaded() )
		{
			$this->addError('Invalid registration. Maybe someone deleted it when you weren\'t looking?');
			$this->request->redirect('manageRegistrations');
		}		

		$rows = ORM::Factory('Payment')->where("register_id=$rid")->find_all();
		$pass = ORM::Factory('Pass')->find($reg->pass_id);
		if (count($rows) == 0)
		{
			$this->addError("This person hasn't paid anything yet!");
		}		

		$this->template->title = "Administration: Manage Payments";
		$this->template->heading = $reg->gname . ' ' . $reg->sname . ' (' . $reg->badge . ') ';
		$this->template->subheading = $pass->name;
		
		$data['entries'][0] = new View('admin/ListItems/PaymentEntry');
		foreach ($rows as $row)
		{
			$data['actions']['edit'] = html::anchor('admin/editPayment/'. $row->id, html::image(url::site('/static/img/edit-copy.png'), __('admin.edit_account')));
			$data['actions']['delete'] = html::anchor('admin/deletePayment/' . $rid . '/' . $row->id, html::image(url::site('/static/img/edit-delete.png'), __('admin.delete_account')));			
			$data['entries'][$row->id] = new View('admin/ListItems/PaymentEntry', array('row' => $row, 'actions' => $data['actions']));				
		}			
			
		$this->template->content = new View('admin/PaymentList', array(
				'reg' => $reg->as_array(),
				'pass' => $pass->as_array(),
				'callback' => 'admin/managePayments', 
				'createText' => 'Create Payment',
				'createLink' => "admin/createPayment/$rid",
				'rows' => $data['entries'])
			);
	}
	
	/*
	* List accounts with admin powers. Only action is to remove account from list of administrators or to add one.
	*
	*
	*/
	function manageAdmin()
	{
		$this->requirePermission('superAdmin'); //Require extra permissions to manage administrators.
		
		// Set headers
		$this->template->title = "Administration: Manage Admins";
		$this->template->heading = "Administration: Manage Admins";
		$this->template->subheading = "Add or remove admin users.";
				
		//$total_rows = Model_Account::getTotalAdminAccounts(); //Modify to getTotalAccounts with admin permissions.
								
		// Calculate the offset.
		//$start = ( Controller_Admin::getMultiplier($page) * Controller_Admin::ROWS_PER_PAGE );	
		$rows = ORM::factory('Account')
            ->join('accounts_usergroups', 'accounts_usergroups.account_id', 'accounts.id')
            ->join('usergroups', 'accounts_usergroups.usergroup_id', 'usergroups.id')
            ->where('usergroups.name', Controller_Admin::ADMIN_USERGROUP)
            //->limit(
            ->find_all(); 
			
		// Header entry. (View with no data generates a header)
		$data['entries'][0] = new View('admin/ListItems/AdminAccountEntry');
		foreach ($rows as $row)
		{
            $data['actions']['edit'] = html::anchor('admin/deleteAdmin/' . $row->id, html::image(url::site('/static/img/edit-delete.png'), __('admin.edit_account')));
            //$data['actions']['delete'] = html::anchor('admin/deleteAccount/' . $row->id, html::image(url::site('/static/img/edit-delete.png'), __('admin.delete_account')));			
            $data['entries'][$row->id] = new View('admin/ListItems/AdminAccountEntry', array('row' => $row, 'actions' => $data['actions']));				
		}	
		
		// Set callback path for form submit (change convention, jump to page)
	
		$this->template->content = new View('admin/list', array(
				'entity' => 'Account',
				'callback' => 'admin/manageAccounts', 
				'createText' => 'Add Admin',
				'createLink' => 'admin/setAdmin', 
				'rows' => $data['entries'], 
				'page' => 1,
				'total_rows' => count($rows))
			);
	}
	
	function createAccount() 
	{
		$this->template->title = "Administration: Create an Account";
		$this->template->heading = "Administration: Create an Account";
		$this->template->subheading = "Create an Account for a convention.";

		$acct = ORM::factory('Account');	
		$fields = $acct->default_fields;
		$fields['status']['values'] = Model_Account::getVerifySelectList();
		
		if ($post = $this->request->post())
		{			
			if ($acct->validate_admin($post, false, true))
			{							
				$acct->save();				
				if ($acct->saved()) {
					$this->addMessage('Created a newly minted account with email: ' . $acct->email);
					$this->request->redirect('admin/manageAccounts');
				}
				else
				{
					$this->addError("Oops. Something went wrong and it's not your fault. Contact the system maintainer please!");
				}				
			}
					
			$errorMsg = 'Oops. You entered something bad! Please fix it! <br />';				
			$errors = $post->errors('form_error_messages');
			foreach ($errors as $error)
				$errorMsg = $errorMsg . ' ' . $error . '<br />';					
		
			$this->addError($errorMsg);					
			
			$this->template->content = new View('admin/Account', array(
				'row' => $post,
				'fields' => $fields,
				'callback' => 'createAccount'
			)); 
					
		} 
		else 
		{		
			$this->template->content = new View('admin/Account', array(
				'row' => $acct->as_array(),
				'fields' => $fields,
				'callback' => 'createAccount'
			));
		}
	}
	
	function createPass()
	{
		// Set headers
		$this->template->title = "Administration: Create a Pass";
		$this->template->heading = "Administration: Create a Pass";
		$this->template->subheading = "Create a pass for a convention.";
		
		$pass = ORM::factory('Pass');
		$crows = ORM::factory('Convention')->find_all()->select_list('id', 'name');		
		$fields = $pass->default_fields;
		$fields['convention_id']['values'] = $crows;
		
		if ($post = $this->request->post())
		{
			$post['startDate'] = Controller_Admin::parseSplitDate($post, 'startDate');
			$post['endDate'] = Controller_Admin::parseSplitDate($post, 'endDate');
			
			if ($pass->validate_admin($post))
			{			
				$pass->startDate = strtotime($post['startDate']);
				$pass->endDate = strtotime($post['endDate']);
				
				$pass->save();				
				if ($pass->saved()) {
					$this->addMessage('Created a newly minted pass called: ' . $pass->name);
					$this->request->redirect('admin/managePasses');
				}
				else
				{
					$this->addError("Oops. Something went wrong and it's not your fault. Contact the system maintainer please!");
				}
			}
						
			$errorMsg = 'Oops. You entered something bad! Please fix it! <br />';				
			$errors = $post->errors('form_error_messages');
			foreach ($errors as $error)
				$errorMsg = $errorMsg . ' ' . $error . '<br />';					
		
			$this->addError($errorMsg);
			
			$this->template->content = new View('admin/Pass', array(
				'crows' => $crows,	
				'row' => $post,
				'fields' => $fields,
				'callback' => 'createPass'
			));
					
		}	
		else {
			$this->template->content = new View('admin/Pass', array(
				'crows' => $crows,	
				'row' => $pass->as_array(),
				'fields' => $fields,
				'callback' => 'createPass'
			));
		}			
	}
	
	function action_createConvention() {
		// Set headers
		$this->template->title = "Administration: Create a Convention";
		$this->template->heading = "Administration: Create a Convention";
		$this->template->subheading = "Create a convention.";
		
		$conv = ORM::factory('Convention');	
		$fields = $conv->default_fields;
        $post = $conv->as_array();
		
		if ($post = $this->request->post())
		{			
			$post['start_date'] = Controller_Admin::parseSplitDate($post, 'start_date');
			$post['end_date'] = Controller_Admin::parseSplitDate($post, 'end_date');
		
            $conv->values($post);
            try {
                $conv->save();
                $this->addMessage('Created a newly minted convention named ' . $conv->name);
                $this->request->redirect('admin/manageConventions');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errorMsg = 'Oops. You entered something bad! Please fix it! <br />';				
                $errors = $e->errors('form_error_messages');
                foreach ($errors as $error)
                    $errorMsg = $errorMsg . ' ' . $error . '<br />';					
            
                $this->addError($errorMsg);					
            }				
            catch (Exception $e)
            {
                $this->addError("Oops. Something went wrong and it's not your fault. Contact the system maintainer please!");
            }
        }
        $this->template->content = new View('admin/Convention', array(
            'row' => $post,
            'fields' => $fields,
            'callback' => 'createConvention'
        )); 
    }
	
	/* Step 1 */
	function createRegistration() {
		// Set headers
		$this->template->title = "Administration: Create Registration";
		$this->template->heading = "Administration: Create Registration";
		$this->template->subheading = "Create a registration for a convention.";
	
		$reg = ORM::factory('Registration');
		$crows = ORM::factory('Convention')->find_all()->select_list('id', 'name');	
		
		$fields = $reg->formo_defaults;
		$fields['convention_id'] = array( 'type'  => 'select', 'label' => 'Convention', 'required'=>true );
		$fields['convention_id']['values'] = $crows;
	
		if ($post = $this->request->post())
		{
			//Validate valid convention id, validate account - create if not exist email. Then pass it on as a POST.
			if (Model_Convention::validConvention($post['convention_id']) && isset($post['email']) && !empty($post['email']))
			{
				$aid = Model_Account::createAccount($post['email']);
				if ($aid != -1)
					$this->request->redirect("admin/createRegistration2/" . $post['convention_id'] . "/$aid"); //Move to next STEP
				else
					$this->addError("Oops. An internal error occured. Try again.");
			}
						
			$this->addError("One or more fields are blank!");	//pass_field_reg_error_convention_id_missing	
			$this->template->content = new View('admin/Registration', array(
				'row' => $post,
				'fields' => $fields,
				'callback' => 'createRegistration'
			));
		}
		else 
		{
			$this->template->content = new View('admin/Registration', array(
				'row' => $reg->as_array(),
				'fields' => $fields,
				'callback' => 'createRegistration'
			));		
		}	
	}
	
	/* Step 2 */
	function createRegistration2($cid = NULL, $aid = NULL) {
	
		//Not allowed to be lazy in checking input here.
		if ( (!isset($cid) || !is_numeric($cid) || $cid <= 0) || (!isset($aid) || !is_numeric($aid) || $aid <= 0))
			die("You're not allowed to be here!");
			
		//TODO: Deal with the case where one of the below fails to load.
		$reg = ORM::factory('Registration');
		$crows = ORM::factory('Convention')->find_all()->select_list('id', 'name');	
		$fields = $reg->formo_defaults;
		
		$fields['pass_id']['values'] = ORM::Factory('Pass')->where("convention_id=$cid")->find_all()->select_list('id', 'name');
		
		if ($post = $this->request->post())
		{
			$fieldName = 'dob';
			$post[$fieldName] = implode('-', 
                        array(
                            @sprintf("%04d", $post[$fieldName . '-year']), 
                            @sprintf("%02d", $post[$fieldName . '-month']), 
                            @sprintf("%02d", $post[$fieldName . '-day'])
                        )
                    );		
		
			if ($reg->validate_admin($post))
			{			
				$reg->convention_id = $cid;
				$reg->account_id = $aid;
				$reg->save();				
				if ($reg->saved()) {
					$this->addMessage('Created a newly minted registration for: ' . $reg->gname . ' ' . $reg->sname);
					$this->request->redirect('admin/manageRegistrations');
				}
				else
				{
					$this->addError("Oops. Something went wrong and it's not your fault. Contact the system maintainer please!");
				}
			}
						
			$errorMsg = 'Oops. You entered something bad! Please fix it! <br />';				
			$errors = $post->errors('form_error_messages');
			foreach ($errors as $error)
				$errorMsg = $errorMsg . ' ' . $error . '<br />';					
		
			$this->addError($errorMsg);
			
			$this->template->content = new View('admin/Registration2', array(
				'row' => $post,
				'fields' => $fields,
				'callback' => "createRegistration2/$cid/$aid"
			));
		} else {
			$acct = ORM::factory('Account')->find($aid);
			if (!$acct->loaded())
				die('Serious error here. The account is supposed to exist but it doesn\'t.');
				
			$reg->email = $acct->email;
			
			/* Full registration at this step. */
			$this->template->content = new View('admin/Registration2', array(
					'row' => $reg->as_array(),
					'fields' => $fields,
					'callback' => "createRegistration2/$cid/$aid"
				));
		}
	}
	
	/*
	* $rid - Registration ID to add payment to. The rest can be determined using the registration ID.
	*/
	function createPayment($rid = NULL)
	{
		if ($rid == NULL || !is_numeric($rid))
			die('Get out of here!');
			
		$pay = ORM::Factory('Payment'); 
		$reg = ORM::Factory('Registration')->find($rid);
		$pass = ORM::Factory('Pass')->find($reg->pass_id);
		
		//TODO: Redirect properly.
		if (!$reg->loaded())
			die('Unable to retrieve registration');
		
		$fields = $pay->default_fields;
		$fields['type']['values'] = array('paypal' => 'Instant (Paypal)', 'mail' => 'Mail', 'inperson' => 'In Person');
		$fields['payment_status']['values'] = $pay->getPaymentStatusSelectList();	
			
		if ($post = $this->request->post())
		{
			if ($pay->validate_admin($post))
			{				
				$pay->register_id = $rid;
				$pay->last_modified = $this->auth->getAccount()->id;
				$pay->save();
				if ($pay->saved()) {
					$this->addMessage('Payment created for the amount of: ' . $pay->mc_gross . ' (' . $pay->type . ') ');
					
					/* Check status of payment for registration */				
					$this->updatePaymentStatus($reg, $pass);						
					$this->request->redirect("admin/managePayments/$rid");
				}
				else
				{
					$this->addError("Oops. Something went wrong and it's not your fault. Contact the system maintainer please!");
				}
			}		
			
			$errorMsg = 'Oops. You entered something bad! Please fix it! <br />';				
			$errors = $post->errors('form_error_messages');
			foreach ($errors as $error)
				$errorMsg = $errorMsg . ' ' . $error . '<br />';					
		
			$this->addError($errorMsg);

			//Do validation here.
			$this->template->content = new View('admin/Payment', array(
					'row' => $post,
					'fields' => $fields,
					'callback' => "createPayment/$rid"
				));
		}
		else
		{
			$this->template->content = new View('admin/Payment', array(
					'row' => $pay->as_array(),
					'fields' => $fields,
					'callback' => "createPayment/$rid"
				));
		}
	}	
	
	function editAccount($id = NULL) {	
		// Set headers
		$this->template->title = "Administration: Edit an Account";
		$this->template->heading = " Administration: Edit an Account";
		$this->template->subheading = "Edit an Account associated with a convention. ";
		
		/* If no ID or bad ID defined, kill it with fire. */
		if ($id == NULL || !is_numeric($id))
			die('No direct access allowed. Go away D:');
				
		$acct = ORM::factory('Account')->find($id);
		$fields = $acct->default_fields;
		$fields['status']['values'] = Model_Account::getVerifySelectList();
		
		/* If pass is not loaded, we have a problem */
		if (!$acct->loaded())
		{
			$errorMsg = 'That pass does not exist! Maybe someone deleted it while you were busy?<br />';				
			$this->request->redirect('admin/manageAccounts');
		}
		
		if ($post = $this->request->post())
		{			
			if ($acct->validate_admin($post, false, false))
			{						
				$acct->save();				
				if ($acct->saved()) {
					$this->addMessage('Edited successfully the Account with email: ' . $acct->email);
					$this->request->redirect('admin/manageAccounts');
				}
				else
				{
					$this->addError("Oops. Something went wrong and it's not your fault. Try again or contact the system maintainer please!");
				}
			}
						
			$errorMsg = 'Oops. You entered something bad! Please fix it! <br />';				
			$errors = $post->errors('form_error_messages');
			foreach ($errors as $error)
				$errorMsg = $errorMsg . ' ' . $error . '<br />';					
		
			$this->addError($errorMsg);
			
			$this->template->content = new View('admin/Account', array(	
				'row' => $post,
				'fields' => $fields,
				'callback' => "editAccount/$id" 
			));
					
		}	
		else {		
			//Parse UNIX timestamp back to something we can use.		
			$this->template->content = new View('admin/Account', array(
				'row' => $acct->as_array(),
				'fields' => $fields,
				'callback' => "editAccount/$id"
			));
		}	
	}
	
	function editPass($id = NULL) {
		// Set headers
		$this->template->title = "Administration: Edit a Pass";
		$this->template->heading = " Administration: Edit a Pass";
		$this->template->subheading = "Edit a pass associated with a convention. ";
		
		/* If no ID or bad ID defined, kill it with fire. */
		if ($id == NULL || !is_numeric($id))
			die('No direct access allowed. Go away D:');
				
		$pass = ORM::factory('Pass')->find($id);
		$crows = ORM::factory('Convention')->find_all()->select_list('id', 'name');		
		$fields = $pass->default_fields;
		$fields['convention_id']['values'] = $crows;
		
		/* If pass is not loaded, we have a problem */
		if (!$pass->loaded())
		{
			$errorMsg = 'That pass does not exist! Maybe someone deleted it while you were busy?<br />';				
			$this->request->redirect('admin/managePasses');
		}
		
		if ($post = $this->request->post())
		{
			$post['startDate'] = Controller_Admin::parseSplitDate($post, 'startDate');
			$post['endDate'] = Controller_Admin::parseSplitDate($post, 'endDate');
			
			if ($pass->validate_admin($post))
			{			
				$pass->startDate = strtotime($post['startDate']);
				$pass->endDate = strtotime($post['endDate']);				
				
				$pass->save();								
				if ($pass->saved()) {
					$this->addMessage('Edited the pass (now) named: ' . $pass->name);
					$this->request->redirect('admin/managePasses');
				}
				else
				{
					$this->addError("Oops. Something went wrong and it's not your fault. Try again or contact the system maintainer please!");
				}
			}
						
			$errorMsg = 'Oops. You entered something bad! Please fix it! <br />';				
			$errors = $post->errors('form_error_messages');
			foreach ($errors as $error)
				$errorMsg = $errorMsg . ' ' . $error . '<br />';					
		
			$this->addError($errorMsg);
			
			$this->template->content = new View('admin/Pass', array(
				'crows' => $crows,	
				'row' => $post,
				'fields' => $fields,
				'callback' => "editPass/$id"
			));
					
		}	
		else {		
			//Parse UNIX timestamp back to something we can use.		
			$this->template->content = new View('admin/Pass', array(
				'crows' => $crows,	
				'row' => $pass->as_array(),
				'fields' => $fields,
				'callback' => "editPass/$id"
			));
		}	
	}
	

	function action_editConvention($id = NULL)
	{
		// Set headers
		$this->template->title = "Administration: Edit a Convention";
		$this->template->heading = "Administration: Edit a Convention";
		$this->template->subheading = "Edit a convention.";
		
		/* If no ID or bad ID defined, kill it with fire. */
		if ($id == NULL || !is_numeric($id))
			die('No direct access allowed. Go away D:');
		
		$conv = ORM::factory('Convention')->find($id);	
		$fields = $conv->default_fields;
		
		/* If pass is not loaded, we have a problem */
		if (!$conv->loaded())
		{
			$errorMsg = 'That pass does not exist! Maybe someone deleted it while you were busy?<br />';				
			$this->request->redirect('admin/manage');
		}
		
		if ($post = $this->request->post())
		{			
			$post['start_date'] = Controller_Admin::parseSplitDate($post, 'start_date');
			$post['end_date'] = Controller_Admin::parseSplitDate($post, 'end_date');
		
			if ($conv->validate_admin($post, false, true))
			{			
				$conv->start_date = strtotime($post['start_date']);
				$conv->end_date = strtotime($post['end_date']); //Bug - does not work for dates before the base time.
 			
				$conv->save();				
				if ($conv->saved()) {
					$this->addMessage('Successfully changed the convention (now) named ' . $conv->name);
					$this->request->redirect('admin/manageConventions');
				}
				else
				{
					$this->addError("Oops. Something went wrong and it's not your fault. Contact the system maintainer please!");
				}				
			}
					
			$errorMsg = 'Oops. You entered something bad! Please fix it! <br />';				
			$errors = $post->errors('form_error_messages');
			foreach ($errors as $error)
				$errorMsg = $errorMsg . ' ' . $error . '<br />';					
		
			$this->addError($errorMsg);					
			
			$this->template->content = new View('admin/Convention', array(
				'row' => $post,
				'fields' => $fields,
				'callback' => "editConvention/$id"
			)); 
					
		} 
		else 
		{		
			$this->template->content = new View('admin/Convention', array(
				'row' => $conv->as_array(),
				'fields' => $fields,
				'callback' => "editConvention/$id"
			));
		}	
	}
	
	function editRegistration($rid = NULL)
	{
		/* Can change all fields. */	
		//Not allowed to be lazy in checking input here.
		if ( (!isset($rid) || !is_numeric($rid) || $rid <= 0) )
			die("You're not allowed to be here!");
			
		//TODO: Deal with the case where one of the below fails to load.
		$reg = ORM::factory('Registration')->find($rid);
		if (! $reg->loaded() )
		{
			$this->addError('Grace says this registration does not exist.');
			$this->request->redirect('admin/manageRegistrations/');
		}
		
		$crows = ORM::factory('Convention')->find_all()->select_list('id', 'name');	
		$fields = $reg->formo_defaults;
		
		$fields['pass_id']['values'] = ORM::Factory('Pass')->where("convention_id", $reg->convention_id)->find_all()->select_list('id', 'name');
		
		if ($post = $this->request->post())
		{
			$fieldName = 'dob';
			$post[$fieldName] = implode('-', 
                        array(
                            @sprintf("%04d", $post[$fieldName . '-year']), 
                            @sprintf("%02d", $post[$fieldName . '-month']), 
                            @sprintf("%02d", $post[$fieldName . '-day'])
                        )
                    );		
		
			if ($reg->validate_admin($post))
			{			
				// die ('stop!' . $post['city'] . $post['prov'] . '|' . $reg->city . $reg->prov);
				// Non-required fields. Validate doesn't seem to set fields that aren't required.
				$reg->city = $post['city'];
				$reg->prov = $post['prov'];
				$reg->save();				
				if ($reg->saved()) {
					$this->addMessage('Successfully edited registatration for: ' . $reg->gname . ' ' . $reg->sname);
					$this->request->redirect('admin/manageRegistrations');
				}
				else
				{
					$this->addError("Oops. Something went wrong and it's not your fault. Contact the system maintainer please!");
				}
			}
						
			$errorMsg = 'Oops. You entered something bad! Please fix it! <br />';				
			$errors = $post->errors('form_error_messages');
			foreach ($errors as $error)
				$errorMsg = $errorMsg . ' ' . $error . '<br />';					
		
			$this->addError($errorMsg);
			
			$this->template->content = new View('admin/Registration2', array(
				'row' => $post,
				'fields' => $fields,
				'callback' => "editRegistration/$rid"
			));
		} else {
			/* Full registration at this step. */
			$this->template->content = new View('admin/Registration2', array(
					'row' => $reg->as_array(),
					'fields' => $fields,
					'callback' => "editRegistration/$rid"
				));
		}
		
	}
	
	function editPayment($id = NULL)
	{
		if ($id == NULL || !is_numeric($id))
			die('Get out of here!');
			
		$pay = ORM::Factory('Payment')->find($id); 
		$reg = ORM::Factory('Registration')->find($pay->register_id);
		$pass = ORM::Factory('Pass')->find($reg->pass_id);

		if (!$pay->loaded())
		{
			$this->addError('Selected payment is invalid. Maybe it was deleted while you were busy?');
			$this->request->redirect('admin/managePayments/' . $pay->register_id);
		}
		
		$fields = $pay->default_fields;
		
		//Move to model.
		$fields['type']['values'] = array('paypal' => 'Instant (Paypal)', 'mail' => 'Mail', 'inperson' => 'In Person');
		$fields['payment_status']['values'] = $pay->getPaymentStatusSelectList();
		
		//Do dropdown for payment type. What kind of selections can we have?		
		if ($post = $this->request->post())
		{
			if ($pay->validate_admin($post))
			{				
				$pay->last_modified = $this->auth->getAccount()->id;
				$pay->save();				
				if ($pay->saved()) {				
					$this->addMessage('Edited payment to the amount of: ' . $pay->mc_gross . ' (' . $pay->type . ') ');
					$this->updatePaymentStatus($reg, $pass);						
					$this->request->redirect('admin/managePayments/' . $pay->register_id);
				}
				else
				{
					$this->addError("Oops. Something went wrong and it's not your fault. Contact the system maintainer please!");
				}
			}		
			
			$errorMsg = 'Oops. You entered something bad! Please fix it! <br />';				
			$errors = $post->errors('form_error_messages');
			foreach ($errors as $error)
				$errorMsg = $errorMsg . ' ' . $error . '<br />';					
		
			$this->addError($errorMsg);

			//Do validation here.
			$this->template->content = new View('admin/Payment', array(
					'row' => $post,
					'fields' => $fields,
					'callback' => 'editPayment/' . $pay->id
				));
		}
		else
		{
			$this->template->content = new View('admin/Payment', array(
					'row' => $pay->as_array(),
					'fields' => $fields,
					'callback' => 'editPayment/' . $pay->id
				));
		}
	}
	
	function deleteConvention($id = NULL)
	{
		Controller_Admin::__delete($id, 'Convention', 'deleteConvention', 'manageConventions');
	}
	
	function deleteAccount($id = NULL) {
		Controller_Admin::__delete($id, 'Account', 'deleteAccount', 'manageAccounts');
	}
	
	function deletePass($id = NULL)
	{
		Controller_Admin::__delete($id, 'Pass', 'deletePass', 'managePasses');		
	}
	
	function deleteRegistration($id = NULL)
	{
		Controller_Admin::__delete($id, 'Registration', 'deleteRegistration', 'manageRegistrations');
	}
	
	function deletePayment($rid = NULL, $id = NULL)
	{	
		//TODO: Update status as necessary!
		Controller_Admin::__delete($id, 'Payment', "deletePayment/$rid", "managePayments/$rid", true);				
	}
	
	function setAdmin()
	{	
		$this->requirePermission('superAdmin'); //Require extra permissions to manage administrators.
		$fields = array('email' => array( 'type'  => 'text', 'label' => 'Email', 'required'=>true 								));
		
		if ($post = $this->request->post())
		{
            $post['email'] = trim($post['email']);
            $group = ORM::Factory('usergroup', Controller_Admin::ADMIN_USERGROUP);
			$acct = ORM::Factory('Account')->where('email', $post['email'])->find();
			if ($acct->loaded() && !$acct->has($group))
			{
				$acct->add($group);
				$acct->save();
				$this->addMessage('Account login ' . $acct->email . ' was granted administrator access.');
				$this->request->redirect('admin/manageAdmin');
			}
		}
		else
		{
			/* Full registration at this step. */
			$this->template->content = new View('admin/Admin', array(
				'row' => $reg['email'] = '',
				'fields' => $fields,
				'callback' => "setAdmin"
			));		
		}
	}
	
	function deleteAdmin($id = NULL)
	{
		$this->requirePermission('superAdmin'); //Require extra permissions to manage administrators.
		if ($id == NULL || !is_numeric($id))
			die('Get out of here!');
			
		$acct = ORM::Factory('Account')->find($id);
		if ($acct->loaded() && $acct->has(ORM::Factory('usergroup', 3)))
		{
			$acct->remove(ORM::Factory('usergroup', 3));
			$acct->save();		
			$this->addMessage('Account login ' . $acct->email . ' was stripped of admin access.');
		}
		else
		{
			$this->addError("Not a valid (admin) account.");
		}
	
		$this->request->redirect('admin/manageAdmin');
	}
	
	function search($entity = NULL)	
	{		
		//Determine search term (POST).
		$post = $this->request->post();	
		
		if (isset($post['search_term']))
			$search_term = $post['search_term'];		
		else
			$search_term = null;
	
		//Go context sensitive...
		$rows = null;
		if ($entity == 'Registration' && $search_term != null)
		{
			$this->template->heading = "Searching for Registrations";
			$rows = ORM::Factory('Registration')
				->orlike('email', $search_term)
				->orlike('gname', $search_term)
				->orlike('sname', $search_term)
				->orwhere('id',$search_term)
				->find_all();
		}
		else if ($entity == 'Account' && $search_term != null)
		{
			$this->template->heading = "Searching for Accounts";
			$rows = ORM::Factory('Account')
				->orlike('email', $search_term)
				->orwhere('id',$search_term)
				->find_all();
		}
		else if ($entity == 'Convention' && $search_term != null)
		{
			$this->template->heading = "Searching for Conventions";
			$rows = ORM::Factory('Convention')
				->orlike('name', $search_term)
				->orwhere('id',$search_term)
				->find_all();
		}
		else if ($entity == 'Pass' && $search_term != null)
		{
			$this->template->heading = "Searching for Passes";
			$rows = ORM::Factory('Pass')
				->orlike('name', $search_term)
				->orwhere('id',$search_term)
				->find_all();
		}
	
		// Header entry. (View with no data generates a header)					
		if ($rows != null)
		{			
			$data['entries'][0] = new View("admin/ListItems/$entity" . 'Entry');
			foreach ($rows as $row)
			{
				$data['actions']['edit'] = html::anchor("admin/edit$entity/". $row->id, html::image(url::site('/static/img/edit-copy.png'), __('admin.edit_account')));
				$data['actions']['delete'] = html::anchor("admin/delete$entity/" . $row->id, html::image(url::site('/static/img/edit-delete.png'), __('admin.delete_account')));			
				$data['entries'][$row->id] = new View("admin/ListItems/$entity" . 'Entry', array('row' => $row, 'actions' => $data['actions']));				
			}			
		} else if ($search_term == null) {
			$this->addError('No search term entered.');
			$data['entries'] = null;
		} else {
			$this->addError('Searching for something that does not exist!');
			$data['entries'] = null;
		}
		
		// Set callback path for form submit (change convention, jump to page)
		$this->template->content = new View('admin/Search', array(
				'callback' => "admin/search/$entity", 
				'rows' => $data['entries'], 
		));	
	}
	
	/* Common use functions */
	private function __delete($id, $entityType, $callback, $return, $updatePaymentStatus = NULL)
	{
		/* If no ID or bad ID defined, kill it with fire. */
		if ($id == NULL || !is_numeric($id))
			die('No direct access allowed. Go away D:');
			
		$row = ORM::factory($entityType)->find($id);			
		
		if (isset($row->name))
			$entityName = $row->name;
		else if (isset($row->email))
			$entityName = $row->email;
		else
			$entityName = 'Type: ' . $row->type; //hack.
		
		/* If row is defined (only if ID was set) and row was loaded... */
		if ($row->loaded())
		{
			/* POST value YES ... do delete */
			if ($val = $this->request->post('Yes'))
			{
				if ($updatePaymentStatus)
				{
						//We need to fetch reg, pass, and payment objects...
						$pay = ORM::Factory('Payment')->find($id);
						$reg = ORM::Factory('Registration')->find($pay->register_id);
						$pass = ORM::Factory('Pass')->find($reg->pass_id);						
				}			
			
				if ($row->delete()) 
				{
					$this->addMessage($entityName . " was deleted. D:");	

					if ($updatePaymentStatus)
					{							
						$this->updatePaymentStatus($reg, $pass);							
					}				
					
					$this->request->redirect("admin/$return");
				}
				else
				{
					$this->addError("Failed to delete convention with ID: $id! Please try again.");				
					$this->request->redirect("admin/$return");
				}	
			}		
			/* User changed mind. */
			else if ($val = $this->request->post('No'))
			{
				$this->request->redirect("admin/$return");
			}

			$this->template->content = new View('admin/delete', array(
				'entityType' => $entityType,
				'entityName' => $entityName,
				'callback' => $callback,
				'id' => $id
			));
		}
		else {
			$this->addError("Loading error: $id $entityType $callback $return");	
			$this->request->redirect("admin/$return");
		}
	}
	
	/*
	* Export is limited to exporting registration information. We will need to know the convention to export for and any conditions the
	* exporter wants applied. We will do this in two stages. One to determine the convention, and the other to determine the remaining
	* conditions to be applied. (GO checkboxes).
	*
	* Conditions:
	*   * Include those with a certain age or greater (or less).
	*   * Export by pass type.
	*   * Export by registration status.
	*/
	public function action_export()
	{
		// Set headers
		$this->template->title = "Administration: Export";
		$this->template->heading = "Administration: Export";
		$this->template->subheading = "Export Registration Info to CSV";
	
		$reg = ORM::factory('Registration');
		$crows = ORM::factory('Convention')->find_all()->select_list('id', 'name');	
		
		$fields = $reg->formo_defaults;
		$fields['convention_id'] = array( 'type'  => 'select', 'label' => 'Convention', 'required'=>true );
		$fields['convention_id']['values'] = $crows;
	
		if ($post = $this->request->post())
		{
			//Validate valid convention id, validate account - create if not exist email. Then pass it on as a POST.
			if (Model_Convention::validConvention($post['convention_id']))
			{
				$this->request->redirect("admin/export2/" . $post['convention_id']); //Move to next STEP				
			}
						
			$this->addError("One or more fields are blank!");	//pass_field_reg_error_convention_id_missing	
			$this->template->content = new View('admin/Export', array(
				'row' => $post,
				'fields' => $fields,
				'callback' => 'export'
			));
		}
		else 
		{
			$this->template->content = new View('admin/Export', array(
				'row' => $reg->as_array(),
				'fields' => $fields,
				'callback' => 'export'
			));		
		}	
	}
	
	public function action_export2($cid)
	{
		if (!isset($cid) || !is_numeric($cid) || $cid <= 0)
			die('Get out of here!');
	
		$passes = ORM::Factory('Pass')->where("convention_id", $cid)->find_all();
		$status_values = Model_Registration::getStatusValues();
		
		if ($post = $this->request->post())
		{			
			$export_passes = array();
			$export_status = array();
			$export_age = array();
	
			//Determine what to export. This is a bit cheap but it works...
			foreach($post as $k => $v):			
			
				/* Pass */
				if ($k[0] == 'p')
				{
					$temp = explode("_", $k);
					$export_passes[$temp[1]] = $temp[1];
				}	
				
				/* Status */
				else if ($k[0] == 's')
				{
					$temp = explode("_", $k);
					$export_status[$temp[1]] = $temp[1];
				}			
				
			endforeach;		
			
			$this->doExport($cid, $export_passes, $export_status);
		}
		
		$this->template->content = new View('admin/Export2', array(
				'passes' => $passes,
				'status_values' => $status_values,
				'callback' => "export2/$cid"
			));
	}
		
	/*
	* Validate and determine the convention id to use. If $convention_id is 
	* NULL, determine it from a result set of convention entries. 
	*/
	private function getConventionId($convention_id, $crows)
	{
		/* POST Variable defines $convention_id */
		if ($convention_id == NULL) 
		{
			$cid = $this->request->post('convention_id');
			
			if (isset($cid) && is_numeric($cid))			
				return $cid;
			else if (isset($cid))
				$this->addError("Attempted to view an invalid convention, reverting to default.");
			
		}
		
		/* GET Variable defines $convention_id */
		else if ($convention_id != NULL)
		{
			if (is_numeric($convention_id))
				return $convention_id;
			else
				$this->addError("Attempted to view an invalid convention, reverting to default.");
		}
		
		/* No valid GET/POST defined if it makes it this far. */
		if ($row = current($crows->as_array()))
			return $row->id;
		else
			return -1;		
	}
	
	private function parseSplitDate(array & $post, $fieldName)
	{
		$ret = implode('-', 
			array(
				@sprintf("%04d", $post[$fieldName . '-year']), 
				@sprintf("%02d", $post[$fieldName . '-month']), 
				@sprintf("%02d", $post[$fieldName . '-day'])
			)
		);	
        unset ($post[$fieldName . '-year']); 
        unset ($post[$fieldName . '-month']);
        unset ($post[$fieldName . '-day']);
        return $ret;
	}
	
	/*
	* Validate and determine the page multiplier to use when fetching results from the DB.
	*/
	private function getMultiplier($page)
	{
		// Page variable is a number.
		if (isset($page) && is_numeric($page))
		{
			$multiplier = $page - 1; // Subtract one since we're working starting from zero.
		}		
		// Non-set or non-valid multiplier.
		else if (!isset($multiplier) || !is_numeric($multiplier))
		{
			$multiplier = 0; 
		}
		
		return $multiplier;	
	}	
	
	/*
	 * One of the below states will be set. UNPROCESSED, PROCESSING, FAILED are not applicable for manual changes.
	 * If an admin wants to cancel a registration, simply delete it?
	 * const STATUS_NOT_ENOUGH	 = 2; // Payment recieved is not enough to pay cost of pass.
     * const STATUS_PAID        = 99; // Fully working and paid	
	 */
	
	private function updatePaymentStatus($reg, $pass)
	{		
		/* Check status of payment for registration. Set only if not PAID status. */			
		if (Model_Payment::staticGetTotal($reg->id) >= $pass->price)
		{
			//Only if the status is not set to PAID should we set it. This way, we won't spam confirmation mail on changes that do not affect PAID status.
			if ($reg->status != Model_Registration::STATUS_PAID) {
				$reg->status = Model_Registration::STATUS_PAID;		
			}				
		}
		else
		{
			$reg->status = Model_Registration::STATUS_NOT_ENOUGH;
		}		
				
		$reg->save();
	}
	
	/*
	* doExport - Export all information pertaining to the registration and pass cost.
	*
	* $cid is the Convention identifier that we will export the list of registrations from.
	* $passes is the array of allowed pass types for registrations to be included in the exported list.
	* $status is the array of allowed status values for registrations exported to the list.
	* 
	* $age is a single value of either "all", "minor", "adult", or "none".
	*/
	private function action_doExport($cid, $passes, $status)
	{
		$query = ORM::Factory('Registration');
		
		if ($cid == null || !is_numeric($cid)) {
			die('Get out of here!');	
		}
	
		if ($passes != null && is_array($passes) && count($passes) > 0)
		{
			$query = $query->in('registrations.pass_id', implode(",", $passes));
		}
	
		if ($status != null && is_array($status) && count($status) > 0)
		{
			$query = $query->in('registrations.status', implode(",", $status));
		}	
	
		//Lazy vs eager? We're going to use it all...get it all in one go.
		$results = $query->where("registrations.convention_id", $cid)->with('pass')->with('account')->with('convention')->find_all();		
		$csv_content = "";	
		
		/* Generate the content */
		if (count($results) > 0)
		{
			$csv_content = $results[0]->getColumns() . "\n"; //Have it output actual column names...
			foreach($results as $result):	
				$temp = $result->as_array();
				
				/* Change to more meaningful values */
				$temp['pass_id'] = $result->pass->name . ' ( $' . $result->pass->price . ' )';
				$temp['account_id'] = $result->account->email;
				$temp['convention_id'] = $result->convention->name;
				$temp['status'] = Model_Registration::regStatusToString($temp['status']);		
				
				//$values = array_values($temp);	
				//$csv_content .= implode(",", $values) . "\n";
				$first = true;
				foreach($temp as $value):
					if (!$first) 
						$csv_content .= ',';						
					else 
						$first = false;
				
					$csv_content .= "\"" . $value . "\"";
				endforeach;
				
				$csv_content .= "\n";
			endforeach;				
		}		
			
		$filename = 'Registrations_' . date("n_d_Y_G_H") . '.csv';
		header("Content-type: application/csv");
		header("Content-Disposition: attachment; filename=$filename");
		header("Pragma: no-cache");	
		header("Expires: 0");

	    print $csv_content;
	    exit;		
	}

    public function action_testClock()
    {
        header("Content-type: text/plain");
        print date("r");
	    exit;		
    }
}
