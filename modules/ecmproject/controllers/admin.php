<?php

/**
 * Admin Controller
 * 
 * All admin functionality is implemented here.
 * @author Stephen Tiu <stt@sfu.ca>
 * @version 1.0
 * @package ecm
 */


class Admin_Controller extends Controller 
{ 
	const ROWS_PER_PAGE = 4;

	function __construct()
	{
		parent::__construct();
		$this->requireLogin();
		$this->requirePermission('admin'); //Force requirement of full administrative access.
	
		/* We don't even use the menu right now.(or at least I don't)...*/
		$this->addMenuItem(array('title'=>'Administration', 'url'=>'admin'));
		$this->addMenuItem(array('title'=>'Add Registration', 'url'=>'convention/editReg'));

		return;
	}

	function index()
	{
		$this->view->title = Kohana::lang('admin.admin_area');
		$this->view->heading = Kohana::lang('admin.admin_area');
		$this->view->subheading = Kohana::lang('admin.admin_area_desc');
					
		$this->view->content = new View('admin/main');
	}

	function manageConventions($page = NULL)
	{
		// Set headers
		$this->view->title = "Administration: Manage Conventions";
		$this->view->heading = "Administration: Manage Conventions";
		$this->view->subheading = "Create, edit and delete conventions";
				
		$total_rows = Convention_Model::getTotalConventions();
								
		// Calculate the offset.
		$start = ( Admin_Controller::getMultiplier($page) * Admin_Controller::ROWS_PER_PAGE );	
		$rows = ORM::factory('Convention')->find_all( Admin_Controller::ROWS_PER_PAGE, $start );
			
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
			$data['actions']['edit'] = html::anchor('admin/editConvention/'. $row->id, html::image('img/edit-copy.png', Kohana::lang('admin.edit_account')));
			$data['actions']['delete'] = html::anchor('admin/deleteConvention/' . $row->id, html::image('img/edit-delete.png', Kohana::lang('admin.delete_account')));			
			$data['entries'][$row->id] = new View('admin/ListItems/ConventionEntry', array('row' => $row, 'actions' => $data['actions']));				
		}	
		
