<?php
require_once 'functions.php';
sec_session_start ();

if (! login_check ())
	echo "You have to be logged in to upload files";
$user_id = $_SESSION ['user_id'];
$uploaddir = USERDATA . '/uploads/' . $user_id . '/';
$uploadfile = tempnam ( $uploaddir, "ul_" );

if (move_uploaded_file ( $_FILES ['userfile'] ['tmp_name'], $uploadfile )) {
	
	if (! $stmt = $mysqli->prepare ( "INSERT INTO uploads SET user_id=?, name=?, original_name=?, mime_type=?" )) {
		unlink ( $uploadfile );
		die ( "Error connecting to the database" );
	}
	$name = basename ( $uploadfile );
	$original_name = basename ( $_FILES ['userfile'] ['name'] );
	// TODO find a more unexploitable way to find the mimetype
	$mime_type = $_FILES ['userfile'] ['type'];
	
	$stmt->bind_param ('isss', $user_id, $name, $original_name, $mime_type );
	$stmt->execute ();
	if ($stmt->affected_rows != 1) {
		unlink ( $uploadfile );
		die ( "Error saving data to the database. The file was not uploaded" );
	}
	
	if( ! $stmt = $mysqli->prepare ( "SELECT upload_id FROM uploads WHERE user_id=? AND name=? LIMIT 1" )) {
		unlink($uploadfile);
		die ( "Database Error");
	}
	$stmt->bind_param ('is', $user_id, $name );
	$stmt->execute ();
	$stmt->store_result ();
	$stmt->bind_result ( $id );
	$stmt->fetch ();
	
	echo "File is valid, and was successfully uploaded. You can view it <a
href='view6.php?id=" . $id . "'>here</a>n";
} else {
	echo "File uploading failed.n";
}
?>