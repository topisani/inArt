<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 */
?>
<!DOCTYPE html>
<html>
<head>

<title><?php echo get_page_title();?></title>
<!--TODO add site meta-->
	<?php ia_styles()?>

</head>

<body>
	<div id="header">
	<form action="<?php echo \Enums\Files\Forms::LOGIN_URL ?>" method="post"
			name="login_form">
			Username / Email: <input type="text" name="email" />
			 Password: <input type="password" name="password" id="password" /> 
				<input type="submit" value="Login" onclick="loginformhash(this.form, this.form.password);" />
		</form>

<?php
global $db;
if ( Users::login_check( $db ) == true ) {
	echo '<p>Currently logged in as ' . htmlentities ( $_SESSION ['username'] ) . '.     ';

	echo '<a href="<?php echo \Enums\Files\Forms::LOGOUT_URL ?>">Log out</a>.</p>';
} else {
	echo '<p>Currently logged out.';
	echo "<a href='/register'>Register</a></p>";
}
?>
</div>

