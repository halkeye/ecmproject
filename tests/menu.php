<?php
define('BASE_DIR', realpath(dirname(__FILE__).'/../'));
require_once('simpletest/autorun.php');
require_once(BASE_DIR.'/lib/menu.php');

class TestOfMenu extends UnitTestCase 
{
    function TestOfMenu()
    {
        Menu::getInstance()->set(
                '/', 
                array('Pages_Index','index')
        );
        Menu::getInstance()->set(
                '/test', 
                array('Pages_Index','test')
        );
        Menu::getInstance()->set(
                '/test/*', 
                array('Pages_Index','testWildCard')
        );
        /*
         * how do you do wildcard links?
         * Do we need them?
         */
        Menu::getInstance()->set(
                '/test/*/test', 
                array('Pages_Index','testWildCardTest')
        );
    }

    function testIndex() 
    {
        $ret = Menu::getInstance()->get('/');
        $this->assertEqual($ret, array('Pages_Index', 'index'), '/ class');
    }

    function testBadUrl() 
    {
        $ret = Menu::getInstance()->get('/asdasd');
        $this->assertNull($ret, 'bad url');
    }

    function testWildcard()
    {
        $ret = Menu::getInstance()->get('/test/blah');
        $this->assertEqual($ret, array('Pages_Index', 'testWildCard'));
    }
    
    function testWildcardTest()
    {
        $ret = Menu::getInstance()->get('/test/blah/test');
        $this->assertEqual($ret, array('Pages_Index','testWildCard') );
    }
}
