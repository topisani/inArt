<?php
$action = $params['action'];
switch ( $action ) {
	case 'profile':
		include ( __DIR__ . "/edit-profile.php" );
		break;
	case 'post':
		include( __DIR__ . "/edit-post.php" );
		break;
}
