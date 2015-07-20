<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/includes/functions.php';
require_once ROOT . 'classes/User.class.php';
sec_session_start(); // Our custom secure way of starting a PHP session.

if (isset($_POST['email'], $_POST['p'])) {
	$email = $_POST['email'];
	$password = $_POST['p']; // The hashed password.
	
	$login = Users::login( $email, $password, $db );
	
	if ($login === true) {
		// Login success
		header('Location: ../protected_page.php');
	} else {
		// Login failed
		header('Location: ../login.php?error='.$login);
	}
} else {
	// The correct POST variables were not sent to this page.
	echo 'Invalid Request';
}
