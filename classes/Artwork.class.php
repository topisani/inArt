<?php
require_once( __DIR__ . '/DB.class.php' );
require_once( __DIR__ . '/User.class.php' );

use \Enums\DB\Posts as Table;
class Artwork {

	private $db;
	private $posts;

	protected $user_id;
	protected $id;
	protected $name;
	protected $text;
	protected $media;
	protected $date;
	protected $options;

	private $columns = [
		'user_id' => 'user_id',
		'id' => 'artwork_id',
		'name' => 'post_name',
		'text' => 'post_text',
		'media' => 'post_media_ids',
		'date' => 'post_date',
		'options' => 'post_options'
	];

	public function __construct( $user_id, $artwork_id, DB $db ) {
		if ( $db->select( 'artworks', 'user_id', [ 
				'user_id = ?' => $user_id,
				'artwork_id = ?' => $artwork_id,
				'post_id = ?' => 0,
			] )->has_rows() ) {
			$this->db = $db;
			$this->user_id = $user_id;
			$this->id = $artwork_id;
			$this->read_db();
		} else {
			throw Exception( 'Artwork does not exist. Run Artwork::new() to create it' );
		}

	}	

	public function read_db() {
		$result = $this->db->select( 'artworks', '*', [ 
				'user_id = ?' => $this->user_id,
				'artwork_id = ?' => $this->id,
				'post_id = ?' => 0,
			] );
		$this->name = $result->get_first( 'post_name' );
		$this->text = $result->get_first( 'post_text' );
		$this->media = explode( ',', $result->get_first( 'post_media_ids' ) );
		$this->type = $result->get_first( 'post_type' );
		$result = $this->db->select( 'artworks', 'post_id', [
				'user_id = ?' => $this->user->user_id,
				'artwork_id = ?' => $this->id,
				'post_id <> ?' => 0
			] );
		$this->posts = [];
		foreach ( $result->rows as $v ) {
			$this->posts[] = $v['post_id'];
		}
	}

	public function __set( $name, $value ) {
		if ( property_exists( $this, $name ) ) {
			$this->{$name} = $value;
			$data = [ $name => $value ];
			$this->db->update( 'artworks', $data, [ 
				'user_id' => $this->user->user_id,
				'artwork_id' => $this->artwork_id,
				'post_id' => 0,
			] );	
		}
	}

	public function __GET( $name ) {
		if ( property_exists( $this, $name ) ) {
			return $this->{$name};
		}
	}

	public static function create( $user, DB $db ) {
		$id = $db->max( Table::TABLE, Table::ARTWORK, [ ' = ?' => $user->user_id ] )  + 1;
		$data = [
			Table::USER => $user->user_id,
			Table::ARTWORK => $id,
			Table::POST => 0
		];
		$db->insert( Table::TABLE, $data );
		return new Artwork( $user, $id, $db ); 
	}
	
	public function get_posts() {
		$return = [];
		foreach ( $this->posts as $post ) {
			$return[] = new Post( $this->user, $this->id, $post, $this->db );
		}
		return $return;
	}

	public function get_link() {
		return '/' . $this->user->username . '/artwork/' . $this->id;
	}
	
}

class Post {
	public function __construct( $user_id, $artwork_id, $post_id, DB $db ) {
		if ( $db->select( Table::TABLE, Table::ARTWORK, [ 
				Table::USER . ' = ?' => $user->user_id,
				Table::ARTWORK . ' = ?' => $artwork_id,
				Table::POST . ' = ?' => $post_id,
			] )->has_rows() ) {
			$this->db = $db;
			$this->user = $user;
			$this->artwork_id = $artwork_id;
			$this->id = $post_id;
			$this->read_db();
		} else {
			throw Exception( 'Post does not exist. Run Post::create() to create it' );
		}
	}

	public function read_db() {
		$result = $this->db->select( Table::TABLE, '*', [ 
				Table::USER    . ' = ?' => $this->user->user_id,
				Table::ARTWORK . ' = ?' => $this->artwork_id,
				Table::POST    . ' = ?' => $this->id,
			] );
		$this->name = $result->get_first( Table::NAME );
		$this->text = $result->get_first( Table::TEXT );
		$this->media = explode( ',', $result->get_first( Table::MEDIA ) );
		$this->date = $result->get_first( Table::DATE );
		$this->options = $result->get_first( Table::OPTIONS );
	}

	public function __set( $name, $value ) {
		if ( property_exists( $this, $name ) ) {
			$this->{$name} = $value;
			$data = [ $name => $value ];
			$this->db->update( Table::TABLE, $data, [ 
				Table::USER => $this->user->user_id,
				Table::ARTWORK => $this->artwork_id,
				Table::POST => $this->id,
			] );	
		}
	}

	public function __GET( $name ) {
		if ( property_exists( $this, $name ) ) {
			return $this->{$name};
		}
	}

	public static function create( $user, $artwork_id, DB $db ) {
		$id = $db->max( Table::TABLE, Table::POST, [ 
			Table::USER . ' = ?' => $user->user_id,
			Table::ARTWORK . ' = ?' => $artwork_id,
		] )  + 1;
		$data = [
			Table::USER => $user->user_id,
			Table::ARTWORK => $artwork_id,
			Table::POST => $id
		];
		$db->insert( Table::TABLE, $data );
		return new Post( $user, $artwork_id, $id, $db ); 
	}
	

	
	public function get_link() {
		return '/' . $this->user->username . '/artwork/' . $this->artwork_id . '/post/' . $this->id;
	}
}

