<?php

//Renew the session time
renew_session();

$action = ( isset( $_GET['action'] ) ) ? $_GET['action'] : 'allposts';
$x_button = '';
$get_status = '';
$alert_msg = '';

//Get sort and display options and keep it on session
if ( ( isset( $_GET['posts-page'] ) && isset( $_SESSION['ppp'] ) ) || ( isset( $_GET['posts-page'] ) && ! isset( $_SESSION['ppp'] ) ) ) {
    $_SESSION['ppp'] = $_GET['posts-page'];
} else if ( ! isset( $_GET['posts-page'] ) && ! isset( $_SESSION['ppp'] ) ) {
    $_SESSION['ppp'] = 10;
}

if ( ( isset( $_GET['posts-sort'] ) && isset( $_SESSION['ps'] ) ) || ( isset( $_GET['posts-sort'] ) && ! isset( $_SESSION['ps'] ) ) ) {
    $_SESSION['ps'] = $_GET['posts-sort'];
} else if ( ! isset( $_GET['posts-sort'] ) && ! isset( $_SESSION['ps'] ) ) {
    $_SESSION['ps'] = 'LMOD_DESC';
}


switch ( $action ) {
    case 'activatepost':
        //Variables when displaying posts in the trash
        if ( isset( $_GET['postid'] ) && ! empty( $_GET['postid'] ) ) {
            if ( activate_post( $_GET['postid'] ) ) {
                $alert_msg = '<div class="success-msg"><p>Your post has been succesfully activated.</p></div>';
            }
        }
        $get_status = 'ACTIVE';
        $x_button = 'trash';
        break;
    case 'trashedposts':
        //Variables when displaying posts in the trash
        $get_status = 'INACTIVE';
        $x_button = 'delete';
        break;

    case 'trashfromlist':
        //Variables once a post has been move to the trash;
        if ( isset( $_GET['postid'] ) && ! empty( $_GET['postid'] ) ) {
            if ( trash_post( (int)$_GET['postid'] ) ) {
                $alert_msg = '<div class="success-msg"><p>Your post has been succesfully moved to the trash.</p></div>';
            }
        }
        $get_status = 'ACTIVE';
        $x_button = 'trash';
        break;
    case 'deletefromlist':
        if ( isset( $_GET['postid'] ) && ! empty( $_GET['postid'] ) ) {
            if ( delete_post( (int)$_GET['postid'] ) ) {
                $alert_msg = '<div class="success-msg"><p>Your post has been succesfully deleted.</p></div>';
            }
        }
        $get_status = 'INACTIVE';
        $x_button = 'delete';
        break;
    case 'draftposts':

        $get_status = 'DRAFT';
        $x_button = 'trash';
        break;
    default:
        $get_status = 'ACTIVE';
        $x_button = 'trash';
        break;
}

$posts_list = get_posts_list( $get_status, $_SESSION['ps'] );


//Initiate Pagination
$posts_pages = new Paginate( array(
	'items' => $posts_list,
	'items_per_page' => $_SESSION['ppp']
) );

?>

