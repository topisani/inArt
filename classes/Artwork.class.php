<?
require_once( __DIR__ . '/DB.class.php' );
require_once( __DIR__ . '/User.class.php' );

class Artwork {

	private $db;
	private $posts;

	protected $user;
	protected $id;
	protected $name;
	protected $text;
	protected $media;
	protected $type;

	public function __construct( User $user, $artwork_id, DB $db = new DB() ) {
		if ( $db->select( 'artworks', 'user_id', [ 
				'user_id' => $user->user_id,
				'artwork_id' => $artwork_id,
				'post_id' => 0,
			] )->has_rows() ) {
			$this->db = $db;
			$this->user = $user;
			$this->id = $artwork_id;
			$this->read_db();
		} else {
			throw Exception( 'Artwork does not exist. Run Artwork::new() to create it' );
		}

	}	

	public function read_db() {
		$result = $this->db->select( 'artworks', '*', [ 
				'user_id' => $this->user->user_id,
				'artwork_id' => $this->artwork_id,
				'post_id' => 0,
			] );
		$this->name = $result->get_first( 'post_name' );
		$this->text = $result->get_first( 'post_text' );
		$this->media = explode( ',', $result->get_first( 'post_media' ) );
		$this->type = $result->get_first( 'post_type' );
		$result = $this->db->select( 'artworks', 'post_id', [
				'user_id' => $this->user->user_id,
				'artwork_id' => $this->artwork_id,
			] );
		$posts = array_values( $result->rows );
	}

	public function __set( $name, $value ) {
		if ( property_exists( $this, $name ) ) {
			$this->{$name} = $value;
			$data = [ $name => $value ]
			$this->db->update( 'artworks', $data, [ 
				'user_id' => $this->user->user_id,
				'artwork_id' => $this->artwork_id,
				'post_id' => 0,
			] );	
		}
	}

	public static function new( $user, DB $db = new DB() ) {
		$id = $db->max( 'artworks', 'artwork_id', [ 'user_id = ?' => $user->user_id ] )  + 1;
		$data = [
			'user_id' => $user->user_id,
			'artwork_id' => $id,
			'post_id' => 0
		];
		$db->insert( 'artworks', $data );
		return new Artwork( $user, $id, $db ); 
	}
	
	public function get_posts() {
		$return = [];
		foreach ( $posts as $post ) {
			$return[] = new Post( $this->user, $this->id, $post, $this->db );
		}
		return $return;
	}
	
}

class Post {
	private $db;

	protected $user;
	protected $artwork_id;
	protected $id;
	protected $name;
	protected $text;
	protected $media;
	protected $role;
	protected $type;

	public function __construct( User $user, $artwork_id, $post_id, DB $db = new DB() ) {
		if ( $db->select( 'artworks', 'user_id', [ 
				'user_id' => $user->user_id,
				'artwork_id' => $artwork_id,
				'post_id' => $post_id,
			] )->has_rows() ) {
			$this->db = $db;
			$this->user = $user;
			$this->artwork_id = $artwork_id;
		} else {
			throw Exception( 'Post does not exist. Run \Artwork\Post::new() to create it' );
		}
	}

	public function read_db() {
		$result = $this->db->select( 'artworks', '*', [ 
				'user_id' => $this->user->user_id,
				'artwork_id' => $this->artwork_id,
				'post_id' => $this->post_id,
			] );
		$this->name = $result->get_first( 'post_name' );
		$this->text = $result->get_first( 'post_text' );
		$this->media = explode( ',', $result->get_first( 'post_media' ) );
		$this->role = $result->get_first( 'post_role' );
		$this->type = $result->get_first( 'post_type' );
	}

	public function __set( $name, $value ) {
		if ( property_exists( $this, $name ) ) {
			$this->{$name} = $value;
			$data = [ $name => $value ]
			$this->db->update( 'artworks', $data, [ 
				'user_id' => $this->user->user_id,
				'artwork_id' => $this->artwork_id,
				'post_id' => $this->id,
			] );	
		}
	}

	public static function new( $user, $artwork_id, DB $db = new DB() ) {
		$this->db = $db;
		$this->user = $user;
		$this->artwork_id = $artwork_id;
		$this->id = $db->max( 'artworks', 'post_id' ) + 1;
	}
}

