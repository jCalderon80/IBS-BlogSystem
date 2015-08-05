<?php
$pubc = count( get_posts_list( 'ACTIVE' ) );
$drac = count( get_posts_list( 'DRAFT' ) );
$trac = count( get_posts_list( 'INACTIVE' ) );
$appc = count( get_comments_list( 'APPROVED' ) );
$penc = count( get_comments_list( 'PENDING' ) );
?>
<aside id="main-navigation">
	<input id="main-controls" name="" type="checkbox">
	<label for="main-controls">
		<p class="top-title">Main Controls</p>
		<section class="nav-wrapper">
            <label class="setting-container">
                <ul>
                    <li><a href="<?php echo ADMINURL . '?action=newpost'; ?>">Create new post</a></li>
                </ul>
            </label>
			<input class="side-switch" id="content-switch" name="nav-section" type="radio">
			<label for="content-switch" class="setting-container">
				<p class="setting-title">Posts Manager</p>
				<ul class="setting-options">
					<li><a href="<?php echo ADMINURL . '?action=allposts'; ?>">Published Posts (<strong><?php echo $pubc; ?></strong>)</a></li>
                    <li><a href="<?php echo ADMINURL . '?action=draftposts'; ?>">Draft Posts (<strong><?php echo $drac; ?></strong>)</a></li>
                    <li><a href="<?php echo ADMINURL . '?action=trashedposts'; ?>">Trash Bin (<strong><?php echo $trac; ?></strong>)</a></li>
				</ul>
			</label>
			<input class="side-switch" id="comments-switch" name="nav-section" type="radio">
			<label for="comments-switch" class="setting-container">
				<p class="setting-title">Comments Manager</p>
				<ul class="setting-options">
					<li><a href="<?php echo ADMINURL . '?action=allcomments'; ?>">All Comments (<strong><?php echo $appc; ?></strong>)</a></li>
					<li><a href="<?php echo ADMINURL . '/?action=pendingcomments'; ?>">Pending Comments (<strong><?php echo $penc; ?></strong>)</a></li>
				</ul>
			</label>
			<input class="side-switch" id="settings-switch" name="nav-section" type="radio">
			<label for="settings-switch" class="setting-container">
				<p class="setting-title">Blog Settings</p>
				<ul class="setting-options">
					<li><a href="<?php echo ADMINURL . '?action=postsettings'; ?>">Posts Settings</a></li>
					<li><a href="<?php echo ADMINURL . '?action=rsssettings'; ?>">RSS Feed Settings</a></li>
					<li><a href="<?php echo ADMINURL . '?action=commentsettings'; ?>">Comments Settings</a></li>
				</ul>
			</label>
            <!-- COMMING SOON-->
            <!--<input class="side-switch" id="backup-restore" name="nav-section" type="radio">
			<label for="backup-restore" class="setting-container">
				<p class="setting-title">Backup and Restore</p>
				<ul class="setting-options">
					<li><a href="<?php echo ADMINURL . '?action=backupibs'; ?>">Backup blog</a></li>
					<li><a href="<?php echo ADMINURL . '?action=restoreibs'; ?>">Restore blog</a></li>
				</ul>
			</label>-->
		</section>
	</label>
</aside>