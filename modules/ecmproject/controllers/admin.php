<?php

/**
 * Admin Controller
 * 
 * All admin 
 * @author Stephen Tiu <stt@sfu.ca>
 * @version 1.0
 * @package ecm
 */


class Admin_Controller extends Controller 
{
	function __construct()
	{
		parent::__construct();
		$this->requireLogin();
	
		$this->view->menu += array(
			array('title'=>'Register', 'url'=>'convention'),
			array('title'=>'Administration', 'url'=>'admin'),
		);

		return;
	}

	function index()
	{
		$this->view->title = Kohana::lang('admin.admin_area');
		$this->view->heading = Kohana::lang('admin.admin_area');
		$this->view->subheading = Kohana::lang('admin.admin_area_desc');
					
		$this->view->content = new View('admin/index');
	}

	//TODO: Add new account feature, search, paged viewing. Implement delete account, edit account.
	function manageAccounts()
	{
		$this->view->title = Kohana::lang('admin.admin_accounts');
		$this->view->heading = Kohana::lang('admin.admin_accounts');
		$this->view->subheading = Kohana::lang('admin.admin_area');
		
		$data['entries'] = array();
		$rows = ORM::factory('Account')->find_all();		//Move to account model.

		$data['cmd_target'] = 'manageAccounts';
		$data['commands']['create'] = html::anchor('admin/createAccount', 'Create new Account', 'Create new Account');
		$data['headers']['id'] = array('width' => '10%', 'name' => 'ID');
		$data['headers']['email'] = array('width' => '35%', 'name' => 'Email');
		$data['headers']['status'] = array('width' => '15%', 'name' => 'Status');
		$data['headers']['last'] = array('width' => '30%', 'name' => 'Last Login');
		$data['headers']['edit'] = array('width' => '5%', 'name' => 'Edit');
		$data['headers']['delete'] = array('width' => '5%', 'name' => 'Delete');
			
		/* Modify view/controller so we do this once. */
		foreach ($rows as $row)
		{
			$data['actions']['edit'] = html::anchor('admin/editAccount/'. $row->id, html::image('img/edit-copy.png', Kohana::lang('admin.edit_account')));
			$data['actions']['delete'] = html::anchor('admin/deleteAccount/' . $row->id, html::image('img/edit-delete.png', Kohana::lang('admin.delete_account')));
			$data['entries'][$row->id] = new View('lists/account', array('row' => $row, 'actions' => $data['actions']));			
		}	

		$this->view->content = new View('admin/list', $data);
	}
	
	function createAccount() 
	{
		if ($post = $this->input->post())
		{			 
			$account = ORM::factory('Account');
            if ($account->validate_admin($post, FALSE, TRUE))
            {
				$account->status = $account->stringToStatus($post->status);
                $account->save();

				if ($account->status == Account_Model::ACCOUNT_STATUS_UNVERIFIED)
				{
					$code = $account->generateVerifyCode();
					$account->sendValidateEmail($code);
				}
				
				$this->addMessage("Account " . $account->email . " was created successfully."); //TODO: Lang it.
				url::redirect('admin/manageAccounts');
			}
			else
				{
					$errors = $post->errors('form_error_messages');
					foreach ($errors as $error)
						$this->addError($error);
						
					$data['row']->email = $post->email;
					$data['row']->status = $post->status;
				}			
		} else
		{
			$data['row']->email = '';
			$data['row']->status = 'UNVERIFIED';
		}
		
		$this->view->content = new View('admin/createAccount', $data);			
	}
	
	function editAccount($id = NULL) {	
		if (isset($id))
			$row = ORM::factory('Account')->find($id);		
				
		$data = array();
		
		if (isset($row) && $row->loaded)
		{ 
			if ($post = $this->input->post())
			{			
				if ($row->validate_admin($post))
				{				
					$row->status = $row->stringToStatus($post->status);
					$row->save();
					$this->addMessage("The changes were applied successfully."); //TODO: Lang it.
					url::redirect('admin/manageAccounts');
				}
				else
				{
					$errors = $post->errors('form_error_messages');
					foreach ($errors as $error)
						$this->addError($error);
						
					$data['row'] = $row;	
					$data['row']->email = $post->email;
					$data['row']->status = $row->stringToStatus($post->status);
				}			
			}
			else 
			{	
				$data['row'] = $row;							
			}			
		}		
		else
		{
			$this->addError(Kohana::lang('admin.account_not_exist'));	
			url::redirect('admin/manageAccounts');
		}	
		
		$this->view->title = Kohana::lang('admin.edit_account');
		$this->view->heading = Kohana::lang('admin.edit_account') . ' : ' . $row->email;
		$this->view->subheading = "Careful now...";
		
		$this->view->content = new View('admin/editAccount', $data);
	}
	
