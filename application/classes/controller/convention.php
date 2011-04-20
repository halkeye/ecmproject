<?php defined('SYSPATH') OR die('No Direct Script Access');

/**
 * User Controller
 * 
 * All user related webpage and functionality.
 * @author Gavin Mogan <ecm@gavinmogan.com>
 * @version 1.0
 * @package ecm
 */


class Controller_Convention extends Base_MainTemplate 
{
    const STEP1 = "convention/editReg";
    const STEP2 = "convention/checkout";

    function before()
    {
        $ret = parent::before();
        $this->requireLogin();
        $this->requireVerified();
        $this->addMenuItem(
                array('title'=>'Add Registration', 'url'=>Controller_Convention::STEP1)
        );
        return $ret;
    }

    function action_index()
    {
        $regs = Model_Registration::getByAccount($this->auth->get_user()->id);
        if (!$regs->count()) { $this->request->redirect(Controller_Convention::STEP1); }
        else { $this->request->redirect('/convention/checkout'); }

        $data['conventions'] = array();
        foreach ($regs as $row) 
        {
            $row->incomplete = $row->pass_id ? false : true;
            $data['conventions'][$row->convention_id]->name = $row->convention_name;
            $data['conventions'][$row->convention_id]->id = $row->convention_id;
            $data['conventions'][$row->convention_id]->regs[] = $row;
        }
        $this->template->content = new View('convention/index', $data);
        
        return;
    }

	/* Force limitation: cannot delete registrations unless they are unprocessed */
	//TODO: Put strings to lang file.
	function action_deleteReg($reg_id = NULL)
	{
		$reg_id = isset($reg_id) ? intval($reg_id) : NULL;
		$reg = ORM::factory('registration', $reg_id);
		
		if (!$reg->loaded())
		{
			$this->addError(__('convention.not_loaded'));
			$this->request->redirect("convention/checkout");
		}
		
		/* Prevent users from using deleteReg with an arbitrary number */
		if ($reg->account_id != $this->auth->getAccount()->id)
		{
			$this->addError(__('convention.not_loaded') . $reg->account_id);
			$this->request->redirect("convention/checkout");
		}
		
		if ($reg->status != Model_Registration::STATUS_UNPROCESSED)
		{
			$this->addError(__('convention.registration_already_processed_unable_to_edit'));
			$this->request->redirect("convention/checkout");
        }
				
		if ($post = $this->request->post())
        {
			if ($val = $post['Yes'])
			{
				if ($reg->delete())
				{
					$this->addMessage(__('convention.delete_success'));	
				}
				else
				{
					$this->addError(__('convention.delete_error'));	
				}
			}
			
			$this->request->redirect("convention/checkout");
		}
				
		$this->template->content = new View('convention/deleteReg', array('reg'=>$reg));

	}
	
	function action_viewReg($reg_id = NULL)
	{
		$this->template->title = "Viewing Registration...";		
	
		$reg_id = isset($reg_id) ? intval($reg_id) : NULL;
		$reg = ORM::factory('registration', $reg_id);
		$pass = ORM::factory('Pass', $reg->pass_id);
		
		if (!$reg->loaded())
		{
			$this->addError(__('convention.not_loaded'));
			$this->request->redirect("convention/checkout");
		}
		
		$this->template->heading = $pass->name . ' for ' . $reg->gname . ' ' . $reg->sname;
		$this->template->subheading = 'Viewing registration...';
		$this->template->content = new View('convention/viewReg', array('reg' => $reg, 'pass' => $pass));
	}
	
    function action_editReg($reg_id = NULL)
    {
        $reg_id = isset($reg_id) ? intval($reg_id) : NULL;

        $reg = $reg_id ? ORM::factory('registration',$reg_id) : ORM::factory('registration');
        if (!$reg->loaded())
        {
            $reg->account_id    = $this->auth->get_user()->id;
            //$reg->email         = $this->auth->getAccount()->email;
        }
        else
        {
            if ($reg->status != Model_Registration::STATUS_UNPROCESSED)
            {
                $this->addError(__('convention.registration_already_processed_unable_to_edit'));
                $this->request->redirect('/convention/viewReg/'.$reg_id);
                return;
            }
        }
        $passesQuery = $reg->getPossiblePassesQuery();
            
        $fields = $reg->formo_defaults;
        $form = array();
        $errors = array();

        foreach (array_keys($fields) as $field) 
        { 
            $form[$field] = $reg->$field; 
            /*$errors[$field] = '';*/
        }
        foreach ($passesQuery->find_all() as $pass)
        {
            $fields['pass_id']['values'][$pass->id] = $pass->__toString();
        }

        if ($post = $this->request->post())
        {
            foreach ($fields as $fieldName=>$fieldData)
            {
                if ($fieldData['type'] == 'date')
                {
                    $post[$fieldName] = ECM_Form::parseSplitDate($post, $fieldName);
                }
            }

            if (!isset($post['agree_toc']))
                $post['agree_toc'] = false;
            $post['account_id'] = $this->auth->getAccount()->id;
            $reg->values($post);
            try {
                $reg->save();
                $this->request->redirect(Controller_Convention::STEP2);
                return;
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors('form_error_messages');
            }

            // repopulate the form fields
            $form = arr::overwrite($form, $post);
        }
        $this->template->content = new View('convention/register', array(
            'form'=>$form, 'errors'=> $errors,
            'fields'=>$fields, 'url' => ($reg_id ? 'convention/editReg/'. $reg_id : 'convention/editReg')
        ));
    }
    
