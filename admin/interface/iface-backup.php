<?php
renew_session();
?>
<article id="backup-ibs" class="container ibs-body">
	<header>
		<h2>Backup your blog content.</h2>
		<p>Set automatic backups or backup your blog manually, choose what to backup.</p>
	</header>
	<section>
		<form action="<?php ?>" method="POST">
			<h3>When to backup</h3>
			<div id="backup-time">
				<p>
					<label>Last time backed up:</label>
					<input type="date" value="No backup yet.">
				</p>
				<p>
					<input name="backup-frame" type="radio">
					<label>Backup every week.</label>
				</p>
				<p>
					<input name="backup-frame" type="radio">
					<label>Backup every month.</label>
				</p>
					<input name="backup-frame" type="radio">
					<label>Backup Manually.</label>
					<button type="submit" name="manual-backup" disabled>Backup Now</button>
				</p>
			</div>
			<h3>What to backup</h3>
			<div id="backup-items">
				<p>
					<input name="backup-item" type="checkbox">
					<label>Blog posts</label>
				</p>
				<p>
					<input name="backup-item" type="checkbox">
					<label>Comments</label>
				</p>
				<p>
					<input name="backup-item" type="checkbox">
					<label>Users</label>
				</p>
				<p>
					<input name="backup-item" type="checkbox">
					<label>All Settings</label>
				</p>
				<p>
					<input name="backup-item" type="checkbox">
					<label>RSS Feed</label>
				</p>
			</div>
			<button type="submit" name="backup-settings">Set Backup Settings</button>
		</form>
	</section>
</article>