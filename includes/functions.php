<?php
include_once ($_SERVER ['DOCUMENT_ROOT'] . '/config.php');

$mysqli = new mysqli ( HOST, USER, PASSWORD, DATABASE );

/**
 * Starts a secure session.
 *
 * Should be run at the top of every page
 *
 * NOTE: not needed if ia_header() is present
 */
function sec_session_start() {
	$session_name = 'sec_session_id'; // Set a custom session name
	$secure = SECURE;
	// This stops JavaScript being able to access the session id.
	$httponly = true;
	// Forces sessions to only use cookies.
	if (ini_set ( 'session.use_only_cookies', 1 ) === FALSE) {
		error_page ( "Could not initiate a safe session (ini_set)" );
		exit ();
	}
	// Gets current cookies params.
	$cookieParams = session_get_cookie_params ();
	session_set_cookie_params ( $cookieParams ["lifetime"], $cookieParams ["path"], $cookieParams ["domain"], $secure, $httponly );
	// Sets the session name to the one set above.
	session_name ( $session_name );
	session_start (); // Start the PHP session
	session_regenerate_id ( true ); // regenerated the session, delete the old one.
}

/**
 * Redirects to an error page with given message
 *
 * @param string $message
 *        	the error message to display
 */
function error_page(string $message) {
	header ( 'Location: ../error.php?err=' . $message );
}
/**
 * Echoes the content of the header file '/header.php'.
 *
 * To be placed at the top of every page.
 *
 * @param string $title
 *        	the title of the current page
 */
function ia_header($title = '') {
	sec_session_start ();
	set_page_title ( $title );
	return include ('header.php');
}
/**
 * Echoes the content of the footer file '/footer.php'.
 *
 * To be placed at the bottom of every page.
 */
function ia_footer() {
	return include ('footer.php');
}

/**
 * Set title of current page
 *
 * @param string $title
 *        	title
 */
function set_page_title($title) {
	$_SESSION ['page-title'] = $title;
}
/**
 * Get title of the current page
 *
 * @return page title
 */
