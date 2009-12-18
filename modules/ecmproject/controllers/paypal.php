<?php

class Paypal_Controller extends Controller_Core
{
    function registrationPaypalIPN()
    {

        $p = new Paypal();
        $data = $_POST;

        if (IN_PRODUCTION)
        {
            if (!$p->validate_ipn()) 
            {
                $msg = "Unable to validate ipn: " . $p->getLastError();
                print $msg;
                Kohana::log('error', "[PAYPAL] $msg");
                return;
            }
            $data = $p->getIpnData();
        }
        
        Kohana::log('debug','[PAYPAL] Data Dump - ' . var_export($data,1));
        foreach (range(1, $data['num_cart_items']) as $count)
        {
            if (!isset($data['item_number'.$count])) 
            {
                Kohana::log('error',"[PAYPAL] unable to find item $count - " . var_export($_POST,1));
                break;
            }
            list($reg_id, $pass_id) = explode('|', $data['item_number'.$count]);
            Kohana::log('info',"[PAYPAL] Starting $count - $reg_id/$pass_id");
            
            $reg = ORM::Factory('registration')->where('id', $reg_id)->find();
            if (!$reg->loaded)
            {
                Kohana::log('error',"[PAYPAL] processing $count - Unable to load $reg_id");
                continue;
            }
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
            
            Kohana::log('info',"[PAYPAL] Finished $count - $reg_id/$pass_id");
        }
        
        return;
   
    }
}
