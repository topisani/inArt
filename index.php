<?php
require_once( 'includes/functions.php' );
$rewrite_rules = [
	'file-view'                => "/media/(?'id'\d+)",
	'register'                 => "/register",
	'login'                    => "/login",
	'user/post-single'         => "/(?'user'[\w\-]+)/artwork/(?'artwork_id'\d+)/post/(?'id'\d+)",
	'user/artwork-single'      => "/(?'user'[\w\-]+)/artwork/(?'id'\d+)",
	'user/profile'             => "/(?'user'[\w\-]+)",
	'edit/index'               => "/edit/(?'action'[\w\-]+)",
	'home'                     => "/"
];

$uri = rtrim( dirname( $_SERVER["SCRIPT_NAME"] ), '/' );
$uri = '/' . trim( str_replace( $uri, '', $_SERVER['REQUEST_URI'] ), '/' );
$uri = urldecode( $uri );

foreach ( $rewrite_rules as $action => $rule ) {
    if ( preg_match( '~^'.$rule.'$~', $uri, $params ) ) {
        include( TEMPLATE_DIR . $action . '.php' );
        exit();
    }
}

// nothing is found so handle the 404 error
include( TEMPLATE_DIR . '404.php' );?>
