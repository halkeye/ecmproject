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
		$this->view->title = 'Administration Area';
		$this->view->heading = 'Administration Area';
		$this->view->subheading = 'Manage conventions, passes, permissions and other details';
					
		$this->view->content = new View('admin/index');
	}

	//TODO: Add new account feature, search, paged viewing. Implement delete account, edit account.
	function manageAccounts()
	{
		$this->view->title = 'Admin: Manage Accounts';
		$this->view->heading = 'Account Management';
		$this->view->subheading = 'Administration Area';
		
		$data['entries'] = array();
		$rows = ORM::factory('Account')->find_all();		//Move to account model.

		/* Go through each object in the iterator. */
		foreach ($rows as $row)
		{
			$data['entries'][$row->id] = array();
			$data['entries'][$row->id]['id'] = $row->id;
			$data['entries'][$row->id]['email'] = $row->email;
			$data['entries'][$row->id]['status'] = $row->statusToString();
							
			//$data['entries'][$row->id]['created'] = date("M j, Y g:i a", $row->created);
			
			/* Replace with a : ?*/
			if (isset($row->login))
				$data['entries'][$row->id]['login'] = date("M j, Y H:i", $row->login);		
			else
				$data['entries'][$row->id]['login'] = '--';
				
			/* Actions to print beside each entry. */
			$data['entries'][$row->id]['actionEdit'] = html::anchor('user/editUser/'. $row->id, html::image('img/edit-copy.png', 'Edit this account'));		
			$data['entries'][$row->id]['actionDelete'] = html::anchor('admin/deleteAccount/' . $row->id, html::image('img/edit-delete.png', 'Delete this account'));
		}	
		
		$this->view->content = new View('admin/list', $data);
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
					$this->addMessage("Failed to delete account with ID: $id! Please try again.");				
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
				$data['entityType'] = 'account';
				$data['entityName'] = $row->email;
				$this->view->content = new View('admin/delete', $data);
			}			
		}
		
		/* Row not defined/id was not set/deletion of a non existant ID. */
		else {
			$this->addMessage("The specified account no longer exists.");	
			url::redirect('admin/manageAccounts');
		}
	}
	
	function manageRegistrations()
	{
		$this->view->title = 'Admin: Manage Registrations';
		$this->view->heading = 'Registration Management';
		$this->view->subheading = 'Administration Area';
	}
	
	function managePasses() 
	{
		$this->view->title = 'Admin: Manage Passes';
		$this->view->heading = 'Pass Management';
		$this->view->subheading = 'Administration Area';
	}
	
	function manageConventions()
	{
		$this->view->title = 'Admin: Manage Conventions';
		$this->view->heading = 'Convention Management';
		$this->view->subheading = 'Administration Area';
	}
}
