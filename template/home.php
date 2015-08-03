<?php
if( Users::login_check( $db ) ) {
	include( TEMPLATE_DIR . 'profile.php' );
	exit();
}

ia_header( 'Home' );

echo 'Welcome home.';

ia_footer();