		// Set callback path for form submit (change convention, jump to page)
		$this->view->content = new View('admin/list', array(
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
		$this->view->title = "Administration: Manage Accounts";
		$this->view->heading = "Administration: Manage Accounts";
		$this->view->subheading = "Create, edit and delete accounts";
				
		$total_rows = Account_Model::getTotalAccounts();
								
		// Calculate the offset.
		$start = ( Admin_Controller::getMultiplier($page) * Admin_Controller::ROWS_PER_PAGE );	
		$rows = ORM::factory('Account')->find_all( Admin_Controller::ROWS_PER_PAGE, $start );
			
		// Extra validation.
		if (count($rows) == 0 && $total_rows > 0)
		{
			$this->addError('Invalid page number.');
		}
		else if ($total_rows == 0)
		{
			$this->addError("D: No one's used our system yet! (You shouldn't be getting this - if you are, something's wrong) D:");
		}			
		
		// Header entry. (View with no data generates a header)
		$data['entries'][0] = new View('admin/ListItems/AccountEntry');
		foreach ($rows as $row)
		{
			$data['actions']['edit'] = html::anchor('admin/editAccount/'. $row->id, html::image('img/edit-copy.png', Kohana::lang('admin.edit_account')));
			$data['actions']['delete'] = html::anchor('admin/deleteAccount/' . $row->id, html::image('img/edit-delete.png', Kohana::lang('admin.delete_account')));			
			$data['entries'][$row->id] = new View('admin/ListItems/AccountEntry', array('row' => $row, 'actions' => $data['actions']));				
		}	
		
		// Set callback path for form submit (change convention, jump to page)
	
		$this->view->content = new View('admin/list', array(
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
		$this->view->title = "Administration: Manage Passes";
		$this->view->heading = "Administration: Manage Passes";
		$this->view->subheading = "Create, edit and delete passes related to any convention";
			
							
		// Get all conventions, determine and validate convention_id, and get total number of passes for the particular convention_id.
		$crows = ORM::factory('Convention')->find_all();	
		$convention_id = Admin_Controller::getConventionId($convention_id, $crows);						
		$crows = $crows->select_list('id', 'name');					
		$total_rows = Pass_Model::getTotalPasses($convention_id);
							
							
		// Calculate the offset.
		$start = ( Admin_Controller::getMultiplier($page) * Admin_Controller::ROWS_PER_PAGE );	
		$rows = ORM::factory('Pass')->where("convention_id = $convention_id")->find_all( Admin_Controller::ROWS_PER_PAGE, $start );
			
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
			$data['actions']['edit'] = html::anchor('admin/editPass/'. $row->id, html::image('img/edit-copy.png', Kohana::lang('admin.edit_account')));
			$data['actions']['delete'] = html::anchor('admin/deletePass/' . $row->id, html::image('img/edit-delete.png', Kohana::lang('admin.delete_account')));			
			$data['entries'][$row->id] = new View('admin/ListItems/PassEntry', array('row' => $row, 'actions' => $data['actions']));				
		}	
		
		// Set callback path for form submit (change convention, jump to page)
		$this->view->content = new View('admin/list', array(
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
		$this->view->title = "Administration: Manage Registrations";
		$this->view->heading = "Administration: Manage Registrations";
		$this->view->subheading = "Create, edit and delete registrations related to a convention";
		
		
		// Get all conventions, determine and validate convention_id, and get total number of passes for the particular convention_id.
		$crows = ORM::factory('Convention')->find_all();	
		$convention_id = Admin_Controller::getConventionId($convention_id, $crows);						
		$crows = $crows->select_list('id', 'name');					
		$total_rows = Registration_Model::getTotalRegistrations($convention_id);
		
		// Calculate the offset.
		$start = ( Admin_Controller::getMultiplier($page) * Admin_Controller::ROWS_PER_PAGE );	
		$rows = ORM::factory('Registration')->where("convention_id = $convention_id")->find_all( Admin_Controller::ROWS_PER_PAGE, $start );
			
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
			$data['actions']['edit'] = html::anchor('admin/editRegistration/'. $row->id, html::image('img/edit-copy.png', Kohana::lang('admin.edit_account')));
			$data['actions']['delete'] = html::anchor('admin/deleteRegistration/' . $row->id, html::image('img/edit-delete.png', Kohana::lang('admin.delete_account')));			
			$data['entries'][$row->id] = new View('admin/ListItems/RegistrationEntry', array('row' => $row, 'actions' => $data['actions']));				
		}	
		
		// Set callback path for form submit (change convention, jump to page)
		$this->view->content = new View('admin/list', array(
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
		if (! $reg->loaded )
		{
			$this->addError('Invalid registration. Maybe someone deleted it when you weren\'t looking?');
			url::redirect('manageRegistrations');
		}		

		$rows = ORM::Factory('Payment')->where("register_id=$rid")->find_all();
		$pass = ORM::Factory('Pass')->find($reg->pass_id);
		if (count($rows) == 0)
		{
			$this->addError("This person hasn't paid anything yet!");
		}		

		$this->view->title = "Administration: Manage Payments";
		$this->view->heading = $reg->gname . ' ' . $reg->sname . ' (' . $reg->badge . ') ';
		$this->view->subheading = $pass->name;
		
		$data['entries'][0] = new View('admin/ListItems/PaymentEntry');
		foreach ($rows as $row)
		{
			$data['actions']['edit'] = html::anchor('admin/editPayment/'. $row->id, html::image('img/edit-copy.png', Kohana::lang('admin.edit_account')));
			$data['actions']['delete'] = html::anchor('admin/deletePayment/' . $rid . '/' . $row->id, html::image('img/edit-delete.png', Kohana::lang('admin.delete_account')));			
			$data['entries'][$row->id] = new View('admin/ListItems/PaymentEntry', array('row' => $row, 'actions' => $data['actions']));				
		}			
			
		$this->view->content = new View('admin/PaymentList', array(
				'reg' => $reg->as_array(),
				'pass' => $pass->as_array(),
				'callback' => 'admin/managePayments', 
				'createText' => 'Create Payment',
				'createLink' => "admin/createPayment/$rid",
				'rows' => $data['entries'])
			);
	}
	
	function createAccount() 
	{
		$this->view->title = "Administration: Create an Account";
		$this->view->heading = "Administration: Create an Account";
		$this->view->subheading = "Create an Account for a convention.";

		$acct = ORM::factory('Account');	
		$fields = $acct->default_fields;
		$fields['status']['values'] = Account_Model::getVerifySelectList();
		
		if ($post = $this->input->post())
		{			
			if ($acct->validate_admin($post, false, true))
			{							
				$acct->save();				
				if ($acct->saved) {
					$this->addMessage('Created a newly minted account with email: ' . $acct->email);
					url::redirect('admin/manageAccounts');
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
			
			$this->view->content = new View('admin/Account', array(
				'row' => $post,
				'fields' => $fields,
				'callback' => 'createAccount'
			)); 
					
		} 
		else 
		{		
			$this->view->content = new View('admin/Account', array(
				'row' => $acct->as_array(),
				'fields' => $fields,
				'callback' => 'createAccount'
			));
		}
	}
	
	function createPass()
	{
		// Set headers
		$this->view->title = "Administration: Create a Pass";
		$this->view->heading = "Administration: Create a Pass";
		$this->view->subheading = "Create a pass for a convention.";
		
		$pass = ORM::factory('Pass');
		$crows = ORM::factory('Convention')->find_all()->select_list('id', 'name');		
		$fields = $pass->default_fields;
		$fields['convention_id']['values'] = $crows;
		
		if ($post = $this->input->post())
		{
			$post['startDate'] = Admin_Controller::parseSplitDate($post, 'startDate');
			$post['endDate'] = Admin_Controller::parseSplitDate($post, 'endDate');
			
			if ($pass->validate_admin($post))
			{			
				$pass->startDate = strtotime($post['startDate']);
				$pass->endDate = strtotime($post['endDate']);
				
				$pass->save();				
				if ($pass->saved) {
					$this->addMessage('Created a newly minted pass called: ' . $pass->name);
					url::redirect('admin/managePasses');
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
			
			$this->view->content = new View('admin/Pass', array(
				'crows' => $crows,	
				'row' => $post,
				'fields' => $fields,
				'callback' => 'createPass'
			));
					
		}	
		else {
			$this->view->content = new View('admin/Pass', array(
				'crows' => $crows,	
				'row' => $pass->as_array(),
				'fields' => $fields,
				'callback' => 'createPass'
			));
		}			
	}
	
	function createConvention() {
		// Set headers
		$this->view->title = "Administration: Create a Convention";
		$this->view->heading = "Administration: Create a Convention";
		$this->view->subheading = "Create a convention.";
		
		$conv = ORM::factory('Convention');	
		$fields = $conv->default_fields;
		
		if ($post = $this->input->post())
		{			
			$post['start_date'] = Admin_Controller::parseSplitDate($post, 'start_date');
			$post['end_date'] = Admin_Controller::parseSplitDate($post, 'end_date');
								
			/*
			foreach ($post as $k => $v):
				print "$k => $v <br />";
			endforeach;
			*/
		
			if ($conv->validate_admin($post, false, true))
			{			
				$conv->start_date = strtotime($post['start_date']);
				$conv->end_date = strtotime($post['end_date']); //Bug - does not work for dates before the base time.
 			
				$conv->save();				
				if ($conv->saved) {
					$this->addMessage('Created a newly minted convention named ' . $conv->name);
					url::redirect('admin/manageConventions');
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
			
			$this->view->content = new View('admin/Convention', array(
				'row' => $post,
				'fields' => $fields,
				'callback' => 'createConvention'
			)); 
					
		} 
		else 
		{		
			$this->view->content = new View('admin/Convention', array(
				'row' => $conv->as_array(),
				'fields' => $fields,
				'callback' => 'createConvention'
			));
		}	
	}
	
	/* Step 1 */
	function createRegistration() {
		// Set headers
		$this->view->title = "Administration: Create Registration";
		$this->view->heading = "Administration: Create Registration";
		$this->view->subheading = "Create a registration for a convention.";
	
		$reg = ORM::factory('Registration');
		$crows = ORM::factory('Convention')->find_all()->select_list('id', 'name');	
		
		$fields = $reg->formo_defaults;
		$fields['convention_id'] = array( 'type'  => 'select', 'label' => 'Convention', 'required'=>true );
		$fields['convention_id']['values'] = $crows;
	
		if ($post = $this->input->post())
		{
			//Validate valid convention id, validate account - create if not exist email. Then pass it on as a POST.
			if (Convention_Model::validConvention($post['convention_id']) && isset($post['email']) && !empty($post['email']))
			{
				$aid = Account_Model::createAccount($post['email']);
				if ($aid != -1)
					url::redirect("admin/createRegistration2/" . $post['convention_id'] . "/$aid"); //Move to next STEP
				else
					$this->addError("Oops. An internal error occured. Try again.");
			}
						
			$this->addError("One or more fields are blank!");	//pass_field_reg_error_convention_id_missing	
			$this->view->content = new View('admin/Registration', array(
				'row' => $post,
				'fields' => $fields,
				'callback' => 'createRegistration'
			));
		}
		else 
		{
			$this->view->content = new View('admin/Registration', array(
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
		
		if ($post = $this->input->post())
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
				if ($reg->saved) {
					$this->addMessage('Created a newly minted registration for: ' . $reg->gname . ' ' . $reg->sname);
					url::redirect('admin/manageRegistrations');
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
			
			$this->view->content = new View('admin/Registration2', array(
				'row' => $post,
				'fields' => $fields,
				'callback' => "createRegistration2/$cid/$aid"
			));
		} else {
			$acct = ORM::factory('Account')->find($aid);
			if (!$acct->loaded)
				die('Serious error here. The account is supposed to exist but it doesn\'t.');
				
			$reg->email = $acct->email;
			
			/* Full registration at this step. */
			$this->view->content = new View('admin/Registration2', array(
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
		
		if (!$reg->loaded)
			die('Unable to retrieve registration');
		
		$fields = $pay->default_fields;
		
		//Move to model.
		$fields['type']['values'] = array('instant' => 'Instant (Paypal)', 'mail' => 'Mail', 'inperson' => 'In Person');
		$fields['payment_status']['values'] = array('Pending' => 'Pending', 'Completed' => 'Completed', 'Denied' => 'Denied');	
		
		//Do dropdown for payment type. What kind of selections can we have?		
		if ($post = $this->input->post())
		{
			if ($pay->validate_admin($post))
			{				
				$pay->register_id = $rid;
				$pay->save();
				if ($pay->saved) {
					$this->addMessage('Payment created for the amount of: ' . $pay->mc_gross . ' (' . $pay->type . ') ');
					
					/* Check status of payment for registration */				
					if ($pay->getTotal() >= $pass->price) {
						$reg->status = Registration_Model::STATUS_PAID;
						$reg->save();
					}
					/* If total payment no longer equals or exceeds cost of badge, mark as failed registration. */
					else
					{
						$reg->status = Registration_Model::STATUS_NOT_ENOUGH;
						$reg->save();
					}
					
					url::redirect("admin/managePayments/$rid");
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
			$this->view->content = new View('admin/Payment', array(
					'row' => $post,
					'fields' => $fields,
					'callback' => "createPayment/$rid"
				));
		}
		else
		{
			$this->view->content = new View('admin/Payment', array(
					'row' => $pay->as_array(),
					'fields' => $fields,
					'callback' => "createPayment/$rid"
				));
		}
	}	
	
	function editAccount($id = NULL) {	
		// Set headers
		$this->view->title = "Administration: Edit an Account";
		$this->view->heading = " Administration: Edit an Account";
		$this->view->subheading = "Edit an Account associated with a convention. ";
		
		/* If no ID or bad ID defined, kill it with fire. */
		if ($id == NULL || !is_numeric($id))
			die('No direct access allowed. Go away D:');
				
		$acct = ORM::factory('Account')->find($id);
		$fields = $acct->default_fields;
		$fields['status']['values'] = Account_Model::getVerifySelectList();
		
		/* If pass is not loaded, we have a problem */
		if (!$acct->loaded)
		{
			$errorMsg = 'That pass does not exist! Maybe someone deleted it while you were busy?<br />';				
			url::redirect('admin/manageAccounts');
		}
		
		if ($post = $this->input->post())
		{			
			if ($acct->validate_admin($post, false, false))
			{			
				foreach ($acct->as_array() as $k => $v)
				{
					print "$k => $v <br />";
				}
			
				$acct->save();				
				if ($acct->saved) {
					$this->addMessage('Edited successfully the Account with email: ' . $acct->email);
					url::redirect('admin/manageAccounts');
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
			
			$this->view->content = new View('admin/Account', array(	
				'row' => $post,
				'fields' => $fields,
				'callback' => "editAccount/$id" 
			));
					
		}	
		else {		
			//Parse UNIX timestamp back to something we can use.		
			$this->view->content = new View('admin/Account', array(
				'row' => $acct->as_array(),
				'fields' => $fields,
				'callback' => "editAccount/$id"
			));
		}	
	}
	
	function editPass($id = NULL) {
		// Set headers
		$this->view->title = "Administration: Edit a Pass";
		$this->view->heading = " Administration: Edit a Pass";
		$this->view->subheading = "Edit a pass associated with a convention. ";
		
		/* If no ID or bad ID defined, kill it with fire. */
		if ($id == NULL || !is_numeric($id))
			die('No direct access allowed. Go away D:');
				
		$pass = ORM::factory('Pass')->find($id);
		$crows = ORM::factory('Convention')->find_all()->select_list('id', 'name');		
		$fields = $pass->default_fields;
		$fields['convention_id']['values'] = $crows;
		
		/* If pass is not loaded, we have a problem */
		if (!$pass->loaded)
		{
			$errorMsg = 'That pass does not exist! Maybe someone deleted it while you were busy?<br />';				
			url::redirect('admin/managePasses');
		}
		
		if ($post = $this->input->post())
		{
			$post['startDate'] = Admin_Controller::parseSplitDate($post, 'startDate');
			$post['endDate'] = Admin_Controller::parseSplitDate($post, 'endDate');
			
			if ($pass->validate_admin($post))
			{			
				$pass->startDate = strtotime($post['startDate']);
				$pass->endDate = strtotime($post['endDate']);				
				
				$pass->save();								
				if ($pass->saved) {
					$this->addMessage('Edited the pass (now) named: ' . $pass->name);
					url::redirect('admin/managePasses');
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
			
			$this->view->content = new View('admin/Pass', array(
				'crows' => $crows,	
				'row' => $post,
				'fields' => $fields,
				'callback' => "editPass/$id"
			));
					
		}	
		else {		
			//Parse UNIX timestamp back to something we can use.		
			$this->view->content = new View('admin/Pass', array(
				'crows' => $crows,	
				'row' => $pass->as_array(),
				'fields' => $fields,
				'callback' => "editPass/$id"
			));
		}	
	}
	

	function editConvention($id = NULL)
	{
		// Set headers
		$this->view->title = "Administration: Edit a Convention";
		$this->view->heading = "Administration: Edit a Convention";
		$this->view->subheading = "Edit a convention.";
		
		/* If no ID or bad ID defined, kill it with fire. */
		if ($id == NULL || !is_numeric($id))
			die('No direct access allowed. Go away D:');
		
		$conv = ORM::factory('Convention')->find($id);	
		$fields = $conv->default_fields;
		
		/* If pass is not loaded, we have a problem */
		if (!$conv->loaded)
		{
			$errorMsg = 'That pass does not exist! Maybe someone deleted it while you were busy?<br />';				
			url::redirect('admin/manage');
		}
		
		if ($post = $this->input->post())
		{			
			$post['start_date'] = Admin_Controller::parseSplitDate($post, 'start_date');
			$post['end_date'] = Admin_Controller::parseSplitDate($post, 'end_date');
		
			if ($conv->validate_admin($post, false, true))
			{			
				$conv->start_date = strtotime($post['start_date']);
				$conv->end_date = strtotime($post['end_date']); //Bug - does not work for dates before the base time.
 			
				$conv->save();				
				if ($conv->saved) {
					$this->addMessage('Successfully changed the convention (now) named ' . $conv->name);
					url::redirect('admin/manageConventions');
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
			
			$this->view->content = new View('admin/Convention', array(
				'row' => $post,
				'fields' => $fields,
				'callback' => "editConvention/$id"
			)); 
					
		} 
		else 
		{		
			$this->view->content = new View('admin/Convention', array(
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
		if (! $reg->loaded )
		{
			$this->addError('Grace says this registration does not exist.');
			url::redirect('admin/manageRegistrations/');
		}
		
		$crows = ORM::factory('Convention')->find_all()->select_list('id', 'name');	
		$fields = $reg->formo_defaults;
		
		$fields['pass_id']['values'] = ORM::Factory('Pass')->where("convention_id", $reg->convention_id)->find_all()->select_list('id', 'name');
		
		if ($post = $this->input->post())
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
				if ($reg->saved) {
					$this->addMessage('Successfully edited registatration for: ' . $reg->gname . ' ' . $reg->sname);
					url::redirect('admin/manageRegistrations');
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
			
			$this->view->content = new View('admin/Registration2', array(
				'row' => $post,
				'fields' => $fields,
				'callback' => "editRegistration/$rid"
			));
		} else {
			/* Full registration at this step. */
			$this->view->content = new View('admin/Registration2', array(
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

		if (!$pay->loaded)
		{
			$this->addError('Selected payment is invalid. Maybe it was deleted while you were busy?');
			url::redirect('admin/managePayments/' . $pay->register_id);
		}
		
		$fields = $pay->default_fields;
		
		//Move to model.
		$fields['type']['values'] = array('instant' => 'Instant (Paypal)', 'mail' => 'Mail', 'inperson' => 'In Person');
		$fields['payment_status']['values'] = array('Pending' => 'Pending', 'Completed' => 'Completed', 'Denied' => 'Denied');	
		
		//Do dropdown for payment type. What kind of selections can we have?		
		if ($post = $this->input->post())
		{
			echo $pay->id;
			if ($pay->validate_admin($post))
			{				
				$pay->save();
				if ($pay->saved) {
					$this->addMessage('Payment created for the amount of: ' . $pay->mc_gross . ' (' . $pay->type . ') ');
					
					/* Check status of payment for registration */				
					if ($pay->getTotal() >= $pass->price) {
						$reg->status = Registration_Model::STATUS_PAID;
						$reg->save();
					}
					/* If total payment no longer equals or exceeds cost of badge, mark as failed registration. */
					else
					{
						$reg->status = Registration_Model::STATUS_NOT_ENOUGH;
						$reg->save();
					}
					
					url::redirect('admin/managePayments/' . $pay->register_id);
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
			$this->view->content = new View('admin/Payment', array(
					'row' => $post,
					'fields' => $fields,
					'callback' => 'editPayment/' . $pay->id
				));
		}
		else
		{
			$this->view->content = new View('admin/Payment', array(
					'row' => $pay->as_array(),
					'fields' => $fields,
					'callback' => 'editPayment/' . $pay->id
				));
		}
	}
	
	function deleteConvention($id = NULL)
	{
		Admin_Controller::__delete($id, 'Convention', 'deleteConvention', 'manageConventions');
	}
	
	function deleteAccount($id = NULL) {
		Admin_Controller::__delete($id, 'Account', 'deleteAccount', 'manageAccounts');
	}
	
	function deletePass($id = NULL)
	{
		Admin_Controller::__delete($id, 'Pass', 'deletePass', 'managePasses');
	}
	
	function deleteRegistration($id = NULL)
	{
		Admin_Controller::__delete($id, 'Registration', 'deleteRegistration', 'manageRegistrations');
	}
	
	function deletePayment($rid = NULL, $id = NULL)
	{	
		//TODO: Update status as necessary!
		Admin_Controller::__delete($id, 'Payment', "deletePayment/$rid", "managePayments/$rid");
	}
	
	function search()
	{
		
	
	}
	
	/* Common use functions */
	public function __delete($id, $entityType, $callback, $return)
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
		if ($row->loaded)
		{
			/* POST value YES ... do delete */
			if ($val = $this->input->post('Yes'))
			{
				if ($row->delete()) 
				{
					$this->addMessage($entityName . " was deleted. D:");				
					url::redirect("admin/$return");
				}
				else
				{
					$this->addError("Failed to delete convention with ID: $id! Please try again.");				
					url::redirect("admin/$return");
				}	
			}		
			/* User changed mind. */
			else if ($val = $this->input->post('No'))
			{
				url::redirect("admin/$return");
			}

			$this->view->content = new View('admin/delete', array(
				'entityType' => $entityType,
				'entityName' => $entityName,
				'callback' => $callback,
				'id' => $id
			));
		}
		else {
			$this->addError("Loading error: $id $entityType $callback $return");	
			url::redirect("admin/$return");
		}
	}
	
	/*
	* Validate and determine the convention id to use. If $convention_id is 
	* NULL, determine it from a result set of convention entries. 
	*/
	function getConventionId($convention_id, $crows)
	{
		/* POST Variable defines $convention_id */
		if ($convention_id == NULL) 
		{
			$cid = $this->input->post('convention_id');
			
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
	
	function parseSplitDate($post, $fieldName)
	{
		return implode('-', 
			array(
				@sprintf("%04d", $post[$fieldName . '-year']), 
				@sprintf("%02d", $post[$fieldName . '-month']), 
				@sprintf("%02d", $post[$fieldName . '-day'])
			)
		);	
	}
	
	/*
	* Validate and determine the page multiplier to use when fetching results from the DB.
	*/
	function getMultiplier($page)
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
}
