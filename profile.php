<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/includes/functions.php';
ia_header ('Profile');

$username = (isset( $_GET['user'] ))  ? $_GET['user'] : (login_check() ? $_SESSION['username'] : '');
if( $username == '') error_page('no profile selected and not logged in');
?>

<h1><?php echo $username?></h1>
<?php
ia_footer();
?>
