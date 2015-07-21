<?php
require_once( __DIR__ . '../config.php' );
require_once( __DIR__ . 'Error.class.php' );

class Files {

	$db;

	public function __construct( DB $db ) {
		$this->db = $db;
	}

	/**
	 * Upload a file to the given users directory
	 *
	 * @param string $user_id 
	 * @param string $form_text $_FILES [$form_text]
	 * @return int $upload_id
	 */
	private function upload( $user_id, $form_text  ) {
		$uploaddir = USERDATA . $user_id . '/';
		$original_name = basename( $_FILES[$form_text]['name'] );
		$basename = pathinfo( tempnam( $uploaddir, 'ul_' ) )['filename'];
		$uploadfile = $uploaddir . $basename . '.' . pathinfo( $original_name )['extension'];
		$mime_type = $_FILES[$form_text]['type'];

		if ( move_uploaded_file( $_FILES[$form_text]['tmp_name'], $uploadfile ) ) {
			@chmod( $uploadfile, FILE_PERMISSIONS );
			$data = array( 
				'user_id' => $user_id, 
				'name' => basename( $uploadfile ), 
				'original_name' => $original_name, 
				'mime_type' => $mime_type 
			);
			if ( !$db->insert( 'uploads', $data ) ) {
				unlink( $uploadfile );
				die( "Error saving data to the database. The file was not uploaded" );
			}
			$result = $db->select( 'uploads', 'upload_id', array( 
				'user_id = ?' => $user_id, 
				'name = ?' => basename( $uploadfile ) 
			) );
			return $result->get_first( 'upload_id' );
		}
		return false;
	}

	
	/**
	 * Create a new directory, and give it the appropriate permissions.
	 *
	 * @param string $path
	 */
	public static function mkdir( $path ) {
		mkdir( $path, DIRECTORY_PERMISSIONS, true );
	}
	
	/**
	 * Delete directory including all contents
	 *
	 * @param string $path
	 */
	public static function rmdir( $path ) {
		if ( !is_dir( $path ) ) {
			throw new InvalidArgumentException( 'Files::rmdir( $path ): $path must be an existing directory' );
		}
		foreach ( glob( $path . '/*' ) as $file ) { 
			if ( is_dir( $file ) ) {
				Files::rmdir( $file );
			} else {
				unlink( $file );
			}	
		} 
		rmdir($dir); 
	}
	/**
	 * Make sure path has trailing slash
	 *
	 * @param string $path
	 */
	public static function trail_slash( $path ) {
		if ( !Files::is_path( $path ) ) {
			throw new InvalidArgumentException( 'Files::trail_slash( $path ): $path must be directory' );
		}
		if ( !preg_match( '\/$' ) )$path .= '/';
		return $path;
	}
	/**
	 * Make sure path has no trailing slash
	 *
	 * @param string $path
	 */
	public static function no_trail_slash( $path ) {
		if ( !Files::is_path_dir( $path ) ) {
			throw new InvalidArgumentException( 'Files::no_trail_slash( $path ): $path must be directory' );
		}
		return rtrim( $path '/' );
	}
	/**
	 * Is the given string a valid path
	 *
	 * @param string $path
	 */
	public static function is_path( $path ) {
		$match = preg_match( '^\.?\/?(((\.?[0-9a-zA-Z]+[_\-\ \.]*)+|\.\.)/?)+~?$', $path );
		return ( $match === 1 );
	}
	/**
	 * Is the given path a file, real or hypothetic
	 *
	 * @param string $path
	 */
	public static function is_path_file( $path ) {
		if ( !Files::is_path( $path ) ) {
			throw new InvalidArgumentException( 'Files::is_path_file( $path ): $path must be a valid file path' );
		}
		if ( is_file( $path ) ) {
			return true;
		}
		//Does path end in *.*
		if ( preg_match( '(\.?[0-9a-zA-Z]+[_\-\ \.]*)+\.([0-9a-zA-Z]+[_\-\]*)+\~?$', $path ) ) return true;
		return false;
	}
	/**
	 * Is the given path a directory, real or hypothetic
	 *
	 * @param string $path
	 */
	public static function is_path_dir( $path ) {
		if ( !Files::is_path( $path ) ) {
			throw new InvalidArgumentException( 'Files::is_path_dir( $path ): $path must be a valid file path' );
		}
		if ( !Files::is_path_file( $path ) ) return true;
		return false;	
	}
}


