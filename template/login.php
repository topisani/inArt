<?php
ia_header ('Log in');

if (isset ( $_GET ['error'] )) {
	echo '<p class="error">Error Logging In: ';
	switch ($_GET ['error']) {
		case 1:
			echo 'Wrong password';
			break;
		case 2:
			echo 'Wrong email';
			break;
		case 3:
			echo 'Account locked';
			break;
		case 4:
			error_page('Could not connect to database');
			break;
		default:
			echo 'Unknown Error';
	}
}
?>

<form action="/includes/process_login.php" method="post"
	name="login_form">
	Username / Email: <input type="text" name="email" /> Password: <input
		type="password" name="password" id="password" /> <input type="submit"
		value="Login" onclick="loginformhash(this.form, this.form.password);" />
</form>

<?php
if ( Users::login_check( $db ) ) {
	echo '<p>Currently logged in as ' . htmlentities ( $_SESSION ['username'] ) . '.</p>';
	
	echo '<p>Do you want to change user? <a href="<?php echo \Enums\Files\Forms::LOGOUT ?>">Log out</a>.</p>';
} else {
	echo '<p>Currently logged out.</p>';
	echo "<p>If you don't have a login, please <a href='/register'>register</a></p>";
}

ia_footer();
?>
