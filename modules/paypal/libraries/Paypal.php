<?php defined('SYSPATH') or die('No direct script access.');


class Paypal
{
    protected $config   = null;
    protected $handle   = null;
    protected $fields   = array();
    public $itemName    = null;
    public $itemId      = null;
    public $price       = null;

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

        $this->handle = new paypal_class;             // initiate an instance of the class
        $this->handle->paypal_url = $config['url'];
        $this->handle->ipn_log = false; 

        $this->handle->ipn_log = true; 
        $this->handle->ipn_log_file = '/tmp/ipn_results.log';

    }

    public function getIpnData() { return $this->handle->ipn_data; }

    public function getLastError() { return $this->handle->last_error; }

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

    function addField($field, $value) 
    {
        $this->fields[$field] = $value;
    }

    public function paypalView($notify_url = null, $return_url = null, $cancel_url = null) 
    { 
        if (!$this->itemName) { throw new Exception('No item name defined'); } 
        if (!$this->itemId)   { throw new Exception('No itemid defined'); } 
        if (!$this->price)    { throw new Exception('No price defined'); } 

        $view = new View('paypal'); 
        $view->url       = $this->config['url'];
        $view->business  = $this->config['business'];
        $view->image_dir = $this->config['image_dir'];
        $view->itemName  = $this->itemName;
        $view->itemId    = $this->itemId;
        $view->price     = $this->price;
        $view->fields    = $this->fields;

        return $view->render(FALSE);
    }
}
