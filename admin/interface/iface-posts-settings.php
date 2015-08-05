<?php

renew_session();

$update_msg = '';

if ( isset( $_POST) && ! empty( $_POST ) ) {

	if ( update_posts_settings( $_POST ) ) {
		$update_msg = '<div class="green-msg"><p>New settings have been successfully accepted.</p></div>';
	} else {
        $update_msg = '<div class="warning-msg"><p>Something went wrong, changes were not accepted.</p></div>';
    }
}

if ( file_exists( LOCKPATH . 'posts-config.json' ) ) {
    //Get settings file
    $set_file = get_json_content( LOCKPATH . 'posts-config.json' );
    $sets_now = $set_file['postssettings'];

    //Get individual settings value
    //ALLOW SECTION
    $allowcomments = ( $sets_now['allowcomments'] === '1' ) ? 'checked' : '';
    $allowwritercard = ( $sets_now['allowwritercard'] === '1' ) ? 'checked' : '';
    $allowsidebarposts = ( isset( $sets_now['allowsidebarposts'] ) ) ? $sets_now['allowsidebarposts'] : '';
    $allowpostsperpage = ( isset( $sets_now['allowpostsperpage'] ) ) ? $sets_now['allowpostsperpage'] : '';
    $allowcatfeed = ( $sets_now['allowcatfeed'] === '1' ) ? 'checked' : '';

    //SOCIAL SECTION
    $sbar = $sets_now['socialbar'];
    $socialfbID = ( ! empty( $sbar['socialfbID'] ) ) ? $sbar['socialfbID'] : '';
    $socialbarshow = ( $sbar['socialbarshow'] === '1' ) ? 'checked' : '';
    $socialfb = ( $sbar['socialfacebook'] === '1' ) ? 'checked' : '';
    $socialgplus = ( $sbar['socialgplus'] === '1' ) ? 'checked' : '';
    $socialpin = ( $sbar['socialpinterest'] === '1' ) ? 'checked' : '';
    $socialtwit = ( $sbar['socialtwitter'] === '1' ) ? 'checked' : '';
    $sociallink = ( $sbar['sociallinkedin'] === '1' ) ? 'checked' : '';
    $socialbartype = ( isset( $sbar['socialbartype'] ) ) ? $sbar['socialbartype'] : '';
    $socialbarpos = ( isset( $sbar['socialbarposition'] ) ) ? $sbar['socialbarposition'] : '';

    //SETTING SECTION
    $setpermalink = ( ! empty( $sets_now['setpermalink'] ) ) ? $sets_now['setpermalink'] : '';
    $setautosave = ( ! empty( $sets_now['setautosave'] ) ) ? $sets_now['setautosave'] : '';
    $settrashposts = ( ! empty( $sets_now['settrashposts'] ) ) ? $sets_now['settrashposts'] : '';
} else {
    //Show error
    $update_msg = '<div class="warning-msg"><p>There is an internal error, configuration could not be retrieved.</p></div>';
}

