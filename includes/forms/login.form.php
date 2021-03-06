<?php
sec_session_start(); // Our custom secure way of starting a PHP session.

if (isset($_POST['email'], $_POST['p'])) {
	$email = $_POST['email'];
	$password = $_POST['p']; // The hashed password.
	
	$login = Users::login( $email, $password, $db );
	
	if ($login === true) {
		// Login success
		header( 'Location: /' );
	} else {
		// Login failed
		header( 'Location: /login?error=' . $login );
	}
} else {
	// The correct POST variables were not sent to this page.
	echo 'Invalid Request';
}
