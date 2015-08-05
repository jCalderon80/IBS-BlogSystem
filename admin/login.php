<?php
	//Login form

	$notfound_msg = '';
	$loggingyouin = '';
    $welcome_msg = 'IBS';
	
	if ( isset( $_POST['logingin'] ) && isset( $_POST['username'] ) && isset( $_POST['secretphrase'] ) ) {

		if ( get_user( $_POST['username'] ) !== false ) {

			$the_one = get_user( $_POST['username'] );

			// User Found proceed to check
			if ( validate_password( $_POST['secretphrase'], $the_one[0]['secretphrase'] ) ) {

				$right_now = time();

				$_SESSION['LOGGED_IN'] = true;
				$_SESSION['S_STARTED'] = $right_now;
				$_SESSION['CLIENT'] =  ( isset( $the_one[0]['name'] ) && ! empty( $the_one[0]['name'] ) ) ? $the_one[0]['name'] : $the_one[0]['username'];
				$_SESSION['CLIENT_ID'] = $the_one[0]['id'];
				$_SESSION['LAST_ACTIVITY'] = $right_now;

				$loggingyouin = '<div class="over-top-msg"><p>Access Accepted!</p><p>I am logging you in!</p></div>';

				unset( $_POST );

				header( 'refresh:2;url=' . get_site_url() . '/admin/' );

			} else {

				unset( $_POST, $the_one );
				$notfound_msg = '<div class="warn-msg"><p>You have failed to provide me with the right information.</p></div>';
			}

		} else {
			// User NOT found
			$notfound_msg = '<div class="warn-msg"><p>I cannot recognize that username.</p></div>';

		}

	}

?>

<!DOCTYPE html>
<html>
<head>
	<title>IBS | iGuana Blog System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
    <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro' rel='stylesheet' type='text/css'>
	<style type="text/css">
        * {
            margin: 0;
            border: 0;
        }

        html,
        body {
            height: 100%;
        }

        body {
            background: #eee;
            font-family: 'SOurce Sans Pro', Verdana, Arial;
        }

        img {
            width: 100%;
            height: auto;
            display: block;
        }
        h3 {
            margin: 15px 0;
        }

        form p {
            margin: 10px 0;
        }

        label {
            display: block;
        }

        input {
            text-align: center;
            font-family: 'Source Sans Pro', Verdana, Arial;
            padding: 6px; 
            box-shadow: 0 0 4px 0 rgba(0,0,0,.2),
				        0 2px 4px 2px rgba(0,0,0,.2);
	        border-color: transparent;
	        z-index: 20;
        }

        input:not([type="submit"]):hover {
            box-shadow: 0 0 4px 0 rgba(0,0,0,.2),
				0 4px 4px 2px rgba(0,0,0,.2);
	        z-index: 30;
        }

        input:not([type="submit"]) {
            width: 70%;
        }

        [type="submit"] {
            background: #1F3A93;
            color: #fff;
            padding: 10px 30px;
            margin: 13px 0;
        }

        [type="submit"]:hover {
            box-shadow: 0 0 4px 0 rgba(0,0,0,.2),
				0 8px 16px 2px rgba(0,0,0,.2);
	        z-index: 80;
        }

        .over-top-msg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba( 255, 255, 255, .95 );
            color: #1F3A93;
            font-size: 3em;
            text-align: center;
            padding-top: 100px;
        }

        .login-box {
            text-align: center;
            background: #fff;
            width: 320px;
            margin: 40px auto 0;
            padding:20px;
            border: solid 18px #1F3A93;
        }

        .ibs-logo {
            overflow: hidden;
            background: #1F3A93;
            width: 120px;
            height: 120px;
            margin: 40px auto;
        }

        @media only screen and ( max-width: 759px ){
            .login-box {
                width: 75%;
            }
        }
	</style>
</head>
<body>
	<?php echo $loggingyouin; ?>
	<div id="main-login">
		<article class="login-body">
			<section class="login-box">
                <h1>WELCOME TO <?php echo $welcome_msg; ?></h1>
                <figure class="ibs-logo">
                    <img src="interface/images/IBS-logo.png" />
                </figure>
				<div>
					<h3>Login to start writting!</h3>
					<?php echo $notfound_msg; ?>
					<?php echo $session_ended; ?>
					<form action="<?php echo get_site_url() . '/admin/'; ?>" method="POST">
						<p>
							<label for="username">Username</label>
							<input id="username" name="username" type="text" maxlength="20" required>
						</p>
						<p>
							<label for="password">Password</label>
							<input id="password" name="secretphrase" type="password" minlength="8" required>
						</p>
						<input type="submit" name="logingin" value="LOGIN">
					</form>
				</div>
			</section>
		</article>
	</div>
</body>
</html>

<?php

	function get_user( $username ) {

		$output;
		$findings = 0;
		$user_found = array();

		$users_file = json_decode( file_get_contents( ADMINPATH . 'locker/users-config.json' ), true );

		$users = $users_file['users'];

		foreach ( $users  as $user => $attrs ) {

			if ( $attrs['username'] == $username ) {
				$findings = $findings + 1;
				array_push( $user_found , $attrs );

			}

		}

		if ( $findings === 1 ) {

			$output = $user_found;
			unset( $user_found );

		} else {

			unset( $user_found );
			$output = false;

		}

		return $output;

	}
?>