	function deleteAccount($id = NULL) {
	
		if (isset($id))
			$row = ORM::factory('Account')->find($id);			
		
		/* If row is defined (only if ID was set) and row was loaded... */
		if (isset($row) && $row->loaded)
		{
			/* POST value YES ... do delete */
			if ($val = $this->input->post('Yes'))
			{
				if ($row->delete()) 
				{
					$this->addMessage("Account with ID: $id was deleted.");				
					url::redirect('admin/manageAccounts');
				}
				else
				{
					$this->addError("Failed to delete account with ID: $id! Please try again.");				
					url::redirect('admin/manageAccounts');
				}	
			}		
			/* User changed mind. */
			else if ($val = $this->input->post('No'))
			{
				url::redirect('admin/manageAccounts');
			}			
			
			/* User needs confirm screen. */
			else if ($row->loaded)				
			{
				$this->view->title = 'Admin: Delete Account';
				$this->view->heading = 'ARE YOU SURE?';
				$this->view->subheading = 'Deleting anything is not to be taken lightly.';
				
				$data['id'] = $row->id;
				$data['entityType'] = 'Account';
				$data['entityName'] = $row->email;
				$this->view->content = new View('admin/delete', $data);
			}			
		}
		
		/* Row not defined/id was not set/deletion of a non existant ID. */
		else {
			$this->addMessage(Kohana::lang('admin.account_not_exist'));	
			url::redirect('admin/manageAccounts');
		}
	}
	
	function manageRegistrations()
	{
		$this->view->title = Kohana::lang('admin.admin_registrations');
		$this->view->heading = Kohana::lang('admin.admin_registrations');
		$this->view->subheading = Kohana::lang('admin.admin_area');
		
		$data['cmd_target'] = 'manageRegistrations';
	}
	
	function editRegistrations()
	{
	
	}
	
