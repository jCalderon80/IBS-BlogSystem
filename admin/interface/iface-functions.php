<?php

/**
 * GENERAL FUNCTIONS FOR THE ADMIN INTERFACE
 */

/****************************/
/*  USER PROFILE FUNCTIONS  */
/****************************/

/**
 * Retrieves a user from storage by its ID number.
 * @param  int $id              Must be an integer
 * @return mix|boolean|array    Returns the user array if success, returns false if fail.
 */
function get_user_by_id( $id ) {
	$output = false;
	$findings = 0;
	$user_found = array();

	foreach ( get_user_data()  as $user => $attrs ) {
		if ( $attrs['id'] == $id ) {
			$findings = $findings + 1;
			array_push( $user_found , $attrs );
		}
	}
	if ( $findings === 1 ) {
		$output = $user_found[0];
		unset( $user_found );
	} else {
		unset( $user_found );
	}
	return $output;
}

/**
 * [update_user description]
 * @param  [type] $data [description]
 * @return [type]       [description]
 */
function update_user( $data ) {
	$output = false;
	$actual_data = get_user_by_id( $data['id'] );
	$new_data = $data;
	$full_file = get_json_content( LOCKPATH . 'users-config.json' );
	$users = $full_file['users'];
	$updated_file;

	//Update Array
	foreach ( $new_data as $attr => $value ) {
		$actual_data[$attr] = $value;
	}

	//Attach user to file
	foreach ( $users as $key => $user_data ) {
		if ( $user_data['id'] === $actual_data['id'] ) {
			$full_file['users'][$key] = $actual_data;
		}
	}

	//$updated_file = json_encode( $full_file );

	if ( write_to_json( LOCKPATH . 'users-config.json', $full_file ) ) {
		$output = true;
	}

	return $output;

}

/**
 * [get_user_data description]
 * @return [type] [description]
 */
function get_user_data() {
	$user_file = json_decode( file_get_contents( LOCKPATH . 'users-config.json' ), true );
	$output = $user_file['users'];
	return $output;
}

/****************************/
/*  POSTS SETTINGS FUNCIONS */
/****************************/

/**
 * [update_posts_settings description]
 * @param  [type] $data [description]
 * @return [type]       [description]
 */
function update_posts_settings( $data ) {
	$output = false;
	$full_file = get_json_content( LOCKPATH . 'posts-config.json' );
	$settings = $full_file['postssettings'];
	$new_settings = $data;
    $allow_comments = '';
	$updated_settings = array();
    $s_social = '/^social/';

	foreach ( $new_settings as $setting => $value ) {

		if ( preg_match( $s_social, $setting ) ) {

            $settings['socialbar'][$setting] = $value;

		} else if ( $setting === 'allowcomments' ) {
            $settings[$setting] = $value;
			$allow_comments = $value;
		} else {
            $settings[$setting] = $value;
        }
	}

    //Remove
    unset( $settings['settings-tab'] );

	$updated_settings['postssettings'] = $settings;

	if ( write_to_json( LOCKPATH . 'posts-config.json', $updated_settings ) ) {
        update_commenting( $allow_comments );
		$output = true;
	}

	return $output;
}

function update_commenting( $value ) {
    $comments = get_comments_list();
    $posts_file = get_json_content( CONTENTPATH . 'content.json' );
    $pps =& $posts_file['posts'];
    $pids = array();
    $flag = false;
    $output = false;

    //Get id of posts already having comments.
    //approved or pending
    if ( is_array ( $comments ) &&  ! empty( $comments ) ) {
        foreach( $comments as $cc ) {
            if( ! empty( $cc['postparent'] ) ) {
                array_push( $pids, (int)$cc['postparent'] );
            }
        }
    }

    $pids = array_values( array_unique( $pids ) );

    //Update comment-enable on posts with no comments
    if ( is_array( $posts_file ) && ! empty( $posts_file ) && ! empty( $pids ) ) {
        foreach( $pps as &$p ) {
            if ( ! in_array( (int)$p['id'], $pids, true ) ) {
                $p['comments']['enable'] = $value;
            }
        }
        $flag = true;
    }

    if( $flag === true ) {
        if ( write_to_json( CONTENTPATH . 'content.json', $posts_file ) ) {
            $output = true;
        }
    }

    return $output;
    
}

