<?php

/**
 * Class: Error
 * 
 * Used to throw error messages of different levels
 */
abstract class Error {

	/**
	 * Stops the everything and prints the given message
	 *
	 * @param mixed $message
	 */
	static function stop( $message ) {
		die( $message );
	}
	
}
