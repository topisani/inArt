<?php
/**
 * These are the database login details
*/
define('HOST','localhost');     // The host you want to connect to.
define('USER','inart');    // The database username.
define('PASSWORD','inart');    // The database password.
define('DATABASE','inart');    // The database name.

define("CAN_REGISTER", "any");
define("DEFAULT_ROLE", "member");

define("SECURE", FALSE);    // FOR DEVELOPMENT ONLY!!!!

// Amount of login attempts before the account gets locked
define("LOGIN_ATEMPTS", 0);
?>