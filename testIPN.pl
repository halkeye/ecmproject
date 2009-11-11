#!/usr/bin/perl
#
use strict;
use warnings;
use LWP;
use CGI;

my %data = (
        'mc_gross' => '40.00',
        'protection_eligibility' => 'Eligible',
        'address_status' => 'confirmed',
        'item_number1' => '7|2',
        'payer_id' => '7N24QEYARZB6L',
        'tax' => '0.00',
        'address_street' => '1 Maire-Victorin
        ',
        'payment_date' => '13:38:12 Oct 10, 2009 PDT',
        'payment_status' => 'Completed',
        'charset' => 'windows-1252',
        'address_zip' => 'M5A 1E1',
        'mc_shipping' => '0.00',
        'mc_handling' => '0.00',
        'first_name' => 'Test',
        'mc_fee' => '1.46',
        'address_country_code' => 'CA',
        'address_name' => 'Test User',
        'notify_version' => '2.8',
        'custom' => '7',
        'payer_status' => 'unverified',
        'business' => 'AE10_1249882783_biz@gavinmogan.com',
        'address_country' => 'Canada',
        'num_cart_items' => '1',
        'mc_handling1' => '0.00',
        'address_city' => 'Toronto',
        'verify_sign' => 'AFcWxV21C7fd0v3bYYYRCpSSRl31AGsCCZXD8XDclCCqLFk5JV33wywE',
        'payer_email' => 'gavin_1249883403_per@gavinmogan.com',
        'mc_shipping1' => '0.00',
        'tax1' => '0.00',
        'txn_id' => '1WJ02183NE485544L',
        'payment_type' => 'instant',
        'last_name' => 'User',
        'address_state' => 'Ontario',
        'item_name1' => '3-day adult pass PRE-REG - kjlhasljk',
        'receiver_email' => 'AE10_1249882783_biz@gavinmogan.com',
        'payment_fee' => '',
        'quantity1' => '1',
        'receiver_id' => '5YSSX539BUQAN',
        'txn_type' => 'cart',
        'mc_gross_1' => '40.00',
        'mc_currency' => 'CAD',
        'residence_country' => 'CA',
        'test_ipn' => '1',
        'transaction_subject' => '7',
        'payment_gross' => '',
);
      
my $uri = URI->new;
$uri->query_form( \%data );
      
my $ua = LWP::UserAgent->new;
$ua->agent("MyApp/0.1 ");

my $req = HTTP::Request->new(POST => 'http://barkdog.halkeye.net:6080/ecmproject/index.php/paypal/registrationPaypalIPN');
$req->content_type('application/x-www-form-urlencoded');
$req->content($uri->query());
# Pass request to the user agent and get a response back
my $res = $ua->request($req);

# Check the outcome of the response
print $res->content;
