<?php
require_once '../functions.php';
sec_session_start();

$user_id = $_GET['user_id'];
$uploaddir = USERDATA . '/uploads/' . $user_id . '/';
$upload_id = $_GET['upload_id'];
if ( !is_numeric( $upload_id ) && !is_numeric( $user_id ) ) {
	die( "IDs must be numeric" );
}

$results = $db->select( 'uploads', 'name, mime_type', array( 
		'user_id = ?' => $user_id, 
		'upload_id = ?' => $upload_id 
) );

if ( !$results->has_rows() ) die( "File not found" );

$results->bind_vars( $mime = 'mime_type', $name = 'name' );


header( "Content-Type: " . $mime);
readfile( $uploaddir . $name );
?>
