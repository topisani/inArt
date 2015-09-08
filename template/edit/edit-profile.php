<?php
if ( Users::login_check( $db ) ) {
	Error::stop( 'Not logged in' );
}
ia_header( "Edit Profile" );
$username = $_SESSION['username'];
$user = User::get( $username, $db );
echo ( '<img height="100px" src="' . $user->get_avatar() . '"/>' );
ia_upload("Set Avatar", "avatar" );

