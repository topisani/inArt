<?php
require_once( 'includes/functions.php' );
$rewrite_rules = [
	'file-view'                => "/media/(?'id'\d+)",
	'register'                 => "/register",
	'login'                    => "/login",
	'user/artwork'             => "/(?'user'[\w\-]+)/artwork/(?:(?'id'\d+)|(?'name'[\w\-]+))",
	'user/profile'             => "(?:/(?'user'[\w\-]+))",
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
