<?php

$post_data = array();

function prepare_file ( $request ) {
	//Data holder
	global $post_data;

	//Check if the request has been sent and store the data
	//in the global $post_array
	if ( $_POST['publish'] || $_POST['draft'] ) {

		foreach ( $_POST as $tag => $data ) {
			
			switch ( $tag ) {
				case 'author-id':
					$post_data['author']['id'] = $data;
					break;
				case 'author-name':
					$post_data['author']['name'] = $data;
					break;
				default:
					$post_data['tag'] = $data;
					break;
			}
		}

	}
	
	//Full content of the article is saved separately
	$article_content = $post_data['content'];

	//unset the $post_data['content'] and store the content file name;
	unset( $post_data['content'] );
	$post_data['content'] = $post_data['url'] . 'content';

	//Set the file names
	$json_file = $post_data['id'] . '-' . $post_data['url'] . 'json';
	$content_file = $post_data['content'];

	//Writing and creating all the needed files
	if ( ! write_article( $json_file, $post_data ) && ! write_content( $content_file, $article_content ) ) {
		// Log error and die if any of the files fail to be written
	}

	//If files written successfully continue with .htaccess and rss-fee
	
	//Create or update rss-feed file
	if ( ! write_rss() ) {
		//Log the error if writing of rss fails
	}

	//Create or update .htaccess
	if ( ! write_htaccess() ) {
		//Log the error if writing of .htaccess fails
		
	}

}

function write_article ( $file_name, $file_data ) {
	$output;

	if ( file_exists( $file_name ) ) {
		//Update file
	} else {
		//Create file

	}
}

function write_content ( $file_name, $file_data ) {
	$output;

	if ( file_exists( $file_name ) ) {
		//Update content
	} else {
		//Create content
		$fhandler = fopen( $file_name, 'w' );

		if ( fwrite( $fhandler, string) ) {
			# code...
		}
	}
}

// Creates or updates the htaccess file for the blog directory
function write_htaccess () {
	global $post_data;

	$output;
	$htaccess = '/.htaccess'; // Prepend the website url

	//prepare file
	$initial_string = "Options +FollowSymlinks\r\nRewriteEngine On\r\n\r\n#Rewrite Rules for blog pages";

	//prepare new rule
	$new_rule = 'RewriteRule ^blog/' . $post_data['url'] . '$ article-page.php?id=' . $post_data['id'] . ' [NC,L]' . "\r\n";

	//if .htaccess exists AND it's not empty add new rule to the end
	// else create file and write first rule
	if ( file_exists( $htaccess ) && filesize( $htaccess ) != 0 ) {

		//If write success return true, otherwise false
		if ( file_put_contents( $htaccess , $new_rule, FILE_APPEND | LOCK_EX ) ) {
			$output = true;
		} else {
			$output = false;
		}

	} else {
		$htaccess_handler = fopen( $htaccess, 'w' );

		// Concatenate strings
		$file_string = $initial_string . $new_rule;

		//If write success return true, otherwise false
		if ( fwrite( $htaccess_handler, $file_string ) ) {
			$output = true;
		} else {
			$output = false;
		}

		fclose( $htaccess_handler );
	}

	return $output;

}

function write_rss () {
	global $post_data;

	$item = array( 'guid', 'title', 'pubDate', 'link', 'description' );

	$rss_filename = 'rss-blogfeed.xml';

	// Check if RSS file exist
	if ( file_exists( $rss_file ) && filesize( $rss_file ) != 0 ) {

		$rss_update = new DOMDocument();
		$rss_update->load( $rss_file );

	} else {

		try {

			$rss_file = new DOMDocument( '1.0', 'utf-8' );

			$rss_file->formatOutput = true;

			//Create the rss tag and set the version 2.0
			$rss_tag = $rss_file->crateElement( 'rss' );
			$rss_attr = $rss_file->createAttribute( 'version' );
			$rss_attr->value = '2.0';

			//Append attribute to rss tag
			$rss_tag->appendChild( $rss_attr );

			//Create channel
			$channel_element = $rss_file->createElement( 'channel' );

			//Create channel children and append to it.
			foreach ( $channel as $channel_tag ) {
				$t_chan = $rss_file->createElement( $channel_tag );
				$channel_element->appendChild( $t_chan );
			}

			//Create item
			$item_element = $rss_file->createElement( 'item' );

			//Create item children and append to it
			foreach ( $item as $item_tag) {
				$t_item = $rss_file->createElement( $item_tag );
				$item_element->appendChild( $t_item );
			}

			//Append item to channel
			$channel_element->appendChild( $item_element );

			//Append channel to rss
			$rss_tag->appendChild( $rss_tag );

			//Append rss to file
			$rss_file->appendChild( $rss_tag );

			$rss_file->save( $rss_filename );

		} catch (Exception $e) {
			// Log error if failing
		}

	}

}

?>