?>
<article id="posts-settings" class="container ibs-body">
	<header>
		<h2>Posts Settings</h2>
		<?php echo $update_msg; ?>
		<p class="setting-description">Choose different settings for your blog home page, single post page and blog administrator system.</p>
	</header>
	<section id="setting-container">
		<form action="<?php echo ADMINURL . '?action=postsettings' ?>" method="POST">
            <div class="posts-tabs">
                <input id="display-settings" class="settings-tab" type="radio" name="settings-tab" checked/>
                <label class="tab-text" for="display-settings">Display Options</label>
                <div class="settings-content">
                    <h3>Single post pages</h3>
                    <p class="setting-description">Select what items to show in a single post page</p>
                    <p>
				        <input type="hidden" name="allowcomments" value="0">
				        <input id="allowcomments" type="checkbox" name="allowcomments" value="1" <?php echo $allowcomments; ?>>
				        <label for="allowcomments"><i class="pretty-check"></i>Allow commenting in all posts<br><i class="warning-msg">Posts with comments pending or posted will not be affected by this option in any way.</i></label>
			        </p>
			        <p>
				        <input type="hidden" name="allowwritercard" value="0">
				        <input id="allowwritercard" type="checkbox" name="allowwritercard" value="1" <?php echo $allowwritercard; ?>>
				        <label for="allowwritercard"><i class="pretty-check"></i>Show the writer information card bellow every post.</label>
			        </p>
                    <!-- COMMING SOON -->
                    <!--<p>
                        <label for="sidebar-posts">
                            Display a list of 5
                            <select id="sidebar-posts" name="allowsidebarposts">
                                <option value="recent" <?php echo ( $allowsidebarposts === 'recent' ) ? 'selected' : ''; ?>>Recent posts</option>
                                <option value="related"<?php echo ( $allowsidebarposts === 'related' ) ? 'selected' : ''; ?>>Related posts</option>
                            </select>
                             in the sidebar.
                        </label>
                    </p>-->
                    <h3>Blog home/feed page</h3>
                    <p>
                        <label for="posts-per-page">
                            Show the 
                            <select id="posts-per-page" name="allowpostsperpage">
                                <option value="5" <?php echo ( $allowpostsperpage === '5' ) ? 'selected' : ''; ?>>5</option>
                                <option value="10" <?php echo ( $allowpostsperpage === '10' ) ? 'selected' : ''; ?>>10</option>
                                <option value="15" <?php echo ( $allowpostsperpage === '15' ) ? 'selected' : ''; ?>>15</option>
                            </select>
                             recent posts in your blog home page.
                        </label>
                    </p>
                    <!-- COMMING SOON -->
                    <!--<p>
                        <input type="hidden" name="allowcatfeed" value="0">
				        <input id="displaycatfeed" type="checkbox" name="allowcatfeed" value="1" <?php echo $allowcatfeed; ?>>
                        <label for="displaycatfeed"><i class="pretty-check"></i>Display a list of categories available in the blog feed sidebar</label>
                    </p>-->
                </div><!-- .settings-content -->
                <div class="makeup-space"></div><!-- .makeup-space -->
                <input id="social-settings" class="settings-tab" type="radio" name="settings-tab"/>
                <label class="tab-text" for="social-settings">Social Bar Settings</label>
                <div class="settings-content">
                    <h3>Social Network Details</h3>
                    <div>
                        <p>
                            <label>Facebook App ID<br /><i class="warning-msg">If no APP ID is included facebook buttons will fail to show.</i></label>
                            <input name="socialfbID" type="text" maxlength="100" value="<?php echo $socialfbID; ?>"/>
                        </p>
                    </div>
                    <div class="ly-2-half">
                        <div>
                            <h3>Display Bar</h3>
                            <p>
				                <input type="hidden" name="socialbarshow" value="0">
				                <input id="socialbar" type="checkbox" name="socialbarshow" value="1" <?php echo $socialbarshow; ?>>
				                <label for="socialbar"><i class="pretty-check"></i>Show a social network bar<br /><i class="warning-msg">Display a bar of share and like buttons in every post.</i></label>
			                </p>
                        </div>
                        <div>
                            <h3>Select Networks</h3>
                            <p>
                                <input name="socialfacebook" type="hidden" value="0"/>
                                <input id="facebook" name="socialfacebook" type="checkbox" value="1" <?php echo $socialfb; ?>/>
                                <label for="facebook" class="fb-icon"><i class="pretty-check"></i>Facebook</label>
                            </p>
                            <p>
                                <input name="socialgplus" type="hidden" value="0"/>
                                <input id="googleplus" name="socialgplus" type="checkbox" value="1" <?php echo $socialgplus; ?>/>
                                <label for="googleplus" class="gp-icon"><i class="pretty-check"></i>Google Plus</label>
                            </p>
                            <p>
                                <input name="socialpinterest" type="hidden" value="0"/>
                                <input id="pinterest" name="socialpinterest" type="checkbox" value="1" <?php echo $socialpin; ?>/>
                                <label for="pinterest" class="pin-icon"><i class="pretty-check"></i>Pinterest</label>
                            </p>
                            <p>
                                <input name="socialtwitter" type="hidden" value="0"/>
                                <input id="twitter" name="socialtwitter" type="checkbox" value="1" <?php echo $socialtwit; ?>/>
                                <label for="twitter" class="tweet-icon"><i class="pretty-check"></i>Twitter</label>
                            </p>
                            <p>
                                <input name="sociallinkedin" type="hidden" value="0"/>
                                <input id="linkedin" name="sociallinkedin" type="checkbox" value="1" <?php echo $sociallink; ?>/>
                                <label for="linkedin" class="link-icon"><i class="pretty-check"></i>LinkedIn</label>
                            </p>
                        </div>
                    </div>
                    <div class="ly-2-half">
                        <div>
                            <h3>Select Social Bar type</h3>
				            <p>
					            <input id="optshare" type="radio" name="socialbartype" value="SHARE" <?php echo ( $socialbartype === 'SHARE' ) ? 'checked' : '' ?>>
					            <label for="optshare"><i class="pretty-radio"></i>Show Share Bar</label>
				            </p>
				            <p>
					            <input id="optlike" type="radio" name="socialbartype" value="LIKE" <?php echo ( $socialbartype === 'LIKE' ) ? 'checked' : '' ?>>
					            <label for="optlike"><i class="pretty-radio"></i>Show Like Bar</label>
				            </p>
				            <p>
					            <input id="optsharelike" type="radio" name="socialbartype" value="SHARE_LIKE" <?php echo ( $socialbartype === 'SHARE_LIKE' ) ? 'checked' : '' ?>>
					            <label for="optsharelike"><i class="pretty-radio"></i>Show Share and Like in single bar</label>
				            </p>
			            </div>
			            <div>
                            <h3>Select Position</h3>
				            <p>
					            <input id="opttop" type="radio" name="socialbarposition" value="TOP" <?php echo ( $socialbarpos === 'TOP' ) ? 'checked' : ''; ?>>
					            <label for="opttop"><i class="pretty-radio"></i>Top (Before Title)</label>
				            </p>
				            <p>
					            <input id="opthead" type="radio" name="socialbarposition" value="HEAD" <?php echo ( $socialbarpos === 'HEAD' ) ? 'checked' : ''; ?>>
					            <label for="opthead"><i class="pretty-radio"></i>Header (Between Title and Excerpt)</label>
				            </p>
				            <p>
					            <input id="optbottom" type="radio" name="socialbarposition" value="BOTTOM" <?php echo ( $socialbarpos === 'BOTTOM' ) ? 'checked' : ''; ?>>
					            <label for="optbottom"><i class="pretty-radio"></i>Bottom (After post)</label>
				            </p>
				            <p>
					            <input id="optheadbottom" type="radio" name="socialbarposition" value="HEAD_BOTTOM" <?php echo ( $socialbarpos === 'HEAD_BOTTOM' ) ? 'checked' : ''; ?>>
					            <label for="optheadbottom"><i class="pretty-radio"></i>Header and Bottom</label>
				            </p>
				            <p>
					            <input id="opttopbottom" type="radio" name="socialbarposition" value="TOP_BOTTOM" <?php echo ( $socialbarpos === 'TOP_BOTTOM' ) ? 'checked' : ''; ?>>
					            <label for="opttopbottom"><i class="pretty-radio"></i>Top And Bottom</label>
				            </p>
			            </div>
                    </div>
                </div><!-- .settings-content -->
                <div class="makeup-space"></div><!-- .makeup-space -->
                <input id="gen-settings" class="settings-tab" type="radio" name="settings-tab"/>
                <label class="tab-text" for="gen-settings">General Settings</label>
                <div class="settings-content">
                    <h3>Select single post's URL structure</h3>
                    <p>
                        <input id="blogpost-link" name="setpermalink" type="radio" value="/blog/post-title" <?php echo ( $setpermalink === '/blog/post-title' ) ? 'checked' : ''; ?>/>
                        <label for="blogpost-link"><i class="pretty-radio"></i><?php echo get_site_url(); ?><strong>/blog/*post-title</strong></label>
                    </p>
                    <p>
                        <input id="blogcatpost-link" name="setpermalink" type="radio" value="/blog/category/post-title" <?php echo ( $setpermalink === '/blog/category/post-title' ) ? 'checked' : ''; ?>/>
                        <label for="blogcatpost-link"><i class="pretty-radio"></i><?php echo get_site_url(); ?><strong>/blog/*category/*post-title</strong></label>
                    </p>
                    <!-- COMMING SOON -->
                    <!--<h3>Auto Save</h3>
                    <p class="setting-description">Turn on Editor auto-save mode by selecting a time interval.</p>
                    <p>
                        <input id="save-5" type="radio" name="setautosave" value="5" <?php echo ( $setautosave === '5' ) ? 'checked' : ''; ?>/>
                        <label for="save-5"><i class="pretty-radio"></i>Every 5 minutes.</label>
                    </p>
                    <p>
                        <input id="save-15" type="radio" name="setautosave" value="15" <?php echo ( $setautosave === '15' ) ? 'checked' : ''; ?>/>
                        <label for="save-15"><i class="pretty-radio"></i>Every 15 minutes.</label>
                    </p>
                    <p>
                        <input id="save-30" type="radio" name="setautosave" value="30" <?php echo ( $setautosave === '30' ) ? 'checked' : ''; ?>/>
                        <label for="save-30"><i class="pretty-radio"></i>Every 30 minutes.</label>
                    </p>
                    <p>
                        <input id="manual-save" type="radio" name="setautosave" value="manual" <?php echo ( $setautosave === 'manual' ) ? 'checked' : ''; ?>/>
                        <label for="manual-save"><i class="pretty-radio"></i>I'll save it manually.</label>
                    </p>-->

                    <!--COMMING SOON-->
                    <!--<h3>Clean Trashed Posts</h3>
                    <p class="setting-description">Trashed items will waste the space in your server unless you delete them. Set auto-deletion by selecting a time interval.</p>
                    <p>
                        <input id="days-7" type="radio" name="settrashposts" value="7" <?php echo ( $settrashposts === '7' ) ? 'checked' : ''; ?>/>
                        <label for="days-7"><i class="pretty-radio"></i>Empty trash every week.</label>
                    </p>
                    <p>
                        <input id="days-30" type="radio" name="settrashposts" value="30" <?php echo ( $settrashposts === '30' ) ? 'checked' : ''; ?>/>
                        <label for="days-30"><i class="pretty-radio"></i>Empty trash every month</label>
                    </p>
                    <p>
                        <input id="manual-trash" type="radio" name="settrashposts" value="manual" <?php echo ( $settrashposts === 'manual' ) ? 'checked' : ''; ?>/>
                        <label for="manual-trash"><i class="pretty-radio"></i>Empty manually</label>
                    </p>-->
                </div><!-- .settings-content -->
                <div class="makeup-space"></div><!-- .makeup-space -->
            </div>
			<div class="button-wrapper">
				<button class="mat-button button" type="submit">Apply Settings</button>
			</div>
		</form>
	</section>
</article>