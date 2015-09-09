<?php
$user_id = $_GET['user_id'];
$uploaddir = USERDATA . $user_id . '/';
$upload_id = $_GET['upload_id'];
if ( !is_numeric( $upload_id ) && !is_numeric( $user_id ) ) {
	die( "IDs must be numeric" );
}

$results = $db->select( 'uploads', 'name, mime_type, original_name', array( 
		'user_id = ?' => $user_id, 
		'upload_id = ?' => $upload_id 
) );

if ( !$results->has_rows() ) die( "File not found" );

$mime = $results->get_first( 'mime_type' );
$name = $results->get_first( 'name' );
$original_name = $results->get_first( 'original_name' );
$file = $uploaddir . $name;

if (file_exists($file)) {
	ob_clean();
	ob_start();
	header("Content-Type: " . $mime);
	header("Content-Length: " . filesize($file));
	header('Content-Disposition: inline; filename="' . $original_name . '"');
	readfile($file);
	ob_end_flush();
    exit;
}
