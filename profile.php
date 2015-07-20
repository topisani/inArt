<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
ia_header( 'Profile' );

$username = ( isset( $_GET['user'] ) ) ? $_GET['user'] : ( Users::login_check( $db ) ? $_SESSION['username'] : '' );
if ( $username == '' ) {
	Error::stop( 'no profile selected and not logged in' );
} else if ( !Users::exists( $username, $db ) ) {
	Error::stop( 'Given user does not exist' );
} else {

	
	$user = User::get( $username, $db );

	echo ( '<img height="100px" src="' . $user->get_avatar() . '"/>' );
?>

<h1><?php echo $username?></h1>

<?php
	echo ( "<p>Set avatar</p>" );
	ia_upload( 'Upload avatar', "avatar" );
}
ia_footer();
