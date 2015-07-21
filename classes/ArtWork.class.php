<?
require_once( __DIR__ . '/DB.class.php' );
require_once( __DIR__ . '/User.class.php' );

namespace ArtWork {

class Result {
	
	private $db;

	public $user;
	public $art_id;
	public $upoads = [];
	public $desc = [];
	public $content = '';

	public __construct( User $user, $art_id, DB $db = new DB() ) {
		$this->art_id = $art_id;
	}
	// Needed columns in table:
	// int           user_id
	// int           artwork_id
	// POSTS: 
	// array int     post_media
	// array string  post_media_descs
	// string        post_text
	// boolean       is_result
}
}