/****************************/
/*    RSS FEED FUNCTIONS    */
/****************************/

function get_rss_channel() {
    $rss_content = file_get_contents( DOMPATH . 'rss-blogfeed.xml' );
	$rss_file = new SimpleXMLElement( $rss_content );

    return $rss_file->channel;
}

rssitem_exist(1);

function rssitem_exist( $guid ) {
    $channel = get_rss_channel();

    foreach( $channel[0] as $tag => $value ) {
        if ( $tag == 'item' ) {
        
        }
    }
}

function update_rss_channel( $data ) {
    $path = DOMPATH . 'rss-blogfeed.xml';
	$rss_file = new DOMDocument();
	$rss_file->formatOutput = true;

    $rss_file->load( $path );

    $rss_file->getElementsByTagName('title')->item(0)->nodeValue = $data['title'];
    $rss_file->getElementsByTagName('description')->item(0)->nodeValue = $data['description'];
    $rss_file->getElementsByTagName('lang')->item(0)->nodeValue = $data['lang'];

    if ( $rss_file->save( $path ) ) {
    	$output = true;
    } else {
        $output = false;
    }
    
    return $output;
}

$samp = array(
    'guid'=> 1,
    'title'=>'some title',
    'link'=>'somelink.html',
    'description'=>'some description'
);

add_rss_item( $samp );

function add_rss_item( $item ) {
    $path = DOMPATH . 'rss-blogfeed.xml';
    $rss_file = new DOMDocument();
    $rss_file->formatOutput = true;
    $t_arr = array();

    //Create item
    $rss_item = $rss_file->createElement('item');

    foreach( $item as $tag => $node ) {
        $t_arr[$tag] = $rss_file->createElement($tag);
        $t_arr[$tag]->nodeValue = $node;
    }

    foreach( $t_arr as $tag ) {
        $rss_file->appendChild( $tag );
    }

   

}

function create_item_array( $rss_data ) {
    $rss_item = array();

    foreach( $rss_data as $rss_tag => $rss_val ) {
        switch( $rss_tag ) {
            case 'id':
                $rss_item['guid'] = $rss_val;
                break;
            case 'title':
                $rss_item['title'] = $rss_val;
                break;
            case 'url':
                $rss_item['link'] = $rss_val;
                break;
            case 'modified':
                $rss_item['pubDate'] = $rss_val;
                break;
            case 'excerpt':
                $rss_item['description'] = $rss_val;
                break;
            case 'category':
                $rss_item['category'] = $rss_val;
                break;
            case 'author':
                $rss_item['author'] = $rss_val['name'];
                break;
        }
    }

    return $rss_item;
}

/****************************/
/*     EDITOR FUNCTIONS     */
/****************************/

/**
 * Retrieves a list of all existing post' ID's and based on last ID returned
 * it will create the next new ID, if fail returns false.
 * @return array|bool|double|int
 */
function get_new_postid() {
    $list_of_ids = array();
    $new_id = 1;

    if ( file_exists( CONTENTPATH . 'content.json' ) ) {
        $content = json_decode( file_get_contents( CONTENTPATH . 'content.json' ), true );

        if ( content_posts_exist() ) {
            foreach ( $content['posts'] as $post ) {
                array_push( $list_of_ids, (int) $post['id'] );
            }

            asort( $list_of_ids );
            $new_id = end( $list_of_ids ) + 1;
        }
    }

    return $new_id;
}

/**
 * Used with $_POST data or an array of key->values to 'publish' a new post into the 
 * system. It saves all the meta into the content.json and the post content into its
 * own after named
 * file.
 * @param mixed $post_data Work with $_POST data
 * @return bool|mixed returns string of errors on fails and true on success
 */
