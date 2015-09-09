<?php
sec_session_start();

if ( !Users::login_check( $db ) ) die ( "You have to be logged in to upload files" );
$type = $_POST['type'];
$user_id = $_SESSION['user_id'];
$upload_id = Files::upload( $user_id, 'userfile', $db );
if ( $upload_id === false ) {
	die ( "File uploading failed" );
}
switch ( $type ) {
case 'avatar':
	$user = new User( $user_id, $db );
	$user->set_avatar( $upload_id );
	break;
}
