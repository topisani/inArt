<?php
require_once( CLASSES_DIR . 'Artwork.class.php' );

$user_id = $values['user'];
$artwork = new Artwork( $user_id, $values['id'], $db );
$posts = $artwork->get_posts();
ia_header( $user_id . '|' . $artwork->name );

foreach ( explode( $artwork->media, ',' ) as $media_id) {
	echo '<img src="' . $artwork->user->getUpload( $media_id ) . '"/>';
}
?>
<h1><?php echo $artwork->name ?></h1>
<div class="post-list">
<?php 
foreach ( $posts as $post ) {
?>
	<div class="post-list-item">
		<a href="<?php echo $post->get_link() ?>" ><?php echo $post->name ?></a>
	</div>
<?php 
}


?>
</div>
<?php
ia_footer();