function create_post( $post_data, $file_data = '', $draft = false ) {
    $new_post = create_post_array( $post_data );
    $posts_file = '';
    $errors = '';

    //Set empty fields
    $new_post['status'] = ( $draft === true ) ? 'DRAFT' : 'ACTIVE';

    //Check if content.json has any content and if it does "posts" should be the 
    //parent array at top of the tree
    if ( content_posts_exist() ) {
        $posts_file = get_json_content( CONTENTPATH . 'content.json' );
    } else {
        $posts_file = array( 'posts'=>array() );
    }

     //Check if post exist already
    if( ! post_exist( (int)$post_data['id'] ) ) {

        // Upload header image first.
        if ( ! empty( $file_data ) ) {
            $uploaded = upload_img( $file_data, 5000000 );
            $new_post['image'] = $uploaded['response'];
            if( ! $uploaded['success'] ) {
                $errors .= "Your header image was not uploaded.\r\n";
            }
        }

        // Add post to the database
        array_push($posts_file['posts'], $new_post );

        // Create [name].content file, and update database if succeed
        if ( create_content( $post_data['url'] . '.content', $post_data['content'] ) ) {
           if ( ! write_to_json( CONTENTPATH . 'content.json', $posts_file ) ) {
               $errors.= "Your post could not be saved, please try again.\r\n";
           }
        } else {
            $errors.= ".contentError: Your post could not be saved, please try again.\r\n";
        }
    }
    
    return ( empty( $errors ) ) ? true : $errors;
}

/**
 * Summary of update_post
 * @param mixed $post_data 
 * @param mixed $file_data 
 * @param mixed $draft 
 * @return bool|string
 */
function update_post( $post_data, $file_data = '', $draft = null ) {
    $found = true;
    $errors = '';
    $updated_post = create_post_array( $post_data );
    $i = 0;
    
    //Check if the post exist to proceed
    if ( post_exist( (int)$post_data['id'] ) ) {
        $posts_file = get_json_content( CONTENTPATH . 'content.json' );
        $posts =& $posts_file['posts'];

        // Update database and update image if submitted
        while( $i < count( $posts ) ) {

            if ( $posts[$i]['id'] === (int)$post_data['id'] ) {
                //Avoid updating .content file and url
                $updated_post['content'] = $posts[$i]['content'];
                $updated_post['url'] = $posts[$i]['url'];

                //Set extra settings
                if ( $draft === null ) {
                    $updated_post['status'] = $posts[$i]['status'];
                } else {
                    $updated_post['status'] = ( $draft === true ) ? 'DRAFT' : 'ACTIVE';
                }

                //Update image
                if( ! empty( $file_data ) ) {
                    $uploaded = upload_img( $file_data, 5000000 );
                    $updated_post['image'] = $uploaded['response'];
                    if( ! $uploaded['success'] ) {
                        $errors .= "Your header image was not updated.\r\n";
                    }
                } else {
                    $updated_post['image'] = $posts[$i]['image'];
                }

                //Place new post in place of old one
                $posts[$i] = $updated_post;
                break;
            }
            $i++;
        }

        //If posts was found and database updated continue with .content and
        //saving the database
        if ( $found === true ) {
            if( create_content( $post_data['url'] . '.content', $post_data['content'] ) ) {
                if( ! write_to_json( CONTENTPATH . 'content.json', $posts_file ) ) {
                    $errors .= "Your post could not be saved please try again\r\n";
                }
                $errors .= "contentError:Your post could not be saved please try again\r\n";
            }

        }

    } else {
        $errors .= "Post could not be found\r\n";
    }

    return ( empty( $errors ) ) ? true : $errors;
}

/**
 * Summary of create_content
 * @param mixed $file_name 
 * @param mixed $content 
 * @return bool
 */
function create_content( $file_name, $content ) {
    $output = false;

    if ( ( $file = fopen ( CONTENTPATH . $file_name, 'w' ) ) ) {
        if ( fwrite( $file, $content ) ) {
            $output = true;

            fclose( $file );
        }
    }

    return $output;
}

/**
 * It arranges the $_POST array into a multidimensional array meeting the
 * database specs
 * @param mixed $post_data 
 * @return array|bool returns the post array on success and false on fail
 */
