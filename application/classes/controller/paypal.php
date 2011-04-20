<?php

class Controller_Paypal extends Controller
{
    public function action_registrationPaypalIPN()
    {

        $p = new Paypal();

        $p->validateIPN();
        $data = $this->input->post();
        
        $total_items = 1;
        if (isset($data['num_cart_items']))
            $total_items = $data['num_cart_items'];
        else
            while (isset($data['item_number'.$total_items+1])) { $total_items++; }

        foreach (range(1, $total_items) as $count)
        {
            if (!isset($data['item_number'.$count]) || strpos($data['item_number'.$count], "|") === FALSE ) 
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
