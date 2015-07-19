<?php
require_once( "file_upload.php" );

if ( !set_user_setting( $user_id, "avatar", $upload_id ) ) {
	die( "Database error in avatar_upload.php");
}

