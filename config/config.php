<?php

define('BASE_DIR', realpath(dirname(__FILE__).'/../'));
define('DIR_SEPARATOR', strtoupper (substr(PHP_OS, 0,3)) == 'WIN' ? '\\' : '/');
define('BASE_URL', dirname($_SERVER['SCRIPT_NAME'])); 

/* Define directory paths */
define('INCL_PATH', 'lib');
define('MODL_PATH', 'modules');
define('THEME_PATH', 'themes');

/* Define database settings */
define('DB_ADDR', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'pass');
define('DB_NAME', 'aehr');
define('DB_PORT', '3306');
define('DB_DRIVER', 'mysqli');

define('DEFAULT_MOD', 'user');
define('THEME', 'default');

require(BASE_DIR.DIR_SEPARATOR.MODL_PATH.DIR_SEPARATOR.'Base.php');
require(BASE_DIR.DIR_SEPARATOR.INCL_PATH.DIR_SEPARATOR.'Router.php');
require(BASE_DIR.DIR_SEPARATOR.INCL_PATH.DIR_SEPARATOR.'Template.php');
// put full path to Smarty.class.php
require(BASE_DIR.DIR_SEPARATOR.INCL_PATH.DIR_SEPARATOR.'smarty'.DIR_SEPARATOR.'Smarty.class.php');

