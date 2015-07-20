<?php
require_once ( $_SERVER['DOCUMENT_ROOT'] . '/config.php' );
require_once ( ROOT . 'classes/DB.class.php' );
require_once ( ROOT . 'classes/Error.class.php' );

/**
 * Class: User
 * @param $user_id
 * @param DB $db
 */
class User {

	private $db;

	public $user_id;

	public $username;

	public $email;

	public $password;

	public $salt;

	function __construct( $user_id, DB $db ) {
		$table = 'users';
		$columns = 'email, user_id, username, password, salt';
		$type = login_type( $login );
		$condition = array(
				$type . ' = ?' => $login
		);
		$result = $db->select( $table, $columns, $condition, 1 );
		$row( $result->has_rows() ) ? $result->rows[0] : false;
		if ( $row ) {
			foreach ( $row as $k => $v ) {
				${$k} = $v;
			}
		} else {
			Error::stop( "User does not exist" );
		}
	}

	/**
	 * Add a failed login attempt to database
	 */
	function add_attempt() {
		$now = time();
		$this->db->insert( 'login-attempts', array(
				'user_id' => $this->user_id,
				'time = ?' => $now
		) );
	}

	/**
	 * Remove all failed login attempts
	 *
	 * @return int amount of attempts removed
	 */
	function reset_attempts() {
		return $this->db->delete( 'login-attempts', array(
				'user_id = ?' => $this->user_id
		) );
	}

	/**
	 * Check wether a user is locked due to too many wrong attempts
	 *
	 * @return boolean
	 */
	function check_attempts() {
		$now = time();
		$valid_attempts = $now - ( 2 * 60 * 60 );
		$result = $this->db->select( 'login_attempts', 'time', array(
				'user_id = ?' => $this->user_id,
				'time > ?' => $valid_attempts
		) );
		if ( ( $result->num_rows > LOGIN_ATEMPTS ) && ( ( LOGIN_ATEMPTS ) != 0 ) ) {
			return true;
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
	 * @param unknown $key
	 * @param unknown $value
	 */
	function set_user_setting( $key, $value ) {
		$data = array(
				'user_id' => $this->user_id,
				'setting' => $key,
				'value' => $value
		);
		return $this->db->insert( 'user_settings', $data, array(
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
	function get_user_setting( $key ) {
		$condition = array(
			'user_id = ?' => $this->user_id,
			'setting = ?' => $key
		);
		$result = $this->db->select( 'user_settings', 'value', $condition );
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
	function get_avatar( ) {
		$default = ROOT . '/content/images/default_user/avatar.png';
		$avatarid = $this->get_user_setting( "avatar" );
		if ( !$avatarid ) return $default;
		return $this->get_upload( $avatarid );
	}

	/**
	 * Sets user avatar to the given image.
	 *
	 * @param unknown $upload_id
	 *        upload id of avatar image
	 */
	function set_avatar( $upload_id ) {
		set_user_setting( $this->user_id, "avatar", $upload_id );
	}

	/**
	 * Returns path to viewer for uploaded file
	 *
	 * @param mixed $upload_id
	 */
	function get_upload( $upload_id ) {
		return "includes/files/file_view.php?user_id=" . $this->user_id . "&upload_id=" . $upload_id;
	}

	// ####################################################
	// STATIC FUNCTIONS
	// ####################################################

	/**
	 * Log in
	 *
	 * @param unknown $login
	 *        email or username
	 * @param unknown $password
	 *        password
	 * @param DB $db
	 *        Database
	 * @return string|boolean <ul>
	 *         <li> true: logged in successfully</li>
	 *         <li> 1: incorrect password</li>
	 *         <li> 2: user does not exist</li>
	 *         <li> 3: user locked</li>
	 */
	static function login( $login, $password, DB $db ) {
		$user = User::get( $login, $db );
		if ( $user === false ) return '2';
		$user_id = $user->user_id;
		$username = $user->username;
		$db_password = $user->password;
		$salt = $user->salt;

		$password = hash( 'sha512', $password . $salt );
		if ( $this->check_attempts() == true ) {
			return '3';
		} else {
			if ( $db_password === $password && $db_password != null ) {
				$user_browser = $_SERVER['HTTP_USER_AGENT'];
				// XSS protection as we might print this value
				$user_id = preg_replace( "/[^0-9]+/", "", $user_id );
				$_SESSION['user_id'] = $user_id;
				// XSS protection as we might print this value
				$username = preg_replace( "/[^a-zA-Z0-9_\-]+/", "", $username );
				$_SESSION['username'] = $username;
				$_SESSION['login_string'] = hash( 'sha512', $password . $user_browser );
				// Login successful.
				$this->reset_attempts();
				return true;
			} else {
				// Password is not correct
				add_brute();
				return '1';
			}
		}
	}

	/**
	 * Check wether a user is logged in to the current session
	 *
	 * @return boolean
	 */
	static function login_check( DB $db ) {
		// Check if all session variables are set
		if ( isset( $_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'] ) ) {
			$user_id = $_SESSION['user_id'];
			$login_string = $_SESSION['login_string'];
			// Get the user-agent string of the user.
			$user_browser = $_SERVER['HTTP_USER_AGENT'];
			$result = $db->select( 'users', 'password', array(
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

	/**
	 * Get User object for currently logged in user
	 *
	 * @param DB $db Database
	 * @return User|bool new User object or false if not logged in
	 */
	static function get_current( DB $db ) {
		if ( User::login_check( $db ) ) {
			return new User( $_SESSION['user_id'], $db );
		}
		return false;
	}


	/**
	 * Return a new User object for the user with the given $login
	 *
	 * @param mixed $login can be email, userid, or username
	 * @param DB $db
	 * @return User|bool new User object or false if no user was found
	 */
	static function get( $login, DB $db ) {
		$type = User::login_type( $login );
		$result = $db->select( 'users', 'user_id', [ 
			$type . ' = ?' => $login 
		] );
		if ( !$result->has_rows() ) return false;
		return new User( $result->get_first( 'user_id' ), $db );
	}

	/**
	 * Check if user with given $login exists
	 *
	 * @param mixed $login can be email, userid, or username
	 * @param DB $db Database object
	 * @return boolean
	 */
	static function exists( $login, DB $db ) {
		$type = User::login_type( $login );
		$result = $db->select( 'users', '*', [
				$type . ' = ?' => $login
		] );
		return $result->has_rows();
	}

	/**
	 * Determines if the given string is the email, id, or name of a user
	 *
	 * @param unknown $login
	 * @return string 'email', 'user_id', or 'username'
	 */
	private static function login_type( $login ) {
		if ( filter_var( $login, FILTER_VALIDATE_EMAIL ) ) return 'email';
		if ( is_numeric( $login ) ) return 'user_id';
		return 'username';
	}

}
