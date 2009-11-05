<?php

/**
 * User Controller
 * 
 * All user related webpage and functionality.
 * @author Gavin Mogan <ecm@gavinmogan.com>
 * @version 1.0
 * @package ecm
 */


class Convention_Controller extends Controller 
{
    const STEP1 = "convention/editReg";
    const STEP2 = "convention/checkout";

    function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->addMenuItem(
                array('title'=>'Add Registration', 'url'=>Convention_Controller::STEP1)
        );
        return;
    }

    function index()
    {
        $regs = Registration_Model::getByAccount($this->auth->get_user()->id);
        if (!$regs->count()) { url::redirect(Convention_Controller::STEP1); }
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

    function editReg($reg_id = NULL)
    {
        $reg_id = isset($reg_id) ? intval($reg_id) : NULL;

        $reg = ORM::factory('registration', $reg_id);
        if (!$reg->loaded)
        {
            $reg->convention_id = ORM::Factory('convention')->getCurrentConvention();
            $reg->account_id    = $this->auth->get_user()->id;
            $reg->email         = $this->auth->getAccount()->email;
        }
        else
        {
            if ($reg->status != Registration_Model::STATUS_UNPROCESSED)
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
            $fields['pass_id']['values'][$pass->id] = $pass;
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
            if ($reg->validate($post))
            {
                $reg->save();
                url::redirect(Convention_Controller::STEP2);
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
            /* We don't really trust this data so lets make sure people havn't messed with the params at all */
            if ($regids[$reg->id]['pass_id'] != $reg->pass->id)
                throw Exception('Data has been tampered with');
            /* Now if the status was unprocessed before, mark it as being processed (Anything else is handled by other handlers */
            if ($reg->status == Registration_Model::STATUS_UNPROCESSED)
                $reg->status = Registration_Model::STATUS_PROCESSING;
            /* Update modules if they've been changed */
            $reg->save();
        }

        $this->view->content = "success page".
            var_export($regids,1).
        "";
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
            $this->addError('FIXME - No registrations to process');
            return;
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
        /* Our "checkout template" */
        $this->view->content = new View('convention/checkoutOther', $data);
    }


}
