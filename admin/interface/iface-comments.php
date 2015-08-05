<?php
	renew_session();
    //Manager variables
    $alert_msg = '';
    $manager_title = '';
    $user = get_user_byNAME( $_SESSION['CLIENT'] );
    $status = 'APPROVED';

    if ( isset( $_POST['adminreply'] ) ) {
        if ( submit_comment( $_POST ) !== false ) {
            $alert_msg = '<div class="success-msg">Your comment has been posted.<p></p></div>';
        } else {
            $alert_msg = '<div class="warning-msg">An error occurred, your comment has not been posted.<p></p></div>';
        }
    }

    if ( isset( $_GET ) && isset( $_GET['action'] ) ) {
        switch ( $_GET['action'] ) {
            case 'allcomments':
            case 'adminreplied':
                //Manager variables
                $manager_title = 'Approved Comments';

                //Comments variables
                $status = 'APPROVED';
                break;
            case 'pendingcomments':
                //Manager variables
                $manager_title = 'Pending Comments';

                //Comments variables
                $status = 'PENDING';

                break;
            case 'deletecomment':
                if ( isset( $_GET['commentid'] ) && isset( $_GET['status'] ) ) {
                    if ( delete_comment( $_GET['commentid'] ) ) {
                        $alert_msg = '<div class="success-msg"><p>Comment has been deleted successfully.</p></div>';
                    } else {
                        $alert_msg = '<div class="warning-msg"><p>Comment could not be deleted, please try again.</p></div>';
                    }

                    $manager_title = ucfirst( strtolower( $_GET['status'] ) ) . ' comments';
                    $status = $_GET['status'];
                }
                break;
            case 'approvecomment':
                if ( isset( $_GET['commentid'] ) && $_GET['status'] == 'PENDING' ) {
                    if ( approve_comment( $_GET['commentid'] ) ) {
                        $alert_msg = '<div class="success-msg"><p>Comment was approved successfully. Now is under <strong>All Comments</strong> section.</p></div>';
                    } else {
                        $alert_msg = '<div class="warning-msg"><p>Unable to approved comment, please try again.</p></div>';
                    }

                    $status = $_GET['status'];
                }
                break;
        }
    }

    //$comments_pages = new Paginate();
?>
<article id="comments-manager" class="container ibs-body">
	<header>
        <h1>Comments Manager</h1>
        <h2><?php echo $manager_title; ?></h2>
        <div class="tips-wrapper">
            <input class="tip-launcher" id="quick-tips" type="checkbox"/>
		    <label class="tip-l-box" for="quick-tips">
                <h4>Quick Tips</h4>
            </label>
            <div class="quick-tips-box mat-refresh">
                <ul>
                    <li><strong>IMPORTANT</strong> Comments replied from the admin area will be published automatically.</li>
                    <li><strong>ACTIONS</strong>
                        <ul>
                            <li><strong>APPROVE</strong> Approve a comment to be published in the post.</li>
                            <li><strong>REPLY</strong> Opens a box to reply to the comment.</li>
                            <li><strong>DELETE</strong>Deletes a comment from the system, approved or pending, no way to retrieve it.</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
	</header>
    <?php echo $alert_msg; ?>
    <section>
        <?php
        $comments =  get_comments_list( $status );
        display_comments_nav( $comments );
        ?>
    </section>
    <!-- REPLY BOX TEMPLATE -->
    <div id="comment-box-template" style="display:none;">
        <form action="<?php echo ADMINURL . '?action=adminreplied&status=APPROVED'; ?>" method="POST">
            <input type="hidden" name="user-email" value="" />
            <input type="hidden" name="user-website" value="" />
            <p>
                <span>Replying as:</span><input type="text" name="user-name" value="<?php echo ( empty( $user['name'] ) ) ? $user['username'] : $user['name']; ?>" readonly/>
                <input type="datetime" name="date" value="<?php echo date('Y-m-d H:i:s', time() ); ?>" readonly/>
            </p>
            <textarea name="content"></textarea>
            <p>
                <input class="button" name="adminreply" class="full-button" type="submit" value="SUBMIT"/>
            </p>
        </form>
    </div>
</article>