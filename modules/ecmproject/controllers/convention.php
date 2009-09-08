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
    const STEP1 = "convention/generalInfo";
    const STEP2 = "convention/badgeChoice";

    function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        return;
    }

    function index()
    {
        $this->view->menu += array(
                array('title'=>'Add Registration', 'url'=>Convention_Controller::STEP1),
        );
        $regs = Registration_Model::getByAccount($this->auth->get_user()->id);
        if (!$regs->count()) { url::redirect(Convention_Controller::STEP1); }

        $data['conventions'] = array();
        foreach ($regs as $row) 
        {
            $row->incomplete = $row->pass_id ? false : true;
            $data['conventions'][$row->convention_id]->name = $row->convention_name;
            $data['conventions'][$row->convention_id]->id = $row->convention_id;
            $data['conventions'][$row->convention_id]->regs[] = $row;
        }
        $this->view->content = new View('convention/registrations', $data);
        
        return;
    }

    function generalInfo($reg_id = NULL)
    {
        $reg_id = intval($reg_id);
        $form = Formo::factory();
        $form->plugin('table');
        $form->plugin('orm');
        $form->plugin('csrf');
        $form->plugin('required');

        if ($reg_id) { $form->orm('registration', $reg_id); }
        else { $form->orm('registration'); }
        $reg = $form->get_model('registration');

        if (!$reg->loaded)
        {
            $reg->convention_id = ORM::Factory('convention')->getCurrentConvention();
            $reg->account_id    = $this->auth->get_user()->id;
        }
            
        $form->mval('registration', 'mval');
        $form->add_rule('badge', array($reg,'_unique_badge_form'), 'Non unique badge name');

        $form->add('submit');

        if ( $form->validate())
        {
            $reg = $form->get_model('registration');
            $form->save();
            $id = $reg->id;
            url::redirect(Convention_Controller::STEP2.'/'.$id);
        }
        $this->view->content = $form->get();
    }
    
    function badgeChoice($reg_id)
    {
        $this->requireVerified();

        $reg_id = intval($reg_id);
        $reg = ORM::Factory('registration')
            ->with('convention')
            ->with('account')
            ->find($reg_id);
        $convention = $reg->convention;
        $account = $reg->account;
        
        $data = array();

        /* FIXME */
        $data['notify_url'] = url::site('/paypal/registrationPaypalIPN/' .$reg->id);
        $data['return_url'] = url::site('/convention/registrationReturn/'.$reg->id);
        $data['cancel_url'] = url::site('/convention/registrationCancel/'.$reg->id);

        $passes = ORM::Factory('pass')->where('convention_id', $convention->id);
        /* $passes->where('ageReq', ); */
        $data['passes'] = $passes->find_all();
        $this->view->content = new View('convention/badgeChoice', $data);
    }

    function registrationCancel($reg_id)
    {
        $reg_id = intval($reg_id);
        $reg = ORM::Factory('registration')->find($reg_id);
        $this->view->content = "cancel/fail page";
    }
    
    function registrationReturn($reg_id)
    {
        $reg_id = intval($reg_id);
        $reg = ORM::Factory('registration')->find($reg_id);
        $this->view->content = "success page";
    }

}
