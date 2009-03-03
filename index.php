<?php
/* Start a session */
session_start();

include('config/config.php');

Class Registry {}

/* Create registry to store global variables */
$registry = new Registry;
//$db = new DB;
    
/* Start a database connection */
//$db->connect(DB_DRIVER);
    
/* Add stuff we need to registry */	
$registry->router = new router($registry);

$registry->template = new Smarty();
$registry->template->template_dir = THEME_PATH.DIR_SEPARATOR.THEME.DIR_SEPARATOR.'templates';
$registry->template->compile_dir  = THEME_PATH.DIR_SEPARATOR.THEME.DIR_SEPARATOR.'compiled';
$registry->template->cache_dir    = THEME_PATH.DIR_SEPARATOR.THEME.DIR_SEPARATOR.'cache';
$registry->template->config_dir   = THEME_PATH.DIR_SEPARATOR.THEME.DIR_SEPARATOR.'configs';
$registry->template->assign('_registry', $registry);
$registry->template->assign('_baseURL', BASE_URL);
$registry->template->assign('_themeURL', BASE_URL.'/'.THEME_PATH.'/'.THEME.'/');

/* No Database Yet
$registry->db = $db;	
*/
    
/* Run the controller requested */
$registry->router->loader();
