<?php
	
	if ( isset( $_SESSION['LOGGED_IN'] ) && $_SESSION['LOGGED_IN'] === true ) {
		session_unset();
		session_destroy();
		session_regenerate_id(true);
	}

?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<header>
		<a href="<?php echo get_site_url() . '/admin/'; ?>">Back To Login</a>
	</header>
	<div>
		<article>
			THANKS
		</article>
<?php
	include_once dirname( __FILE__ ) . '/interface/iface-footer.php';
?>