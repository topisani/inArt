<?php
require_once 'functions.php';
sec_session_start ();

$user_id = $_GET ['user_id'];
$uploaddir = USERDATA . '/uploads/' . $user_id . '/';
$upload_id = $_GET ['upload_id'];
if (! is_numeric ( $upload_id ) && ! is_numeric ( $user_id )) {
	die ( "IDs must be numeric" );
}

if (! $stmt = $mysqli->prepare ( 'SELECT name, mime_type FROM uploads WHERE user_id=? AND upload_id=?' )) {
	die ( "Error fetching data from the database" );
}
$stmt->bind_param ( 'ii', $user_id, $upload_id );
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($name, $mime_type);
$stmt->fetch();

if ($stmt->num_rows != 1) {
	die ( "File not found" );
}
header ( "Content-Type: " . $mime_type );
readfile ( $uploaddir . $name );
?>