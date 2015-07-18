<?php
/**
 * Contains funcitons
 */
include_once ( $_SERVER['DOCUMENT_ROOT'] . '/classes/DB.class.php' );
$db = new DB();

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
function error_page( string $message ) {
	header( 'Location: ../error.php?err=' . $message );
}

/**
 * Echoes the content of the header file '/header.php'.
 * To be placed at the top of every page.
 * 
 * @param string $title
 *        the title of the current page
 */
function ia_header( $title = '' ) {
	sec_session_start();
	set_page_title( $title );
	return include ( 'header.php' );
}

/**
 * Echoes the content of the footer file '/footer.php'.
 * To be placed at the bottom of every page.
 */
function ia_footer() {
	return include ( 'footer.php' );
}

/**
 * Set title of current page
 * 
 * @param string $title
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
		echo '<link rel="stylesheet" href="css/' . $style . '.css" />';
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
		echo '<script type="text/JavaScript" src="js/' . $script . '.js"></script>';
	}
}
// ####################################################
// USER TOOLS
// ####################################################

/**
 * Get username from id
 * 
 * @param int $userid
 *        user id
 * @return string|NULL|FALSE username, null on database error, false on unexisting id
 */
function get_username( int $userid ) {
	global $db;
	return $db->select( 'members', 'username', array( 
			'id = ?' => $userid 
	), 1 )->get_first( 'username' );
}

/**
 * Get user id from name
 * 
 * @param string $username
 *        username
 * @return int|NULL|FALSE user id, null on database error, false on invalid username
 */
function get_user_id( $username ) {
	global $db;
	return $db->select( 'members', 'id', array( 
			'username = ?' => $username 
	), 1 )->get_first( 'id' );
}

function user_exists( $username ) {
	global $db;
	return $db->select( 'members', 'id', array( 
			'username = ?' => $username 
	) )->has_rows();
}

/**
 * Determines if the given string is the email, id, or name of a user
 * 
 * @param unknown $login        
 * @return string 'email', 'id', or 'name'
 */
function login_type( $login ) {
	if ( filter_var( $login, FILTER_VALIDATE_EMAIL ) ) return 'email';
	if ( is_numeric( $login ) ) return 'id';
	return 'username';
}

/**
 * Get user_id, email, username, password, and salt for the given login meaning either id, email or username
 * 
 * @param unknown $login        
 * @return NULL|multitype: [user_id, email, username, password, salt] as array
 */
function get_user_info( $login ) {
	global $db;
	$table = 'members';
	$columns = 'email, id, username, password, salt';
	$type = login_type( $login );
	$result = $db->select( $table, $columns, array( 
			$type . ' = ?' => $login 
	), 1 );
	return ( $result->has_rows() ) ? $result->rows[0] : false;
}

/**
 * Add a failed login atempt for given user
 * 
 * @param
 *        $user_id
 */
function add_brute( $user_id ) {
	global $db;
	$now = time();
	$db->insert( 'login-attempts', array( 
			'user_id' => $user_id, 
			'time = ?' => $now 
	) );
}

/**
 * Remove all failed login atempts
 * 
 * @param unknown $user_id        
 * @return int amount of attempts removed
 */
function reset_brute( $user_id ) {
	global $db;
	return $db->delete( 'login-attempts', array( 
			'user_id = ?' => $user_id 
	) );
}

/**
 * Check wether a user is locked due to too many wrong attempts
 * 
 * @param integer $user_id
 *        the id of the user to check for
 * @return boolean
 */
function checkbrute( $user_id ) {
	global $db;
	$now = time();
	$valid_attempts = $now - ( 2 * 60 * 60 );
	$result = $db->select( 'login_attempts', 'time', array( 
			'user_id = ?' => $user_id, 
			'time > ?' => $valid_attempts 
	) );
	
	if ( ( $result->num_rows > LOGIN_ATEMPTS ) && ( ( LOGIN_ATEMPTS ) != 0 ) ) {
		return true;
	} else {
		return false;
	}
}
// ####################################################
// LOGIN & REGISTER
// ####################################################

/**
 * Log in
 * 
 * @param unknown $login
 *        email or username
 * @param unknown $password
 *        password
 * @return string|boolean <ul>
 *         <li> true: logged in successfully</li>
 *         <li> 1: incorrect password</li>
 *         <li> 2: user does not exist</li>
 *         <li> 3: user locked</li>
 *         <li> 4: database error</li>
 */
