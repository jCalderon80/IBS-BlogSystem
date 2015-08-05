<?php

// Setup Configuration file

//Get Site Url, the url reflects the location ot the admin folder.
$setup_site_url = preg_replace ( '/([\/]admin[\/])$/', '', get_requested_url() );

//Get Language from browser
$setup_site_lang =  substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

//Variables
$site_data = array( 'name' => '', 'description' => '' );
$user_data = array( 'username' => '', 'secretphrase' => '' );
$success_msg = '';


if ( isset( $_POST['confignow'] ) ) {
	unset( $_POST['confignow'] );

	//Separate the data
	
	//User Data
	$user_data['id'] = '1';
	$user_data['username'] = $_POST['username'];
	$user_data['secretphrase'] = create_hash ( $_POST['secretphrase'] );

	//Unset user data from $_POST;
	foreach ( $user_data as $key => $value ) {
		unset( $_POST[$key] );
	}


	//Create all the needed files and server configurations
	if ( config_init( $_POST ) && rss_init( $_POST ) && admin_init( $user_data ) ) {

		unset( $user_data['secretphrase'] );
		foreach ( $_POST as $key => $value ) {
			$site_data[$key] = $value;
		}

		$success_msg = '<div class="over-top-msg"><p>Please wait, Your blog is being configured.</p></div>';

		header( 'refresh:3;url=' . dirname( $_SERVER['PHP_SELF'] ) );

	} 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>iGuana Blog | Initial Setup</title>
</head>
<body>
	<?php echo $success_msg; ?>
	<header>
		<h1>Welcome to iGuana Blog System</h1>
	</header>
	<article>
		<section>
			<h2>INITIAL SETUP</h2>
			<h3>Welcome to IBS</h3>
			<p>I need to gather some info to make the Blog work.</p>
			<p>Some information is automatically populated according to your server, you can modify these values if needed, but is recommended to leave them as is.</p>
			<p>Some info will be used to automatically create and update your RSS feed.</p>
            <p>NOTE: Some values are not allow to be edited after this configuration for safety reasons.</p>
			<div class="setup-form">
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
					<div class="form-section">
						<h3>Administrator Setup</h3>
						<p>
							<label>Admin Username <i>See requirements<strong class="csstool-tip">Alpha Numeric<br>And special Characters: - _</strong></i></label>
							<input id="username" name="username" type="text" value="<?php echo $user_data['username']; ?>" pattern="[a-zA-Z 0-9 (-_)]{5,}" maxlength="30" required placeholder="required">
						</p>
						<p>
							<label>Admin Password <i>See requirements<strong class="csstool-tip">Minimum 8 characters<br>At least one number<br>and one special character: !@#$%-_</strong></i></label>
							<input id="secretprhase" name="secretphrase" type="password" pattern="[a-zA-Z \d{1,} (!@#$%-_){1,} ]{8,}" required>
						</p>
					</div>
					<div class="form-section">
						<h3>Site info</h3>
						<p>
							<label for="url">Site Domain/URL</label>
							<input id="url" name="url" type="url" value="<?php echo $setup_site_url; ?>" placeholder="Required" value="<?php ?>">
						</p>
						<p>
							<label for="name">Site Name</label>
							<input id="name" name="name" type="text" value="<?php echo $site_data['name']; ?>" pattern="[a-zA-Z 0-9]+" maxlength="55" required placeholder="Required">
						</p>
						<p>
							<label for="description">Site Description <i>55 Chars. Max</i></label>
							<input id="description" name="description" type="text" value="<?php echo $site_data['description']; ?>" pattern="[a-zA-Z 0-9\-_\.\x22\x27]+" maxlength="55" required placeholder="Required">
						</p>
						<p>
							<label for="lang">Site Language <i>2 character code</i></label>
							<input id="lang" name="lang" type="text" value="<?php echo $setup_site_lang; ?>" maxlength="5" pattern="[a-z]{2}" required placeholder="Required">
						</p>
						<p>
							<label for="charset">Character Encoding <i>utf-8 recommended for most cases</i></label>
							<select id="charset" name="charset">
								<option value="utf-8" selected>Universal Alphabet (UTF-8)</option>
								<!--<option value="iso-8859-1">Western Alphabet (ISO)</option>
								<option value="iso-8859-2">Central European Alphabet (ISO)</option>-->
								<option value="iso-8859-3">Latin 3 Alphabet (ISO)</option>
								<!--<option value="iso-8859-4">Baltic Alphabet (ISO)</option>
								<option value="iso-8859-5">Cyrillic Alphabet (ISO)</option>
								<option value="iso-8859-6">Arabic Alphabet (ISO)</option>
								<option value="iso-8859-7">Greek Alphabet (ISO)</option>
								<option value="iso-8859-8">Hebrew Alphabet (ISO)</option>-->
							</select>
						</p>
					</div>
					<input class="button" name="confignow" type="submit" value="Configure my blog now!">
				</form>
			</div>
			<div class="warning-box">
				<p><strong>IMPORTANT</strong></p>
				<p>If you are installing "The Blog System" in a local machine, once ready to production do not upload the configuration file (<i>site-config.json</i>) as it contains data from your local machine.</p>
				<p>You will see this page again once you visit your blog during production, but all content will be intact</p>
			</div>
		</section>
	</article>
	<?php include_once IFACEPATH . 'iface-footer.php'; ?>

<?php

	function admin_init ( $data ) {
		$output;
		$filename = 'users-config.json';

		$users_config = array( 'users' => array( $data ) );

		$user_handler = fopen( ADMINPATH . 'locker/' . $filename, 'w' );

		if ( fwrite( $user_handler, json_encode( $users_config ) ) ) {
			$output = true;
		} else {
			$output = false;
		}

		fclose( $user_handler );

		return $output;
	}

	function config_init ( $data ) {
		$output;
		$filename = 'site-config.json';

		$site_config = array( 'site_configuration' => $data );

		$file_handler = fopen( DOMPATH . $filename, 'w' );

		if ( fwrite( $file_handler, json_encode( $site_config ) ) ) {
			$output = true;
		} else {
			$output = false;
		}

		fclose( $file_handler );

		return $output;

	}

	function rss_init ( $data ) {
		$output;
		unset( $data['charset'] );
		$filename = 'rss-blogfeed.xml';

		//Create XML handler
		$rss_file = new DOMDocument( '1.0', 'utf-8' );
		$rss_file->formatOutput = true;

		//Create the rss tag and set the version 2.0
		$rss_tag = $rss_file->createElement( 'rss' );
		$rss_attr = $rss_file->createAttribute( 'version' );
		$rss_attr->value = '2.0';

		//Append attribute to rss tag
		$rss_tag->appendChild( $rss_attr );

		//Create channel
		$channel_element = $rss_file->createElement( 'channel' );

		//Create channel children and append to it.
		foreach ( $data as $chan_tag => $chan_txt ) {

            switch ( $chan_tag ) {
                case 'url':
                    $chan_tag = 'link';
                    break;
                case 'name':
                    $chan_tag = 'title';
                    break;
            }
			
			$t_chan = $rss_file->createElement( $chan_tag );
			$t_chan = $channel_element->appendChild( $t_chan );

			$t_node = $rss_file->createTextNode( $chan_txt );
			$t_node = $t_chan->appendChild( $t_node );
		}

		//Append channel to rss tag
		$rss_tag->appendChild( $channel_element );

		//Append rss tag to file
		$rss_file->appendChild( $rss_tag );

		if ( $rss_file->save( DOMPATH . $filename ) ) {
			$output = true;
		} else {
			$output = false;
		}

		return $output;
	}
?>