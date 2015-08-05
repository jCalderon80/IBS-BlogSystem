<?php

//Define Admin URL
define( 'ADMINURL', get_site_url() . '/admin/' );
define( 'UPLOADURL', get_site_url() . '/images/upload/' );

//Get session data
$user_in_session = $_SESSION['CLIENT'];
$action = ( isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ) ? $_GET['action'] : '';

$actionfile;

include_once IFACEPATH . 'iface-functions.php';

if ( $action == 'logout' ) {
	include_once ADMINPATH . 'logout.php';
} else {
	include_once IFACEPATH . 'iface-dash.php';
    include_once IFACEPATH . 'iface-sidenav.php';

	switch ( $action ) {
		case 'userprofile':
		case 'updateprofile':
			//
			$actionfile = 'iface-uprofile.php';
			break;
		case 'postsettings':
			//
			$actionfile = 'iface-posts-settings.php';
			break;
		case 'commentsettings':
			//
			$actionfile = 'iface-comments-settings.php';
			break;
		case 'rsssettings':
			//
			$actionfile = 'iface-rss-settings.php';
			break;
		case 'allcomments':
		case 'pendingcomments':
        case 'deletecomment':
        case 'approvecomment':
        case 'adminreplied':
			//
			$actionfile = 'iface-comments.php';
			break;
		case 'editpost':
		case 'newpost':
			//
			$actionfile = 'iface-editor.php';
			break;
		case 'changepassword':
			$actionfile = 'iface-change-password.php';
			break;
		case 'backupibs':
			$actionfile = 'iface-backup.php';
			break;
		case 'restoreibs':
			$actionfile = 'iface-restore.php';
			break;
        case 'allposts':
		case 'deletefromlist':
		default:
			//
			$actionfile = 'iface-posts.php';
			$action = 'allposts';
			break;
	}

	include_once IFACEPATH . $actionfile;
}

include_once IFACEPATH . 'iface-footer.php';

function renew_session() {
	if ( isset( $_SESSION['LAST_ACTIVITY'] ) ) {
		$_SESSION['LAST_ACTIVITY'] = time();
	}
}

?>