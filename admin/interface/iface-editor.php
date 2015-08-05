<?php

renew_session();

//Get global settings
$global_settings = get_post_settings();

$admin_action = '?action=newpost';
$publish_bt = 'publish';

$editor_title = '';
$alert_msg = '';
$url_container = '';

$actual_author = get_user_byname( $_SESSION['CLIENT'] );
$post_id = '';
$post_date = date( 'Y-m-d H:i:s', time() );
$post_url = '';
$post_title = '';
$post_author = '';
$post_author_id = '';
$post_author_url = '';
$post_excerpt = '';
$post_content = '';
$post_comments = $global_settings['allowcomments'];
$post_image = '';
$post_comment_text = '';
$post_comment_option = '';
$post_comment_hidden = 0;

if ( isset( $_POST ) && ! empty( $_POST['title'] ) ) {

    $admin_action = '?action=editpost&postid=' . $_POST['id'];
    $image_upload = ( isset( $_FILES ) && $_FILES['image']['error'] == 0 ) ? $_FILES : '';

    // If publish button submitted
    if ( isset( $_POST['publish'] ) || isset( $_POST['draftnew'] ) ) {
        $draft = ( isset( $_POST['draftnew'] ) ) ? true : false;

        if ( create_post( $_POST, $image_upload, $draft ) ) {
            $alert_msg = '<div class="success-msg"><p>Your post has been successfully published.<br>You can continue editing or leave safely.</p></div>';
        } else {
            $alert_msg = '<div class="warning-msg"><p>Something went wrong please try again.</p></div>';
        }

    //If save button submitted
    } else if ( isset( $_POST['save'] ) || isset( $_POST['draftsaved'] ) || isset( $_POST['publishdraft'] ) ) {
        if ( isset( $_POST['save'] ) ) {
            $draft = null;
        } else if ( isset( $_POST['draftsaved'] ) ) {
            $draft = true;
        } else if( isset( $_POST['publishdraft'] ) ) {
            $draft = false;
        }

        if ( update_post( $_POST, $image_upload, $draft ) ) {
            $alert_msg = '<div class="success-msg"><p>Your post has been successfully updated.<br>You can continue editing or leave safely.</p></div>';
        } else {
            $alert_msg = '<div class="warning-msg"><p>Your post could not be updated, please try again.</p></div>';
        }

    }
}


if ( isset( $_GET['action'] ) ) {
    //Set variables for editor and new post
    if ( $_GET['action'] =='newpost' ) {

        //Set variables for new post
        $post_id = get_new_postid();
        $post_author = ( isset( $actual_author['name'] ) && ! empty( $actual_author['name'] ) ) ? $actual_author['name'] : $actual_author['username'];
        $post_author_id = $actual_author['id'];
        $post_author_url = ( isset( $actual_author['url'] ) && ! empty( $actual_author['url'] ) ) ? $actual_author['url'] : '';

        //Some Editor variables
        $editor_title = 'New Post';
        $url_container = 'data-target';
        $admin_action = '?action=editpost&postid=' . $post_id;
        $draft_bt = 'draftnew';

    } else if ( $_GET['action'] == 'editpost' && isset( $_GET['postid'] ) ) {
        //Set variables for editor and post being edited

        //Some Editor Variables
        $editor_title = 'Edit Post';
        $url_container = 'data-no-target';
        $publish_bt = 'save';
        $admin_action = '?action=editpost&postid=' . $_GET['postid'];
        $draft_bt = ( get_post_status( $_GET['postid'] ) === 'DRAFT' ) ? 'publishdraft' : 'draftsaved';

        //Get requested post, must typecast id to int.
        $post = get_post_data_byID( (int)$_GET['postid'] );
        $post_id = $post['id'];
        $post_title = $post['title'];
        $post_date = $post['date'];
        $post_url = $post['url'];
        $post_author = $post['author']['name'];
        $post_author_id = $post['author']['id'];
        $post_author_url = $post['author']['url'];
        $post_excerpt = $post['excerpt'];
        $post_image = ( isset( $post['image'] ) && ! empty( $post['image'] ) ) ? sprintf( '<figure><img src="%1$s" alt="%2$s"/></figure>', UPLOADURL . $post['image'], $post['image'] ) : '<p>No image found, try uploading one.</p>';
        $post_content = get_post_content_byID( $post_id );
        $post_comments = $post['comments']['enable'];
        $post_comment_option = ( post_has_comments( (int)$post['id'] ) ) ? 'disabled' : '';
        $post_comment_hidden = ( post_has_comments( (int)$post['id'] ) ) ? '1' : '0';
        $post_comment_text = ( post_has_comments( (int)$post['id'] ) ) ? '<p>Users have posted comments in this post, you cannot change this option.</p>' : '';
    }
    
}

