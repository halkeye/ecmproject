<?php defined('SYSPATH') or die('No direct script access.');

class Input extends Input_Core
{
    public $originalPost = array();
    function __construct()
    {
        $this->originalPost = $_POST;
        parent::__construct();
    }
}
