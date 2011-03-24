<?php

class Model_Usergroups_permissions extends ORM
{
    var $_table_name = 'usergroups_permissions';

    function a() { print var_export($this->_table_columns,1 ); } 
}

