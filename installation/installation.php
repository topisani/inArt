<?php
require_once '../includes/functions.php';
$query = "
	CREATE TABLE `".DATABASE."`.`members` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
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
        `name` VARCHAR(50) NOT NULL,
		`original_name` VARCHAR(30) NOT NULL,
		`mime_type` VARCHAR(30) NOT NULL,
		`upload_id` INT(11) NOT NULL AUTO_INCREMENT,
		PRIMARY KEY (`user_id`, `upload_id`)
    ) ENGINE=MyISAM;
			
	CREATE TABLE `".DATABASE."`.`user_settings` (
        `user_id` INT(11) NOT NULL,
        `key` VARCHAR(50) NOT NULL,
		`value` VARCHAR(400) NOT NULL,
		PRIMARY KEY (`user_id`, `key`)
    ) ENGINE=InnoDB;
	";

$stmt = $mysqli->prepare($query);
$stmt->execute();

?>