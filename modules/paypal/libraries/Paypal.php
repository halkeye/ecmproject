<?php defined('SYSPATH') or die('No direct script access.');


class Paypal
{
    protected $handle = null;

    public function __construct()
    {
        if ( ! class_exists('paypal_class', FALSE))
        {
            require Kohana::find_file('vendor', 'paypal.class',TRUE, 'php');
        }
        // Load default configuration
        $config = Kohana::config('paypal');
            
        $this->handle = new paypal_class;             // initiate an instance of the class
        $this->handle->paypal_url = $config['url'];
    }

    public function __call($method, $arguments) 
    {
        if (!$this->handle)
            throw new Kohana_Exception('paypal.no_handler_defined');

        switch (count($arguments))
        {
            case 0:
                return $this->handle->$method(); 
                break;
            case 1:
                return $this->handle->$method($arguments[0]); 
                break;
            case 2:
                return $this->handle->$method($arguments[0], $arguments[1]); 
                break;
            default:
                // This is a very very slow call, so above tries to prevent it
                return call_user_func_array(array($this->handle, $method), $arguments);
                break;
        }
        return $this;
    }

    public function paypalView() 
    { 
        return new View('paypal'); 
    }
}
