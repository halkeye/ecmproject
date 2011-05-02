<?php

class Controller_Paypal extends Controller
{
    public function action_registrationPaypalIPN()
    {
        $config = Kohana::config('ecmproject');
        $email = Email::factory($config['registration_subject']);
        $email->from($config['outgoing_email_address'], $config['outgoing_email_name']);

        $data = array();

        Kohana::$log->add(Log::DEBUG, "REQUEST_URI: " . $_SERVER['REQUEST_URI']);

        $p = new Paypal();

        $emailAddr = "";
        $passes = array();

        try {
            $p->validateIPN();

            $data = $_POST;

            $total_items = 1;
            if (isset($data['num_cart_items']))
                $total_items = $data['num_cart_items'];
            else
                while (isset($data['item_number'.$total_items+1])) { $total_items++; }

            foreach (range(1, $total_items) as $count)
            {
                if (!isset($data['item_number'.$count]) || strpos($data['item_number'.$count], "|") === FALSE ) 
                {
                    Kohana::$log->add(Log::ERROR, "[PAYPAL] unable to find item $count - " . var_export($_POST,1));
                    break;
                }
                list($reg_id, $pass_id) = explode('|', $data['item_number'.$count]);
                Kohana::$log->add(Log::NOTICE, "[PAYPAL] Starting $count - $reg_id/$pass_id");

                $reg = ORM::Factory('registration');
                $ret = $reg
                    ->with('convention')
                    ->with('account')
                    ->where('registrations.id', '=', $reg_id)
                    ->find();
                if (!$reg->loaded())
                {
                    Kohana::$log->add(Log::ERROR,"[PAYPAL] processing $count - Unable to load $reg_id");
                    continue;
                }

                $data['name'] = $reg->gname . ' ' . $reg->sname;
                $emailAddr = $reg->account->email;

                /* To make sure they paid the right one */
                if ($reg->pass_id != $pass_id)
                {
                    $passes[$pass_id] = ORM::Factory('Pass', $pass_id);
                    $reg->convention_id = $passes[$pass_id]->convention_id;
                    $reg->convention = ORM::Factory('Convention', $reg->convention_id);
                    $reg->pass_id = $pass_id;
                    $reg->pass = $passes[$pass_id];
                }
                $reg->status = Model_Registration::STATUS_PAID;

                $payment = ORM::Factory('payment');
                $payment->payer_id = $data['payer_id'];

                $payment->reg_id = $reg_id;
                $payment->type = 'paypal';
                $payment->mc_gross = $data['mc_gross_' . $count];
                $payment->payment_date = strtotime($data['payment_date']);
                /* Fixme - Should be normalized. Payment_Model::paypal_to_status($data['payment_status']); */
                $payment->payment_status = $data['payment_status'];
                $payment->txn_id = $data['txn_id'];
                $payment->receipt_id = "????-????-????-????";
                $payment->mod_time = time();
                $payment->payment_type = 'paypal';
                $payment->save();

                $reg->save();

                Kohana::$log->add(Log::NOTICE,"[PAYPAL] Finished $count - $reg_id/$pass_id");
                
                $data['registrations'][$reg->convention->name][] = $reg;
            }

        }
        catch (Exception $e) { 
            Kohana::$log->add(Log::ERROR,"[PAYPAL] Error doing stuff: $e");
            exit();
        }
        
        if ($emailAddr)
        {
            $view = new View('convention/reg_success', $data);

            $email->message($view->render(),'text/html');

            $email->to($emailAddr);
            $email->send();
        }
        return;
    }
    
    public function action_testEmail()
    {
        $config = Kohana::config('ecmproject');
        $email = Email::factory($config['registration_subject']);
        $email->from($config['outgoing_email_address'], $config['outgoing_email_name']);

        $regs = ORM::Factory('Registration')
           # ->where('id', 'IN', array(1,2))
            ->with('convention')
            ->with('account')
            ->find_all();

        $data = array();
        foreach ($regs as $reg)
        {
            $data['registrations'][$reg->convention->name][] = $reg;
            $data['name'] = $reg->gname . ' ' . $reg->sname;
        }

        $view = new View('convention/reg_success', $data);

        $msg = $view->render();
        /*
        $email->message($msg,'text/html');
        $email->to("halkeye@gmail.com");
        $email->send();
        */

        print $msg;
        exit();
    }
}
