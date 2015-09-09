<?php
$username = $params['user'];
$post = new Post( User::get( $username, $db ), $params['artwork_id'], $params['id'], $db );

ia_header( $username . '|' . $post->name );

foreach ( $post->media as $media_id) {
	echo '<img src="' . $post->user->get_upload( $media_id ) . '"/>';
}
?>
<h1><?php echo $post->name ?></h1>
<p class="post-text"><?php echo $post->text ?></p>
<?php
ia_footer();
