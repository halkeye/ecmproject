<?php

Abstract Class Module_Base
{
    /*
     * @registry object
     */
    protected $registry;

    function __construct($registry) {
        $this->registry = $registry;
    }

    /**
     * @all controllers must contain an index method
     */
    abstract function install();
    abstract function index();
    abstract function permissions();
    abstract function auth($action);
}
