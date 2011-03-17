<?php defined('SYSPATH') OR die('No direct access allowed.'); 

class Assets
{
	protected static $stylesheets = array();
	protected static $javascripts = array();

    public static function addCSS($filename, $weight = 0)
    {
        self::$stylesheets[$filename] = $weight;
    }
    
    public static function getCSS()
    {
        return array_keys(self::$stylesheets);
    }

    public static function addJS($filename, $weight = 0)
    {
        self::$javascripts[$filename] = $weight;
    }

    public static function getJS()
    {
        return array_keys(self::$javascripts);
    }
}
