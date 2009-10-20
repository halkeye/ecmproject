<?php

class FormTest_Controller extends Controller_Core
{
    public function index()
    {
        echo new View('_formtest');
        return;
    }
}

