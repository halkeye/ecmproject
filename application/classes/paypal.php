<?php defined('SYSPATH') or die('No direct script access.');


class Paypal
{
    protected $config   = null;
    protected $handle   = null;
    protected $fields   = array();

    protected $paypal_url;

    public function __construct($config = array())
    {
        // Append default auth configuration
        $config =  Arr::merge((array) Kohana::config('paypal'), $config);

        // Save the config in the object
        $this->config = $config;

        $this->paypal_url = isset($config['url']) ? $config['url'] : 'https://www.paypal.com/cgi-bin/webscr';
    }

    public function getIpnData() { return $this->ipn_data; }

    public function validateIPN()
    {
        $post_string = '';
        foreach ($_POST as $field=>$value)
        { 
            # Kohana very "nicely" sanitizes the $_POST variable, but leaves the $_REQUEST behind
            # so lets use that instead
            $value = $_REQUEST[$field];

            // Handle escape characters, which depends on setting of magic quotes 
            $value = stripslashes($value); 
            $value = urlencode($value); 
            $post_string .= $field.'='.$value.'&'; 
        }

        $post_string.="cmd=_notify-validate"; // append ipn command

        if (in_array('curl', get_loaded_extensions())) 
        {
            // post back to PayPal system to validate
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$this->paypal_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array("Content-Type: application/x-www-form-urlencoded", "Content-Length: " . strlen($post_string)));
            curl_setopt($ch, CURLOPT_HEADER , 0);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            Kohana::$log->add(Log::DEBUG, 'Paypal - Sent to curl: '. $post_string);
            $response = @curl_exec($ch);
            $curl_err = curl_error($ch);
            curl_close($ch);
            
            Kohana::$log->add(Log::DEBUG,'Paypal - Got to curl: '. $response);
          
            if (stripos($response, "VERIFIED") !== FALSE)
                return true;       
            
            Kohana::$log->add(Log::ERROR,'Paypal Error - validation: IPN Validation Failed. - ' . $response);
            throw new Exception('Paypal Error - validation - IPN Validation Failed. - ' . $response);
            return;
        }

        /*
        // parse the paypal URL
        $url_parsed=parse_url($this->paypal_url);        

        if ($url_parsed['scheme'] == 'https') {
            $url_parsed['port'] = 443;
            $ssl = 'ssl://';
        } else {
            $url_parsed['port'] = 80;
            $ssl = '';
        }

        // open the connection to paypal
        $fp = fsockopen($ssl.$url_parsed['host'],$url_parsed['port'],$err_num,$err_str,30); 
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
        */
    }

    private function writeToSocket($fp, $string)
    {
        fputs($fp, $string . "\r\n");
        Kohana::log('debug', 'Paypal - Wrote to socket: '. $string);
    }
}
