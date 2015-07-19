<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
ia_header( 'Profile' );

$username = ( isset( $_GET['user'] ) ) ? $_GET['user'] : ( login_check() ? $_SESSION['username'] : '' );
if ( $username == '' ) error_page( 'no profile selected and not logged in' );
$user_id = $_SESSION['user_id'];

echo ( '<img height="100px" src="' . get_avatar( $user_id ) . '"/>' );
?>

<h1><?php echo $username?></h1>

<?php
echo ( "<p>Set avatar</p>" );
ia_upload( 'Upload avatar', "avatar" );

ia_footer();
