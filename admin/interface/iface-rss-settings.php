<?php
renew_session();

$update_msg = '';

if ( isset( $_POST ) && ! empty( $_POST ) ) {
    if ( update_rss_channel( $_POST ) ) {
        $update_msg = '<div class="green-msg"><p>New settings have been succesfully accepted.</p></div>';
    } else {
        $update_msg = '<div class="warning-msg"><p>Something went wrong, changes were not accepted.</p></div>';
    }
    
}

$actual_rss = get_rss_channel();

?>
<article id="rss-settings" class="container ibs-body">
	<header>
		<h2>RSS Feed Settings</h2>
		<p>
			<label>Your RSS Feed URL: </label>
			<input type="url" onclick="this.select()" value="<?php echo $actual_rss->link . '/rss-blogfeed.xml'; ?>" readonly>
		</p>
        <br />
        <?php echo $update_msg; ?>
		<p>Changes made here only affect your blog RSS feed file, therefor will be visible on the RSS Feed.</p>
		<p>Every post you publish and every revision you make to any of your posts will be reflected in your RSS Feed.</p>
        <div class="button-wrapper">
			<a class="button mat-button" onclick="enable_button(this)" ontouch="enable_button(this)" data-switch="OFF" data-button="go">Edit</a>
        </div>
	</header>
	<section>
		<form action="<?php echo ADMINURL . '?action=rsssettings'; ?>" method="POST">
            <p>
                <label>Site URL</label>
                <input name="link" type="url" value="<?php echo $actual_rss->link; ?>" disabled/>
            </p>
			<p>
				<label>Site Name</label>
				<input name="title" class="enable-target" data-default="readonly" type="text" maxlength="" value="<?php echo $actual_rss->title; ?>" readonly>
			</p>
			<p>
				<label>Site Description</label>
                <textarea name="description" class="enable-target" data-default="readonly" maxlength="255" readonly><?php echo $actual_rss->description; ?></textarea>
			</p>
			<p>
				<label>Site Language</label>
				<input name="lang" class="enable-target" data-default="readonly" type="text" maxlength="2" value="<?php echo $actual_rss->lang;    ?>" readonly>
			</p>
			<p class="button-wrapper">
				<input class="enable-target mat-button button" type="submit" data-default="disabled" value="Apply Settings" disabled>
			</p>
		</form>
	</section>
	<footer>
		
	</footer>
</article>