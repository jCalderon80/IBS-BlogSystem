<?php
renew_session();

$update_msg = '';

if ( isset( $_POST['id'] ) && ! empty( $_POST['id'] ) ) {

	$the_user = get_user_by_id( $_POST['id'] );
	if ( update_user( $_POST ) ) {
		$update_msg = '<div class="green-msg"><p>Your profile was updated succesfully.</p></div>';
	} else {
		$update_msg = '<div class="warning-msg"><p>Something went wrong, your profile was not updated.</p></div>';
	}

}

$this_user = get_user_by_id( $_SESSION['CLIENT_ID'] );

?>
<article id="user-profile" class="container ibs-body">
	<header>
		<h2>User Profile</h2>
		<?php echo $update_msg; ?>
		<p><i>Add or change the info in your profile, it could be displayed in the author footer section of your posts, depending on your posts settings.</i></p>
		<div class="button-wrapper">
			<a class="button mat-button" onclick="enable_button(this)" ontouch="enable_button(this)" data-switch="OFF" data-button="go">Edit</a>
		</div>
	</header>
	<section>
		<form id="user-form" method="POST" action="<?php echo ADMINURL . '?action=updateprofile' ?>">
			<input name="id" type="hidden" value="<?php echo $_SESSION['CLIENT_ID']; ?>">
			<p>
				<label>Username<br><i class="warning-msg">You cannot edit your username</i></label>
				<input type="text" data-default="disabled" value="<?php echo $this_user['username']; ?>" disabled>
			</p>
			<p>
				<label>Name</label>
				<input class="enable-target" name="name" type="text" pattern="[a-zA-Z ]+" maxlength="50" data-default="readonly" value="<?php echo ( isset( $this_user['name'] ) ) ? $this_user['name'] : ''; ?>" readonly>
			</p>
			<p>
				<label>Email</label>
				<input class="enable-target" name="email" type="email" maxlength="100" data-default="readonly" value="<?php echo ( isset( $this_user['email'] ) ) ? $this_user['email'] : ''; ?>" readonly>
			</p>
			<p>
				<label>Website</label>
				<input class="enable-target" name="url" type="url" maxlength="100" data-default="readonly" value="<?php echo ( isset( $this_user['url'] ) ) ? $this_user['url'] : ''; ?>" readonly>
			</p>
			<p>
				<label>Description</label>
				<textarea class="enable-target" name="description" maxlength="255" data-default="readonly" readonly><?php echo ( isset( $this_user['description'] ) ) ? $this_user['description'] : ''; ?></textarea>
			</p>
			<p class="button-wrapper">
				<input class="enable-target mat-button button" type="submit" data-default="disabled" value="Update Profile" disabled>
			</p>
		</form>
        <!-- COMMING SOON -->
		<!--<div class="button-wrapper right-button">
			<a class="mat-button button" href="<?php echo ADMINURL . '?action=changepassword'; ?>">Change Password</a>
		</div>-->
	</section>
	<!--<footer>
		<p><strong>NOTE:</strong> At this moment my blog system only accepts one administrator, I am working to allow more users to be added to your account soon.</p>
		<p>Once the feature is ready you will be prompted to update your system.</p>
	</footer>-->
</article>