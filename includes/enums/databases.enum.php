<?php
namespace Enums\DB;

use \MyCLabs\Enum\Enum as Enum;

class Posts extends Enum {
	const TABLE = 'posts';

	const USER = 'user_id';
	const ARTWORK = 'artwork_id';
	const POST = 'post_id';
	const NAME = 'post_name';
	const TEXT = 'post_text';
	const MEDIA = 'post_media_ids';
	const DATE = 'post_date';
	const OPTIONS = 'post_options';
}

