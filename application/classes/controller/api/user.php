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
{
    public function action_user()
    {
        return json_encode(array(
            "moo" => 1
        ));
    }
} 
