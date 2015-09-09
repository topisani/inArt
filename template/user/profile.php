<?php
$username = ( isset( $params['user'] ) && $params['user'] !== '' ) ? $params['user'] : ( Users::login_check( $db ) ? $_SESSION['username'] : null );
if ( $username === null ) {
	Error::stop( 'Not logged in' );
} else if ( !Users::exists( $username, $db ) ) {
	Error::stop( 'Given user does not exist' );
} else {
ia_header( $username );
	
	$user = User::get( $username, $db );

	echo ( '<img height="100px" src="' . $user->get_avatar() . '"/>' );
?>

<h1><?php echo $username?></h1>
<h2>Artworks</h2>
<?php
foreach ( $user->get_artworks() as $post ):
?>
	<div class="post-list-item">
		<a href="<?php echo $post->get_link() ?>" ><?php echo $post->name ?></a>
	</div>
<?php endforeach; ?>
<h2>Posts</h2>
<?php
foreach ( $user->get_posts() as $post ):
?>
	<div class="post-list-item">
		<a href="<?php echo $post->get_link() ?>" ><?php echo $post->name ?></a>
	</div>
<?php 
endforeach;
}
ia_footer();
