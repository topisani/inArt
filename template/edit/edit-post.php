<?php
if ( Users::login_check( $db ) ) {
	Error::stop( 'Not logged in' );
}
$username = $_SESSION['username'];
$user = User::get( $username, $db );
$user_id = $_GET['user_id'];
$artwork_id = $_GET['artwork_id'];
$post_id = $_GET['post_id'];
try {
	$post = new Post( $user_id, $artwork_id, $post_id, $db );
	$new_post = false;
} catch ( Exception $e ) {
	$post = Post::create( $user_id, $artwork_id, $post_id, $db );
	$new_post = true;
}
$edit_new = ( $new_post ) ? "new" : "edit";
ia_header( ucfirst( $edit_new ) . " Post" );

?>

<h1><?php ucfirst( $edit_new ) ?> Post</h1>
<form action="" method="post">
	Post title:
	<input type="text" name="post_title">
	<br>
	
