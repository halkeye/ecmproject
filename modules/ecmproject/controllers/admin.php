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
			$data['entries'][$row->id]['actionEdit'] = html::anchor('user/editUser?id=' . $row->id, html::image('img/edit-copy.png', 'Edit this account'));		
			$data['entries'][$row->id]['actionDelete'] = html::anchor('admin/deleteAccount?id=' . $row->id, html::image('img/edit-delete.png', 'Delete this account'));
		}	
		
		$this->view->content = new View('admin/list', $data);
	}
	
	function deleteAccount() {
		//Better way to do this...?
		$id = $this->input->get('id');
		$val = $this->input->post('Yes');
		
		if (isset($val))
		{
			$id = $this->input->post('id');
			if (ORM::factory('Account')->delete( $id )) 
			{
				$this->addMessage("Account with ID: $id was deleted.");				
				url::redirect('admin/manageAccounts');
			}
			else
			{
				$this->addMessage("Failed to delete account with ID: $id!");				
				url::redirect('admin/manageAccounts');
			}				
		}
		else if (isset($id))
		{
			//Fetch the account to be deleted. 			
			$row = ORM::factory('Account')->find( $id ); 
			
			if ($row->loaded == TRUE) 
			{
				$this->view->title = 'Admin: Delete Account';
				$this->view->heading = 'ARE YOU SURE?';
				$this->view->subheading = 'Deleting anything is not to be taken lightly.';
				
				$data['id'] = $row->id;
				$data['entityType'] = 'account';
				$data['entityName'] = $row->email;
				$this->view->content = new View('admin/delete', $data);
			}
			else 
			{
				$this->view->title = 'Admin: Delete Account';
				$this->view->heading = 'Delete Account';
				$this->view->subheading = 'Administration Area';
				$this->view->content = '<p>The account requested for deletion does not exist.</p>';		
			}
		}	
		else
		{							
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
