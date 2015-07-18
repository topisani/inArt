<?php
require_once ( 'config.php' );

class DB {

	private $mysqli;

	function __construct() {
		$this->$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE );
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
	 */
	function select( string $table, string $columns, array $condition = null, int $limit = null ) {
		$values[0] = '';
		foreach ( $condition as $key => $value ) {
			$keys .= $key . ", ";
			$values[] = $value;
			$values[0] .= get_type( $value );
		}
		$keys = rtrim( $keys, ',' );
		$str = "
				SELECT " . $columns . " 
				FROM " . $table . "
				";
		if ( isset( $condition ) ) $str .= ' WHERE ' . $keys;
		if ( isset( $limit ) ) $str .= ' LIMIT ' . $limit;
		$str .= ";";
		
		if ( !$stmt = $this->$mysqli->prepare( $str ) ) trigger_error( "Database Error on select()" );
		call_user_func_array( array( 
				$stmt, 
				'bind_param' 
		), $values );
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
	function insert( string $table, array $data, array $update_data = null ) {
		$values[0] = '';
		foreach ( $data as $key => $value ) {
			$keys .= $key . ", ";
			$values[] = $value;
			$values[0] .= get_type( $value );
			$questionmarks .= '?, ';
		}
		$keys = rtrim( $keys, ',' );
		$str = '
				INSERT INTO ' . $table . ' ( ' . $keys . ' ) 
				VALUES (' . $questionmarks . ' ) 
				';
		if ( isset( $update_data ) ) {
			$str .= ' ON DUPLICATE KEY UPDATE';
			foreach ( $update_data as $key => $value ) {
				$str .= $key . "=?, ";
				$values[] = $value;
				$values[0] .= get_type( $value );
			}
			$str = rtrim( $str, ',' );
		}
		$str .= ";";
		
		if ( !$stmt = $this->$mysqli->prepare( $str ) ) trigger_error( "Database Error on insert()" );
		call_user_func_array( array( 
				$stmt, 
				'bind_param' 
		), $values );
		$stmt->execute();
		if ( $stmt->affected_rows == 1 ) {
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
	function delete( string $table, array $condition = null, int $limit = null, string $order_by = null ) {
		$values[0] = '';
		foreach ( $condition as $key => $value ) {
			$keys .= $key . ", ";
			$values[] = $value;
			$values[0] .= get_type( $value );
		}
		$keys = rtrim( $keys, ',' );
		$str = '
				DELETE FROM ' . $table . '
				';
		if ( isset( $condition ) ) $str .= ' WHERE ' . $keys;
		if ( isset( $limit ) ) $str .= ' LIMIT ' . $limit;
		if ( isset( $order_by ) ) $str .= ' ORDER BY (' . $orderby . ')';
		$str .= ";";
		
		if ( !$stmt = $this->$mysqli->prepare( $str ) ) trigger_error( "Database Error on Delete()" );
		call_user_func_array( array( 
				$stmt, 
				'bind_param' 
		), $values );
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

	private function get_result() {
	}
}

class DB_result {

	private $mysqli_result;

	public $rows;

	public $num_rows;

	function __construct( mysqli_result $mysqli_result ) {
		$this->mysqli_result = $mysqli_result;
		
		while ( $row = $mysqli_result->fetch_assoc() ) {
			$this->rows[] = $data;
		}
		$this->num_rows = sizeof( $this->rows );
	}

	/**
	 * Gets an array of values for $key from each row
	 * 
	 * @param unknown $key        
	 */
	function get_value( $key ) {
		foreach ( $rows as $row ) {
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
		return $rows[0][$key];
	}

	function has_rows() {
		return $this->num_rows > 0;
	}
	/**
	 * Binds values from first row into the given variables. 
	 * Variables must equal the name of the column to get.
	 * @param unknown ...$args
	 */
	function bind_vars( &...$args ) {
		foreach ( $args as $k => &$v ) {
			$v = $rows[0][$v];
		}
	}
}




