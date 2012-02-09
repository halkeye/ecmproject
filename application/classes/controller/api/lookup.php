<?php defined('SYSPATH') or die('No direct script access.');
/**
 * API-User api
 *
 * @package    
 * @category   Controller
 * @author     Gavin Mogan
 * @copyright  (c) 2012 KodeKoan
 */
class Controller_API_Lookup extends OAuth2_Controller
#class Controller_API_Lookup extends Controller
{
    public function before() 
    {
        Kohana_Exception::$error_view = "api/error";
        $this->request->headers['Content-Type'] = 'application/json';
        return parent::before();
    }

    public function action_user()
    {
        $this->response->body(
            json_encode(array(
                "moo" => 1,
                "client" => $this->_oauth_client,
                'user_id' => $this->_oauth_user_id,
            ))
        );
    }
} 
