<?php

class Paypal_Controller extends Controller_Core
{
    function registrationPaypalIPN($reg_id)
    {
        $reg_id = intval($reg_id);

        $p = new Paypal();
        if (!$p->validate_ipn()) 
        {
            $msg = "Unable to validate ipn: " . $p->getLastError();
            print $msg;
            Kohana::log('error', $msg);
            return;
        }

        $data = $p->getIpnData();

        /* FIXME: Add timestamp of event */
        $result = Database::instance()->query("REPLACE INTO payments SET raw_data=?,register_id=?,type='paypal'",
                var_export($data,1), $reg_id
        );

        $reg = ORM::Factory('registration')->find($reg_id);
        $reg->pass_id = $data['item_number'];
        $reg->save();


        $msg = "IPN for $reg_id successful";
        print $msg;
        Kohana::log('info', $msg);
        return;
    }
}
