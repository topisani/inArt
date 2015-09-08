<?php
require_once( __DIR__ . '/../functions.php' );
require_once( CLASSES_DIR . 'User.class.php' );

if ( isset( $_POST['username'], $_POST['email'], $_POST['p'] ) ) {

	$user = Users::create( $_POST['username'], $_POST['email'], $_POST['p'], $db );
	$user->login();
	redirect("/");
}

