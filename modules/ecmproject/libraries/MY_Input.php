<?php defined('SYSPATH') or die('No direct script access.');

class Input extends Input_Core
{
    public $originalPost = array();
    function __construct()
    {
        foreach ($_POST as $key=>$value) { $this->originalPost[$key] = $value; }
        #$this->originalPost = $_POST;
        parent::__construct();
    }
}
