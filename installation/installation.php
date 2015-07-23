<?php
require_once( __DIR__ . '../includes/functions.php' );
$query = "
	CREATE TABLE `".DATABASE."`.`users` (
        `user_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(30) NOT NULL,
        `email` VARCHAR(50) NOT NULL,
        `password` CHAR(128) NOT NULL,
        `salt` CHAR(128) NOT NULL 
    ) ENGINE = InnoDB;
	 
	CREATE TABLE `".DATABASE."`.`login_attempts` (
        `user_id` INT(11) NOT NULL,
        `time` VARCHAR(30) NOT NULL
    ) ENGINE=InnoDB
		
	CREATE TABLE `".DATABASE."`.`uploads` (
        `user_id` INT(11) NOT NULL,
		`upload_id` INT(11) NOT NULL AUTO_INCREMENT,
		`original_name` VARCHAR(30) NOT NULL,
		`mime_type` VARCHAR(30) NOT NULL,
		`name` VARCHAR(50) NOT NULL,
		PRIMARY KEY (`user_id`, `upload_id`)
    ) ENGINE=MyISAM;
			
	CREATE TABLE `".DATABASE."`.`user_settings` (
        `user_id` INT(11) NOT NULL,
        `setting` VARCHAR(50) NOT NULL,
		`value` TEXT NOT NULL,
		PRIMARY KEY (`user_id`, `key`)
    ) ENGINE=InnoDB;
	
	CREATE TABLE `" . DATABASE . "`.`artworks` (
		`user_id` INT(11) NOT NULL,
		`artwork_id` SMALLINT NOT NULL,
		`post_id` SMALLINT NOT NULL, 
		`post_name` TINYTEXT,
		`post_text` TEXT,
		`post_media_ids` VARCHAR(50),
		`post_role` TINYINT,
		`post_type` TINYINT,
	   	unique KEY (`user_id`, `artwork_id`, `post_id`),
    	CONSTRAINT fk_uid_users 
    	FOREIGN KEY (user_id) 
    	REFERENCES users(user_id)
    	ON DELETE CASCADE	
	) ENGINE=InnoDB;

";

$stmt = $db->mysqli->prepare($query);
$stmt->execute();

?>
