<?php
$error = filter_var( $params['err'], FILTER_SANITIZE_STRING );

if (! $error) {
	$error = 'Oops! An unknown error happened.';
}
ia_header ($error);
?>

<h1>There was a problem</h1>
<p class="error"><?php echo $error; ?></p>

<?php ia_footer()?>