function create_post_array( $post_data ) {
    $output = false;
    $author_rg = '/author-/';
    $comm_rg = '/comments-/';
    $new_post = array();
    $mod_date = '';

    if ( isset( $post_data['publish'] ) ) {
        unset( $post_data['publish'] );
    } else if ( isset( $post_data['save'] ) ) {
        unset( $post_data['save'] );
        $mod_date = date( 'Y-m-d H:i:s', time() );
    } else if ( isset( $post_data['draftnew'] ) ) {
        unset( $post_data['draftnew'] );
    } else if ( isset( $post_data['draftsaved'] ) ) {
        unset( $post_data['draftsaved'] );
    } else if ( isset( $post_data['publishdraft'] ) ) {
        unset( $post_data['publishdraft'] );
    }

    //arrange the data and create content file
    foreach ($post_data as $item => $value) {
        if ( preg_match( $author_rg, $item ) ) {
            $aut_key = preg_replace( $author_rg, '', $item );
            $new_post['author'][$aut_key] = $value;
        } else if( preg_match( $comm_rg, $item ) ) {
            $com_key = preg_replace( $comm_rg, '', $item );
            $new_post['comments'][$com_key] = $value;
        } else {
            switch ( $item ) {
                case 'content':
                    $new_post[$item] = post_url_exist( $post_data['url'] ) . '.content';
                    break;
                case 'date':
                    $new_post[$item] = $value;
                    $new_post['modified'] = ( empty( $mod_date ) ) ? $value : $mod_date;
                    break;
                case 'url':
                    $new_post[$item] = post_url_exist($value);
                    break;
                case 'id':
                    $new_post[$item] = (int)$value;
                    break;
                default:
                    $new_post[$item] = htmlspecialchars( $value );
                    break;
            }    
        }
    }

    $new_post['image'] = '';
    $new_post['status'] = '';

    return ( is_array( $new_post ) ) ? $new_post : $output;
}

function post_url_exist( $post_url ) {
    $output = $post_url;
    $posts = get_posts_list();
    $found_urls = array();
    $url_exist = false;
    

    //if url exists, check for renamed urls like [url]-1,[url]-2,[url]-12
        
    foreach( $posts as $post ) {

        if ( $post_url == $post['url'] ) {
            $url_exist = true;
        }

        if( preg_match( '/^' . $post_url . '/', $post['url'] ) ) {
                
            if ( preg_match( '/[0-9]+$/', $post['url'], $df) ) {
                array_push( $found_urls, (int)$df[0] );
            }

        }
    }

    if( ! empty( $found_urls ) ) {
        asort( $found_urls );
        $output = $post_url . '-' . ( end($found_urls) + 1 );
    } else if ( empty( $found_urls ) && $url_exist ) {
        $output = $post_url . '-1';
    }

    return $output;
}

/**
 * Upload images (jpg,jpeg,gif,png) to the server
 * @param mixed $image must pass the $_FILES variable
 * @param mixed $size_limit must define the size of allowed files in kb
 * @return string returns the image path in the server with success, error message if fails.
 */