function login( $login, $password ) {
	global $mysqli;
	$info = get_user_info( $login );
	if ( $info === null ) return '4';
	if ( $info === false ) return '2';
	$user_id = $info['user_id'];
	$username = $info['username'];
	$db_password = $info['password'];
	$salt = $info['salt'];
	// hash the password with the unique salt.
	$password = hash( 'sha512', $password . $salt );
	// If the user exists we check if the account is locked
	// from too many login attempts
	if ( checkbrute( $user_id, $mysqli ) == true ) {
		// Account is locked
		// TODO some way to unlock
		return '3';
	} else {
		if ( $db_password == $password ) {
			// Password is correct!
			// Get the user-agent string of the user.
			$user_browser = $_SERVER['HTTP_USER_AGENT'];
			// XSS protection as we might print this value
			$user_id = preg_replace( "/[^0-9]+/", "", $user_id );
			$_SESSION['user_id'] = $user_id;
			// XSS protection as we might print this value
			$username = preg_replace( "/[^a-zA-Z0-9_\-]+/", "", $username );
			$_SESSION['username'] = $username;
			$_SESSION['login_string'] = hash( 'sha512', $password . $user_browser );
			// Login successful.
			return true;
		} else {
			// Password is not correct
			add_brute( $user_id );
			return '1';
		}
	}
}

/**
 * Check wether a user is logged in to the current session
 * 
 * @return boolean
 */
function login_check() {
	global $db;
	// Check if all session variables are set
	if ( isset( $_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'] ) ) {
		$user_id = $_SESSION['user_id'];
		$login_string = $_SESSION['login_string'];
		// Get the user-agent string of the user.
		$user_browser = $_SERVER['HTTP_USER_AGENT'];
		$result = $db->select( 'members', 'password', array( 
				'id = ?' => $user_id 
		), 1 );
		
		if ( $result->num_rows == 1 ) {
			$password = $result->get_first( 'password' );
			$login_check = hash( 'sha512', $password . $user_browser );
			if ( $login_check == $login_string ) {
				return true;
			}
		}
	}
	return false;
}

// ####################################################
// USER SETTINGS
// ####################################################

/**
 * Set a value in the user_settings table
 * If key already exists the value is changed
 * returns false on database error
 * 
 * @param unknown $user_id        
 * @param unknown $key        
 * @param unknown $value        
 */
function set_user_setting( $user_id, $key, $value ) {
	global $db;
	$data = array( 
			'user_id' => $user_id, 
			'setting' => $key, 
			'value' => $value 
	);
	return $db->insert( 'user_settings', $data, array( 
			'value' => $value 
	) );
}

/**
 * Get a value from the user_settings table
 * 
 * @param int $user_id
 *        user ID
 * @param string $key
 *        setting key
 * @return unknown|null null on database error or unset key.
 */
function get_user_setting( $user_id, $key ) {
	global $db;
	$condition = array( 
			'user_id = ?' => $user_id, 
			'setting = ?' => $key 
	);
	$result = $db->select( 'user_settings', 'value', $condition );
	return $result->get_first( 'value' );
}
// ####################################################
// AVATAR
// ####################################################

/**
 * Path to the avatar of the given user.
 * If no avatar exists, the default is returned.
 * 
 * @param unknown $user_id        
 * @return string path to avatar.
 */
function get_avatar( $user_id ) {
	$default = ROOT . '/content/images/default_user/avatar.png';
	$avatarid = get_user_setting( $user_id, "avatar" );
	if ( !$avatarid ) return $default;
	return get_upload( $user_id, $avatarid );
}

/**
 * Sets user avatar to the given image.
 * 
 * @param unknown $user_id
 *        topisanitopisani
 * @param unknown $upload_id
 *        upload id of avatar image
 */
function set_avatar( $user_id, $upload_id ) {
	set_user_setting( $user_id, "avatar", $upload_id );
}

// ####################################################
// FILE HANDLING
// ####################################################
function ia_upload( $desc, $type ) {
	$id = rand();
	echo '
        <form enctype="multipart/form-data" action="includes/files/' . $type . '_upload.php" method="POST" target="' . $id . '_ul">
        <input type="hidden" name="MAX_FILE_SIZE" value="' . MAX_FILE_SIZE . '" />
        ' . $desc . ' <input name="userfile" type="file" /><br />
        <input type="submit" value="Upload File" />
        </form>
        <iframe id="' . $id . '_ul" class="hidden_upload" name="' . $id . '_ul" style="display:none" ></iframe>
        <script type="text/javascript">
window.' . $id . '_ul.
';
}

function get_upload( $user_id, $upload_id ) {
	return "file_view.php?user_id=" . $user_id . "&upload_id=" . $upload_id;
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