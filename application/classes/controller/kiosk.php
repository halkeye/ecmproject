<?php defined('SYSPATH') OR die('No Direct Script Access');

/**
 * Kiosk Controller
 * 
 * Kiosk Support
 * @author Gavin Mogan <gavin@kodekoan.com>
 * @version 1.1
 * @package ecm
 */


class Controller_Kiosk extends Base_MainTemplate 
{ 
//    public $template = 'kioskTemplate';

    function before()
    {
        $ret = parent::before();
        $this->requireLogin();
        $this->requirePermission('kiosk'); //Force requirement of full administrative access minimum.
        return $ret;
    }

    public function action_index()
    {
        $this->template->title =        __('Kiosk');
        $this->template->heading =      __('Kiosk');
        $this->template->subheading =   __('Find Registrations');
                    
        $this->template->content = new View('kiosk/main');
    }
    
    public function action_lookupReg()
    {
        header("Content-type: application/json");
        $reg = ORM::Factory('Registration')
            ->with('convention')
            ->with('pass')
            ->with('location')
            ->findByRegId($_GET['regId']);
        if (!$reg->loaded()) {
            print json_encode(array('error'=>1, 'not_found'=>1), JSON_FORCE_OBJECT);
            exit;
        }

        $data = array();
        $data['gname'] = $reg->gname;
        $data['sname'] = $reg->sname;
        $data['pretty_reg_id'] = $reg->getRegID();
        $data['pass'] = $reg->pass->name;
        $data['convention'] = $reg->convention->name;
        $data['pickup_status'] = intval($reg->pickupStatus);
        $data['pickup_status_name'] = $reg->pickupToString();
        $data['status_name'] = $reg->statusToString();
        $data['status'] = intval($reg->status);

        print json_encode($data, JSON_FORCE_OBJECT);
        exit;
    }

    public function action_testClock() {
        header("Content-type: text/plain");
        print date("r");
        exit;       
    }
}
