<?php
require_once '../functions.php';
sec_session_start();

if ( !login_check() ) echo "You have to be logged in to upload files";
$user_id = $_SESSION['user_id'];
$uploaddir = USERDATA . $user_id . '/';
$original_name = basename( $_FILES['userfile']['name'] );
$basename = pathinfo( tempnam( $uploaddir, "ul_" ) )['filename'];
$uploadfile = $uploaddir . $basename . '.' . pathinfo( $original_name )['extension'];
$mime_type = $_FILES['userfile']['type'];


if ( move_uploaded_file( $_FILES['userfile']['tmp_name'], $uploadfile ) ) {
	@chmod( $uploadfile, FILE_PERMISSIONS );
	$data = array( 
			'user_id' => $user_id, 
			'name' => basename( $uploadfile ), 
			'original_name' => $original_name, 
			'mime_type' => $mime_type 
	);
	if ( !$db->insert( 'uploads', $data ) ) {
		unlink( $uploadfile );
		die( "Error saving data to the database. The file was not uploaded" );
	}
	$result = $db->select( 'uploads', 'upload_id', array( 
			'user_id = ?' => $user_id, 
			'name = ?' => basename( $uploadfile ) 
	) );
	$upload_id = $result->get_first( 'upload_id' );
} else {
	echo "File uploading failed";
}
?>
