<?php

Abstract Class Module_Base
{
    /*
     * @registry object
     */
    protected $registry;

    function __construct($registry) 
    {
        $this->registry = $registry;
        $this->moduleName = strtolower(get_class($this));
    }
   
    function setTemplate($templateFile)
    {
        $this->registry->template->setTemplate($this->moduleName .'-'. $templateFile);
    }

    /**
     * @all controllers must contain an index method
     */
    abstract function install();
    abstract function index();
    abstract function permissions();
    abstract function auth($action);
}
