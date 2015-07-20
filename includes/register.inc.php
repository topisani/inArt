<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
require_once ROOT . 'classes/User.class.php';

if ( isset( $_POST['username'], $_POST['email'], $_POST['p'] ) ) {

	$user = Users::create( $_POST['username'], $_POST['email'], $_POST['p'], $db );
	$user->login();
	header( 'Location: ./profile.php' );
}

