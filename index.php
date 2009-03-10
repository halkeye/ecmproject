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
$registry->template = new Template($registry);

/* No Database Yet
$registry->db = $db;	
*/
    
/* Run the controller requested */
$registry->router->loader();
