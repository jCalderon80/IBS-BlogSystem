<?php

renew_session();

?>
<article id="comments-settings" class="container ibs-body">
	<header>
		<h2>Comments Settings</h2>
	</header>
	<section id="comments-settings">
		<form action="<?php echo ADMINURL . '?action=updatecommentsettings'; ?>" method="POST">
            <h3>Approve comments</h3>
			<p>
				<input type="hidden" name="approvecomments" value="0">
				<input id="approvecomments" type="checkbox" name="approvecomments" value="1">
				<label for="approvecomments"><i class="pretty-check"></i>Approve comments before being published.</label>
			</p>
            <!-- COMMING SOON -->
            <!--<h3>Control comments</h3>
			<p>
				<input id="censorbad" type="radio" name="censorcomments">
				<label for="censorbad"><i class="pretty-radio"></i>Censor comments with bad language.</label>
			</p>
			<p>
				<input id="denybad" type="radio" name="censorcomments">
				<label for="denybad"><i class="pretty-radio"></i>Never publish comments with bad language.</label>
			</p>
            <p>
                <label>
                    Disable commenting in posts 
                    <select>
                        <option value="">after 30 days</option>
                        <option value="">after 90 days</option>
                        <option value="">Never</option>
                    </select>
                </label>
            </p>
			<p>
				<label>A Message to your commentators.<i>This message will show in every comment section in every post.</i></label>
				<textarea></textarea>
			</p>-->
			<div class="button-wrapper">
				<input class="mat-button button" type="submit" value="Apply Settings">
			</div>
		</form>
	</section>
	<footer>
		
	</footer>
</article>