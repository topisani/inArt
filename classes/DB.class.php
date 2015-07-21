<?php
require_once ( __DIR__ . '/../config.php' );

class DB {

	public $mysqli;

	function __construct() {
		$this->mysqli = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE );
	}

	/**
	 *
	 * @param string $table
	 *        Table to select from.
	 * @param string $columns
	 *        Comma sepperated list of columns to select.
	 * @param array $condition
	 *        Array of conditions. 'column = ?' => 'value'. can be null for all rows
	 * @param int $limit
	 *        [optional] Limits the number of results. Leave empty for no limit.
	 * @param string $order_by
	 *        [optional] Comma seperated list of columns to order by.
	 */
	function select( $table, $columns, $condition = null, $limit = null, $order_by = null ) {
		$str = "
				SELECT " . $columns . " 
				FROM " . $table . "
				";
		$this->where( $condition, $values, $str );
		$this->limit( $limit, $str );
		$this->order_by( $order_by, $str );
		$stmt = $this->mysqli->prepare( $str );
		if ( !$stmt ) echo( "Database Error on select()" );
		trigger_error( $stmt->error );
		trigger_error(  $this->mysqli->error );
		$this->bind_values( $stmt, $values );
		$stmt->execute();
		return new DB_result( $stmt->get_result() );
	}

	/**
	 * Inserts data into a table
	 * 
	 * @param string $table
	 *        Table to insert data into
	 * @param array $data
	 *        Data to insert. $column => $value
	 * @param array $update_data
	 *        If set, this is an array of data to update on duplicate primary or unique key
	 * @return boolean Wether the insert succeded.
	 */
	function insert( $table, $data, $update_data = null ) {
		$str = '
				INSERT INTO ' . $table . '
				SET 
				';
		$this->data( $data, $values, $str );
		if ( isset( $update_data ) ) {
			$str .= ' ON DUPLICATE KEY UPDATE';
			$this->data( $update_data, $values, $str );
		}
		if ( !$stmt = $this->mysqli->prepare( $str ) ) die( "Database Error on insert()" );
		$this->bind_values( $stmt, $values );
		$stmt->execute();
		if ( $stmt->affected_rows > 0 ) {
			return true;
		}
		return false;
	}

	/**
	 * Delete rows from $table.
	 * 
	 * @param string $table
	 *        Table to delete rows from.
	 * @param array $condition
	 *        Array of conditions. 'column = ?' => 'value'
	 * @param int $limit
	 *        Max rows to delete.
	 * @param string $order_by
	 *        Comma seperated list of columns to order by.
	 * @return number of rows deleted.
	 */
	function delete( $table, $condition = null, $limit = null, $order_by = null ) {
		$str = '
				DELETE FROM ' . $table . ' 
				';
		$this->where( $condition, $values, $str );
		$this->limit( $limit, $str );
		$this->order_by( $order_by, $str );
		$stmt = $this->mysqli->prepare( $str );
		echo $stmt->error;
		echo $this->mysqli->error;
		if ( !$stmt ) Error::stop( "Database Error on Delete()" );
		$this->bind_values( $stmt, $values );
		$stmt->execute();
		return $stmt->affected_rows;
	}

	/**
	 * Returns string i, d, or s depending on the type of $param.
	 * for use with mysqli::bind_result().
	 * 
	 * @param unknown $param        
	 */
	private function get_type( $param ) {
		if ( filter_var( $param, FILTER_VALIDATE_INT ) ) return "i";
		if ( filter_var( $param, FILTER_VALIDATE_FLOAT ) ) return "d";
		return "s";
	}

	/**
	 * Binds values for prepared statement
	 * 
	 * @param unknown $stmt        
	 * @param unknown $values        
	 */
	private function bind_values( &$stmt, &$values ) {
		$refs = array();
		foreach ( $values as $key => $value ) {
			$refs[$key] = &$values[$key];
		}
		call_user_func_array( array( 
				$stmt, 
				'bind_param' 
		), $refs );
	}

	/**
	 * Concats WHERE clause for the given $condition array to string $str
	 * 
	 * @param array $condition        
	 * @param array $values
	 *        Array to put params into.
	 *        Passed by refference.
	 * @param string $str
	 *        Query to concat WHERE clause to.
	 *        Passed by refference.
	 */
	private function where( $condition, &$values, &$str ) {
		if ( isset( $condition ) ) {
			$str .= ' WHERE ';
			$values[0] = isset( $values ) ? $values[0] : '';
			foreach ( $condition as $key => $value ) {
				$str .= ' ' . $key . " AND";
				$values[] = $value;
				$values[0] .= $this->get_type( $value );
			}
			$str = rtrim( $str, 'AND' );
		}
	}

	/**
	 * Concats LIMIT clause to string $str
	 * 
	 * @param int $limit
	 *        amount to limit, can be null
	 * @param string $str
	 *        Query to concat LIMIT clause to.
	 *        Passed by refference.
	 */
	private function limit( $limit, &$str ) {
		if ( isset( $limit ) ) $str .= ' LIMIT ' . $limit;
	}

	/**
	 * Concats ORDER BY clause to string $str
	 * 
	 * @param string $order_by
	 *        Argument for ORDER BY, without parenthesies.
	 * @param string $str
	 *        Query to concat ORDER BY clause to.
	 *        Passed by refference.
	 */
	private function order_by( $order_by, &$str ) {
		if ( isset( $order_by ) ) $str .= ' ORDER BY ' . $order_by;
	}

	/**
	 * unpacks $data array into '$key1 = ?, $key2 = ?' etc.
	 * and adds the parameters to the $values array in the right order
	 * 
	 * @param array $data        
	 * @param array $values
	 *        Array to put params into
	 *        $values[0] is a string of types.
	 *        Passed by refference.
	 * @param string $str
	 *        Query to concat data to.
	 *        Passed by reffernce.
	 */
	private function data( $data, &$values, &$str ) {
		if ( isset( $data ) ) {
			$values[0] = isset( $values ) ? $values[0] : '';
			foreach ( $data as $key => $value ) {
				$str .= ' ' . $key . " = ?,";
				$values[] = $value;
				$values[0] .= $this->get_type( $value );
			}
			$str = rtrim( $str, ',' );
		}
	}
}

class DB_result {

	private $mysqli_result;

	public $rows;

	public $num_rows;

	function __construct( $mysqli_result ) {
		$this->mysqli_result = $mysqli_result;
		$this->rows == array();
		if ( $mysqli_result !== false ) {
			while ( $row = $mysqli_result->fetch_assoc() ) {
				if ( $row == null ) {
					$this->rows == array();
					break;
				}
				$this->rows[] = $row;
			}
		}
		$this->num_rows = sizeof( $this->rows );
	}

	/**
	 * Gets an array of values for $key from each row
	 * 
	 * @param unknown $key        
	 */
	function get_value( $key ) {
		foreach ( $this->rows as $row ) {
			$values[] = $row[$key];
		}
		return $values;
	}

	/**
	 * Gets value of column $key from first row
	 * 
	 * @param unknown $key        
	 */
	function get_first( $key ) {
		return $this->rows[0][$key];
	}

	function has_rows() {
		return $this->num_rows > 0;
	}

	/**
	 * Binds values from first row into the given variables.
	 * Variables must equal the name of the column to get.
	 * 
	 * @param unknown ...$args        
	 */
	function bind_vars( &...$args ) {
		foreach ( $args as $k => &$v ) {
			$v = ( $this->has_rows() ) ? $this->rows[0][$v] : null;
		}
	}
}




