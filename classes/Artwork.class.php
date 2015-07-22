<?
require_once( __DIR__ . '/DB.class.php' );
require_once( __DIR__ . '/User.class.php' );

namespace Artwork {

class Post {
	
	private $db;

	protected $user;
	protected $artwork_id;
	protected $post_id;
	protected $post_name;
	protected $post_text;
	protected $post_media;
	protected $post_role;
	protected $post_type;

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
		$this->post_name = $result->get_first( 'post_name' );
		$this->post_text = $result->get_first( 'post_text' );
		$this->post_media = explode( ',', $result->get_first( 'post_media' ) );
		$this->post_role = $result->get_first( 'post_role' );
		$this->post_type = $result->get_first( 'post_type' );
	}

	public function __set( $name, $value ) {
		if ( property_exists( $this, $name ) ) {
			$this->{$name} = $value;
			$data = [ $name => $value ]
			$this->db->update( 'artworks', $data, [ 
				'user_id' => $this->user->user_id,
				'artwork_id' => $this->artwork_id,
				'post_id' => $this->post_id,
			] );	
		}
	}

	public static function new( $user, $artwork_id, DB $db = new DB() ) {
		$this->db = $db;
		$this->user = $user;
		$this->artwork_id = $artwork_id;
		$this->post_id = $db->max( 'artworks', 'post_id' ) + 1;
	}
}
}
