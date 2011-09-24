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
    const STEP1 = "convention/checkout";
    const STEP2 = "convention/checkout";

    function before()
    {
        $ret = parent::before();
        $this->requireLogin();
        $this->requireVerified();
        return $ret;
    }

    function action_index()
    {
        $this->request->redirect('/convention/checkout');
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
			$this->addError(__('Oops. The registration seems to have disappeared...'));
			$this->request->redirect("convention/checkout");
		}
		
		/* Prevent users from using deleteReg with an arbitrary number */
		if ($reg->account_id != $this->auth->getAccount()->id)
		{
			$this->addError(__('Oops. The registration seems to have disappeared...') . $reg->account_id);
			$this->request->redirect("convention/checkout");
		}
		
		if ($reg->status != Model_Registration::STATUS_UNPROCESSED)
		{
			$this->addError(__('Cannot delete a registration for which payment is being processed.'));
			$this->request->redirect("convention/checkout");
        }
				
		if ($post = $this->request->post())
        {
			if ( isset($post['Yes']) && $post['Yes'] )
			{
				if ($reg->delete())
				{
					$this->addMessage(__('Deleted the ticket from your shopping cart.'));	
				}
				else
				{
					$this->addError(__('Failed to delete the ticket! Please try again or contact the webmaster.'));	
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
			$this->addError(__('Oops. The registration seems to have disappeared...'));
			$this->request->redirect("convention/checkout");
		}
		
		$this->template->heading = $pass->name . ' for ' . $reg->gname . ' ' . $reg->sname;
		$this->template->subheading = 'Viewing registration...';
		$this->template->content = new View('convention/viewReg', array('reg' => $reg, 'pass' => $pass));
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
        while (isset($_POST['item_number'.$count]) && $_POST['item_number'.$count])
        {
            $data = explode('|', $_POST['item_number'.$count]);
            $regids[$data[0]] = array('id' => $data[0], 'pass_id' => $data[1]); 
            $count++;
        }

        $registrations = ORM::factory('registration')
            ->with('account')
            ->with('pass')
            ->where('registrations.id', 'IN', array_keys($regids))
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
		$user = $this->auth->get_user();
		
		$welcome = __('Purchase tickets for ') . htmlentities($user->gname) . ' ' . htmlentities($user->sname);
		$edit_link = html::anchor('/user/changeName', __('(Change my name)'), array('class' => 'small_link'), null, true);
		
        $this->template->heading    = $welcome . ' ' . $edit_link;
        $this->template->subheading = __('Register and purchase tickets for events here.'); 


        $data = Kohana::config('paypal');
        $data['passes'] = ORM::Factory('registration')
            ->getPossiblePassesQuery()
            ->with('convention')
            ->find_all();

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
        if (strpos($data['notify_url'], 'moocow.local') !== FALSE) {
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
    
    public function action_addRegistration()
    {
        $reg = ORM::factory('registration');
        
        $pass = $reg->getPossiblePassesQuery()->with('convention')->where('passes.id', '=', $_POST['pass_id'])->find();
        if (!$pass->loaded())
        {
            $this->addError('Pass provided is no longer valid');
            $this->request->redirect('/convention/checkout'); 
        }

        $reg->convention_id = $pass->convention->id;
        $reg->pass_id = $pass->id;
        $reg->email = $this->auth->getAccount()->email;
        $reg->sname = $this->auth->getAccount()->sname;
        $reg->gname = $this->auth->getAccount()->gname;

        //$reg->email = $this->auth->getAccount()->email;
        $reg->phone = $this->auth->getAccount()->phone;
        $reg->account_id = $this->auth->getAccount()->id;
        $reg->status = Model_Registration::STATUS_UNPROCESSED;

        try {

            $id = $reg->reserveTickets(1);
            if ( $id ) { //Reserve tickets. Return at least 1 except in case of failure (not enough tickets left).
                $reg->build_regID(array('comp_loc'=>'ECM', 'comp_id'=> $id), array('ECM') , $pass->convention_id);
                $reg->save(); 
                $reg->finalizeTickets(1, true); //Save has gone through. Finalize reservation. Added parameter if next_id is being used. FIXME to something more elegant.
                $this->addMessage( __('Added the ticket, :name to the cart.', array(':name' => $reg->gname . ' '. $reg->sname) ));
            }
            else if ($reg->pass_id > 0) {					
                $this->addError("No more tickets to allocate for " . $pass->convention->name . ' - ' . $pass->name . ". Please select a different pass.");
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