    function action_registrationCancel($reg_id)
    {
        $reg_id = intval($reg_id);
        $reg = ORM::Factory('registration',$reg_id);
        $this->template->content = "cancel/fail page";
    }
    
    function action_registrationReturn()
    {
        $regids = array();
        /* Pull out some of the data returned from paypal success link */
        $count = 1;
        var_dump($_REQUEST);
        while ($_GET['item_number'.$count])
        {
            $data = explode('|', $_GET['item_number'.$count]);
            $regids[$data[0]] = array('id' => $data[0], 'pass_id' => $data[1]); 
            $count++;
        }

        $registrations = ORM::factory('registration')
            ->with('account')
            ->with('pass')
            ->in('registrations.id', array_keys($regids))
            ->find_all();
        foreach ($registrations as $reg)
        {
            /* Now if the status was unprocessed before, mark it as being processed (Anything else is handled by other handlers */
            if ($reg->status != Model_Registration::STATUS_UNPROCESSED)
                continue;

            /* We don't really trust this data so lets make sure people havn't messed with the params at all */
            if ($regids[$reg->id]['pass_id'] != $reg->pass->id)
                throw Exception('Data has been tampered with');

            $reg->status = Model_Registration::STATUS_PROCESSING;
            /* Update modules if they've been changed */
            $reg->save();
        }

        $this->template->heading = "Thank you";
        $this->template->subheading = "";
        $this->template->content = new View('convention/paypalReturn');
    }

    public function action_checkout()
    {
        $this->requireVerified();
        $this->template->heading    = __('convention.checkout_heading');
        $this->template->subheading = __('convention.checkout_subheading'); 


        $data = Kohana::config('paypal');
        $data['passes'] = ORM::Factory('registration')->getPossiblePassesQuery()->find_all();

        /* get all the registrations we need */
        $data['registrations'] = ORM::Factory('registration')->getForAccount($this->auth->getAccount()->id);
        /*if (!$data['registrations']->count()) 
        {
			$this->request->redirect('user/index'); 
        }
         */

        /* Config file is currently 'url', lets map it to 'paypal_url' incase any other url is used */
        $data['paypal_url'] = $data['url'];
        unset($data['url']);

        /* Where paypal should tell us about successful transactions */
        $data['notify_url'] = url::site('/paypal/registrationPaypalIPN',TRUE);
		
        ### FIXME - This needs an external url, so can't be localhost
        if (strpos($data['notify_url'], 'moocow.localhost') !== FALSE) {
            $data['notify_url'] = 'http://moocow.halkeye.net:4080/ecmproject/index.php/paypal/registrationPaypalIPN';
        }

        /* Where to send the user when we complete */ 
        $data['return_url'] = url::site('/convention/registrationReturn',TRUE);
        /* where to send the user if they back out */
        $data['cancel_url'] = url::site('/convention/registrationCancel',TRUE);

        /* Our "checkout template" */
        $this->template->content = new View('convention/checkout');
        foreach ($data as $key=>$value)
        {
            $this->template->content->$key = $value;
        }
    }
    
    public function action_checkoutOther()
    {
        $this->requireVerified();
        $this->template->heading    = __('convention.checkout_other_heading');
        $this->template->subheading = __('convention.checkout_other_subheading'); 

        $data = array();
		
		/* Fetch all registrations that are marked with status UNPROCESSED, or PARTIAL PAYMENT */
		$data['registrations'] = ORM::Factory('Registration')->getForAccount($this->auth->getAccount()->id);
		if (!$data['registrations']->count()) 
        {
            $this->addError(__('convention.cart_no_items'));
			$this->request->redirect('user/index'); 
            return;
        }
		
        /* Our "checkout template" */
        $this->template->content = new View('convention/checkoutOther', $data);
    }

    public function action_addRegistration()
    {
        $reg = ORM::factory('registration');
        
        $pass = $reg->getPossiblePassesQuery()->where('id', '=', $_POST['pass_id'])->find();
        if (!$pass->loaded())
        {
            $this->addError('Pass provided is no longer valid');
            $this->request->redirect('/convention/checkout'); 
        }

        $reg->pass_id = $pass->id;
        list($reg->gname, $reg->sname) = explode(' ', $this->auth->getAccount()->name, 2);

        // FIXME - hardcoded reg_id to be 1
        $reg->reg_id = sprintf('%s_%s_%s', $pass->convention_id, 'ECM', '1');

        //$reg->email = $this->auth->getAccount()->email;
        $reg->phone = $this->auth->getAccount()->phone;
        $reg->account_id = $this->auth->getAccount()->id;
        $reg->status = Model_Registration::STATUS_UNPROCESSED;

        try {
            if ( $reg->reserveTickets() ) { //Reserve tickets. Return at least 1 except in case of failure (not enough tickets left).
                $reg->save(); 
                $reg->finalizeTickets(); //Save has gone through. Finalize reservation.
                $this->addMessage( __('Created a new registration, ') . $reg->reg_id);
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
            foreach ($e->errors() as $field => $msg)
            {
                $this->addError("$field is $msg");
            }
        }
        $this->request->redirect('/convention/checkout'); 
    }


}
