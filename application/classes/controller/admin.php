<?php defined('SYSPATH') OR die('No Direct Script Access');

/**
 * Admin Controller
 * 
 * All admin functionality is implemented here.
 * @author Stephen Tiu <stt@sfu.ca>
 * @version 1.1
 * @package ecm
 *
 * TODO: Generize manage/create/edit.
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
        $this->template->title = 		__('Administration');
        $this->template->heading = 		__('Administration');
        $this->template->subheading = 	__('Manage the various parts of this system.');
                    
        $this->template->content = new View('admin/main');
    }

    function action_manageConventions($page = NULL) {
        // Set headers
        $this->template->title =        __('Admin: Event List');
        $this->template->heading =      __('Admin: Event List');
        $this->template->subheading =   __('Create, modify and delete events.');
		
        $this->genericManageEntity('Convention', $page);            
    }    
    function action_manageAccounts($page = NULL) {
        // Set headers
        $this->template->title = "Administration: Manage Accounts";
        $this->template->heading = "Administration: Manage Accounts";
        $this->template->subheading = "Create, edit and delete accounts";
                
		$this->genericManageEntity('Account', $page); 					
    }
	
	//Have two optional attributes: convention_id and convention rows for a select.
	//Allow for n attributes to be included as necessary? array($k => $v)
	
    function action_managePasses($convention_id = NULL, $page = NULL) {
        // Set headers
        $this->template->title = 		__('Admin: Manage Tickets');
        $this->template->heading = 		__('Admin: Manage Tickets');
        $this->template->subheading = 	__('Create, modify and delete tickets associated with events.');
            
        $crows = ORM::factory('Convention')->find_all();    
		
		$convention_id = $this->session->get_once('admin_convention_id', Controller_Admin::getConventionId($convention_id, $crows) );                              
        $crows = $crows->as_array('id', 'name');    
		
        $total_rows = Model_Pass::getTotalPasses($convention_id);
        $start = ( Controller_Admin::getMultiplier($page) * Controller_Admin::ROWS_PER_PAGE );  
        $rows = ORM::factory('Pass')
            ->where('convention_id','=',$convention_id)
            ->limit( Controller_Admin::ROWS_PER_PAGE )
            ->offset( $start )
            ->find_all();
            
        $this->validateRows($rows, $total_rows, 'ticket');             
        $data = $this->generateViewRows($rows, 'Pass');   
                
        // Set callback path for form submit (change convention, jump to page)
        $this->template->content = new View('admin/list', array(
                'entity' => 'Pass',
                'crows' => $crows, 
                'callback' => 'admin/managePasses', 
                'createText' => __('Create new Ticket'),
                'createLink' => url::site('admin/createPass',TRUE),
                'rows' => $data['entries'], 
                'convention_id' => $convention_id,
                'page' => $page,
                'total_rows' => $total_rows)
            );
    }
    function action_manageRegistrations($convention_id = NULL, $page = NULL) {
        // Set headers
        $this->template->title = "Administration: Manage Registrations";
        $this->template->heading = "Administration: Manage Registrations";
        $this->template->subheading = "Create, edit and delete registrations related to a convention";
                
        // Get all conventions, determine and validate convention_id, and get total number of passes for the particular convention_id.
        $crows = ORM::factory('Convention')->find_all();    
        $convention_id = Controller_Admin::getConventionId($convention_id, $crows);                     
        $crows = $crows->as_array('id', 'name');                    
        $total_rows = Model_Registration::getTotalRegistrations($convention_id);
        
        // Calculate the offset.
        $start = ( Controller_Admin::getMultiplier($page) * Controller_Admin::ROWS_PER_PAGE );  
        $rows = ORM::factory('Registration')
            ->where('convention_id','=',$convention_id)
            ->limit( Controller_Admin::ROWS_PER_PAGE )
            ->offset( $start )
            ->find_all();
            
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
            $data['actions']['edit'] = html::anchor('admin/editRegistration/'. $row->id, html::image(url::site('/static/img/edit-copy.png', TRUE), array('title'=>__('admin.edit_account'))));
            $data['actions']['delete'] = html::anchor('admin/deleteRegistration/' . $row->id, html::image(url::site('/static/img/edit-delete.png',TRUE), array('title'=>__('admin.delete_account'))));
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
    function action_managePayments($rid = NULL) {
        if (!isset($rid) || !is_numeric($rid))
            die('Get out of here!');
            
        //Get registration and then get associated payment entries (if any) 
        $reg = ORM::Factory('Registration',$rid);
        if (! $reg->loaded() )
        {
            $this->addError('Invalid registration. Maybe someone deleted it when you weren\'t looking?');
            $this->request->redirect('manageRegistrations');
        }       

        $rows = ORM::Factory('Payment')->where("register_id",'=',$rid)->find_all();
        $pass = ORM::Factory('Pass',$reg->pass_id);
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
    function action_manageAdmin() {
        $this->requirePermission('superAdmin'); //Require extra permissions to manage administrators.
        
        // Set headers
        $this->template->title = "Administration: Manage Admins";
        $this->template->heading = "Administration: Manage Admins";
        $this->template->subheading = "Add or remove admin users.";
                
        //$total_rows = Model_Account::getTotalAdminAccounts(); //Modify to getTotalAccounts with admin permissions.
                                
        // Calculate the offset.
        //$start = ( Controller_Admin::getMultiplier($page) * Controller_Admin::ROWS_PER_PAGE );    
        $rows = ORM::factory('Account')
            ->join('accounts_usergroups')->on('accounts_usergroups.account_id', '=', 'accounts.id')
            ->join('usergroups')->on('accounts_usergroups.usergroup_id', '=', 'usergroups.id')
            ->where('usergroups.name', '=', Controller_Admin::ADMIN_USERGROUP)
            ->find_all();

        // Header entry. (View with no data generates a header)
        $data['entries'][0] = new View('admin/ListItems/AdminAccountEntry');
        foreach ($rows as $row)
        {
            $data['actions']['edit'] = html::anchor('admin/deleteAdmin/' . $row->id, html::image(url::site('/static/img/edit-delete.png',TRUE), NULL, __('admin.edit_account')));          
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
    function action_manageLocations($page = NULL) {
        $this->template->title = 		__('Admin: Locations');
        $this->template->heading = 		__('Admin: Locations');
        $this->template->subheading = 	__('Manage Ticket Sale Locations and their prefixes (used for registration ID generation)');
		
		$this->genericManageEntity('Location', $page);  
	}
   
    /* CREATE Actions */   
    function action_createAccount() {
        $this->template->title = 		__('Admin: Create an Account');
        $this->template->heading = 		__('Admin: Create an Account');
        $this->template->subheading = 	__('Create an Account');

        $acct = ORM::factory('Account');    
        $fields = $acct->default_fields;
        $fields['status']['values'] = Model_Account::getVerifySelectList();
        
        if ($post = $this->request->post())
        {     
			$extra_validation = Validation::Factory($post);
			$extra_validation->rule('password', 'matches', array(':validation', 'password', 'confirm_password'));
			$acct->values($post);
			try {
                $acct->save($extra_validation);                              
                $this->addMessage( __('Created a new account, ') . $acct->email);
				$this->requireVerification($acct); // Require verification if status UNVERIFIED.			
                $this->request->redirect('admin/manageAccounts');			
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);      
            } 		    
        } 
		else { 		     
			$post = $acct->as_array();
		}
           
		$this->template->content = new View('admin/Account', array(
			'row' => $acct->as_array(),
			'fields' => $fields,
			'callback' => 'createAccount'
		));      
    }
    function action_createPass()  {
        // Set headers
        $this->template->title = 		__('Admin: Create a Ticket');
        $this->template->heading = 		__('Admin: Create a Ticket');
        $this->template->subheading = 	__('Create a ticket associated to an event');
        
        $pass = ORM::factory('Pass');             
        $fields = $pass->default_fields;
		$crows = ORM::factory('Convention')->find_all()->as_array('id', 'name');   
        $fields['convention_id']['values'] = $crows;
        
        if ($post = $this->request->post())
        {
            $post['startDate'] = ECM_Form::parseSplitDate($post, 'startDate');
            $post['endDate'] = ECM_Form::parseSplitDate($post, 'endDate');
            $pass->values($post);
			
            try {
                $pass->save();
                $this->addMessage( __('Created a new ticket, ') . $pass->name);
				$this->session->set('admin_convention_id', $post['convention_id']);
                $this->request->redirect('admin/managePasses'); 				
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);      
            }               
        }   
        else
        {
            $post = $pass->as_array();
        }

        $this->template->content = new View('admin/Pass', array(
            'crows' => $crows,  
            'row' => $post,
            'fields' => $fields,
            'callback' => 'createPass'
        ));
    }
    function action_createConvention() {
        $this->template->title =        __('Admin: Create an Event');
        $this->template->heading =      __('Admin: Create an Event');
        $this->template->subheading =   __('Create a new event');
        
        $conv = ORM::factory('Convention'); 
        $fields = $conv->default_fields;
        $post = $conv->as_array();
        
        if ($post = $this->request->post())
        {                   
            $conv->values($post);
            try {
                $conv->save();
                $this->addMessage(__('Created a new event, ') . $conv->name);
                $this->request->redirect('admin/manageConventions');
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);      
            }               
            catch (Exception $e)
            {
                $this->addError("Oops. Something went wrong ... but it's not your fault. Contact the system maintainer please!");
            }
        }
        $this->template->content = new View('admin/Convention', array(
            'row' => $post,
            'fields' => $fields,
            'callback' => 'createConvention'
        )); 
    }   
    function action_createRegistration() {
        // Set headers
        $this->template->title = 		__('Admin: Create Registration(s)');
        $this->template->heading = 		__('Admin: Create Registration(s)');
        $this->template->subheading = 	__('Create registrations for an existing event.');
    
        $reg = ORM::factory('Registration');
        $crows = ORM::factory('Convention')->find_all()->as_array('id', 'name');    
        
        $fields = $reg->formo_defaults;
        $fields['convention_id'] = array( 'type'  => 'select', 'label' => 'Convention', 'required'=>true );
        $fields['convention_id']['values'] = $crows;
    
        if ($post = $this->request->post())
        {
			if (Model_Convention::validConvention($post['convention_id'])) 
			{
				$this->session->set('admin_convention_id', $post['convention_id']);
				$this->request->redirect('admin/createRegistration2/');
			}
			else 
			{
				$this->addError("You must select a valid convention before you can continue.");   
			}           
        }

		$this->template->content = new View('admin/Registration', array(
			'row' => $reg->as_array(),
			'fields' => $fields,
			'callback' => 'createRegistration'
		));             
    }
    function action_createRegistration2() {
    
		$post = $this->request->post();
		$alt_convention_id = $this->hasValue($post, 'convention_id') ? $post['convention_id'] : 0;
		$convention_id = $this->session->get_once('admin_convention_id', $alt_convention_id); 
		
		if (!$convention_id && !isset($post['convention_id']))
		{
			$this->addError("You must select a valid convention before you can continue");
			$this->request->redirect('admin/createRegistration');
		}		
            
        $reg = ORM::factory('Registration');
		$con = ORM::factory('Convention', $convention_id);
		if (! $con->loaded() )
        {
            $this->addError('This convention appears to have disappeared into the nether. Please select again.');
            $this->request->redirect('admin/createRegistration/');
        }
		
        $fields = $reg->formo_defaults;        
		$locations = ORM::Factory('Location')->find_all()->as_array('prefix', 'prefix');
		
        $fields['pass_id']['values'] = ORM::Factory('Pass')->where('convention_id', '=', $convention_id)->find_all()->as_array('id', 'name');
		$fields['status']['values']	 = Model_Registration::getStatusValues();		
		$fields['comp_loc']['values'] = $locations;
		$fields['convention_id'] = $convention_id;
        
		$this->template->title = 		__('Admin: Create Registration(s) for ') . $con->name;
        $this->template->heading = 		__('Admin: Create Registration(s) for ') . $con->name;
        $this->template->subheading = 	__('Create registrations for ') . $con->name;
		
        if ($post)
        {           
			$reg->values($post); //Deal with web allocation?
			$reg->reg_id = $this->build_regID($post, $locations, $convention_id); //If validation fails, empty regID and save() will fail.
			$extra_validation = $this->validateEmailOrPhone($post);
			
			//Validate non-object fields comp_loc, comp_id and return a reg_id if fields are valid.		
			//TODO: Merge/fix validation errors.
			try {
				if ( $reg->reserveTickets() ) { //Reserve tickets. Return at least 1 except in case of failure (not enough tickets left).
					$reg->save($extra_validation); 
					$reg->finalizeTickets(); //Save has gone through. Finalize reservation.
					$this->addMessage( __('Created a new registration, ') . $reg->reg_id);
					$this->session->set('admin_convention_id', $post['convention_id']);
				}
				else if ($reg->pass_id > 0) {					
					$this->addError("No more tickets to allocate for " . $fields['pass_id']['values'][$reg->pass_id] . '. Please select a different pass.');				
				}	
				else {
					$this->addError("No pass selected. Please select a pass."); 
				}
			}
			catch (ORM_Validation_Exception $e)
			{				
				$this->parseErrorMessages($e);   			
			}     
			
			$reg->releaseTickets(); //Something went wrong during saving. Rollback everything.
		}
		else
        {			
            $post = $reg->as_array();
			$post['convention_id'] = $convention_id;
			$post['status'] = Model_Registration::STATUS_PAID;
        }
		
		/* Full registration at this step. */
		$this->template->content = new View('admin/Registration2', array(
			'row' => $post,
			'fields' => $fields,
			'callback' => "createRegistration2"
		));
    }
    function action_createPayment($rid = NULL) {
        if ($rid == NULL || !is_numeric($rid))
            die('Get out of here!');
            
        $pay = ORM::Factory('Payment'); 
        $reg = ORM::Factory('Registration',$rid);
        $pass = ORM::Factory('Pass',$reg->pass_id);
        
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
    function action_createLocation() {
		$this->template->title =        __('Admin: Create a Location');
        $this->template->heading =      __('Admin: Create a Location');
        $this->template->subheading =   __('Create a new location');
        
        $loc = ORM::factory('Location'); 
        $fields = $loc->formo_defaults;
        $post = $loc->as_array();
        
        if ($post = $this->request->post())
        {                   
            $loc->values($post);
            try {
                $loc->save();
                $this->addMessage(__('Created a new location, ') . $loc->location);
                $this->request->redirect('admin/manageLocations');
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);      
            }               
            catch (Exception $e)
            {
                $this->addError("Oops. Something went wrong ... but it's not your fault. Contact the system maintainer please!");
            }
        }
        $this->template->content = new View('admin/Location', array(
            'row' => $post,
            'fields' => $fields,
            'callback' => 'createLocation'
        ));
	}
	
	/* EDIT Actions */
	function action_editAccount($id = NULL) {         
        /* If no ID or bad ID defined, kill it with fire. */
        if ($id == NULL || !is_numeric($id))
            die('No direct access allowed. Go away D:');
                
        $acct = ORM::factory('Account',$id);
        $fields = $acct->default_fields;
        $fields['status']['values'] = Model_Account::getVerifySelectList();
        
        /* If pass is not loaded, we have a problem */
        if (!$acct->loaded())
        {
            $errorMsg = 'That pass does not exist! Maybe someone deleted it while you were busy?<br />';                
            $this->request->redirect('admin/manageAccounts');
        }
		
		// Set headers
        $this->template->title = 		__('Admin: Editing account, ') . $acct->email;
        $this->template->heading = 	 	__('Admin: Editing account, ') . $acct->email;
        $this->template->subheading = 	__('Edit the details of an account');
        
        if ($post = $this->request->post())
        {		
			//Why not just add to ruleset of account...? Unset password fields for editing an account so validation rules don't trigger?			
			$extra_validation = Validation::Factory($post);
          		
			if ( $this->hasValue( $post, 'password' ) || $this->hasValue( $post, 'confirm_password' ) )
			{
                $extra_validation->rule('password', 'matches', array(':validation', 'password', 'confirm_password'));
			}
			else 
			{
				unset($post['password']);
				unset($post['confirm_password']);
			}
		
			$acct->values($post);
			try {
                $acct->save($extra_validation);                              
                $this->addMessage('Successfully edited ' . $acct->email);
				$this->requireVerification($acct); // Require verification if status UNVERIFIED.	
                $this->request->redirect('admin/manageAccounts');			
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);      
            }             
			catch (Exception $e)
            {
                $this->addError("Oops. Something went wrong and it's not your fault. Contact the system maintainer please!");
            }
        }   
        else { 		     
			$post = $acct->as_array();
		}
         
        //Parse UNIX timestamp back to something we can use.        
        $this->template->content = new View('admin/Account', array(
            'row' => $acct->as_array(),
            'fields' => $fields,
            'callback' => "editAccount/$id"
        ));
        
    }
    function action_editPass($id = NULL) {
        // Set headers       
        
        /* If no ID or bad ID defined, kill it with fire. */
        if ($id == NULL || !is_numeric($id))
            die('No direct access allowed. Go away D:');
                
        $pass = ORM::factory('Pass',$id);
        $crows = ORM::factory('Convention')->find_all()->as_array('id', 'name');        
        $fields = $pass->default_fields;
        $fields['convention_id']['values'] = $crows;
        
        /* If pass is not loaded, we have a problem */
        if (!$pass->loaded())
        {
            $errorMsg = 'That pass does not exist! Maybe someone deleted it while you were busy?<br />';                
            $this->request->redirect('admin/managePasses');
        }
		
		$this->template->title = 		__('Admin: Editing ticket "' . $pass->name . '"');
        $this->template->heading = 		__('Admin: Editing ticket "' . $pass->name . '"');
        $this->template->subheading = 	__('Edit the details of this ticket.');
        
        if ($post = $this->request->post())
        {
            $post['startDate'] = ECM_Form::parseSplitDate($post, 'startDate');
            $post['endDate'] = ECM_Form::parseSplitDate($post, 'endDate');
            $pass->values($post);
            try {
                $pass->save();                              
                $this->addMessage('Successfully edited ' . $pass->name);
				$this->session->set('admin_convention_id', $post['convention_id']);
                $this->request->redirect('admin/managePasses');			
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);
            }               
            catch (Exception $e)
            {
                $this->addError("Oops. Something went wrong and it's not your fault. Contact the system maintainer please!");
            }
        }   
        else {      
            $post = $pass->as_array();
        }   
        $this->template->content = new View('admin/Pass', array(
            'crows' => $crows,  
            'row' => $post,
            'fields' => $fields,
            'callback' => "editPass/$id"
        ));
    }
    function action_editConvention($id = NULL) {       
        /* If no ID or bad ID defined, kill it with fire. */
        if ($id == NULL || !is_numeric($id))
            die('No direct access allowed. Go away D:');
        
        $conv = ORM::factory('Convention', $id);    
        $fields = $conv->default_fields;
        
        /* If pass is not loaded, we have a problem */
        if (!$conv->loaded())
        {
            $errorMsg = 'That pass does not exist! Maybe someone deleted it while you were busy?<br />';                
            $this->request->redirect('admin/manageConventions');
        }
        
        $this->template->title =        __('Admin: Editing ' . $conv->name); //Escape output?
        $this->template->heading =      __('Admin: Editing ' . $conv->name);
        $this->template->subheading =   __('Edit the details of this event');
        
        if ($post = $this->request->post())
        {   
            $conv->values($post);
            try {
                $conv->save();
                $this->addMessage('Created a newly minted convention named ' . $conv->name);
                $this->request->redirect('admin/manageConventions');
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);      
            }               
            catch (Exception $e)
            {
                $this->addError("Oops. Something went wrong ... but it's not your fault. Contact the system maintainer please!");
            }                   
        } 
            
        $this->template->content = new View('admin/Convention', array(
            'row' => $conv->as_array(),
            'fields' => $fields,
            'callback' => "editConvention/$id"
        ));         
    }
    function action_editRegistration($rid = NULL) {
        /* Can change all fields. */    
        //Not allowed to be lazy in checking input here.
        if ( (!isset($rid) || !is_numeric($rid) || $rid <= 0) )
            die("You're not allowed to be here!");
            
        //TODO: Deal with the case where one of the below fails to load.
        $reg = ORM::factory('Registration',$rid);
        if (! $reg->loaded() )
        {
            $this->addError('Grace says this registration does not exist.');
            $this->request->redirect('admin/manageRegistrations/');
        }
        
        $crows = ORM::factory('Convention')->find_all()->as_array('id', 'name');    
        $fields = $reg->formo_defaults;
        
        $fields['pass_id']['values'] = ORM::Factory('Pass')->where("convention_id", $reg->convention_id)->find_all()->as_array('id', 'name');
        
        if ($post = $this->request->post())
        {
            $fieldName = 'dob';
            $post[$fieldName] = ECM_Form::parseSplitDate($post, $fieldName);
        
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
    function editPayment($id = NULL) {
        if ($id == NULL || !is_numeric($id))
            die('Get out of here!');
            
        $pay = ORM::Factory('Payment', $id); 
        $reg = ORM::Factory('Registration',$pay->register_id);
        $pass = ORM::Factory('Pass',$reg->pass_id);

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
    function action_editLocation($id = NULL) {
		/* TODO: Add a check to see if a location is in use. */
        if ($id == NULL || !is_numeric($id))
            die('No direct access allowed. Go away D:');
				
        $loc = ORM::factory('Location', $id); 
        $fields = $loc->formo_defaults;
		
		/* If pass is not loaded, we have a problem */
        if (!$loc->loaded())
        {
            $errorMsg = 'Non-existent location! Maybe someone wiped it off the map when you weren\'t looking?<br />';                
            $this->request->redirect('admin/manageLocation');
        }
        
		$this->template->title =        __('Admin: Editing ') . $loc->location;
        $this->template->heading =      __('Admin: Editing ') . $loc->location;
        $this->template->subheading =   __('Edit the details of a location.');
		
        if ($post = $this->request->post())
        {                   
            $loc->values($post);
            try {
                $loc->save();
                $this->addMessage(__('Edited location, ') . $loc->location);
                $this->request->redirect('admin/manageLocations');
            }
            catch (ORM_Validation_Exception $e)
            {
                $this->parseErrorMessages($e);      
            }               
            catch (Exception $e)
            {
                $this->addError("Oops. Something went wrong ... but it's not your fault. Contact the system maintainer please!");
            }
        }
		else {
			$post = $loc->as_array();
		}
		
        $this->template->content = new View('admin/Location', array(
            'row' => $post,
            'fields' => $fields,
            'callback' => "editLocation/$id"
        ));
	}
	
	
    function action_deleteConvention($id = NULL)
    {
        Controller_Admin::__delete($id, 'Convention', 'deleteConvention', 'manageConventions');
    }
    
    function action_deleteAccount($id = NULL) {
        Controller_Admin::__delete($id, 'Account', 'deleteAccount', 'manageAccounts');
    }
    
    function action_deletePass($id = NULL)
    {
        Controller_Admin::__delete($id, 'Pass', 'deletePass', 'managePasses');      
    }
    
    function action_deleteRegistration($id = NULL)
    {
        Controller_Admin::__delete($id, 'Registration', 'deleteRegistration', 'manageRegistrations');
    }
    
    function action_deletePayment($rid = NULL, $id = NULL)
    {   
        //TODO: Update status as necessary!
        Controller_Admin::__delete($id, 'Payment', "deletePayment/$rid", "managePayments/$rid", true);              
    }
	function action_deleteLocation($id = NULL) {   
        Controller_Admin::__delete($id, 'Location', 'deleteLocation', 'manageLocations');             
    }
	
    
    function setAdmin()
    {   
        $this->requirePermission('superAdmin'); //Require extra permissions to manage administrators.
        $fields = array('email' => array( 'type'  => 'text', 'label' => 'Email', 'required'=>true                               ));
        
        if ($post = $this->request->post())
        {
            $post['email'] = trim($post['email']);
            $group = ORM::Factory('usergroup', Controller_Admin::ADMIN_USERGROUP);
            $acct = ORM::Factory('Account')->where('email', '=', $post['email'])->find();
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
            
        $acct = ORM::Factory('Account',$id);
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
    
    function action_search($entity = NULL)  
    {       
        $this->template->subheading = __('Displaying search results');
    
        //Determine search term (POST).
        $post = $this->request->post(); 
        
        if (isset($post['search_term']))
            $search_term = '%' . $post['search_term'] . '%';        
        else
            $search_term = null;
    
        //Go context sensitive...
        $rows = null;
        if ($entity == 'Registration' && $search_term != null)
        {
            $this->template->heading = __('Searching for Registrations');
            $rows = ORM::Factory('Registration')
                ->or_where('email', 'LIKE', $search_term)
                ->or_where('gname', 'LIKE', $search_term)
                ->or_where('sname', 'LIKE', $search_term)
                ->or_where('id', '=', $search_term)
                ->find_all();
        }
        else if ($entity == 'Account' && $search_term != null)
        {
            $this->template->heading = __('Searching for Accounts');
            $rows = ORM::Factory('Account')
                ->or_where('email', 'LIKE', $search_term)
                ->or_where('id', '=', $search_term)
                ->find_all();
        }
        else if ($entity == 'Convention' && $search_term != null)
        {
            $this->template->heading = __('Searching for Events');
            $rows = ORM::Factory('Convention')
                ->or_where('name', 'LIKE', $search_term)
                ->or_where('id', '=', $search_term)
                ->find_all();
        }
        else if ($entity == 'Pass' && $search_term != null)
        {
            $this->template->heading = __('Searching for Tickets');
            $rows = ORM::Factory('Pass')
                ->or_where('name', 'LIKE', $search_term)
                ->or_where('id', '=', $search_term)
                ->find_all();
        }
    
        // Header entry. (View with no data generates a header)                 
        if ($rows != null)
        {           
            $data['entries'][0] = new View("admin/ListItems/$entity" . 'Entry');            
            foreach ($rows as $row)
            {
                $data['actions']['edit'] = html::anchor(
                    "admin/edit$entity/". $row->id,
                    html::image(url::site('/static/img/edit-copy.png', TRUE), array('title'=>__("Edit $entity")))
                );
                $data['actions']['delete'] = html::anchor(
                    "admin/delete$entity/" . $row->id,
                    html::image(url::site('/static/img/edit-delete.png',TRUE), array('title'=>__("Delete $entity")))
                );          
            
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
            
        $row = ORM::factory($entityType,$id);           
        
        if (isset($row->name))
            $entityName = $row->name;
        else if (isset($row->email))
            $entityName = $row->email;
		else if (isset($row->location))
			$entityName = $row->location;
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
                        $pay = ORM::Factory('Payment',$id);
                        $reg = ORM::Factory('Registration',$pay->register_id);
                        $pass = ORM::Factory('Pass',$reg->pass_id);                     
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
        $crows = ORM::factory('Convention')->find_all()->as_array('id', 'name');    
        
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
                        
            $this->addError("One or more fields are blank!");   //pass_field_reg_error_convention_id_missing    
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
     * const STATUS_NOT_ENOUGH   = 2; // Payment recieved is not enough to pay cost of pass.
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
	
	
	
	/*
	* Generic data listing method. Generates a list view with create/edit/delete actions based on the $entity name
	* and a page number. 
	*
	* Notes: The $entity name must match the name of the Model and the action must be named accordingly: action_manage$entity
	* 		 Any custom functionality is not supported currently - you must implement your own manage controller code.
	*
	* TODO: Add callback support to override default logic (page data, row processing, required information).
	*
	* string $entity
	* int $page
	* array opt_conditions = NULL
	* array opt_viewAttributes = NULL
	*
	* returns null
	*/
	private function genericManageEntity($entity, $page = NULL, $opt_conditions = NULL, $opt_viewAttributes = NULL) {
		//Get total number of rows. 
		$total_rows = ORM::Factory($entity)->count_all();
				
		//Get the page data. Errors automatically generated in the event of an empty result.
		if ( $rows = $this->getPageData($total_rows, $entity, $page, $opt_conditions) )
		{
			$data = $this->generateViewRows($rows, $entity); 						
		}  
		//Invalid page number. Set required variables.
		else 
		{
			$data = array();
			$data['entries'] = array();
		}
		
		$this->template->content = new View('admin/list', array(
			'entity' => $entity,
			'callback' => "admin/manage$entity", 
			'createText' => __("Create new $entity"),
			'createLink' => url::site("admin/create$entity", TRUE), 
			'rows' => $data['entries'], 
			'page' => $page,
			'total_rows' => $total_rows)
		); 
	}
	
	/*
	* Given an entity, calculate the current page and fetch the entity data associated with that page. 
	* Number of data rows retrieved is defined by the Controller_Admin::ROWS_PER_PAGE constant.
	*
	* int $total_rows
	* string $entity
	* int $page
	* returns Database_Result array()
	* @ref http://kohanaframework.org/3.1/guide/api/ORM#find_all
	*/
	private function getPageData($total_rows, $entity, $page, $opt_conditions = NULL) 
	{					
		//If $page is not a number, getMultiplier will return 0.
		$start = ( Controller_Admin::getMultiplier($page) * Controller_Admin::ROWS_PER_PAGE );
		$rows = ORM::factory($entity)->limit(Controller_Admin::ROWS_PER_PAGE)->offset($start)->find_all();		
		
		//Add optional where conditions.
		
		//Error conditions.
		if (count($rows) == 0 && $total_rows > 0) 
		{
			$this->addError( __('Invalid page number') );
		}
		else if(!count($rows))
		{
			$this->addError( __("You should probably create an $entity to start with.") );
		}		
		
		return $rows;
	}
	
	/* DEPRECATED: Remove all references. */
	private function validateRows($rows, $total_rows, $entity) {
		if (count($rows) == 0 && $total_rows > 0)
        {
            $this->addError('Invalid page number.');
        }
        else if ($total_rows == 0)
        {
            $this->addError("You should probably create an $entity to start with.");
        }     
	}
	
	
	/*
	* Generic row generation method which adds two actions (edit and delete) per row.
	* 
	* $rows - The database results to be used in generating rows.
	* $entity - The entity matching those rows.
	*/
	private function generateViewRows($rows, $entity) {
		//'admin/ListItems/ConventionEntry'
		$data['entries'][0] = new View("admin/ListItems/$entity" . 'Entry');
        foreach ($rows as $row)
        {
            $data['actions']['edit'] = html::anchor(
                "/admin/edit$entity/". $row->id ,
                html::image(url::site('/static/img/edit-copy.png', TRUE), array('title'=>__("Edit $entity"))), 
				null, null, true
            );
            $data['actions']['delete'] = html::anchor(
                "/admin/delete$entity/" . $row->id,
                html::image(url::site('/static/img/edit-delete.png',TRUE), array('title'=>__("Delete $entity"))),
				null, null, true
            );
            $data['entries'][$row->id] = new View(                
				"admin/ListItems/$entity" . 'Entry', 
                array('row' => $row, 'actions' => $data['actions'])
            );
        } 

		return $data;
	}
	
	
	
	private function requireVerification($account) {
		if (!$account->isVerified()) {
			$vcode = $account->generateVerifyCode(Model_Verificationcode::TYPE_VALIDATE_EMAIL);
			$account->sendValidateEmail($vcode->original_code);
		}
	}
	
    private function parseErrorMessages($e) {
        $errorMsg = 'Oops. You entered something bad! Please fix it! <br />';               
        $errors = $e->errors('admin'); //Loads from directory specified by argument here.
        foreach ($errors as $error)		
			if ( is_array($error) ) {
				foreach($error as $nested_error) {
					$errorMsg = $errorMsg . ' ' . $nested_error . '<br />'; 
				}			
			}
			else {
				$errorMsg = $errorMsg . ' ' . $error . '<br />';                    
			}
			
        $this->addError($errorMsg);     
    }
    
	private function hasValue($model, $field) {
		if (!isset($model[$field])) {
			return false;
		}
	
		$value = trim($model[$field]);
		return !( empty($value) );
	}	
	private function validateEmailOrPhone($post) {
		$extra_validation = Validation::Factory($post);
		
		if (empty($post['phone'])) {
			$extra_validation->rule('email', 'not_empty');
		}		
		else if (empty($post['email'])) {
			$extra_validation->rule('phone', 'not_empty');
		}		
		
		return $extra_validation;
	}
	private function build_regID($post, $locations, $convention_id) {
		//ticket_counters
		$validate = Validation::Factory($post)
			->rule('comp_loc', 'in_array', array(':value', array_values($locations)))
			->rule('comp_id', 'numeric')
			->rule('comp_id', 'not_empty');
		//				
			
		if ( !$validate->check() )
		{	
			$error = '';
			foreach($validate->errors('reg_id') as $error_msg) 
			{
				$error .= $error_msg . '<br />';
			}
			
			if ($error) {
				$this->addError($error);
			}
			
			return '';
		}
			
		return sprintf('%s_%s_%s', $convention_id, $post['comp_loc'], $post['comp_id']);
	}	
    
    public function action_testClock() {
        header("Content-type: text/plain");
        print date("r");
        exit;       
    }
}
