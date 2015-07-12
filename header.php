<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 */

include_once 'includes/functions.php';
//TODO is this necessary?
sec_session_start();
?>
<!DOCTYPE html>
<html>
<head>
	
	<title><?php echo get_page_title();?></title>
	
	<?php ia_styles() ?>
		
	<?php ia_scripts() ?>
	
</head>

<body>