function upload_img( $image, $size_limit ) {
    $output = array();
    $fail_msg = '';
    $proceed = true;

    //Check if folder exists and give the rights
    if ( ! file_exists( DOMPATH . '/images' ) ) {
        mkdir( DOMPATH . '/images', 0777 );
        mkdir( UPLOADPATH, 0777 );
    }
    chmod( DOMPATH . '/images', 0777 );

    //Create upload directory if not exist
    if( ! file_exists( UPLOADPATH ) ) {
        mkdir( UPLOADPATH, 0777 );
    }
    chmod( UPLOADPATH, 0777 );

    //if return_image_markup = true, return image markup, else return
    // Success message
    $response;
    $type_rgx = '/^(gif|jpg|jpeg|png)$/i';
    $patt_rgx = array( '/[@$%<>^#!&,:;*?\'\"\`\[\]\/\|\(\)\{\}\\\\]+/', '/[\s\s]+/' );
    $repl_rgx = array( '-', '' );
    $clean_name = preg_replace( $patt_rgx, $repl_rgx, basename( $image['image']['name'] ) );
    $file_target = UPLOADPATH . $clean_name;
    $file_ext = pathinfo( $file_target, PATHINFO_EXTENSION );

    //Check name length
    if( mb_strlen( $file_target ) > 225 ) {
        $proceed = false;
        $fail_msg = 'File name exceeds the length limit.';
    }

    //Check if image
    $is_image = getimagesize( $image['image']['tmp_name'] );
    if ( $is_image === false ) {
        $proceed = false;
        $fail_msg = 'File is NOT an image.';
    }

    //Check file type
    if( ! preg_match( $type_rgx, $file_ext ) ) {
        $proceed = false;
        $fail_msg = 'File extension is not permitted.';
    }

    //File Size
    if( $image['image']['size'] > $size_limit ) {
        $proceed = false;
        $fail_msg = 'File size exceeds the size limit.';
    }

    //Upload the image
    if( $proceed === true ) {

        //Overwrite file if it exist
        if( file_exists( $file_target ) ) {
            chmod( $file_target, 0777 );
            unlink( $file_target );
        }

        //Attempt to upload the file
        if( $image['image']['error'] == 0 ) {
            if ( move_uploaded_file( $image['image']['tmp_name'], $file_target ) ) {
                $output['response'] = $clean_name;
                $output['success'] = true;
            } else {
                $output['response'] = $image['image']['error'] . ': Image upload failed, please try again.';
                $output['success'] = false;
            }
        }

    } else {
        $output['response'] = $fail_msg;
        $output['success'] = false;
    }

    echo $fail_msg;
    return $output;
}

/****************************/
/* POSTS MANAGER FUNCTIONS  */
/****************************/

/**
 * Displays a list of all posts inside a ul element
 * @param mixed $paged_posts 
 */
function display_posts_nav( $paged_posts ) {
    $output = '';
    
    if ( is_array( $paged_posts ) ) {
        $output = '<ul class="posts-list">';
        $delete_bt = 'trash';
        $action_bt = 'edit';
        if ( isset( $_GET['action'] ) ) {
            if ( $_GET['action'] === 'trashedposts' || $_GET['action'] === 'deletefromlist' ) {
                $delete_bt = 'delete';
                $action_bt = 'activate';
            }
        }

	    $list_header = '<li class="list-header"><div class="column-1">Select</div><div class="column-2">ID</div><div class="column-3">Title</div><div class="column-4">Created</div><div class="column-5">Status</div><div class="column-6">Actions</div></li>';

	    $markup = '<li class="posts-list-row">';
        $markup .= '<div class="column-1">';
        $markup .= '<input class="ugly-checkbox to-select" id="%9$s" value="%1$s" name="selected-post[]" type="checkbox">';
        $markup .= '<label for="%9$s">';
        $markup .= '<div class="pretty-radio"></div>';
        $markup .= '</label>';
        $markup .= '</div>';
        $markup .= '<div class="column-2">%1$s</div>';
        $markup .= '<div class="column-3">';
        $markup .= '<a id="post-link-%1$s" href="%10$s" target="_blank">%3$s</a>';
        $markup .= '</div>';
        $markup .= '<div class="column-4">%5$s</div>';
        $markup .= '<div class="column-5">%6$s</div>';
        $markup .= '<div class="column-6">';
        $markup .= '<a href="%2$s" class="button mat-button">%8$s</a>';
        $markup .= '<a href="%4$s" class="button red-button mat-button">%7$s</a>';
        $markup .= '</div>';
	    $markup .= '</li>';
        $output .= $list_header;
	    foreach ( $paged_posts as $post ) {
		    $output .= sprintf( 
                $markup, 
                $post['id'],                                                                //1
                ADMINURL . '?action=' . $action_bt . 'post&postid=' . $post['id'],          //2
                $post['title'],                                                             //3
                ADMINURL . '?action=' . $delete_bt . 'fromlist&postid=' . $post['id'],      //4
                $post['date'],                                                              //5
                $post['status'],                                                            //6
                ucfirst( $delete_bt ),                                                      //7
                ucfirst( $action_bt ),                                                      //8
                'post-' . $post['id'],                                                      //9
                get_site_url() . '/blog/' . $post['url']                                    //10
                );
	    }

        $output .= $list_header;
    }

	echo $output;
}

