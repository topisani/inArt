<?php
/**
 * These are the database login details
*/
define('HOST','localhost');     // The host you want to connect to.
define('USER','inart');    // The database username.
define('PASSWORD','inart');    // The database password.
define('DATABASE','inart');    // The database name.

define("SECURE", FALSE);    // FOR DEVELOPMENT ONLY!!!!

// Amount of login attempts before the account gets locked
define("LOGIN_ATEMPTS", 5);

define('ROOT', $_SERVER['DOCUMENT_ROOT'].'/');
define("USERDATA", ROOT.'/userdata/');
define("MAX_FILE_SIZE", 10000000); //10MB
?>