<article id="posts-manager" class="container ibs-body">
	<header>
		<h2>Posts Manager</h2>
		<div class="tips-wrapper">
			<input id="quick-tips" class="tip-launcher" type="checkbox">
			<label for="quick-tips" class="tip-l-box">
                <h4>Quick Tips</h4>
			</label>
            <div class="quick-tips-box mat-refresh">
                <ul>
                    <li><strong>COLUMNS</strong>
                        <ul>
                            <li><strong>ID:</strong> Unique ID of each post.</li>
                            <li><strong>TITLE:</strong> Clicking the title will open a new tab to preview the post.</li>
                            <li><strong>CREATED:</strong> Date and Time when post was created.</li>
                            <li><strong>STATUS:</strong>
                                <ul>
                                    <li><strong>Active:</strong> Posts with this status are visible to the public.</li>
                                    <li><strong>Draft:</strong> Posts with this status are not visible to the public, but editable.</li>
                                    <li><strong>Inactive:</strong> Posts with this status are not visible, neither editable, and can be deleted completely from the system.</li>
                                </ul>
                            </li>
                            <li><strong>ACTIONS:</strong> Buttons actions change depending on the view.</li>
                        </ul>
                    </li>
                    <li><strong>BUTTONS</strong>
                        <ul>
                            <li><strong>EDIT BUTTON</strong> To make changes to your posts, published or drafts.</li>
                            <li><strong>TRASH BUTTON</strong> Moves a post to the <i>Trash Bin</i>.<br />Posts in the <i>Trash Bin:</i>
                                <ul>
                                    <li>Cannot be edited, you must activate them first.</li>
                                    <li>Can be recovered with the <i>Activate Button</i>.</li>
                                </ul>
                            </li>
                            <li><strong>ACTIVATE BUTTON</strong> Moves a post from the <i>Trash Bin</i> to the <i>Draft posts</i> section, to allow editing.</li>
                            <li><strong>DELETE BUTTON</strong> Entirely deletes a post from your system, and can not be recovered.</li>
                        </ul>
                    </li>
                </ul>
            </div>
		</div>
        <?php echo $alert_msg; ?>
		<div class="full-actions actions-bar">
			<form id="all-posts" action="<?php echo ADMINURL; ?>" method="GET">
				<input name="action" type="hidden" value="<?php echo $action; ?>">
				<ul>
					<li>
						<label for="top-sort-posts">Sort by</label>
						<select id="top-sort-posts" name="posts-sort" onchange="change_all_select(this, 'bottom-sort-posts')">
							<option value="LMOD_DESC" <?php echo ( $_SESSION['ps'] == 'LMOD_DESC' ) ? 'selected' : ''; ?>>Recent first</option>
							<option value="LMOD_ASC" <?php echo ( $_SESSION['ps'] == 'LMOD_ASC' ) ? 'selected' : ''; ?>>Oldest first</option>
							<option value="ASC" <?php echo ( $_SESSION['ps'] == 'ASC' ) ? 'selected' : ''; ?>>By Name Ascending</option>
							<option value="DESC" <?php echo ( $_SESSION['ps'] == 'DESC' ) ? 'selected' : ''; ?>>By Name Descending</option>
							<option value="ID_ASC" <?php echo ( $_SESSION['ps'] == 'ID_ASC' ) ? 'selected' : ''; ?>>By ID Ascending</option>
							<option value="ID_DESC" <?php echo ( $_SESSION['ps'] == 'ID_DESC' ) ? 'selected' : ''; ?>>By ID Descending</option>
						</select>
					</li>
					<li>
						<label for="top-display-posts">Display per page</label>
						<select id="top-display-posts" name="posts-page" onchange="change_all_select(this, 'bottom-display-posts')">
							<option value="10" <?php echo ( $_SESSION['ppp'] == 10 ) ? 'selected' : ''; ?>>10 Posts</option>
							<option value="25" <?php echo ( $_SESSION['ppp'] == 25 ) ? 'selected' : ''; ?>>25 Posts</option>
							<option value="50" <?php echo ( $_SESSION['ppp'] == 50 ) ? 'selected' : ''; ?>>50 Posts</option>
							<option value="100" <?php echo ( $_SESSION['ppp'] == 100 ) ? 'selected' : ''; ?>>100 Posts</option>
						</select>
					</li>
					<li>
						<input class="button mat-button" type="submit" value="Update filters">
					</li>
                    <!-- COMMING SOON -->
                    <!--<li>
                        <button class="button red-button mat-button" type="submit" value="<?php $x_button ?>"><?php echo ucfirst( $x_button ); ?> Selected</button>
                    </li>-->
				</ul>
			</form>
		</div>
	</header>
	<section id="posts-list">
		<?php $posts_pages->pages_bar(); ?>
		<div>
			<?php
				display_posts_nav( $posts_pages->page_content );
			?>
		</div>
		<?php $posts_pages->pages_bar(); ?>
	</section>
	<footer>
		<div class="full-actions actions-bar">
			<ul>
				<li>
					<label for="top-sort-posts">Sort by</label>
					<select id="bottom-sort-posts" onchange="change_all_select(this, 'top-sort-posts')">
						<option value="LMOD_DESC" <?php echo ( $_SESSION['ps'] == 'LMOD_DESC' ) ? 'selected' : ''; ?>>Recent first</option>
							<option value="LMOD_ASC" <?php echo ( $_SESSION['ps'] == 'LMOD_ASC' ) ? 'selected' : ''; ?>>Oldest first</option>
							<option value="ASC" <?php echo ( $_SESSION['ps'] == 'ASC' ) ? 'selected' : ''; ?>>By Name Ascending</option>
							<option value="DESC" <?php echo ( $_SESSION['ps'] == 'DESC' ) ? 'selected' : ''; ?>>By Name Descending</option>
							<option value="ID_ASC" <?php echo ( $_SESSION['ps'] == 'ID_ASC' ) ? 'selected' : ''; ?>>By ID Ascending</option>
							<option value="ID_DESC" <?php echo ( $_SESSION['ps'] == 'ID_DESC' ) ? 'selected' : ''; ?>>By ID Descending</option>
					</select>
				</li>
				<li>
					<label for="top-display-posts">Display per page</label>
					<select id="bottom-display-posts" onchange="change_all_select(this, 'top-display-posts')">
						<option value="10" <?php echo ( $_SESSION['ppp'] == 10 ) ? 'selected' : ''; ?>>10 Posts</option>
							<option value="25" <?php echo ( $_SESSION['ppp'] == 25 ) ? 'selected' : ''; ?>>25 Posts</option>
							<option value="50" <?php echo ( $_SESSION['ppp'] == 50 ) ? 'selected' : ''; ?>>50 Posts</option>
							<option value="100" <?php echo ( $_SESSION['ppp'] == 100 ) ? 'selected' : ''; ?>>100 Posts</option>
					</select>
				</li>
				<li>
					<input class="button mat-button" type="submit" value="Update filters" form="all-posts">
				</li>
                <!-- COMMING SOON -->
                <!--<li>
                    <button class="button red-button mat-button" type="submit" value="<?php $x_button ?>"><?php echo ucfirst( $x_button ); ?> Selected</button>
                </li>-->
			</ul>
		</div>
	</footer>
</article>
<?php
$_SESSION['pubc'] = count( get_posts_list('ACTIVE') );
$_SESSION['drac'] = count( get_posts_list('DRAFT') );
$_SESSION['trac'] = count( get_posts_list('INACTIVE') );
?>