/**
 * Summary of change_post_status
 * @param mixed $post_data 
 * @param mixed $status 
 */
function activate_post( $post_id ) {
    $output = false;
    $i = 0;

    if ( post_exist( (int)$post_id ) ) {
        $posts_file = get_json_content( CONTENTPATH . 'content.json' );
        $posts =& $posts_file['posts'];

        while ( $i < count( $posts ) ) {
        	if( $posts[$i]['id'] === (int)$post_id ) {
                $posts[$i]['status'] = 'DRAFT';
                break;
            }
            $i++;
        }

        if ( write_to_json( CONTENTPATH . 'content.json', $posts_file ) ) {
            $output = true;
        }
    }

    return $output;
}

/**
 * Delete post completely from the system
 * @param int $post_id pass the post id as an integer
 * @return bool true on success, false on fail
 */
function delete_post( $post_id ) {
    $output = false;
    $i = 0;

    if ( post_exist( (int)$post_id ) ) {
        $posts_list = get_json_content( CONTENTPATH . 'content.json' );
        $posts =& $posts_list['posts'];
        
        while( $i < count( $posts ) ) {
            if ( $posts[$i]['id'] === (int)$post_id ) {
                
                if ( delete_content( $posts[$i]['content'] ) ) {
                    unset( $posts[$i] );
                    $posts = array_values( $posts );
                    if ( write_to_json( CONTENTPATH . 'content.json', $posts_list ) ) {
                        $output = true;
                    }
                }
                break;
            }

            $i++;
        }
    }

    return $output;

}

/**
 * Summary of trash_post
 * @param mixed $post_data 
 */
function trash_post( $post_id ) {
    $output = false;
    $posts_list = get_posts_list();
    $new_data = array();
    $i = 0;

    while ( $i < count( $posts_list ) ) {
        if ( $posts_list[$i]['id'] === $post_id ) {
            $posts_list[$i]['status'] = 'INACTIVE';
            break;
        }
    	$i++;
    }

    $new_data['posts'] = $posts_list;

    if ( write_to_json( CONTENTPATH . 'content.json', $new_data ) ) {
        $output = true;
    }

    return $output;
    
}

/**
 * Delete [file_name].content file from content folder
 * @param string $file_name 
 * @return bool  true on success, false on fail
 */
function delete_content( $file_name ) {
    $output = false;
    chmod( CONTENTPATH . $file_name, 0777 );
    if ( unlink( CONTENTPATH . $file_name ) ) {
        $output = true;
    }
    return $output;
}

/*******************************/
/* COMMENTS MANAGER FUNCTIONS  */
/*******************************/

