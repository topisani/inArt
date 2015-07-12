<?php
include_once 'includes/db_connect.php';

set_page_title ( 'Protected Page' );
ia_header ();

if (login_check ( $mysqli ) == true) : ?>

	<p>Welcome <?php echo htmlentities($_SESSION['username']); ?>!</p>
	<p>
		This is an example protected page. To access this page, users must be
		logged in. At some stage, we'll also check the role of the user, so
		pages will be able to determine the type of user authorised to access
		the page.
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