<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
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
		default:
			echo 'Unknown Error';
	}
	echo '<p>Email: '.$_GET ['email'].'</p>';
	echo '<p>Password: '.$_GET ['passwd'].'</p>';
}
?>

<form action="includes/process_login.php" method="post"
	name="login_form">
	Email: <input type="text" name="email" /> Password: <input
		type="password" name="password" id="password" /> <input type="button"
		value="Login" onclick="loginformhash(this.form, this.form.password);" />
</form>

<?php
if (login_check ( $mysqli ) == true) {
	echo '<p>Currently logged in as ' . htmlentities ( $_SESSION ['username'] ) . '.</p>';
	
	echo '<p>Do you want to change user? <a href="includes/logout.php">Log out</a>.</p>';
} else {
	echo '<p>Currently logged out.</p>';
	echo "<p>If you don't have a login, please <a href='register.php'>register</a></p>";
}

ia_footer();
?>