	function managePasses($convention_id = NULL) 
	{
		$this->view->title = Kohana::lang('admin.admin_passes');
		$this->view->heading = Kohana::lang('admin.admin_passes');
		$this->view->subheading = Kohana::lang('admin.admin_area');
		
		$data['entries'] = array();		
		$data['hack'] = true; //Hack!
		
		// Get all conventions, set $convention_id from FORM.
		$crows = ORM::factory('Convention')->find_all();
				
		$convention_id = $this->input->post('convention_id');
		
		// If no convention_id was specified (FORM), use first one.
		if (!isset($convention_id) && ($row = current($crows->as_array())))
		{		
			$convention_id = $row->id;
		}
		else if (!isset($convention_id))
		{
			// TODO: Replace with something useful (or nicer looking).
			die("No conventions found. Replace this message with something else.");
		}
						
		// Okay we're done figuring out what convention to use for displaying passes. Make a select list and get our passes.
		$crows = $crows->select_list('id', 'name');
		$rows = ORM::factory('Pass')->where("convention_id = $convention_id")->find_all();
				
		// Specify commands. Needs better formatting.
		$data['cmd_target'] = 'managePasses';
		$data['commands']['create'] = html::anchor("admin/createPass/$convention_id", html::image('img/document-new.png', 'New Pass') . 'New Pass') ; 			
		$data['commands']['select_convention'] = form::dropdown('convention_id', $crows, $this->input->post('convention_id'));
		
		// Specify headers.
		$data['headers']['name'] = array('width' => '40%', 'name' => 'Name');
		$data['headers']['price'] = array('width' => '10%', 'name' => 'Price');
		$data['headers']['startDate'] = array('width' => '20%', 'name' => 'Start Date');
		$data['headers']['endDate'] = array('width' => '20%', 'name' => 'End Date');
		$data['headers']['edit'] = array('width' => '5%', 'name' => 'Edit');
		$data['headers']['delete'] = array('width' => '5%', 'name' => 'Delete');
		
		// Per row, set actions.
		foreach ($rows as $row)
		{
			$data['actions']['edit'] = html::anchor('admin/editPass/'. $row->id, html::image('img/edit-copy.png', Kohana::lang('admin.edit_account')));
			$data['actions']['delete'] = html::anchor('admin/deletePass/' . $row->id, html::image('img/edit-delete.png', Kohana::lang('admin.delete_account')));
			
			$data['entries'][$row->id] = new View('lists/pass', array('row' => $row, 'actions' => $data['actions']));	
		}		
		
		// Main view.
		$this->view->content = new View('admin/list', $data);
	}
	
	
	function createPass($convention_id = NULL)
	{
		$pass = ORM::factory('Pass');	
		$data['callback'] = "createPass/$convention_id";		
		
		if ($post = $this->input->post())		
		{			
            if ($pass->validate_admin($post))
            {			
				if (!isset($post->isPurchasable)) 				
					$pass->isPurchasable = 0; //Force it to zero.				
				else				
					$pass->isPurchasable = 1;												
					
				if (!isset($post->startDate) || empty($post->startDate)) {
					$pass->startDate = time();
				} else {			
					$pass->startDate = strtotime($post->startDate);
				}
				
				if (!isset($post->endDate) || empty($post->endDate)) {
					$pass->endDate = ORM::factory('Convention')->find($convention_id)->end_date;
				} else {			
					$pass->endDate = strtotime($post->endDate);
				}
													
                $pass->save();

				$this->addMessage("The pass, " . $pass->name . " was created successfully."); //TODO: Lang it.
				url::redirect('admin/managePasses');
			}
			else
				{
					$errors = $post->errors('form_error_messages');
					foreach ($errors as $error)
						$this->addError($error);
						
					$data['convention_id'] = $this->input->post('convention_id');
					$data['row'] = $post->as_array();
					$data['crows'] = ORM::factory('Convention')->find_all()->select_list('id', 'name');
				}			
		} else
		{
			$data['convention_id'] = $convention_id;
			$data['row'] = $pass->as_array(); //Will be blank.
			$data['crows'] = ORM::factory('Convention')->find_all()->select_list('id', 'name');
		}
		
		$this->view->content = new View('admin/Pass', $data);				
	}
	
	function editPass($id = NULL)
	{		
		// If id is not set, we cannot proceed.
		if (!isset($id) || empty($id))
		{
			$this->addMessage("Speak friend and enter. I cannot let you pass.");
			url::redirect('admin/managePasses');
		}
		
		// If pass id was invalid, we cannot proceed.
		$pass = ORM::factory('Pass')->find($id);
		if ( !$pass->loaded )
		{
			$this->addMessage("Oops. Invalid pass selected - perhaps someone deleted it? Please try again.");
			url::redirect('admin/managePasses');
		}
		
		$data['callback'] = "editPass/$id";
		
		if ($post = $this->input->post())		
		{			
            if ($pass->validate_admin($post))
            {			
				if (!isset($post->isPurchasable)) 				
					$pass->isPurchasable = 0; //Force it to zero.				
				else				
					$pass->isPurchasable = 1;												
					
				if (!isset($post->startDate) || empty($post->startDate)) {
					$pass->startDate = time();
				} else {			
					$pass->startDate = strtotime($post->startDate);
				}
				
				if (!isset($post->endDate) || empty($post->endDate)) {
					$pass->endDate = ORM::factory('Convention')->find($this->input->post('convention_id'))->end_date;
				} else {			
					$pass->endDate = strtotime($post->endDate);
				}
													
                $pass->save();

				$this->addMessage("The pass, " . $pass->name . " was created successfully."); //TODO: Lang it.
				url::redirect('admin/managePasses');
			}
			else
				{
					$errors = $post->errors('form_error_messages');
					foreach ($errors as $error)
						$this->addError($error);
						
					$data['convention_id'] = $this->input->post('convention_id');
					$data['row'] = $post->as_array();
					$data['crows'] = ORM::factory('Convention')->find_all()->select_list('id', 'name');
				}			
		}
		else
		{
			$data['convention_id'] = $pass->convention_id;
			$data['row'] = $pass->as_array();		
			$data['crows'] = ORM::factory('Convention')->find_all()->select_list('id', 'name');
			
			$this->view->content = new View('admin/Pass', $data);	
		
		}		
	}
	
