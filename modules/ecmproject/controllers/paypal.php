<?php

class Paypal_Controller extends Controller_Core
{
    function registrationPaypalIPN()
    {
        $p = new Paypal();
        $data = $_POST;
        /*
        if (!$p->validate_ipn()) 
        {
            $msg = "Unable to validate ipn: " . $p->getLastError();
            print $msg;
            Kohana::log('error', $msg);
            return;
        }
        $data = $p->getIpnData();
        */

        $count = 1;

        while (1)
        {
            if (!isset($data['item_number'.$count])) break;

            list($reg_id, $pass_id) = explode('|', $data['item_number'.$count]);
            
            $reg = ORM::Factory('registration')->find($reg_id);
            /* To make sure they paid the right one */
            $reg->pass_id = $pass_id;
            $reg->status = Registration_Model::STATUS_PAID;

            $payment = ORM::Factory('payment');
            $payment->payer_id = $data['payer_id'];

            $payment->register_id = $reg_id;
            $payment->type = 'paypal';
            $payment->mc_gross = $data['mc_gross_' . $count];
            $payment->payment_date = strtotime($data['payment_date']);
            /* Fixme - Should be normalized. Payment_Model::paypal_to_status($data['payment_status']); */
            $payment->payment_status = $data['payment_status'];
            $payment->txn_id = $data['txn_id'];
            $payment->receipt_id = "????-????-????-????";
            $payment->last_modified = $reg->account_id;
            $payment->mod_time = time();
            $payment->save();
            $reg->save();

            /* FIXME - Store raw data somewhere as a log also */
            $count++;
        }
        
        Kohana::log('info',' Paypal data - ' . var_export($_POST,1));
        return;
   
    }
}
