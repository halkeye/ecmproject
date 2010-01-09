<?php defined('SYSPATH') or die('No direct script access.');


class Paypal
{
    protected $config   = null;
    protected $handle   = null;
    protected $fields   = array();

    protected $paypal_url;

    public function __construct($config = array())
    {
        // Paypal Class
        if ( ! class_exists('paypal_class', FALSE))
        {
            require Kohana::find_file('vendor', 'paypal.class',TRUE, 'php');
        }

        // Append default auth configuration
        $config += Kohana::config('paypal');

        // Save the config in the object
        $this->config = $config;

        $this->paypal_url = isset($config['url']) ? $config['url'] : 'https://www.paypal.com/cgi-bin/webscr';
    }

    public function getIpnData() { return $this->ipn_data; }

    public function validateIPN()
    {
        // parse the paypal URL
        $url_parsed=parse_url($this->paypal_url);        

        // generate the post string from the _POST vars aswell as load the
        // _POST vars into an arry so we can play with them from the calling
        // script.
        $post_string = '';    

        $magicQuotes = false;
        if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc() == 1) 
            $magicQuotes = true;

        foreach (Input::instance()->originalPost as $field=>$value)
        { 
            // Handle escape characters, which depends on setting of magic quotes 
            if($magicQuotes) $value = stripslashes($value); 
            $value = urlencode($value); 
            $post_string .= $field.'='.$value.'&'; 
        }
        $post_string.="cmd=_notify-validate"; // append ipn command

        // open the connection to paypal
        $fp = fsockopen($url_parsed['host'],"80",$err_num,$err_str,30); 
        if(!$fp) 
        {
            // could not open the connection.  If loggin is on, the error message
            // will be in the log.
            throw new Kohana_User_Exception('Paypal Error - fsockopen', "fsockopen - $erronum: $errstr");
        }
 
        // Post the data back to paypal
        $this->writeToSocket($fp, "POST ".$url_parsed['path']." HTTP/1.0"); 
        $this->writeToSocket($fp, "Host: ".$url_parsed['host']); 
        $this->writeToSocket($fp, "Content-type: application/x-www-form-urlencoded"); 
        $this->writeToSocket($fp, "Content-length: ".strlen($post_string)); 
        $this->writeToSocket($fp, "Connection: close"); 
        $this->writeToSocket($fp, "");
        $this->writeToSocket($fp, $post_string); 
        $this->writeToSocket($fp, "");

        $response = "";
        // loop through the response from the server and append to variable
        while(!feof($fp))
            $response .= fgets($fp, 1024); 

        fclose($fp); // close connection
        Kohana::log('debug', 'Paypal - Got to socket: '. $response);
      
        if (stripos($response, "VERIFIED") !== FALSE)
            return true;       
        
        throw new Kohana_User_Exception('Paypal Error - validation', "IPN Validation Failed.");
    }

    private function writeToSocket($fp, $string)
    {
        fputs($fp, $string . "\r\n");
        Kohana::log('debug', 'Paypal - Wrote to socket: '. $string);
    }
}
