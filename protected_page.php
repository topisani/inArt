<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/includes/functions.php';
ia_header ('Protected Page');

if ( Users::login_check ( ) == true) : ?>

	<p>Welcome <?php echo htmlentities($_SESSION['username']); ?>!</p>
	<p>
		This is an example protected page. To access this page, users must be
		logged in.
	</p>
	<p>
		Return to <a href="login.php">login page</a>
	</p>
<?php else : ?>
	<p>
		<span class="error">You are not authorized to access this page.</span>
		Please <a href="login.php">login</a>.
	</p>
<?php endif; ?>
<?php ia_footer()?>
