<?php
//Direct the user to the right spot depending on session

if ( ! isset( $_GET['id'] ) ) {
	session_start();
}

//Include regular functions and classes
require_once DOMPATH . 'functions.php';

$session_ended = '';

// Check if session has been inactive for more than 15 minutes and destroy it
if ( isset( $_SESSION['LAST_ACTIVITY'] ) && ( time() - $_SESSION['LAST_ACTIVITY'] ) > 1800 ) {
	session_unset();
	session_destroy();
	$session_ended = '<div class="warning-msg"><p>Your session has ended</p></div>';
}

//echo $_SESSION['ID'];
//echo '<br>';
//echo session_id();


//Check for configuration file
if ( check_config() ) {
	// If session found load dependencies
	if ( isset( $_SESSION['LOGGED_IN'] ) && $_SESSION['LOGGED_IN'] === true ) {
//	if ( check_session() === true ) {

		include_once IFACEPATH . 'interface.php';

	} else {

		// Send back to login area
		require_once GEARPATH . 'PasswordHash.php';
		include_once ADMINPATH . 'login.php';

	}
} else {

	//Include config file setup
	require_once GEARPATH . 'PasswordHash.php';
	include_once ADMINPATH . 'setup.php';

}

/**
 * Check if configuration file exist and if is not empty
 * @return boolean True on success, false on fail.
 */
function check_config() {
	$output;
	$cfile = DOMPATH . 'site-config.json';

	if ( file_exists( $cfile ) ) {
		if ( json_decode( file_get_contents( $cfile ), true ) ) {
			$output = true;
		} else {
			$output = false;	
		}
	} else {
		$output = false;
	}
	return $output;
}

function check_session() {
	$output = false;
	if ( isset( $_SESSION['LOGGED_IN'] ) && $_SESSION['LOGGED_IN'] === true ) {
		$check_id = md5( uniqid( $_SESSION['CLIENT'] . $_SESSION['S_STARTED'], true ) );
		if ( $_SESSION['ID']  === $check_id ) {
			$output = true;
		}

	}
	return $output;
}

?>