	function deletePass($id = NULL)
	{
		if (isset($id))
			$row = ORM::factory('Pass')->find($id);			
		
		/* If row is defined (only if ID was set) and row was loaded... */
		if (isset($row) && $row->loaded)
		{
			/* POST value YES ... do delete */
			if ($val = $this->input->post('Yes'))
			{
				if ($row->delete()) 
				{
					$this->addMessage("Pass with ID: $id was deleted.");				
					url::redirect('admin/managePasses');
				}
				else
				{
					$this->addError("Failed to delete pass with ID: $id! Please try again.");				
					url::redirect('admin/managePasses');
				}	
			}		
			/* User changed mind. */
			else if ($val = $this->input->post('No'))
			{
				url::redirect('admin/managePasses');
			}			
			
			/* User needs confirm screen. */
			else if ($row->loaded)				
			{
				$this->view->title = 'Admin: Delete Passes';
				$this->view->heading = 'ARE YOU SURE?';
				$this->view->subheading = 'Deleting anything is not to be taken lightly.';
				
				$data['id'] = $row->id;
				$data['entityType'] = 'Pass';
				$data['entityName'] = $row->name;
				$this->view->content = new View('admin/delete', $data);
			}			
		}
		
		/* Row not defined/id was not set/deletion of a non existant ID. */
		else {
			$this->addMessage(Kohana::lang('admin.account_not_exist'));	
			url::redirect('admin/manageConventions');
		}	
	}
	
	function manageConventions()
	{
		$this->view->title = Kohana::lang('admin.admin_conventions');
		$this->view->heading = Kohana::lang('admin.admin_conventions');
		$this->view->subheading = Kohana::lang('admin.admin_area');
		
		$data['entries'] = array();
		$rows = ORM::factory('Convention')->find_all();
		
		$data['cmd_target'] = 'manageConventions';
		$data['headers']['name'] = array('width' => '40%', 'name' => 'Name');
		$data['headers']['location'] = array('width' => '50%', 'name' => 'Location');
		$data['headers']['edit'] = array('width' => '5%', 'name' => 'Edit');
		$data['headers']['delete'] = array('width' => '5%', 'name' => 'Delete');
		
		foreach ($rows as $row)
		{
			$data['actions']['edit'] = html::anchor('admin/editConvention/'. $row->id, html::image('img/edit-copy.png', Kohana::lang('admin.edit_account')));
			$data['actions']['delete'] = html::anchor('admin/deleteConvention/' . $row->id, html::image('img/edit-delete.png', Kohana::lang('admin.delete_account')));
			$data['entries'][$row->id] = new View('lists/convention', array('row' => $row, 'actions' => $data['actions']));	
		}		
		
		$this->view->content = new View('admin/list', $data);
	}	
	
	function editConvention()
	{
		/* Can change all fields. */	
	}
	
	function deleteConvention($id = NULL)
	{
		if (isset($id))
			$row = ORM::factory('Convention')->find($id);			
		
		/* If row is defined (only if ID was set) and row was loaded... */
		if (isset($row) && $row->loaded)
		{
			/* POST value YES ... do delete */
			if ($val = $this->input->post('Yes'))
			{
				if ($row->delete()) 
				{
					$this->addMessage("Convention with ID: $id was deleted.");				
					url::redirect('admin/manageConventions');
				}
				else
				{
					$this->addError("Failed to delete convention with ID: $id! Please try again.");				
					url::redirect('admin/manageConventions');
				}	
			}		
			/* User changed mind. */
			else if ($val = $this->input->post('No'))
			{
				url::redirect('admin/manageConventions');
			}			
			
			/* User needs confirm screen. */
			else if ($row->loaded)				
			{
				$this->view->title = 'Admin: Delete Account';
				$this->view->heading = 'ARE YOU SURE?';
				$this->view->subheading = 'Deleting anything is not to be taken lightly.';
				
				$data['id'] = $row->id;
				$data['entityType'] = 'Convention';
				$data['entityName'] = $row->name;
				$this->view->content = new View('admin/delete', $data);
			}			
		}
		
		/* Row not defined/id was not set/deletion of a non existant ID. */
		else {
			$this->addMessage(Kohana::lang('admin.account_not_exist'));	
			url::redirect('admin/manageConventions');
		}	
	}
}
