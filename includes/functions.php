<?php

/*
 * Define directories an URLs
 */
define( 'ROOT_DIR', rtrim( __DIR__, 'includes'  ) );
define( 'TEMPLATE_DIR', ROOT_DIR . 'template/' );
define( 'INCLUDES_DIR', ROOT_DIR . 'includes/' );
define( 'CLASSES_DIR', ROOT_DIR . 'classes/' );
define( 'FORMS_DIR', INCLUDES_DIR . 'forms/' );
define( 'ENUMS_DIR', INCLUDES_DIR . 'enums/' );
define( 'LIBS_DIR', ROOT_DIR . 'libs/' );

define( 'ROOT_URL', '/' );
define( 'TEMPLATE_URL', ROOT_URL . 'templates/' );
define( 'INCLUDES_URL', ROOT_URL . 'includes/' );
define( 'CLASSES_URL', ROOT_URL . 'classes/' );
define( 'FORMS_URL', INCLUDES_URL . 'forms/' );
define( 'ENUMS_URL', INCLUDES_URL . 'enums/' );

/*
 * Include Liberarys
 */
require_once( LIBS_DIR . 'php-enum.php' );

/*
 * Include enums
 */
require_once( ENUMS_DIR . 'databases.enum.php' );
require_once( ENUMS_DIR . 'files.enum.php' );

/*
 * Include classes
 */
require_once( CLASSES_DIR . 'DB.class.php' );
require_once( CLASSES_DIR . 'File.class.php' );
require_once( CLASSES_DIR . 'User.class.php' );
require_once( CLASSES_DIR . 'Artwork.class.php' );
require_once( CLASSES_DIR . 'Error.class.php' );

/*
 * Initiate global variables
 */
$db = new DB();


/* 
 *  F U N C T I O N S:
 */

/**
 * Starts a secure session.
 * Should be run at the top of every page
 * NOTE: not needed if ia_header() is present
 */
function sec_session_start() {
	$session_name = 'sec_session_id'; // Set a custom session name
	$secure = SECURE;
	// This stops JavaScript being able to access the session id.
	$httponly = true;
	// Forces sessions to only use cookies.
	if ( ini_set( 'session.use_only_cookies', 1 ) === FALSE ) {
		error_page( "Could not initiate a safe session (ini_set)" );
		die();
	}
	// Gets current cookies params.
	$cookieParams = session_get_cookie_params();
	session_set_cookie_params( $cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly );
	// Sets the session name to the one set above.
	session_name( $session_name );
	session_start(); // Start the PHP session
	session_regenerate_id( true ); // regenerated the session, delete the old one.
}

/**
 * Redirects to an error page with given message
 * 
 * @param string $message
 *        the error message to display
 */
function error_page( $message ) {
	header( 'Location: /template/error.php?err=' . $message );
}

/**
 * Echoes the content of the header file '/header.php'.
 * To be placed at the top of every page.
 * 
 * @param $title
 *        the title of the current page
 */
function ia_header( $title = '' ) {
	sec_session_start();
	set_page_title( $title );
	return include ( TEMPLATE_DIR . 'header.php' );
}

function redirect($url)
{
    $baseUri="";;

    if(headers_sent())
    {
        $string = '<script type="text/javascript">';
        $string .= 'window.location = "' . $baseUri.$url . '"';
        $string .= '</script>';

        echo $string;
    }
    else
    {
    if (isset($_SERVER['HTTP_REFERER']) AND ($url == $_SERVER['HTTP_REFERER']))
        header('Location: '.$_SERVER['HTTP_REFERER']);
    else
        header('Location: '.$baseUri.$url);

    }
    exit;
}

/**
 * Echoes the content of the footer file '/footer.php'.
 * To be placed at the bottom of every page.
 */
function ia_footer() {
	return include ( TEMPLATE_DIR . 'footer.php' );
}

/**
 * Set title of current page
 * 
 * @param $title
 *        title
 */
function set_page_title( $title ) {
	$_SESSION['page-title'] = $title;
}

/**
 * Get title of the current page
 * 
 * @return page title
 */
function get_page_title() {
	return $_SESSION['page-title'];
}
// ###################################################
// Scripts & styles
// ###################################################

/**
 * Echoes all stylesheets formated as html.
 */
function ia_styles() {
	$styles = [ 
			'main' 
	];
	foreach ( $styles as $style ) {
		echo '<link rel="stylesheet" href="/template/css/' . $style . '.css" />';
	}
}

/**
 * Echoes all javasrcipt formated as html.
 */
function ia_scripts() {
	$scripts = [ 
			'main', 
			'forms', 
			'sha512' 
	];
	foreach ( $scripts as $script ) {
		echo '<script type="text/JavaScript" src="/js/' . $script . '.js"></script>';
	}
}

// ####################################################
// FILE HANDLING
// ####################################################
function ia_upload( $desc, $type ) {
	$id = rand();
	echo '
		<form enctype="multipart/form-data" action="<?php echo \Enums\Files\Userdata::UPLOAD_URL ?>" method="POST" target="' . $id . '_ul">
		<input type="hidden" name="type" value="' . $type . '" />
		<input type="hidden" name="MAX_FILE_SIZE" value="' . MAX_FILE_SIZE . '" />
        ' . $desc . ' <input name="userfile" type="file" /><br />
        <input type="submit" value="Upload File" />
        </form>
        <iframe id="' . $id . '_ul" class="hidden_upload" name="' . $id . '_ul" style="display:none" ></iframe>
	 ';
}

function get_upload( $user_id, $upload_id ) {
	return "<?php echo \Enums\Files\Userdata::VIEW_URL ?>?user_id=" . $user_id . "&upload_id=" . $upload_id;
}
// ####################################################
// MISC
// ####################################################
function esc_url( $url ) {
	if ( '' == $url ) {
		return $url;
	}
	$url = preg_replace( '|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url );
	$strip = array( 
			'%0d', 
			'%0a', 
			'%0D', 
			'%0A' 
	);
	$url = ( string ) $url;
	$count = 1;
	while ( $count ) {
		$url = str_replace( $strip, '', $url, $count );
	}
	$url = str_replace( ';//', '://', $url );
	$url = htmlentities( $url );
	$url = str_replace( '&amp;', '&#038;', $url );
	$url = str_replace( "'", '&#039;', $url );
	if ( $url[0] !== '/' ) {
		// We're only interested in relative links from $_SERVER['PHP_SELF']
		return '';
	} else {
		return $url;
	}
}
