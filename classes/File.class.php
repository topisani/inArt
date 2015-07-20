<?php
require_once( __DIR__ . '../config.php' );
require_once( __DIR__ . 'Error.class.php' );

class Files {
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


