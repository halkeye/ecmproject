<?php

class Unit_test extends Controller 
{

    /**
     * Constructor
     *
     * @return void
     * @author Gavin Mogan
     **/
    function Unit_test()
    {
        parent:: Controller();
        log_message('debug', 'Unit_tests Controller Initialized');
        //$this->load->helper('unit_testing');
        //$this->load->helper('html');
        $this->load->library('unit_test');
    }

    function _user_group_tests()
    {
        $this->load->model('usergroup');

        $ug = new UserGroup();
        $ug->get();
        $ug->delete_all();

        $this->unit->use_strict(TRUE);
        foreach (array('Administrator', 'Moderator', 'Member') as $name)
        {
            $g = new UserGroup();
            $g->name = $name;
            $this->unit->run(
                    $g->validate(), # run
                    $g, # expected
                    $name . ' Group Validate'
            );

            $this->unit->run(
                    !!$g->save(), # run
                    TRUE, # expected
                    $name . ' Group Save'
            );
            $u = new Account();
            $u->limit(1)->get();
            $u->save($g);
        }

    } 

    function index()
    {
        $this->output->enable_profiler(TRUE);
        // result_clear() is a method I added to the unit test class to reset the unit tester between tests. 
        // it's good if you're running multiple tests in one function        
        //$this->unit->result_clear();
        $this->_user_group_tests();

        echo $this->unit->report();
    }

}
