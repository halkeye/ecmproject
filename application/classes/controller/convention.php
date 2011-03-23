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
        if (!$regs->count()) { url::redirect(Controller_Convention::STEP1); }
        else { url::redirect('/convention/checkout'); }

        $data['conventions'] = array();
        foreach ($regs as $row) 
        {
            $row->incomplete = $row->pass_id ? false : true;
            $data['conventions'][$row->convention_id]->name = $row->convention_name;
            $data['conventions'][$row->convention_id]->id = $row->convention_id;
            $data['conventions'][$row->convention_id]->regs[] = $row;
        }
        $this->view->content = new View('convention/index', $data);
        
        return;
    }

	/* Force limitation: cannot delete registrations unless they are unprocessed */
	//TODO: Put strings to lang file.
	function deleteReg($reg_id = NULL)
	{
		$reg_id = isset($reg_id) ? intval($reg_id) : NULL;
		$reg = ORM::factory('registration', $reg_id);
		
		if (!$reg->loaded)
		{
			$this->addError(Kohana::lang('convention.not_loaded'));
			url::redirect("convention/checkout");
		}
		
		/* Prevent users from using deleteReg with an arbitrary number */
		if ($reg->account_id != $this->auth->getAccount()->id)
		{
			$this->addError(Kohana::lang('convention.not_loaded') . $reg->account_id);
			url::redirect("convention/checkout");
		}
		
		if ($reg->status != Model_Registration::STATUS_UNPROCESSED)
		{
			$this->addError(Kohana::lang('convention.registration_already_processed_unable_to_edit'));
			url::redirect("convention/checkout");
		}
				
		if ($post = $this->input->post())
        {
			if ($val = $this->input->post('Yes'))
			{
				if ($reg->delete())
				{
					$this->addMessage(Kohana::lang('convention.delete_success'));	
				}
				else
				{
					$this->addError(Kohana::lang('convention.delete_error'));	
				}
			}
			
			url::redirect("convention/checkout");
		}
				
		$this->view->content = new View('convention/deleteReg', array('reg'=>$reg));

	}
	
	function viewReg($reg_id = NULL)
	{
		$this->view->title = "Viewing Registration...";		
	
		$reg_id = isset($reg_id) ? intval($reg_id) : NULL;
		$reg = ORM::factory('registration', $reg_id);
		$pass = ORM::factory('Pass', $reg->pass_id);
		
		if (!$reg->loaded)
		{
			$this->addError(Kohana::lang('convention.not_loaded'));
			url::redirect("convention/checkout");
		}
		
		$this->view->heading = $pass->name . ' for ' . $reg->gname . ' ' . $reg->sname;
		$this->view->subheading = 'Viewing registration...';
		$this->view->content = new View('convention/viewReg', array('reg' => $reg, 'pass' => $pass));
	}
	
    function editReg($reg_id = NULL)
    {
        $reg_id = isset($reg_id) ? intval($reg_id) : NULL;

        $reg = ORM::factory('registration', $reg_id);
        if (!$reg->loaded)
        {
            $reg->account_id    = $this->auth->get_user()->id;
            $reg->email         = $this->auth->getAccount()->email;
        }
        else
        {
            if ($reg->status != Model_Registration::STATUS_UNPROCESSED)
            {
                $this->addError(Kohana::lang('convention.registration_already_processed_unable_to_edit'));
                url::redirect('/convention/viewReg/'.$reg_id);
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

        if ($post = $this->input->post())
        {
            foreach ($fields as $fieldName=>$fieldData)
            {
                if ($fieldData['type'] == 'date')
                {
                    $post[$fieldName] = implode('-', 
                        array(
                            @sprintf("%04d", $post[$fieldName . '-year']), 
                            @sprintf("%02d", $post[$fieldName . '-month']), 
                            @sprintf("%02d", $post[$fieldName . '-day'])
                        )
                    );
                    unset($post[$fieldName.'-year']);
                    unset($post[$fieldName.'-month']);
                    unset($post[$fieldName.'-day']);
                }
            }

            if (!isset($post['agree_toc']))
                $post['agree_toc'] = false;
            $post['account_id'] = $this->auth->getAccount()->id;
            if ($reg->validate($post))
            {
                $reg->save();
                url::redirect(Controller_Convention::STEP2);
                return;
            }

            // repopulate the form fields
            $form = arr::overwrite($form, $post->as_array());

            // populate the error fields, if any
            // We need to already have created an error message file, for Kohana to use
            // Pass the error message file name to the errors() method
            //$errors = arr::overwrite($errors, $post->errors('form_error_messages'));
            $errors = $post->errors('form_error_messages');
        }
        $this->view->content = new View('convention/register', array('form'=>$form, 'errors'=>$errors, 'fields'=>$fields));
    }
    
    function registrationCancel($reg_id)
    {
        $reg_id = intval($reg_id);
        $reg = ORM::Factory('registration')->find($reg_id);
        $this->view->content = "cancel/fail page";
    }
    
    function registrationReturn()
    {
        $regids = array();
        /* Pull out some of the data returned from paypal success link */
        $count = 1;
        while ($this->input->get('item_number'.$count))
        {
            $data = explode('|', $this->input->get('item_number'.$count));
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

        $this->view->heading = "Thank you";
        $this->view->subheading = "";
        $this->view->content = new View('convention/paypalReturn');
    }

    public function checkout()
    {
        $this->requireVerified();
        $this->view->heading    = Kohana::lang('convention.checkout_heading');
        $this->view->subheading = Kohana::lang('convention.checkout_subheading'); 

        $data = Kohana::config('paypal');
        /* get all the registrations we need */
        $data['registrations'] = ORM::Factory('registration')->getForAccount($this->auth->getAccount()->id);
        if (!$data['registrations']->count()) 
        {
			url::redirect('user/index'); 
        }

        /* Config file is currently 'url', lets map it to 'paypal_url' incase any other url is used */
        $data['paypal_url'] = $data['url'];
        unset($data['url']);

        /* Where paypal should tell us about successful transactions */
        $data['notify_url'] = url::site('/paypal/registrationPaypalIPN');
		
        ### FIXME - This needs an external url, so can't be localhost
        if (strpos($data['notify_url'], 'localhost') !== FALSE) {
            $data['notify_url'] = 'http://barkdog.halkeye.net:6080/ecmproject/index.php/paypal/registrationPaypalIPN';
        }

        /* Where to send the user when we complete */ 
        $data['return_url'] = url::site('/convention/registrationReturn');
        /* where to send the user if they back out */
        $data['cancel_url'] = url::site('/convention/registrationCancel');

        /* Our "checkout template" */
        $this->view->content = new View('convention/checkout', $data);
    }
    
    public function checkoutOther()
    {
        $this->requireVerified();
        $this->view->heading    = Kohana::lang('convention.checkout_other_heading');
        $this->view->subheading = Kohana::lang('convention.checkout_other_subheading'); 

        $data = array();
		
		/* Fetch all registrations that are marked with status UNPROCESSED, or PARTIAL PAYMENT */
		$data['registrations'] = ORM::Factory('Registration')->getForAccount($this->auth->getAccount()->id);
		if (!$data['registrations']->count()) 
        {
            $this->addError(Kohana::lang('convention.cart_no_items'));
			url::redirect('user/index'); 
            return;
        }
		
        /* Our "checkout template" */
        $this->view->content = new View('convention/checkoutOther', $data);
    }


}
