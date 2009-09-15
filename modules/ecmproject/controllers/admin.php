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

	function manageAccounts()
	{
		$this->view->title = 'Admin: Manage Accounts';
		$this->view->heading = 'Account Management';
		$this->view->subheading = 'Administration Area';
		
		$data['entries'] = array();
		$rows = ORM::factory('Account')->find_all();		

		/* Go through each object in the iterator. */
		foreach ($rows as $row)
		{
			$data['entries'][$row->id] = array();
			$data['entries'][$row->id]['id'] = $row->id;
			$data['entries'][$row->id]['email'] = $row->email;
			
			if ($row->status == Account_Model::ACCOUNT_STATUS_UNVERIFIED)
				$data['entries'][$row->id]['status'] = 'UNVERIFIED';
			else if ($row->status == Account_Model::ACCOUNT_STATUS_VERIFIED)
				$data['entries'][$row->id]['status'] = 'VERIFIED';
			else if ($row->status == Account_Model::ACCOUNT_STATUS_BANNED)
				$data['entries'][$row->id]['status'] = 'BANNED';
			else
				$data['entries'][$row->id]['status'] = 'UNKNOWN STATUS';
				
			//$data['entries'][$row->id]['created'] = date("M j, Y g:i a", $row->created);
			
			/* Replace with a : ?*/
			if (isset($row->login))
				$data['entries'][$row->id]['login'] = date("M j, Y H:i", $row->login);		
			else
				$data['entries'][$row->id]['login'] = '--';
		}
		
		//Actions to print beside each entry.
		$data['actions'] = array();
		$data['actions']['edit'] = html::image('img/edit-copy.png', 'Edit this account');
		
		$data['actions']['delete'] = html::image('img/edit-delete.png', 'Delete this account');
		
		$this->view->content = new View('admin/list', $data);
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
