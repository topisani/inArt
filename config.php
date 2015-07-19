<?php
/**
 * These are the database login details
*/
define('DB_HOST','localhost');     // The host you want to connect to.
define('DB_USER','inart');    // The database username.
define('DB_PASSWORD','inart');    // The database password.
define('DB_DATABASE','inart');    // The database name.

define("SECURE", FALSE);    // FOR DEVELOPMENT ONLY!!!!

// Amount of login attempts before the account gets locked
define("LOGIN_ATEMPTS", 5);

define('ROOT', $_SERVER['DOCUMENT_ROOT'].'/');
define("USERDATA", '/var/www/userdata/');
define("MAX_FILE_SIZE", 10000000); //10MB

define("DIRECTORY_PERMISSIONS", 770);
define("FILE_PERMISSIONS", 660)
?>
