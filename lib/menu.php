<?php

class Menu
{
    // Store the single instance of Database 
    private static $instance; 

    public static function getInstance() 
    { 
        if (!self::$instance) 
        { 
            self::$instance = new Menu(); 
        } 

        return self::$instance; 
    }  

    private $menu = array();

    function Menu()
    {
        $this->menu['/']       = array('Pages_Index','index');
    }

    function set($url, $function)
    {
        $this->menu[$url] = $function;
    }

    function get($link)
    {
        // Fix extra slashes
        $parts = explode('/', $link);
        $link = implode('/', $parts);
        
        if (isset($this->menu[$link])) { return $this->menu[$link]; }

        // Wildcard matches
        while (count($parts) > 0)
        {
            array_pop($parts); // Take the last item off the list
            $link = implode('/', $parts);
            if (isset($this->menu[$link.'/*'])) { return $this->menu[$link.'/*']; }
        }
        return NULL;
    }
}
