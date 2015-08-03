<?php
require_once( INCLUDES_DIR  . 'functions.php' );
require_once( CLASSES_DIR   . 'User.class.php' );
$username = ( isset( $params['user'] ) && $params['user'] !== '' ) ? $params['user'] : ( Users::login_check( $db ) ? $_SESSION['username'] : null );
if ( $username === null ) {
	Error::stop( 'Not logged in' );
} else if ( !Users::exists( $username, $db ) ) {
	Error::stop( 'Given user does not exist' );
} else {
ia_header( $username );
	
	$user = User::get( $username, $db );

	echo ( '<img height="100px" src="' . $user->get_avatar() . '"/>' );
?>

<h1><?php echo $username?></h1>

<?php
	echo ( "<p>Set avatar</p>" );
	ia_upload( 'Upload avatar', "avatar" );
}
ia_footer();