function get_page_title() {
	return $_SESSION ['page-title'];
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
 *        	user id
 * @return string|NULL|FALSE username, null on database error, false on unexisting id
 */
function get_username(int $userid) {
	if ($stmt = $mysqli->prepare ( 'SELECT username FROM members WHERE id = ? LIMIT 1' )) {
		$stmt->bind_param ( 'i', $userid );
		$stmt->execute ();
		$stmt->store_result ();
		
		$stmt->bind_result ( $username );
		$stmt->fetch ();
		if ($stmt->num_rows == 1)
			return ( string ) $username;
		return false;
	}
	return null;
}

/**
 * Get user id from name
 *
 * @param string $username
 *        	username
 * @return int|NULL|FALSE user id, null on database error, false on invalid username
 */
function get_userid($username) {
	if ($stmt = $mysqli->prepare ( 'SELECT id FROM members WHERE username = ? LIMIT 1' )) {
		$stmt->bind_param ( 's', $username );
		$stmt->execute ();
		$stmt->store_result ();
		
		$stmt->bind_result ( $userid );
		$stmt->fetch ();
		if ($stmt->num_rows == 1)
			return ( int ) $userid;
		return false;
	}
	return null;
}
function user_exists($username) {
}

/**
 * Determines if the given string is the email, id, or name of a user
 *
 * @param unknown $login        	
 * @return string 'email', 'id', or 'name'
 */
function login_type($login) {
	if (strpos ( $login, '@' ))
		return 'email';
	if (is_numeric ( $login ))
		return 'id';
	return 'name';
}

/**
 * Get user_id, email, username, password, and salt for the given login meaning either id, email or username
 *
 * @param unknown $login        	
 * @return NULL|multitype: [user_id, email, username, password, salt] as array
 *        
 */
function get_user_info($login) {
	global $mysqli;
	switch (login_type ( $login )) {
		case 'email' :
			if ($stmt = $mysqli->prepare ( 'SELECT id, username, password, salt FROM members WHERE email = ? LIMIT 1' )) {
				$stmt->bind_param ( 's', $login );
				$stmt->execute ();
				$stmt->store_result ();
				
				$stmt->bind_result ( $user_id, $username, $password, $salt );
				$stmt->fetch ();
				$email = $login;
			} else
				return null;
			break;
		case 'id' :
			if ($stmt = $mysqli->prepare ( 'SELECT email, username, password, salt FROM members WHERE id = ? LIMIT 1' )) {
				$stmt->bind_param ( 'i', $login );
				$stmt->execute ();
				$stmt->store_result ();
				
				$stmt->bind_result ( $email, $username, $password, $salt );
				$stmt->fetch ();
				$user_id = $login;
			} else
				return null;
			break;
		case 'name' :
			if ($stmt = $mysqli->prepare ( 'SELECT email, id, password, salt FROM members WHERE username = ? LIMIT 1' )) {
				$stmt->bind_param ( 's', $login );
				$stmt->execute ();
				$stmt->store_result ();
				
				$stmt->bind_result ( $email, $user_id, $password, $salt );
				$stmt->fetch ();
				$username = $login;
			} else
				return null;
			break;
		default :
			return null;
	}
	if ($stmt->num_rows == 1)
		return compact ( 'user_id', 'email', 'username', 'password', 'salt' );
	return false;
}
/**
 * Add a failed login atempt for given user
 *
 * @param
 *        	$user_id
 */
function add_brute($user_id) {
	global $mysqli;
	$now = time ();
	$mysqli->query ( "INSERT INTO login_attempts(user_id, time)
			VALUES ('$user_id', '$now')" );
}
/**
 * Remove all failed login atempts
 *
 * @param unknown $user_id        	
 * @return int amount of attempts removed
 */
function reset_brute($user_id) {
	global $mysqli;
	$mysqli->query ( "DELETE FROM login_attempts WHERE user_id = '$user_id'" );
	return $mysqli->affected_rows;
}
/**
 * Check wether a user is locked due to too many wrong attempts
 *
 * @param integer $user_id
 *        	the id of the user to check for
 * @return boolean
 */
function checkbrute($user_id) {
	global $mysqli;
	// Get timestamp of current time
	$now = time ();
	
	// All login attempts are counted from the past 2 hours.
	$valid_attempts = $now - (2 * 60 * 60);
	
	if ($stmt = $mysqli->prepare ( "SELECT time
			FROM login_attempts
			WHERE user_id = ?
			AND time > '$valid_attempts'" )) {
		$stmt->bind_param ( 'i', $user_id );
		
		// Execute the prepared query.
		$stmt->execute ();
		$stmt->store_result ();
		
		// If there have been more than [LOGIN_ATEMPTS] atempts
		if (($stmt->num_rows > LOGIN_ATEMPTS) && ((LOGIN_ATEMPTS) != 0)) {
			return true;
		} else {
			return false;
		}
	}
}
// ####################################################
// LOGIN & REGISTER
// ####################################################
/**
 *
 * Log in
 *
 * @param unknown $login
 *        	email or username
 * @param unknown $password
 *        	password
 * @return string|boolean <ul>
 *         <li> true: logged in successfully</li>
 *         <li> 1: incorrect password</li>
 *         <li> 2: user does not exist</li>
 *         <li> 3: user locked</li>
 *         <li> 4: database error</li>
 */
function login($login, $password) {
	global $mysqli;
	$info = get_user_info ( $login );
	if ($info === null)
		return '4';
	if ($info === false)
		return '2';
	$user_id = $info ['user_id'];
	$username = $info ['username'];
	$db_password = $info ['password'];
	$salt = $info ['salt'];
	
	// hash the password with the unique salt.
	$password = hash ( 'sha512', $password . $salt );
	// If the user exists we check if the account is locked
	// from too many login attempts
	
	if (checkbrute ( $user_id, $mysqli ) == true) {
		// Account is locked
		// TODO some way to unlock
		return '3';
	} else {
		if ($db_password == $password) {
			// Password is correct!
			// Get the user-agent string of the user.
			$user_browser = $_SERVER ['HTTP_USER_AGENT'];
			// XSS protection as we might print this value
			$user_id = preg_replace ( "/[^0-9]+/", "", $user_id );
			$_SESSION ['user_id'] = $user_id;
			// XSS protection as we might print this value
			$username = preg_replace ( "/[^a-zA-Z0-9_\-]+/", "", $username );
			$_SESSION ['username'] = $username;
			$_SESSION ['login_string'] = hash ( 'sha512', $password . $user_browser );
			// Login successful.
			return true;
		} else {
			// Password is not correct
			add_brute ( $user_id );
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
	global $mysqli;
	// Check if all session variables are set
	if (isset ( $_SESSION ['user_id'], $_SESSION ['username'], $_SESSION ['login_string'] )) {
		
		$user_id = $_SESSION ['user_id'];
		$login_string = $_SESSION ['login_string'];
		$username = $_SESSION ['username'];
		
		// Get the user-agent string of the user.
		$user_browser = $_SERVER ['HTTP_USER_AGENT'];
		
		if ($stmt = $mysqli->prepare ( "SELECT password
                                      FROM members
                                      WHERE id = ? LIMIT 1" )) {
			// Bind "$user_id" to parameter.
			$stmt->bind_param ( 'i', $user_id );
			$stmt->execute (); // Execute the prepared query.
			$stmt->store_result ();
			
			if ($stmt->num_rows == 1) {
				// If the user exists get variables from result.
				$stmt->bind_result ( $password );
				$stmt->fetch ();
				$login_check = hash ( 'sha512', $password . $user_browser );
				
				if ($login_check == $login_string) {
					// Logged In!!!!
					return true;
				} else {
					// Not logged in
					return false;
				}
			} else {
				// Not logged in
				return false;
			}
		} else {
			// Not logged in
			return false;
		}
	} else {
		// Not logged in
		return false;
	}
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
function user_avatar_path($user_id) {
	$default = $_SERVER ['DOCUMENT_ROOT'] . '/content/images/default_user/avatar.png';
	$avatar = USERDATA . '/' . $user_id . '/avatar.png';
	if (! file_exists ( $avatar ))
		$avatar = $default;
	return $avatar;
}

// ####################################################
// MISC
// ####################################################
function esc_url($url) {
	if ('' == $url) {
		return $url;
	}
	
	$url = preg_replace ( '|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url );
	
	$strip = array (
			'%0d',
			'%0a',
			'%0D',
			'%0A' 
	);
	$url = ( string ) $url;
	
	$count = 1;
	while ( $count ) {
		$url = str_replace ( $strip, '', $url, $count );
	}
	
	$url = str_replace ( ';//', '://', $url );
	
	$url = htmlentities ( $url );
	
	$url = str_replace ( '&amp;', '&#038;', $url );
	$url = str_replace ( "'", '&#039;', $url );
	
	if ($url [0] !== '/') {
		// We're only interested in relative links from $_SERVER['PHP_SELF']
		return '';
	} else {
		return $url;
	}
}