<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 */
include_once $_SERVER['DOCUMENT_ROOT'].'/includes/functions.php';

?>
<!DOCTYPE html>
<html>
<head>

<title><?php echo get_page_title();?></title>
	
	<?php ia_styles()?>
		
	<?php ia_scripts()?>
	
</head>

<body>
	<div id="header">
		<form action="includes/process_login.php" method="post"
			name="login_form">
			Email: <input type="text" name="email" /> Password: <input
				type="password" name="password" id="password" /> <input
				type="button" value="Login"
				onclick="loginformhash(this.form, this.form.password);" />
		</form>

		<?php
			if (login_check() == true) {
				echo '<p>Currently logged in as ' . htmlentities ( $_SESSION ['username'] ) . '.     ';
	
				echo '<a href="includes/logout.php">Log out</a>.</p>';
			} else {
			echo '<p>Currently logged out.     ';
			echo "<a href='register.php'>Register</a></p>";
		}
		?>
	
	</div>