function display_comments_nav( $paged_comments ) {
    $output = false;
    
    $comments_list = '<div class="comments-list">';

    $header = '<div class="comments-header-nav">
                    <div class="column-1">Select</div>
                    <div class="col-2">Comment</div>
                </div>';

    $markup = '<div class="comment-item">';
    $markup .= '<div class="column-1">';
    $markup .= '<input id="%1$s" value="%2$s" name="selected-comments[]" type="checkbox" />';
    $markup .= '<label for="%1$s"><div class="pretty-radio"></div></label>';
    $markup .= '</div>';
    $markup .= '<div id="attach-%2$s" class="col-2">';
    $markup .= '<input id="%3$s" type="checkbox"/>';
    $markup .= '<label for="%3$s">';
    $markup .= '<p class="clickable" style="cursor:pointer;">%4$s: <strong>%5$s</strong>  %6$s on: <strong>%7$s</strong> and said:</p>';
    $markup .= '</label>';
    $markup .= '<div class="comment-box-container">';
    $markup .= '<p><i>%8$s</i></p>';
    $markup .= '<p>User email: <strong>%9$s</strong> | User website: <strong>%10$s</strong></p>';
    $markup .= '<div class="comment-box-actions">';
    $markup .= '<a data-target="attach-%2$s" class="half-button button mat-button" %11$s>%12$s</a>';
    $markup .= '<a class="half-button button mat-button red-button" href="%13$s">DELETE</a>';
    $markup .= '</div>';
    $markup .= '</div>';
    $markup .= '</div>';
    $markup .= '</div>';

    if ( is_array( $paged_comments ) ) {
        //$comments_list .= $header;

        foreach ( $paged_comments as $comm ) {
            $approve_bt_text = 'APPROVE';
            $approve_bt_href = '';
            $approve_bt_act =  '';
            $repliedto = 'COMMENTED';

            if ( ! empty ( $comm['commentparent'] ) ) {
                $parent = get_comment_data_byID( (int)$comm['commentparent'] );
                $repliedto = 'REPLIED to: ' . $parent['user']['name']; 
            }

            if ( isset( $_GET['status'] ) ) {
                switch ( $_GET['status'] ) {
                    case 'PENDING':
                        $approve_bt_text = 'APPROVE';
                        break;
                    case 'APPROVED':
                        $approve_bt_text = 'REPLY';
                        break;
                }
            } else if ( isset( $_GET['action'] ) ) {
                switch ( $_GET['action'] ) {
                    case 'allcomments':
                        $approve_bt_text = 'REPLY';
                        break;
                    case 'pendingcomments':
                        $approve_bt_text = 'APPROVE';
                        break;
                }
            }

            if ( $approve_bt_text === 'REPLY' ) {
                $cc_par = ( ! empty( $comm['commentparent'] ) ) ? $comm['commentparent'] : '0' ;
                $approve_bt_href = 'onclick="open_comment_box( this, ' . $comm['id'] . ', ' . $cc_par . ', ' . $comm['postparent']  . ' )"';
            } else {
                $approve_bt_href = 'href="' . ADMINURL . '?action=approvecomment&commentid=' . $comm['id'] . '&status=' . $comm['status'] . '"';
            }

        	$comments_list .= sprintf(
                $markup,
                'comment-' . $comm['id'],       //1
                $comm['id'],                    //2
                'comment-box-' . $comm['id'],   //3
                $comm['date'],                  //4
                $comm['user']['name'],          //5
                $repliedto,                     //6
                get_post_title( (int)$comm['postparent'] ), //7
                $comm['content'],               //8
                $comm['user']['email'],          //9
                $comm['user']['website'],        //10
                $approve_bt_href,               //11
                $approve_bt_text,               
                ADMINURL . '?action=deletecomment&commentid='.$comm['id'].'&status='.$comm['status']

            );
        }

        //$comments_list .= $header;
        $comments_list .= '<div>';
        
    }

    echo $comments_list;
}

/**
 * Delete a comment
 * @param int $comment_id 
 * @return bool returns true on success or false on fail.
 */
function delete_comment( $comment_id ) {
    $output = false;
    $i = 0;

    if ( comment_exist( (int)$comment_id ) ) {
        $comments_file = get_json_content( CONTENTPATH . 'comments.json' );
        $comments =& $comments_file['comments'];
        $count = count( $comments );

        while ( $i < $count ) {
            if ( $comments[$i]['id'] === (int)$comment_id ) {
                unset( $comments[$i] );
                $comments = array_values( $comments );
                $deleted = true;
                break;
            }
            $i++;
        }

        if( $deleted === true ) {
            if ( write_to_json( CONTENTPATH . 'comments.json', $comments_file ) ) {
                $output = true;
            }
        }

    }

    return $output;

}

/**
 * Change the status of a given comment from PENDING to APPROVED
 * @param int $comment_id 
 * @return bool Returns true on success and False on fail
 */
function approve_comment( $comment_id ) {
    $output = false;
    $i = 0;

    if ( comment_exist( (int)$comment_id ) ) {
        $comment_file = get_json_content( CONTENTPATH . 'comments.json' );
        $comments =& $comment_file['comments'];

        while ( $i <  count( $comments ) ) {
            if ( $comments[$i]['id'] === (int)$comment_id ) {
                $comments[$i]['status'] = 'APPROVED';
                $approved = true;
                break;
            }
            $i++;
        }

        if ( $approved === true ) {
            if ( write_to_json( CONTENTPATH . 'comments.json', $comment_file ) ) {
                $output = true;
            }
        }
    }

    return $output;
}

?>