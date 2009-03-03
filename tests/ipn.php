<?php


include("../lib/paypal/paypal.class.php");

$p = new paypal_class;
$p->ipn_log_file = 'paypal.log';

// testing only - comment this out for production
#$p->paypal_url = 'https://developer.paypal.com/devscr';
#$p->paypal_url = 'https://developer.paypal.com/us/cgi-bin/devscr';
$p->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

$fd = fopen('varlog.log', "a+");
fwrite($fd, date("Ymd G:i:s") . "\n\n" . var_export($_POST,1) . "\n" . var_export($_GET,1)  . "\n" . var_export($p,1) . "\n");
fclose($fd);
if( $p->validate_ipn() ) {
    echo "Validated";
}

