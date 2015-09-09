<?php
$user_id = $params['user'];
$artwork = new Artwork( User::get( $user_id, $db ), $params['id'], $db );
$posts = $artwork->get_posts();
ia_header( $user_id . '|' . $artwork->name );

foreach ( $artwork->media as $media_id) {
	echo '<img src="' . $artwork->user->get_upload( $media_id ) . '"/>';
}
?>
<h1><?php echo $artwork->name ?></h1>
<p class="post-text"><?php echo $artwork->text ?></p>
<div class="post-list">
<?php 
foreach ( $posts as $post ):
?>
	<div class="post-list-item">
		<a href="<?php echo $post->get_link() ?>" ><?php echo $post->name ?></a>
	</div>
<?php 
endforeach;


?>
</div>
<?php
ia_footer();
