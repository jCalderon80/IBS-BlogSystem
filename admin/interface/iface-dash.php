<?php
$title_dash = 'iGuana Blog System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title></title>
	<?php add_meta_tags(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<link rel="stylesheet" type="text/css" href="<?php echo ADMINURL; ?>interface/css/smartlayout.css">
	<link rel="stylesheet" type="text/css" href="<?php echo ADMINURL; ?>interface/css/style.css">
	<link rel="stylesheet" type="text/css" href="<?php echo ADMINURL; ?>interface/css/md-base.css">
	<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro' rel='stylesheet' type='text/css'>
    <script type="text/javascript" src="<?php echo ADMINURL; ?>interface/js/tinymce/tinymce.min.js"></script>
    <!--<script type="text/javascript" src="<?php echo ADMINURL; ?>interface/js/nicEdit.js"></script>-->
	<script type="text/javascript" src="<?php echo ADMINURL; ?>interface/js/iface-library.js"></script>
    <script type="text/javascript" src="<?php echo ADMINURL; ?>interface/js/main.js"></script>
</head>
<body>
	<header id="dashboard" class="single-container mat-appbar">
		<div class="header-tag">
			<figure class="ibs-logo">
				<img src="<?php echo ADMINURL; ?>interface/images/IBS-logo.png">
			</figure>
			<h1><?php echo $title_dash; ?></h1>
		</div>
		<div class="admin-menu">
			<input id="admin-drop-menu" type="checkbox">
			<label for="admin-drop-menu" class="admin-drop-menu">
				<p class="user-drawer">
					<?php echo $user_in_session; ?>
				</p>
				<ul id="admin-menu-container" class="mat-nav">
					<li><a href="<?php echo ADMINURL . '?action=userprofile'; ?>">Profile</a></li>
					<li><a href="<?php echo ADMINURL . '?action=logout'; ?>">Log Out</a></li>
				</ul>
			</label>
		</div>
	</header>
	<div id="main" class="ly-2-fourth wrap-container">