?>
<article id="post-editor" class="container ibs-body">
	<header>
		<h1>POST EDITOR</h1>
        <div class="tips-wrapper">
            <input class="tip-launcher" id="quick-tips" type="checkbox"/>
		    <label class="tip-l-box" for="quick-tips">
                <h4>Quick Tips</h4>
            </label>
            <div class="quick-tips-box mat-refresh">
                <ul>
                    <li><strong>PUBLISH BUTTON</strong> Will automatically publish your post for all the world to see.</li>
                    <li><strong>DRAFT BUTTON</strong>
                        <ul>
                            <li>On a brand <strong>new</strong> post will save your post as a draft.</li>
                            <li>On a <strong>existing published or draft</strong> post it will save it as a draft.</li>
                        </ul>
                    </li>
                    <li><strong>SAVE BUTTON</strong>
                        Only appears when editing existing posts, or right after you publish or draft your post.
                        <ul>
                            <li>Saves your post, once you save your post you <strong>cannot undo</strong> changes.</li>
                            <li>The save button <strong>don't</strong> change posts' published status, published posts stay</li>
                        </ul>

                    </li>
                </ul>
            </div>
        </div>
	</header>
    <section>
        <h1><?php echo $editor_title; ?></h1>
        <?php echo $alert_msg; ?>
		<div class="editor-wrapper">
			<form id="form-editor" action="<?php echo ADMINURL . $admin_action; ?>" method="POST" enctype="multipart/form-data">
                <div class="ly-2-fourth-rev">
                    <div class="main-editor">
                        <input name="id" type="hidden" value="<?php echo $post_id ?>">
				        <p>
					        <label for="article-title">Post Title <i>*</i></label>
					        <input id="article-title" name="title" type="text" maxlength="100" placeholder="You must type a title for your post." onkeyup="create_url_from_text(this)" required autocomplete="off" value="<?php echo $post_title; ?>">
				        </p>
				        <?php
					        //Add delete button if article already exist
				        ?>
                        <div class="article-data">
                                <p>
					                <label for="article-url"><strong>URL:</strong> <?php echo get_site_url(); ?>/blog/</label>
					                <input id="artcle-url" name="url" type="url" value="<?php echo $post_url; ?>" <?php echo $url_container; ?>="true" readonly>
				                </p>
                                <br/>
				                <p>
					                <label for="article-date"><strong>DATE: </strong></label>
					                <input id="article-date" class="right-margin" name="date" type="datetime" value="<?php echo $post_date; ?>" readonly>
				                </p>
				                <p>
					                <label for="article-author"><strong>AUTHOR: </strong></label>
					                <input id="author-id" name="author-id" type="hidden" value="<?php echo $post_author_id; ?>" readonly>
                                    <input id="author-url" name="author-url" type="hidden" value="<?php echo $post_author_url; ?>" readonly>
					                <input id="article-author" name="author-name" type="text" value="<?php echo $post_author; ?>" readonly>
				                </p>
                        </div>
				        <p>
					        <label for="article-content">Article Content</label>
					        <textarea id="article-content" name="content" placeholder="Let your inspiration flow..."><?php echo $post_content; ?></textarea>
				        </p>
                    </div>
                    <div class="editor-features">
                        <h3>Post features</h3>
                        <p>
                            <label for="article-image">Header Image <i>optional<br>Max size 5MB</i></label>
                        </p>
                        <?php echo $post_image; ?>
                        <p>
				            <input id="article-image" name="image" type="file">
                            <input id="remove-article-image" name="image" type="checkbox" value="remove" checked/>
                            <label for="remove-article-image"><i class="pretty-check"></i></label>
			            </p>
                        <p>
				            <label for="article-excerpt">Post Excerpt</label>
                            <textarea id="article-excerpt" name="excerpt" maxlength="155" placeholder="You can give your readers a good teaser in your blog home page."  oninput="get_text_count(this, 'char-counter')"><?php echo $post_excerpt; ?></textarea>
                            <span class="small-text">You have <i id="char-counter">155</i> characters left.</span>
			            </p>
                        <h3>Post Options</h3>
                        <p>
                            <?php echo $post_comment_text; ?>
                            <input type="hidden" value="<?php echo $post_comment_hidden; ?>" name="comments-enable"/>
                            <input id="enable-comments" name="comments-enable" type="checkbox" value="1" <?php echo ( $post_comments === '1' ) ? 'checked' : ''; ?> <?php echo $post_comment_option; ?>/>
                            <label for="enable-comments"><i class="pretty-check"></i>Allow commenting for this particular post.</label>
                        </p>
                        <h3>Post Actions</h3>
                        <div class="button-wrapper">
					        <input class="button mat-button" onclick="please_wait()" name="<?php echo $publish_bt; ?>" type="submit" value="<?php echo ( $publish_bt === 'publish' ) ? 'Publish it!' : 'Save it!'; ?>">
					        <input class="button mat-button" onclick="please_wait()" name="<?php echo $draft_bt; ?>" type="submit" value="<?php echo ( $draft_bt === 'publishdraft' ) ? 'Publish it!' : 'Draft it!'; ?>">
				        </div>
                    </div>
                </div>
			</form>
		</div>
	</section>
</article>
