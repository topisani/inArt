<?php
$error = filter_input ( INPUT_GET, 'err', $filter = FILTER_SANITIZE_STRING );

if (! $error) {
	$error = 'Oops! An unknown error happened.';
}
set_page_title ( $error );
ia_header ();
?>

<h1>There was a problem</h1>
<p class="error"><?php echo $error; ?></p>

<?php ia_footer()?>