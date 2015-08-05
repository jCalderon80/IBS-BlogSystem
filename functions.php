<?php
/**
 * Functions I use to aid myself in the development of a website.
 *
 * Disclaimer:
 * Use it at your own risk, most functions were tested fully,
 * some have not been tested completely, and not all of them 
 * log errors, feel free to collaborate if you find any bugs.
 * 
 * @author Johnny Calderon
 *
 * CONTENT:
 * 	- Constants and Variables
 * 	- Configuration Functions
 * 	- Site Data Functions
 * 	- Markup Generator Functions
 * 	- File Handler Functions
 * 	- Analytic Functions
 * 	- Social Media Functions
 *  - Post / Blog Functions
 *  - redirect functions
 *  - Classes
 *  - Error Handler Functions
 */

/*******************************************/
/*         CONSTANTS AND VARIABLES         */
/*******************************************/

/**
 * Used for checking processing time of your whole website.
 * Echo get_process-time() in the last line of your last
 * included file to get the processing time as whole.
 */
define( 'START_TIME', microtime( true ) );

/**
 * ---- IMPORTANT!!!!!!!!!
 * Set PRODUCTION to true when deploying the site to production
 */
define( 'PRODUCTION', false );

/**
 * Store configuration parameters in variable
 */
if ( is_array( break_config() ) ) {
	$config_params = break_config();
}

/**
 * Set Realpaths
 */
define( 'CONTENTPATH', dirname( __FILE__ ) . '/content/' );

if ( ! defined( 'LOCKPATH' ) ) {
    define( 'LOCKPATH', dirname( __FILE__ ) . '/admin/locker/' );
}


/**
 * The root directory of your website will be chosen according
 * to PRODUCTION constant, production path on true, development path on false
 */

if ( isset( $config_params['dev_url'] ) || PRODUCTION === true ) {
	$root = ( PRODUCTION ) ? dirname( __FILE__ ) : $config_params['dev_url'];
} else {
	$root = dirname( __FILE__ );
}

/**
 * Associative array which stores all the errors produced during
 * runtime
 */
$site_errors = array();

/****************************************/
/*        CONFIGURATION FUNCTIONS       */
/****************************************/

/**
 * Creates a json file containing main data, to use across
 * website pages.
 * NOTE: Only few functions require the configuration file directly, however other make use of the configuration file indirectly, check the
 *       documentation. It is recommended to create the file at least with the site
 *       url.
 *       charset.
 *
 * @param string $action Defaults to 'info' display configuration in HTML format,
 *               use 'create' to create the file and add settings, use 'update'
 *               to add, remove or update values.
 * @param array $settings Site's settings' parameters and values
 */
function site_config ( $action = 'info', $settings = array() ) {

	global $site_errors;
	$output = '';

	if ( $action == 'create' ) {	
		
		// Check for existing files
		if ( ! file_exists( 'site-config.json' ) ) {
			// Whether user wants json or xml
				
			/* JSON SETUP */
			$site_settings = array ( 'site_configuration' => array ( $settings ) );	
			
			$json_file = fopen( 'site-config.json' , 'w' );
			if ( fwrite( $json_file, json_encode ( $site_settings ) ) ) {
				$output = 'site-config.json created succesfully';
			} else {
				$output = 'Error writing info into site-config.json';
			}
			fclose( $json_file );
		
		} else {
			$site_errors['site_config() found a site-congif.json file already in the system'] = 'CREATE_CONFIG_ERROR';
		}
			
	} elseif ( $action == 'update' ) {
			
		/* JSON CONFIGURATION */
		$site_data = json_decode ( file_get_contents ( 'site-config.json' ), true );
		$cur_settings = $site_data['site_configuration'];
		
		foreach ( $settings as $setting => $value ) :
		
			$cur_settings[$setting] = $value;
			
		endforeach;
		
		$json_file = fopen( 'site-config.json' , 'w' );
		if ( fwrite( $json_file, json_encode ( $cur_settings ) ) ) {
			$output = 'site-config.json updated successfully';
		} else {
			$site_errors['site_config() could not update the file'] = 'UPDATE_CONFIG_ERROR';
		}

		fclose( $json_file );
	
	} elseif ( $action == 'info' ) {
			
		/* DATA FROM JSON CONFIG FILE */
		if ( file_exists ( 'site-config.json' ) ) {
			
			$site_data = json_decode ( file_get_contents ( 'site-config.json' ), true );
			
			$output .= '<div class="site-settings"><h1>SITE SETTINGS</h1><table class="settings-table" border="1"><tr><th>Setting</th><th>Value</th></tr>';
			foreach ( $site_data['site_configuration'] as $site_config ) :
				foreach ( $site_config as $setting => $value ) :
					$output .= sprintf( '<tr><td>%1$s</td><td>%2$s</td></tr>', $setting, $value );
				endforeach;
			endforeach;
			
			$output .= '</table></div><!-- .site-settings -->';
			
		} else {
			$site_errors['site_config() Could not find site-config.json'] = 'INFO_CONFIG_ERROR';
		}
	
	}

	return $output;
}

/**
 * Gets all parameters from site-config.json into an Assoc array
 * @return [Array]
 */
function break_config() {
	global $root;
	$output;

	if ( file_exists( $root . 'site-config.json' ) ) {
		$config = json_decode( file_get_contents( $root . 'site-config.json' ), true );

		$output = $config['site_configuration'];
	} else {
		$output = '';
	}

	return $output;
}

/**
 * GZIP ompression function
 */
function gzip_comp() {
	if ( substr_count ( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) ) :
		ob_start("ob_gzhandler"); 
	else :
		ob_start();
	endif;
}

/**************************************/
/*        SITE DATA FUNCTIONS         */
/**************************************/

/**
 * Get the language of the site from config file
 *
 * @return Language from json o xml file
 */
function get_site_lang () {

	global $site_errors;
	$output = '';

	if ( file_exists( 'site-config.json' ) ) {
		$settings = json_decode ( file_get_contents( 'site-config.json' ) );
		
		$output = $settings->site_configuration->lang;
		
	} else {
		$site_errors['get_site_lang() Could not find site-config.json file.'] = 'CONFIG_ERROR';
	}

	return $output;
}

/**
 * Get the current file displayed by the browser
 * 
 * @return string Returns the file name
 */
function get_current_file () {
	$file = basename ( $_SERVER['PHP_SELF'] );
	return $file;
}

/**
 * Returns the site URL from site-config.xml
 *
 * @param string $action use 'echo' to display the result, defaults to return. 
 */
function get_site_url () {
	global $site_errors, $root;

	$output = '';
	if ( file_exists ( $root . '/site-config.json' ) ) {
		
		$settings = json_decode ( file_get_contents( $root . '/site-config.json' ) );
		
		if ( isset( $settings->site_configuration->url ) && ! empty( $settings->site_configuration->url ) ) {

			$output = $settings->site_configuration->url;

		} else {
			$site_errors['URL parameter not found in configuration file'] = 'CONFIG_ERROR';
		}
		
	} else {
		$site_errors['get_site_url() Could not find site-config.json file.'] = 'CONFIG_ERROR';
	}

	return $output;
}

/**
 * Gets the requested url and returns it or echos it
 */
function get_requested_url () {
	$protocol = ( ! empty ( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ) ? "https://" : "http://";
    $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	
	return $protocol . $url;
}

/**
 * Get data from the redirected url
 * @param  string $url Requested URL
 * @return mixed       Returns an Assoc Array or False if fails.
 */
function get_redirect( $url ) {
	$found;

	$url_array = explode( '/', $url );
	$permalink = end( $url_array );

	if ( file_exists( CONTENTPATH . 'content.json' ) ) {
		$file = json_decode( file_get_contents( CONTENTPATH . 'content.json' ), true );

		$posts = $file['posts'];

		if ( ! $found = array_search( $permalink, array_column( $posts, 'url', 'id' ) ) ) {
			$found = false;
		}

	} else {
		$found = false;
	}

	return $found;

}

/**
 * Gets the file name without the extension to pass it as an ID
 * returns the result
 */
function get_page_id () {
	$file = get_current_file();
	$id = preg_replace ( '/\.php|\.html|\.css|\.js/', '', $file );
	
	return $id;
}

/**
 * Get the actual file name and displays it as the title followed by the site name
 *
 * @param string $title Defines the title for the home page if not entered will
 *              display Home, and it will change accross the pages
 * @param string $site Defines the site name if not passed a text version of the
 *               Domain will be used, this won't change accross the pages
 */
function get_page_title ( $title, $site ) {
	$output = '';
	
	if ( 'index' == get_page_id() ) {
		if ( $title == '' ) {
			$page = 'Home';
		} else {
			$page = $title;
		}
	} else {
		$raw_url = explode ( '/', get_requested_url() );
		$last_el = count ( $raw_url ) - 1;
		$raw_title = ucfirst ( str_replace ( array ( '-', '_' ), ' ', $raw_url[$last_el] ) );
		if ( preg_match ( '/(.php|.ph|.html|.htm)$/', $raw_title ) ) {
			$ar_title = explode ( '.', $raw_title );
			$page = $ar_title[0];
		} else {
			$page = $raw_title;
		}
		//$page = ucfirst ( str_replace ( array( '-', '_' ), ' ', get_page_id() ) );
	}
	$rpc = array( 'http://', 'https://', 'www.', '.com', '.net', '.biz', '.org', '.info', '.tk', '.it', '.ec' );
	$domain = ( $site == null || empty ( $site ) ) ? str_replace( $rpc, '', $_SERVER['HTTP_HOST'] ) : $site;	
	
	$output = $domain . ' | ' . $page; 
	return $output;
}

/**
 * Includes header file for the theme
 *
 * @param string $file The suffix of the file to be included
 */
function get_header ( $file = '' ) {
	global $root, $site_errors;

	$header_file = ( $file == null || $file == '' ) ? 'header.php' : 'header-' . $file . '.php';
	if ( file_exists ( $header_file ) ) {
		include $root . '/' . $header_file;
	} else {
		$site_errors['Header file could not be found'] = 'GET_ERROR';
	}
}

/**
 * Includes Footer file for the theme
 *
 * @param string $file The suffix of the file to be included
 */
function get_footer ( $file = '' ) {
	global $root, $site_errors;

	$footer_file = ( $file == null || $file == '' ) ? 'footer.php' : 'footer-' . $file . '.php';
	if ( file_exists ( $footer_file ) ) {
		include $root . '/' . $footer_file;
	} else {
		$site_errors['Footer file could not be found.'] = 'GET_ERROR';
	}
}

/**
 * Includes a sidebar file whereever it is called
 *
 * @param string $file The suffix of the file to be included e.g. sidebar-home.php
 */
function get_sidebar ( $file = '' ) {
	global $root, $site_errors;

	$sidebar_file = ( isset( $file ) && ! empty( $file ) ) ? 'sidebar-' . $file . '.php' : 'sidebar.php';

	if ( file_exists ( $sidebar_file ) ) {
		include $root . '/' . $sidebar_file;
	} else {
		$site_errors['Sidebar file could not be found'] = 'GET_ERROR';
	}
}

/****************************************/
/*      MARKUP GENERATOR FUNCTIONS      */
/****************************************/

/**
 * Add the default meta tags and any other custom meta tag to the document
 *
 * @param array $tags indicates the value and attribute of the meta tag
 * @param boolean $overwrite true: overwrites the defaults, false: display defaults
 */
function add_meta_tags ( $tags = array(), $overwrite = false ) {
	
	// didplay defaults if overwrite false
	if ( $overwrite == false ) {
		$defaults = array ( 
			array ( 'utf-8' => 'charset' ),
			array ( 'viewport' => 'name', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0' => 'content' )
		);
		array_unshift ( $tags, $defaults[0], $defaults[1] );
	}
		
	foreach ( $tags as $tag => $content ) {
		printf ( '<meta' );
		foreach ( $content as $desc => $attr ) {
			printf ( ' %1$s="%2$s"', $attr, $desc );
		}
		printf ( '/>' );
	}
}

/**
 * Add default link tags and any other custom link tags to the document
 *
 * @param array $tags indicates the file and the rel attribute of the link tags
 * @param boolea $overwrite true: overwrites default, false: display defaults
 */
function add_style_tags ( $tags = array(), $overwrite = false, $use_cache = true ) {
	global $root;

	if ( $overwrite == false ) {
		$tags = array( 'style.css' => 'stylesheet' ) + $tags;
	}

	$file_version = '';
	
	foreach ( $tags as $tag => $rel ) {
		if ( preg_match ( '/^http/', $tag ) ) {
			printf ( '<link rel="%1$s" href="%2$s"/>', $rel, $tag );
		} else {
			if ( $use_cache ) {
				$file_version = '?v' . get_file_version( $tag );
			}
			printf ( '<link rel="%1$s" href="%2$s%3$s"/>', $rel, get_site_url() . '/' . $tag, $file_version );
		}
	}
}

/**
 * Add default scripts files plus any other custom script files
 * It also adds the html5shiv.js for backward html5 compatibility for IE browsers
 * which cannot be overwritten
 *
 * @param array $files defines javascript files to use in the document and whether
 *              they are local or remote, when remote full path must be passed
 * @param boolean $overwrite true: overwrites default files, false: display defaults
 */
function add_script_tags ( $files = array(), $use_cache = true ) {
	$output = '';
	$file_version;

	$output .= '<!--[if lt IE 9]><script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->';

	$files = array( 'https://www.google.com/recaptcha/api.js' => 'remote', ) + $files;
	
	foreach ( $files as $file => $location ) {
		if( $location == 'remote' ) {
			$output .= sprintf ( '<script type="text/javascript" src="%s"></script>', $file );
		} else {
			if ( $use_cache ) {
				$file_version = '?v' . get_file_version( $file );
			}
			$output .= sprintf ( '<script type="text/javascript" src="%1$s%2$s"></script>', get_site_url() . '/' . $file, $file_version );	
		}
	}

	echo $output;
}

/**
 * Creates a navigation bar where applied, if class 'main-nav' is applied the function will echo a navigation toggle element
 * @param  [array] $args Containing the available options for the navigation bar
 * @return [type]       [description]
 */
function add_navbar ( $args ) {
	$output = '';

	// setting defaults
	
	// Position: main-nav{default}, footer-nav, side-nav, content-nav
	$position = ( isset ( $args['position'] ) && ! empty ( $args['position'] ) ) ? $args['position'] : 'main-nav';

	// Toggle_type: text{default}, icon
	$toggle_type = ( isset ( $args['toggle_type'] ) && ! empty ( $args['toggle_type'] ) ) ? $args['toggle_type'] : 'text';

	$a_class = ( isset ( $args['link_class'] ) && ! empty ( $args['link_class'] ) ) ? $args['link_class'] : '';
	$tgl_txt_class = ( isset ( $args['toggle_class'] ) && ! empty ( $args['toggle_class'] ) ) ? 'class="' . $args['toggle_class'] . '"' : '';

	$search_bar = ( isset( $args['search_bar'] ) ) ? $args['search_bar'] : true;

	// Creating the actual navbar
	$output .= sprintf ( '<nav class="%s">', $position  );
	
	if ( $position == 'main-nav' ) {
		
		$toggle_id = ( empty( $args['toggle_id'] ) || $args['toggle_id'] == null ) ? '' : 'id="' . $args['toggle_id'] . '"';
		
		$toggle_class = 'toggle-menu';
		if ( $toggle_type == 'text' ) {
			$toggle_txt = 'MENU';
		} else {
			$toggle_txt = '';
			$toggle_class .= ' toggle-icon';
		}
		
		$output .= sprintf ( '<div %1$s class="%2$s"><a %4$s >%3$s</a></div>', $toggle_id, $toggle_class, $toggle_txt, $tgl_txt_class );
	}
	
	$nav_id = ( isset( $args['nav_id'] ) && ! empty ( $args['nav_id'] ) ) ? 'id="'. $args['nav_id'] .'"' : '';
	
	$output .= sprintf ( '<ul %s class="nav-container">', $nav_id );
	foreach ( $args['nav_links'] as $nav_item => $file ) {
		/**
		 * Compares each file to the current file to apply current class to item
		 */
		$comp_file = ( $file == '/' || empty ( $file ) || $file == '#') ? 'index.php' : $file;
		$current_class = ( get_current_file() == $comp_file ) ? 'current-nav' : '';

		/**
		 * Set classes if any
		 */
		if ( ! empty( $current_class ) || ! empty( $a_class ) ) {
			$link_class = 'class="';
			$link_class .= $current_class;
			$link_class .= ( empty( $current_class) ) ? '' : ' ';
			$link_class .= $a_class;
			$link_class .= '"';
		} else {
			$link_class = '';
		}

		$output .= sprintf ( '<li><a %1$s href="%2$s">%3$s</a></li>', $link_class, get_site_url() . '/' . $file, $nav_item );
	}

	if ( $search_bar !== false ) {
		$output .= '<li class="main-search-bar">
						<form action="search-page.php" method="get">
							<input type="search" name="search" placeholder="Search...">
							<input class="search-button" type="submit" name="submit" value="">
						</form>
					</li>';
	}

	$output .= sprintf ( '</ul></nav><!-- .%s -->', $position );

	echo $output;
}

/**
 * Add a single image to an HTML page.
 * Image will be wrapped inside a figure tag
 *
 * @param array $args multiple settings that will define the image element, parent and siblings
 * 		  
 */
function add_image ( $args = array() ) {
	/*
	'path' => '',
	'class' => '',
	'img_attr' => array(),
	'link' => '',
	'link_title' => '',
	'caption' => '',
	'caption_attr' => array(),
	'caption_pos' => 'bottom'
	*/

	$output = '';

	// Default Variables
	$i_vals = '';
	$c_vals = '';
	$alt_text = '';

	// Set ID for image container
	$id = ( isset( $args['id'] ) && ! empty ( $args['id'] ) ) ? 'id="' . $args['id'] . '"' : '';
	// Set Class for image container
	$class = ( ! empty ( $args['class'] ) ) ? 'class="' . $args['class'] .'"' : '';

	// setup the img attributes
	if ( ! empty ( $args['img_attr'] ) && is_array( $args['img_attr'] ) ) {
		foreach ( $args['img_attr'] as $i_attr => $i_val ) {
			$i_vals .= ' ' . $i_attr . '="' . $i_val . '"';
		}
	}

	// Getting Image alt text
	$alt_patt = array( '/(.jpg|.png|.bmp|.svg|.gif|.jpeg)/', '/[-_]/' );
	$alt_repl = array( '', ' ' );
	$cleaned_path = preg_replace( $alt_patt, $alt_repl, $args['path'] );
	$exploded_path = explode( '/', $cleaned_path);
	$alt_text = 'alt="' . end( $exploded_path ) . '"';

	// setup caption position default value
	$caption_pos = ( isset( $arg['caption_pos'] ) && ! empty( $arg['caption_pos'] ) ) ? $arg['caption_pos'] : 'bottom';
	
	// setup caption attributes
	if ( ! empty ( $args['caption_attr'] ) ) {
		foreach ( $args['caption_attr'] as $c_attr => $c_val ) {
			$c_vals .= ' ' . $c_attr . '="' . $c_val . '"';
		}
	}
	
	// set the link titles if any
	$link_title = '';
	if ( isset( $args['link'] ) && ! empty ( $args['link'] ) ) {
		$link_title = str_replace( array( '-', '_' ), '', $args['link'] );
	}
	
	// print the whole set containing figure, img and figcation tags
	$output .= sprintf ( '<figure %1$s %2$s>', $id, $class );
	
		// top caption
		if ( $caption_pos == 'top' ) {
			if ( ! empty ( $args['caption'] ) ) {
				$output .= sprintf ( '<figcaption %1$s><span>%2$s</span></caption>', $c_vals, $args['caption'] );
			}
		}
		
		// Actual image tag
		if ( ! empty ( $args['link'] ) ) {
			$output .= sprintf ( '<a class="img-link" href="%3$s" title="%4$s"><img %1$s src="%2$s" %5$s/></a>', $i_vals, $args['path'], $args['link'], $link_title, $alt_text );
		} else {
			$output .= sprintf ( '<img %1$s src="%2$s" %3$s/>', $i_vals, $args['path'], $alt_text );
		}
		
		// bottom caption
		if ( $caption_pos == 'bottom' ) {
			if ( ! empty ( $args['caption'] ) ) {
				$output .= sprintf ( '<figcaption %1$s><span>%2$s</span></caption>', $c_vals, $args['caption'] );
			}
		}
		
	$output .= sprintf ( '</figure>' );

	echo $output;
}

/**
 * Embed an SVG file into an html document.
 * IMPORTANT: The framework is built to support modern browsers including IE9 and older
 * There is no fallback for VSG since IE9 supports SVG pretty good
 * @param  string $file the relative or full path of the image
 */
function embed_svg ( $args ) {
	$output = '';
	$path = $args['path'];
	$class = ( isset( $args['class'] ) || ! empty( $args['class'] ) ) ? 'class="' . $args['class'] . '"' : '';
	$link = ( isset( $args['link'] ) || ! empty( $args['link'] ) ) ? $args['link'] : '';


	// Get SVG file content
	$svg_array = preg_grep( '/^\<\?xml/', file( $args['path'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ), PREG_GREP_INVERT );

	$svg_content = implode( '', $svg_array );


	$output .= sprintf ( '<figure %s>', $class );
	if ( ! empty( $link ) ) {
		$output .= sprintf( '<a href="%s">', $link );
	}
	
	$output .= $svg_content;

	if ( ! empty( $link ) ) {
		$output .= '</a>';
	}
	$output .= '</figure>';

	echo $output;
}

/**
 * Creates a gallery from an especific folder in your image server's folder
 *
 * @param args array,
 * @param int $limit the number of images to display, defaults to 10
 * @param string $sort sort images in an especific order or random, defaults to ASC-name
 */
function create_gallery_from ( $args = array() ) {
	global $root;
	$output = '';
	// Set Counter, Variables, Patterns and Replaces
	$c = 0;

	// Set regex patterns to clean up images names
	$pattern = array( '/-thumb|(\.png|\.jpg|\.jpeg|\.gif|\.tiff|\.bmp|\.raw|\.jfif)/', '/-|_/', '/\&/' );
	$replace = array( '', ' ', '<br/>' );
	$image_ext = '/(\.png|\.jpg|\.jpeg|\.gif|\.tiff|\.bmp|\.raw|\.jfif)/';
	$thumb_ext = '/-thumb(\.png|\.jpg|\.jpeg|\.gif|\.tiff|\.bmp|\.raw|\.jfif)/';
	
	// Set Scanning paths and images path
 	$scan_path = ( isset( $args['folder'] ) && ! empty( $args['folder'] ) ) ? dirname ( __FILE__ ) . '/images' . $args['folder'] : dirname ( __FILE__ ) . '/images/';
	$img_path = ( isset ( $args['folder'] ) && ! empty ( $args['folder'] ) ) ? $root . '/images' . $args['folder'] : $root . '/images/';
	
	// Getting all images
 	$raw_images = array_diff( scandir ( $scan_path ), array( '.', '..' ) );

 	// Setting the limit
	$limit = ( isset ( $args['limit'] ) && ! empty ( $args['limit'] ) ) ? $args['limit'] : count( $raw_images );

	$images = array();
	
	// If $args['size'] is randome, randomize images array first
	if ( $args['order'] == 'random' ) {
		shuffle ( $raw_images );
	}
	
	// Filtering the array using size and limit arguments
	foreach ( $raw_images as $type_img ) :
	
		if ( $args['size'] == 'thumbnail' ) {
		
			if ( preg_match ( $thumb_ext, $type_img ) ) {
				if ( $c < $limit ) {
					$images[$c] = $type_img;
					$c++;
				}
			}
			
		} else {
		
			if ( preg_match ( $image_ext, $type_img ) ) {
				if ( $c < $limit ) {
					$images[$c] = $type_img;
					$c++;
				}
			}
			
		}
	endforeach;
	
	// Sort array of images
	if ( $args['order'] != 0 ) {
		arsort ( $images );
	}
	
	// Defined figcaption if true
	if ( $args['caption'] == true ) {
		$caption = '<figcaption class="caption-box">
						<p class="caption-text">%s</p>
				    </figcaption>';
	}
	
	// Finally Print it out
	foreach ( $images as $image ) :
		// Clean up name to use in alt text
		$image_name = preg_replace ( $pattern, $replace, $image );

		// Actual markup
		$output .= sprintf ( '<figure class="%s">', $args['class'] );
		
		if ( ! empty ( $args['before'] ) ) {
			$output .= $args['before'];
		}
		
		// Top Caption
		if ( $args['caption_pos'] == 'top' ) {
			$output .= sprintf ( $caption, $image_name );
		}
		
		// actual image
		$output .= sprintf ( '<img src="%1$s/%2$s" data-target"WHAT" alt="%3$s"/>', $img_path, $image, $image_name );
		
		// Bottom Caption 
		if ( $args['caption_pos'] == 'bottom' ) {
			$output .= sprintf ( $caption, $image_name );
		}
		
		if ( ! empty ( $args['after'] ) ) {
			$output .= $args['before'];
		}
		
		$output .= '</figure>';
	endforeach;

	echo $output;
}

/**
 * Adds a single custom or predefined contact form.
 * This form does not support drop down input tags such as <option>,
 * You can use the class ContactForm for <option> support and use
 * of multiple forms in one page
 * @param array $args Set of options to set the contact form
 *                    email : Recipient email is mandatory
 *                    class : Class of the contact form
 *                    textarea_placeholder : Placeholder text of textarea
 *                    						 defaults to "Type your message"
 *                    g_captcha : activate google captcha, defaults to true
 *                    captcha_options : is an array to set up options as per
 *                    					google reCaptcha docs:
 *                    					- data_sitekey
 *                    					- data_type
 *                    					- data_theme
 *                    					- data_callback
 *                    fields : an array to set up the custom input fields
 *                    		   if assoc array used, the keys will be used to
 *                    		   set <label> tags, otherwise no <label> tags
 *                    		   will be used
 *                    textarea : activate textarea field, defaults to true
 *                    submit_text : text shown submit button, defaults to "submit"
 *                    
 *                    		   			
 */
function add_contact_form ( $args = array() ) {
	global $root;
	//Set default variables
	$output = '';
	$str_p = '';
	$end_p = '';	
	$has_label = false;		// Set <label> tags off by default
	$form_class = ( isset ( $args['class'] ) && ! empty ( $args['class'] ) ) ? $args['class'] : '';
	$txtarea_plcholder = ( isset ( $args['textarea_placeholder'] ) && ! empty ( $args['textarea_placeholder'] ) ) ? $args['textarea_placeholder'] : 'Type your message';

	// Enable Google reCaptcha by default
	$g_captcha = ( isset ( $args['g_captcha'] ) ) ? $args['g_captcha'] : true;

	// Google reCaptcha options
	$captcha_sitekey = ( ! empty( $args['captcha_options']['data_sitekey'] ) ) ? $args['captcha_options']['data_sitekey'] : 'YOUR SITE KEY';
	$captcha_type = ( ! empty( $args['captcha_options']['data_type'] ) ) ? 'data-type="' . $args['captcha_options']['data_type'] . '"' : '';
	$captcha_theme = ( ! empty( $args['captcha_options']['data_theme'] ) ) ? 'data-theme="' . $args['captcha_options']['data_theme'] . '"' : '';
	$captcha_callback = ( ! empty( $args['captcha_options']['data_callback'] ) ) ? 'data-callback="' . $args['captcha_options']['data_callback'] . '"' : '';

	// Get data for hidden fields used to check google recaptcha response
	$hidden_fields = array(
			'visitor_ip' => $_SERVER['REMOTE_ADDR'],
			'visitor_useragent' => $_SERVER['HTTP_USER_AGENT']
		);
	
	// Default fields
	$fields = ( isset ( $args['fields'] ) && is_array( $args['fields'] ) ) ? $args['fields'] : array (
			array ( 'name' => 'name', 'type' => 'text', 'placeholder' => 'Name', 'required' => 'required' ),
			array ( 'name' => 'email', 'type' => 'email', 'placeholder' => 'e-mail', 'required' => 'required' ),
			array ( 'name' => 'subject', 'type' => 'text', 'placeholder' => 'Subject', 'required' => 'required' ),
		);

	// If form has been submitted include the message-script
	if ( isset ( $_POST['submit'] ) ) {
		include $root . '/message-script.php';
	}

	// Check if email address has been passed, if not advice the admin
	if ( ! isset ( $args['email'] ) || empty ( $args['email'] ) ) {
		$output = '<p>You have not declare an email, messages won&#39;t be sent</p>';
	}
	
	// if form has been submitted, store the message that was sent by the form
	if ( ! empty ( $message ) ) {
		$output .= sprintf ( $message, $_POST['name'] );
	}
	
	$output .= sprintf ( '<form class="mail-form %1$s" method="POST" action="%2$s" >', $form_class, get_requested_url() );

	// Add all the input fields into the form
	foreach ( $fields as $type => $field ) {
		$str_p = ( $field['type'] == 'hidden') ?  '' : '<p>';
		$end_p = ( $field['type'] == 'hidden') ?  '' : '</p>';
		if ( is_int ( $type ) ) {
			$output .= $str_p;
			$output .= '<input';
			foreach ( $field as $attr => $val ) {
				$output .= sprintf ( ' %1$s="%2$s"', $attr, $val );
			}	 
			$output .= '/>';
			$output .= $end_p;
		} else {
			$has_label = true;
			$output .= $str_p;
			$output .= sprintf ( '<label class="form-label">%s</label><input', $type );
			foreach ( $field as $attr => $val ) {
				$output .= sprintf ( ' %1$s="%2$s"', $attr, $val );
			}	 
			$output .= '/>';
			$output .= $end_p;
		}
	}
	
	// Include textarea if enabled
	if ( ! isset ( $args['textarea'] ) || $args['textarea'] == true ) {
		if ( $has_label ) {
			$output .= sprintf( '<p><label>%s</label>', $txtarea_plcholder );
			$txtarea_plcholder = '';
		}
		$output .= sprintf ( '<textarea name="message" placeholder="%s"></textarea>', $txtarea_plcholder );

		if ( $has_label ) {
			$output .= '</p>';
		}
	}

	// Add hidden fields
	foreach ($hidden_fields as $key => $value) {
		$output .= sprintf( '<input name="%1$s" type="hidden" value="%2$s"/>', $key, $value );
	}

	// Enable Google no Captcha ReCaptcha
	if ( $g_captcha ) {
		$output .= sprintf( '<div class="g-recaptcha" data-sitekey="%1$s" %2$s %3$s %4$s></div>', $captcha_sitekey, $captcha_type, $captcha_theme, $captcha_callback );
	}
	
	$submit_txt = ( empty ( $args['submit_text'] ) ) ? 'Send Message' : $args['submit_text'];

	$output .= sprintf ( '<input id="submit-form" name="submit" type="submit" value="%s"/>', $submit_txt );
	$output .= '</form>';

	echo $output;
}

/*************************************/
/*      FILE HANDLER FUNCTIONS       */
/*************************************/

/**
 * Get Images File name and path and returns an array
 * @param  array  $args folder: String, Path to get the images from
 *                      sort: String, Sort images by Alpha-Num or Last-Mod.
 *                      limit: Integer, Li;mit the array output.
 * @return array $output Array of image files.
 */
function get_images_from ( $args = array() ) {
	global $root;
	$output;
	$img_files = array();

	// Folder defaults to images folder in your root directory.
	$scan_path = ( isset( $args['folder'] ) && ! empty ( $args['folder'] ) ) ? $root . '/images/' . $args['folder'] : $root . '/images';

	$rel_path = ( isset( $args['folder'] ) && ! empty ( $args['folder'] ) ) ? get_site_url() . '/images/' . $args['folder'] : get_site_url() . '/images';

	$raw_files = array_diff( scandir ( $scan_path ), array( '.', '..' ) );

	$sort = ( isset( $args['sort'] ) && ! empty( $args['sort'] ) ) ? $args['sort'] : 'LMOD_DESC';

	if ( isset( $args['regex'] ) && ! empty( $args['regex'] ) ) {
		$raw_files = preg_grep( $args['regex'] , $raw_files );
	}

	// Add last date modified to array if sorted by last modified date.
	if ( $sort == 'LMOD_ASC' || $sort == 'LMOD_DESC' ) {
		foreach ( $raw_files as $file ) {
			$img_key = $rel_path . '/' . $file;
			$img_files[$img_key] = filemtime( $scan_path . '/' . $file );
		}
	} else {
		foreach ( $raw_files as $img_name ) {
			$img_path = $rel_path . '/' . $img_name;
			array_push( $img_files, $img_path );  
		}
	}

	// LMOD_ASC: Last Modified date descendant
	// LMOD_DESC (Default): Last Modified date ascendant
	// ASC: Name Numeric-Alpha Ascendant
	// DESC: Name Numeric-Alpha Descendant
	switch ( $sort ) {
		case 'LMOD_ASC':
			asort( $img_files );
			$img_files = array_keys( $img_files );
			break;
		case 'ASC':
			sort( $img_files );
			break;
		case 'DESC':
			rsort( $img_files );
			break;
		default:
			arsort( $img_files );
			$img_files = array_keys( $img_files );
			break;
	}


	// Get the files needed by limit
	if ( isset( $args['limit'] ) && ! empty( $args['limit'] ) ) {
		$output = array_slice( $img_files, 0, $args['limit']);
	} else {
		$output = $img_files;
	}

	return ( empty( $output ) ) ? false : $output;
}

/**
 * Get Json File Content
 * Returns an array of the json file content limited by the limit option;
 *
 * @param $args array Set of options to customize the output
 */
function get_json_content( $fullpath ) {
    $output = false;

	// Set the absolute path of the file
	$file = $fullpath;

    if( file_exists( $file ) ) {
        $output = json_decode ( file_get_contents ( $file ), true );
    }

	// return the full object
	return $output;
}

/**
 * [write_to_json description]
 * @param  [type] $data [description]
 * @param  [type] $file [description]
 * @return [type]       [description]
 */
function write_to_json( $file, $data ) {
	$output = false;
	$handler = fopen($file, 'w');

    if( is_array( $data ) ) {
        if ( fwrite( $handler, json_encode( $data ) ) ) {
			$output = true;
		}
    }

	fclose( $handler );

	return $output;
}

/**
 * Returns a version based on the modified time stamp of the file
 * Format: [Month integer].[day of the year integer].[HourMinuteSecond]
 * @param  string $path File path
 * @return string       [description]
 */
function get_file_version ( $path ) {
	global $root;
	$output = '';

	// Get file last modified time stamp
	$mod_time = filemtime( $root . '/' . $path );
	// Break the time into a version format
	$output = date( 'n.z.Gis' , $mod_time);

	return $output;
}

/*************************************/
/*       ANALYTICS FUNCTIONS     	 */
/*************************************/

/**
 * Add Google Analytics
 */
function add_analytics ( $args = array() ) {
	$ggl_ID = $args['google_ID'];
	$ggl_domain = $args['site_domain'];
	
	$analytics = '<script>
		(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,"script","//www.google-analytics.com/analytics.js","ga");
		ga("create", "%1$s", "%2$s");
		ga("send", "pageview");
	</script>';
	
	printf( $analytics, $ggl_ID, $ggl_domain );
}

/*****************************************/
/*        SOCIAL MEDIA FUNCTIONS         */
/*****************************************/
/**
 * facebook url to get counts
 * https://api.facebook.com/method/fql.query?query={select total_count,like_count,comment_count,share_count,click_count from link_stat where url='http//domain.com'}&format=json"
 * Code to get counts
 * $query = "select total_count,like_count,comment_count,share_count,click_count from link_stat where url='{$url}'";
 * $call = "https://api.facebook.com/method/fql.query?query=" . rawurlencode($query) . "&format=json";
 * $ch = curl_init();
 * curl_setopt($ch, CURLOPT_URL, $call);
 * curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 * $output = curl_exec($ch);
 * curl_close($ch);
 * return json_decode($output);
 */
 
/**
 * Twitter URL to get counts for URL's
 * http://urls.api.twitter.com/1/urls/count.json?url=yoururl.com
 */
 
/**
 * Social Widgets Class
 */
function social_init( $args = array() ) {
	
	// Facebook SDK Sccript
	$fb_script = '<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/%1$s/sdk.js#xfbml=1&appId=%2$s&version=v2.0";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, "script", "facebook-jssdk"));</script><!-- facebook script -->';
		
	// Google Script
	$gg_script = '<script type="text/javascript">
		%s
		(function() {
	    var po = document.createElement("script"); po.type = "text/javascript"; po.async = true;
	    po.src = "https://apis.google.com/js/platform.js";
	    var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(po, s);
	  })();
	</script><!-- google plus script -->';
	
	// Pinterest Script
	$pin_script = '<script type="text/javascript" %s async src="//assets.pinterest.com/js/pinit.js"></script><!-- pinterest script -->';
	
	// Twitter script
	$tw_script = '<script>
	!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location) ? "http":"https";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document, "script", "twitter-wjs");</script><!-- twitter Script -->';
	
	// Linkedin script
	$in_script = '<script src="//platform.linkedin.com/in.js" type="text/javascript">lang: %s</script><!-- linkedin Script -->';	
		
	// Languages
	$lang_ggcodes = array (
		'spanish_latin' => 'es-419',
		'spanish' => 'es',
		'english_UK' => 'en-GB',
		'english_USA' => 'en-US',
		'portugues_BRA' => 'pt-BR',
		'portugues_POR' => 'pt-PT',
		'french' => 'fr'
	);
	
	$lang_fbcodes = array (
		'spanish_latin' => 'es_LA',
		'spanish' => 'es_ES',
		'english_UK' => 'en_GB',
		'english_USA' => 'en_US',
		'portugues_BRA' => 'pt_BR',
		'portugues_POR' => 'pt_PT',
		'french' => 'fr_FR'
	);
	
	// Set Variables
	$fb_ID = (string)$args['facebook_APPID'];
	if ( empty ( $args['language'] ) || $args['language'] == 'english_USA' ) :
		$gg_lang = '';
		$fb_lang = 'en_US';
	else :
		$gg_lang = 'window.___gcfg = {lang: "' . $lang_ggcodes[$args['language']] . '"};';
		$fb_lang = $lang_fbcodes[$args['language']];
	endif;
	
	// Set Pinterest variables
	$pin_hover;
	if ( $args['pin_hover'] == true ) {
		$pin_hover = 'data-pin-hover="true"';
	} else {
		$pin_hover = '';
	}
	
	// Print facebook script
	if ( $args['facebook'] == true || empty ( $args['facebook'] ) || $args['facebook'] == null ) {
		printf ( $fb_script, $fb_lang, $fb_ID );
	}
	
	// Print google script
	if ( $args['google'] == true || empty ( $args['google'] ) || $args['google'] == null ) {
		printf ( $gg_script, $gg_lang );
	}
	
	// Print pinterest script
	if ( $args['pinterest'] == true || empty ( $args['pinterest'] ) || $args['pinterest'] == null ) {
		printf ( $pin_script, $pin_hover );
	}
	
	// Print twitter script
	if ( $args['twitter'] == true || empty ( $args['twitter'] ) || $args['twitter'] == null ) {
		printf ( $tw_script );
	}
	
	// Print linkedin script
	if ( $args['linkedin'] == true || empty ( $args['linkedin'] ) || $args['linked'] == null ) {
		printf ( $in_script, $fb_lang );
	}
}

/**
 * Like Bar
 * Includes facebook likes, +1's
 */
function likes_bar ( $args = array() ) {
	$bar_id = ( empty ( $args['id'] ) || $args['id'] == null ) ? '' : 'id="' . $args['id'] . '"';
	$bar_class;
	$fb_url;
	
	if ( $args['facebook']['data-href'] == null || empty ( $args['facebook']['data-href'] ) ) :
		if ( get_page_id() == 'index' ) {
			$fb_url = get_site_url();
		} else {
			$fb_url = get_site_url() . '/' . get_current_file();
		}
	else :
		$fb_url = $args['facebook']['data-href'];
	endif;
	
	// Facebook defaults
	$fb_defaults = array(
		'data-layout' => 'button_count',
		'data-action' => 'like',
		'data-show-faces' => 'false',
		'data-share' => 'false',
		'data-colorscheme' => 'light',
	);
	
	// Google defaults
	$gg_defaults = array(
		'data-size' => 'medium',
		'data-annotation' => 'bubble',
		'data-align' => 'left',
		'expandTo' => '',
		'data-recommendations' => 'false',
	);
	 
	// Assign defaults to variables
	$fb_options = ( empty ( $args['facebook'] ) || $args['facebook'] == null )? $fb_defaults : $args['facebook'];
	$gg_options = ( empty ( $args['google'] ) || $args['google'] == null )? $gg_defaults : $args['google'];
	
	if ( $args['bar_style'] == 'standard' || empty ( $args['bar_style'] ) || $args['bar_style'] == null ) :
	
		// set class attribute for like bar
		$bar_class = 'standard-bar';
		printf ( '<div class="like-bar %s"><ul>', $bar_class );
		
		if ( $args['facebook'] != false || empty ( $args['facebook'] ) || $args['facebook'] == null ) {
			printf ( '<li class="wgt-button"><div class="fb-like"' );
			foreach ( $fb_options as $data => $value ) :
				printf ( ' %1$s="%2$s"', $data , $value );
			endforeach;
			printf ( '></div><!-- .fb-like --></li>' );
		}
		
		if ( $args['google'] != false || empty ( $args['google'] ) || $args['google'] == null ) {
			printf ( '<li class="wgt-button"><div class="g-plusone"' );
			foreach ( $gg_options as $data => $value ) :
				printf ( ' %1$s="%2$s"', $data , $value );
			endforeach;
			printf ( '></div><!-- .g-plusone --></li>' );
		}
			
		printf ( '</ul></div>' );
		
	elseif ( $args['bar_style'] == 'drawer' ) :
		
		// Set class attribute for like bar
		$bar_class = 'drawer-bar';
		$fb_counts = reset ( get_fb_counts( $fb_url ) );
	
		printf ( '<div class="like-bar %1$s"><ul><li class="wgt-button"><div class="fb-top wgt-cover"><span class="count-box">%2$s</span></div><div class="fb-like"', $bar_class, $fb_counts->like_count );
		foreach ( $fb_options as $data => $value ) :
			printf ( ' %1$s="%2$s"', $data , $value );
		endforeach;
		
		printf ( '></div><!-- .fb-like --></li><li class="wgt-button"><div class="gg-top wgt-cover"><span class="count-box">%s</span></div><div class="g-plusone"', $gg_counts );
		foreach ( $gg_options as $data => $value ) :
			printf ( ' %1$s="%2$s"', $data , $value );
		endforeach;
		printf ( '></div><!-- .g-plusone --></li></ul></div>' );
		
	endif;	
}

/**
 * Share bar
 * Includes social buttons to share page url: Facebook, Google+, Twitter, Pinterest and Linkedin
 */
function share_bar ( $args = array() ) {
	
	// Facebook defaults
	$fb_options = array(
		// data-href : Actual url if not defined
		// data-width : Depends on layout
		'data-type' => 'button_count' // box_count, button, icon
	);
	
	// Google defaults
	$gg_options = array(
		// data-href : Actual url if not defined
		// data-width
		// data-height
		'data-annotation' => 'bubble', // inline, vertical-bubble, none
		'data-align' => 'left', // right
		'data-expandTo' => '' // left, top, right, bottom
	);
	
	// Twitter defaults
	$tw_options = array(
		// data-url :actual url if not defined
		// data-via : rel="me"
		// data-text : <title> text
		// data-related :Recommended accounts
		// data-hashtag : 
		// data-counturl : URL being shared
		// data-size
		'data-count' => 'horizontal', // vertical, none
		'data-lang' => 'en',
		'data-dnt' => 'true',
	);
	
	$tw_language = array(
		'en' => 'Tweet',
		'es' => 'Twittear'
	);
	
	// Pinterest defaults
	$pin_options = array(
		// pin-url : Url of site if not defined button will select current url
		// pin-image : Image to use, if not defined button will choose any image available
		// 'data-pin-shape' => 'round' // when not specified shows rectangular button
		// data-pin-height : 20 small, 28 large
		'data-pin-color' => 'white', // Gray or Red
		'data-pin-config' => 'beside' // above, none
	);
	
	// Linkedin defaults
	$in_options = array(
		// data-url : The actual url if not defined
		'data-counter' => 'right' //Top
	);
	
	// Assign options to variables
	if ( is_array( $args['facebook'] ) ) {
		foreach ( $args['facebook'] as $key => $value ) {
			$fb_defaults[$key] = $value;
		}
	}
	
	if ( is_array( $args['google'] ) ) {
		foreach ( $args['google'] as $key => $value ) {
			$gg_options[$key] = $value;
		}
	}
	
	if ( is_array( $args['twitter'] ) ) {
		foreach ( $args['twitter'] as $key => $value ) {
			$tw_options[$key] = $value;
		}
	}
	
	if ( is_array( $args['pinterest'] ) ) {
		foreach ( $args['pinterest'] as $key => $value ) {
			$pin_options[$key] = $value;
		}
	}
	
	if ( is_array( $args['linkedin'] ) ) {
		foreach ( $args['linkedin'] as $key => $value ) {
			$in_options[$key] = $value;
		}
	}
	
	if ( $args['bar_style'] == 'standard' || $args['bar_style'] == null || empty( $args['bar_style'] ) ) :
	
		$bar_class = 'standard-bar';
		
		// print starting container
		printf ( '<div class="share-bar %s"><ul>', $bar_class );
		
		if ( ! empty ( $args['bar_text'] ) ) {
			printf ( '<li class="wgt-text" >%s</li>', $args['bar_text'] );
		}
		
		// Print facebook share
		if ( $args['facebook'] !== false ) {
			printf ( '<li class="wgt-button"><div class="fb-share-button"' );
			foreach ( $fb_options as $data => $value ) :
				printf ( ' %1$s="%2$s"', $data, $value );
			endforeach;
			printf ( '></div></li><!-- facebook -->' );
		}
		
		// Print google share
		if ( $args['google'] !== false ) {
			printf ( '<li class="wgt-button"><div class="g-plus" data-action="share"' );
			foreach ( $gg_options as $data => $value ) :
				printf ( ' %1$s="%2$s"', $data, $value );
			endforeach;
			printf ( '></div></li><!-- googleplus -->' );
		}
		
		// Print linkedin share
		if ( $args['linkedin'] !== false ) {
			printf ( '<li class="wgt-button"><script type="IN/Share"' );
			foreach ( $in_options as $data => $value ) :
				printf ( ' %1$s="%2$s"', $data, $value );
			endforeach;
			printf ( '></script></li><!-- linkedin -->' );
		}
		
		// Print Pin it button
		if ( $args['pinterest'] !== false ) {
			
			$pin_do;
			$pin_urlencoded;
			$pin_button_image;
			
			// Pin Button images
			$pin_large = array (
				'rect' => 'pinit_fg_en_rect_%s_28.png',
				'round' => 'pinit_fg_en_round_red_28.png'
			);
			
			$pin_height = array(
				'small' => '20',
				'large' => '28'
			);
			
			$pin_small = array(
				'rect' => 'pinit_fg_en_rect_%s_20.png',
				'round' => 'pinit_fg_en_round_red_16.png'
			);
			
			// Set the url and image to be pinned if pin-image is defined
			if ( ! empty ( $pin_options['pin-image'] ) ) :
				
				$pin_urlencoded = '?url=';
				$pin_urlencoded .= ( $pin_options['pin-url'] == null || empty ( $pin_options['pin-url'] ) ) ? rawurlencode ( get_site_url() . '/' . get_current_file() ) : rawurlencode ( $pin_options['pin-url'] );
				$pin_urlencoded .= '&media=' . rawurlencode ( $pin_options['pin-image'] );
				$pin_do = 'buttonPin';
			else :
				$pin_urlencoded = '';
				$pin_do = 'buttonBookmark';
			endif;
			
			// Set button image according to data-pin-height, data-pin-color and data-pin-shape
			if ( empty ( $pin_options['data-pin-shape'] ) || $pin_options['data-pin-shape'] == null || $pin_options['data-pin-shape'] == 'rect' ) {
			
				if ( empty ( $pin_options['data-pin-height'] ) || $pin_options['data-pin-height'] == null || $pin_options['data-pin-height'] == 'small' ) {
					$pin_button_image = $pin_small['rect'];
				} elseif ( $pin_options['data-pin-height'] == 'large' ) {
					$pin_button_image = $pin_large['rect'];
				}
				
			 } else {
			
				if ( empty ( $pin_options['data-pin-height'] ) || $pin_options['data-pin-height'] == null ) {
					$pin_button_image = $pin_small[$pin_options['data-pin-shape']];
				} elseif ( $pin_options['data-pin-height'] == 'large' ) {
					$pin_button_image = $pin_large[$pin_options['data-pin-shape']];
				}
			
			}
			
			// Start Printing actual button
			printf ( '<li class="wgt-button">' );
			printf ( '<a href="//www.pinterest.com/pin/create/button/%1$s" data-pin-do="%2$s" data-pin-height="%3$s"', $pin_urlencoded, $pin_do, $pin_height[$pin_options['data-pin-height']] );
			
			foreach ( $pin_options as $data => $value ) :
				if ( $data != 'pin-url' || $data != 'pin-image' || $data != 'data-pin-height' ) {
					printf ( ' %1$s="%2$s"', $data, $value );
				}
			endforeach;
			
			printf ( '><img src="//assets.pinterest.com/images/pidgets/' );
			printf ( $pin_button_image, $pin_options['data-pin-color'] );
			printf ( '"/></a></li><!-- pinterest -->' );
		}
		
		// Print tweet button
		if ( $args['twitter'] !== false ) {
			printf ( '<li class="wgt-button"><a href="https://twitter.com/share" class="twitter-share-button"' );
			foreach ( $tw_options as $data => $value ) :
				printf ( ' %1$s="%2$s"', $data, $value );
			endforeach;
			printf ( '>%s</a></li><!-- twitter -->', $tw_language[$tw_options['data-lang']] );
		}
				
		printf ( '</ul></div>' );
	
	elseif ( $args['bar_style'] == 'drawer' ) :
	
	endif;
}

/**
 * Get facebook pagefeed widget
 * @param  array  $args [description]
 * @return [type]       [description]
 */
function fb_pagefeed ( $args = array() ) {
	$href = $args['href'];
	$data_href = 'data-href="' . $href . '"';
	$data_hide_cover = ( isset ( $args['hide_cover'] ) && ! empty( $args['hide_cover'] ) ) ? 'data-hide-cover="' . $args['hide_cover'] . '"' : 'data-hide-cover="false"';
	$data_show_facepile = ( isset ( $args['show_friend_faces'] ) && ! empty( $args['show_friend_faces'] ) ) ? 'data-show-facepile="' . $args['show_friend_faces'] . '"' : 'data-show-facepile="true"';
	$data_show_posts = ( isset ( $args['show_posts'] ) && ! empty( $args['show_posts'] ) ) ? 'data-show-posts="' . $args['show_posts'] . '"' : 'data-show-posts="false"';
	$data_width = ( isset( $args['width'] ) && ! empty ( $args['width'] ) ) ? 'data-width="' . $args['width'] . '"' : '';
	$data_height = ( isset( $args['height'] ) && ! empty ( $args['height'] ) ) ? 'data-height="' . $args['height'] . '"' : '';

	$pattern = array( '/http:\/\/facebook.com\//', '/\.-_/' );
	$replace_pattern = array( '', ' ' );
	$blockquote = preg_replace( $pattern, $replace_pattern, $href );

	//Pagefeed markup
	$page_box = sprintf ( '<div class="fb-page" %1$s %2$s %3$s %4$s %5$s %6$s><div class="fb-xfbml-parse-ignore"><blockquote cite="%7$s"><a href="%7$s">%8$s</a></blockquote></div></div>', $data_href, $data_hide_cover, $data_show_facepile, $data_show_posts, $data_width, $data_height, $href, $blockquote );

	echo $page_box;
}

/**
 * Get facebook counts
 */
function get_fb_counts ( $url ) {
	$request = "select total_count,like_count,comment_count,share_count,click_count from link_stat where url='{$url}'";
	$call = 'https://api.facebook.com/method/fql.query?query=' . rawurlencode( $request ) . '&format=json';	
	//	$ch = curl_init();
	//	curl_setopt( $ch, CURLOPT_URL, $call );
	//	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	//	$counts = curl_exec( $ch );
	//	curl_close( $ch );
	return json_decode( file_get_contents( $call ) );
	//return json_decode( $counts );
}

/****************************/
/*     BLOG FUNCTIONS       */
/****************************/

/* The following functions are set to work with post or blog content files.
 * These files are: *.content and a global file called content.json
 * content.json file contains a list of all the available posts with some their meta-tags
 * *.content files contain the body of the post, noting else
 * *.content files are simply named after the title of the blog post or post
 * ex. my-first-blog-post.content, the-last-post.content
 */


/**
 * Get an array of all global settings
 * @return mixed
 */
function get_post_settings() {
    $p_settings = get_json_content( LOCKPATH . 'posts-config.json' );

    return $p_settings['postssettings'];

}

/**
 * Get a list of all the existing posts
 * @return array|boolean Returns array of posts if success, false if fails.
 */
function get_posts_list ( $status = 'all', $sort = 'ID_ASC' ) {
	global $root;

    $output = false;
    $sort_dimension = array();
	$posts_dir = $root . '/content/content.json';

	if ( file_exists( $posts_dir ) ) {
        $post_array = json_decode( file_get_contents( $posts_dir ), true );

        if ( is_array( $post_array ) && isset( $post_array['posts'] ) && ! empty( $post_array['posts'] ) ) {
            $output = $post_array['posts'];
            $post_count = count( $output );

            if ( $status !== 'all' ) {
                for ($i = 0; $i < $post_count; $i++) {
                    if ( $output[$i]['status'] != $status ) {
                        unset( $output[$i] );
                    }
                }
            }

            switch ( $sort ) {
                case 'ID_ASC':
                    usort( $output, 'int_id_sort' );
                    break;
                case 'ID_DESC':
                    usort( $output, 'int_id_rsort' );
                    break;
                case 'ASC':
                    usort( $output, 'str_title_sort' );
                    break;
                case 'DESC':
                    usort( $output, 'str_title_rsort' );
                    break;
                case 'LMOD_ASC':
                    usort( $output, 'str_date_sort' );
                    break;
                case 'LMOD_DESC':
                    usort( $output, 'str_date_rsort' );
                    break;
            }
        }
    }
    

    return $output;
}

/**
 * Summary of get_post_data_byID
 * @param mixed $post_id 
 * @return mixed
 */
function get_post_data_byID( $post_id ) {
    $output = false;

    if ( post_exist( (int)$post_id ) ) {
        $posts = get_posts_list();

        foreach ( $posts as $post ) {
    	    if ( $post['id'] === (int)$post_id ) {
                $output = $post;
                break;
            }
        }
    }

    return $output;
    
}


function get_post_content_byID ( $post_id ) {
    $post = get_post_data_byID( $post_id );
    $output = false;

    if ( file_exists( CONTENTPATH . $post['content'] ) ) {
        $output = file_get_contents( CONTENTPATH . $post['content'] );
    }

    return $output;
}

/**
 * Uses get_post_list() to retrieve an Assoc array with
 * post id + filename
 * @return Array           Returns an array of ID -> post File Name
 */
function get_postID_list () {
	$post_list = get_post_list();
	$id_pattern = '/^[0-9]*/';
	$post_files = array();

	foreach ( $post_list as $post_json ) {
		preg_match( $id_pattern , $post_json, $key_id );
		$post_files[$key_id[0]] = $post_json;
	}

	return $post_files;
}


/**
 * Check if a posts exist by using an ID
 * @param int $post_id Pass the id your're looking for
 * @return bool true if exist, false if it doesn't
 */
function post_exist( $post_id ) {
    $posts = get_posts_list();
    $output = false;
    $i = 0;
    
    if ( is_array( $posts ) ) {
        while ( $i < count( $posts )  ) {
            if( $posts[$i]['id'] === $post_id ) {
                $output = true;
                break;
            }
            $i++;
        }
    }

    return $output;
}

function postlink_exist( $url, $cat = '' ) {
    $posts = get_posts_list();
    $output = false;

    if ( is_array( $posts ) ) {
        foreach( $posts as $post ) {
            if( ! empty( $cat ) ) {
                if ( $url == $post['url'] && $cat == $post['category'] ) {
                    $output = true;
                    break;
                }
            } else {
                if( $url == $post['url'] ) {
                    $output = true;
                    break;
                }
            }
        }
    }

    return $output;
}

/**
 * 
 * 
 */
function get_post_status( $post_id ) {
    $output = false;

    if ( post_exist( (int)$post_id ) ) {
        $posts = get_posts_list();
        $i = 0;

        while( $i < count( $posts ) ) {
            if( $posts[$i]['id'] === (int)$post_id ) {
                $output = $posts[$i]['status'];
                break;
            }
            $i++;
        }
    }
    return $output;

}

function get_post_meta() {
}

function get_content_byFILENAME( $content_file_name ) {
    $output = false;
    $file = CONTENTPATH . $content_file_name;
    if ( file_exists( $file ) ) {
        $output = file_get_contents( $file );
    }

    return $output;
}

function get_post () {
    $output = false;
    $id_reg = '/^[0-9]+$/';
    $proceed = false;
    $post = '';
    $header_markup = '';
    $body_markup = '';
    $footer_markup = '';
    $cat = '';

    if( isset( $_GET['postid'] ) ) {
        if( post_exist( (int)$_GET['postid'] ) ) {
            $post = get_post_data_byID( (int)$_GET['postid'] );
        }
    } else if( isset( $_GET['url'] ) ) {
        $cat = ( isset( $_GET['cat'] ) ) ? $_GET['cat'] : '';
        if ( postlink_exist( $_GET['url'], $cat ) ) {
            $post = get_post_data_byLINK( $_GET['url'] );
        }
    }

    if ( ! empty( $post ) && is_array( $post ) && $post['status'] === 'ACTIVE' ) {
        //Get global settings
        $global_set = get_post_settings();
        $sb = $global_set['socialbar'];
        $sbpos = $sb['socialbarposition'];

        //Get All dependencies driven by conditionals
        //Get social bars
        $sb_markup = '';
        if( $sb['socialbarshow'] === '1' ) {
            $sb_markup .= '<div class="post-social-container">';
            switch ( $global_set['socialbar']['socialbartype'] ) {
                case 'LIKE':
                    //like_bar();
                    break;
                case 'SHARE':
                    //share_bar();
                    break;
                case 'SHARE_LIKE':
                    //like_bar()
                    //share_bar();
                    break;
            }
            $sb_markup .= '</div>';
        }

        //Get Writter Card
        $author_card = ( $global_set['allowwritercard'] == '1' ) ? get_author_card( $post['author']['id'] ) : '';

        //Get Comment thread if any
        $comment_thread = ( $post['comments']['enable'] == '1' ) ? display_comment_thread( (int)$post['id'] ) : '';
            
        //Setup markup driven by conditions
        //Setup the excerpt and header image if any.
        $header_excerpt = '';
        $excerpt_markup = ( ! empty( $post['excerpt'] ) ) ? '<div class="post-except">' . $post['excerpt'] . '</div>' : '' ;
        $image_markup = ( ! empty( $post['image'] ) ) ? sprintf ( '<figure class="post-header-image"><img src="%1$s" alt="%1$s"></figure>', get_site_url() . '/images/upload/' . $post['image'] ) : '';

        //Put all the excerpt and header image together
        $header_excerpt .= $image_markup;
        $header_excerpt .= $excerpt_markup;

        //Set post markup by sections: HEADER BODY & FOOTER
        //HEADER
        $h_markup = '<article id="post-%1$s" class="full-post">';
        $h_markup .= '<header class="post-header">';
        $h_markup .= '<h1 class="post-title">%2$s</h1>';
        $h_markup .= '<div class="post-meta">';
        $h_markup .= '<ul>';
        $h_markup .= '<li>Published on: <strong  class="post-meta-date">%3$s</strong></li>';
        $h_markup .= '<li>Written by: <a class="post-meta-author" href="%4$s">%5$s</a></li>';
        $h_markup .= '</ul>';
        $h_markup .= '</div>%7$s %6$s </header>';
        $header_markup = sprintf( $h_markup,
            $post['id'],                                //1
            $post['title'],                             //2
            date( 'Y-m-d', strtotime( $post['date'] ) ),//3
            $post['author']['url'],                     //4
            $post['author']['name'],                    //5
            ( $sbpos === 'TOP' || $sbpos === 'TOP_BOTTOM' ) ? $sb_markup : '' ,    //6
            $header_excerpt                             //7
        );

        //BODY
        $body_markup = sprintf( '<div class="post-body-wrapper"><section class="post-body">
                <div class="post-content">
                    %s
                </div>
            </section>',
             get_content_byFILENAME( $post['content'] )
        );

        //FOOTER
        $footer_markup = sprintf( '</div><footer class="post-footer">
                %1$s <!-- SOCIAL -->
                %2$s <!-- AUTHOR CARD -->
                %3$s <!-- COMMENT THREAD -->
                </footer>
            </article>',
            ( $sbpos === 'BOTTOM' || $sbpos === 'TOP_BOTTOM' ) ? $sb_markup : '', //1
            $author_card,                                                         //2
            $comment_thread                                                       //3
        );
    }

    $output .= '<div class="article-wrapper">';
    $output .= $header_markup;
    $output .= $body_markup;
    $output .= get_post_sidebar( (int)$post['id'], 5 );
    $output .= $footer_markup;
    $output .= '</div>';

    if ( empty( $output ) ) {
        $output = '<div class="not-found"><h1>SORRY!, The page you are looking for does not exist.</h1></div>';
    }

    echo $output;
    return;
}

function get_post_data_byLink( $url ) {
    $i = 0;
    $posts = get_posts_list('ACTIVE');
    $found_post = '';

    while ( $i < count( $posts ) ) {
        if ( $url === $posts[$i]['url'] ) {
            $found_post = $posts[$i];
            break;
        }
    	$i++;
    }
    return $found_post;
}

function get_post_title ( $post_id ) {
    $output = false;

    if ( post_exist( (int)$post_id ) ) {
        $post = get_post_data_byID( (int)$post_id );
        $output = $post['title'];
    }

    return $output;
    
}

function get_blog_feed() {
    $output = false;
    $posts = get_posts_list( 'ACTIVE', 'LMOD_DESC' );

    if ( $posts !== false ) {
        $output = '<div class="blog-feed-container">';
        foreach( $posts as $post ) {
            $output .= '<article id="excerpt-post-' . $post['id'] . '" class="post-excerpt">';
            $output .= '<header class="post-excerpt-header">';
            $output .= '<h1 class="post-excerpt-title">';
            $output .= '<a href="' . get_site_url() . '/blog/' . $post['url'] . '">' . $post['title'] . '</a></h1>';
            $output .= '<div class="post-excerpt-meta">';
            $output .= '<p>Posted on: ';
            $output .= '<strong class="post-excerpt-date">' . $post['date'] . '</strong>, By: ';
            $output .= '<strong class="post-excerpt-author">';
            $output .= '<a href="' . $post['author']['url'] . '">' . $post['author']['name'] . '</a>';
            $output .= '</strong></p>';
            $output .= '</div>';
            $output .= '</header>';
            if ( ! empty( $post['excerpt'] ) ) {
                $output .= '<section class="post-excerpt-content">';
                $output .= '<p>' . $post['excerpt'] . '</p>';
                $output .= '</section>';
            }
            $output .= '<footer class="post-excerpt-actions">';
            $output .= '<p class="post-excerpt-link"><a href="' . get_post_url() . '/blog/' . $post['url'] . '">Read full article.</a></p>';
            $output .= '</footer>';
            $output .= '</article>';
        }
        $output .= '</div>';
    }

    if ( ! $output ) {
        $output = '<div class="not-found">Keep in touch, for new articles.</div>';
    }

    echo $output;
    return;

}

function get_post_sidebar( $post_id, $limit ) {
    $output = '';
    $i = 0;
    $posts = get_posts_list( 'ACTIVE', 'LMOD_DESC' );

    if ( is_array( $posts ) && ! empty( $posts ) ) {
        $output = '<aside class="related-posts">';
        $output .= '<h3>Keep Reading</h3>';
        foreach( $posts as $post ) {
            if ( $post['id'] !== (int)$post_id && $i < $limit ) {
                $url = get_site_url() . '/blog/' . $post['url'];
                $date = date( 'Y-m-d', strtotime( $post['modified'] ) );
                $output .= '<article id="post-' . $post['id'] . '" class="quick-post">';
                $output .= '<header class="quick-post-header">';
                $output .= '<h1 class="quick-post-title"><a href="' . $url . '">' . $post['title'] . '</a></h1>';
                $output .= '<div class="quick-post-date">';
                $output .= '<p>' . $date . '</p>';
                $output .= '</div>';
                $output .= '</header>';
                $output .= '<footer class="quick-post-footer">';
                $output .= '<a class="quick-post-link" href=""></a>';
                $output .= '</footer>';
                $output .= '</article>';

                $i++;
            }
        }
        $output .= '</aside>';
    }

    return $output;
}

function get_blog_sidebar() {
}

/************************/
/*   AUTHOR FUNCTIONS   */
/************************/

function get_user_byNAME( $author_name ) {
    $output = false;
    $users = get_users_list();

    if ( $users !== false ) {
        foreach( $users as $user ) {
            if( $user['name'] === $author_name ) {
                $output = $user;
                break;
            }
        }
    }

    unset( $output['secretphrase'] );

    return $output;
}

function get_user_byID( $user_id ) {
    $users = get_users_list();
    $i = 0;
    $output = false;

    while( $i < count( $users ) ) {
        if ( (int)$users[$i]['id'] === (int)$user_id ) {
            $output = $users[$i];
            break;
        }
        $i++;
    }

    unset( $output['secretphrase'] );

    return $output;
}

function get_users_list() {
    $output = false;
    $users_file = get_json_content( LOCKPATH . 'users-config.json' );

    if ( $users_file !== false ) {
        $output = $users_file['users'];
    }

    return $output;
}

function get_author_card( $user_id ) {
    $output = false;
    $user = get_user_byID( (int)$user_id );


    if ( $user !== false ) {
        $card = '<aside id="author-%1$s" class="author-card">';
        $card .= '<h2>About the Author</h2>';
        $card .= '<address class="author-card-data">';
        $card .= '<h3>%2$s</h3>';
        $card .= '%3$s';
        $card .= '%4$s';
        $card .= '</address>';
        $card .= '</aside>';

        $output = sprintf( $card,
                $user['id'],
                ( empty( $user['name'] ) ) ? $user['username'] : $user['name'],
                ( ! empty( $user['url'] ) ) ? '<p><a href="' . $user['url'] . '">' . $user['url'] . '</a></p>' : '',
                ( ! empty( $user['description'] ) ) ? '<p>' . $user['description'] . '</p>' : ''
            );
    }
    return $output;
}

/************************/
/*  COMMENTS FUNCTIONS  */
/************************/

function get_comments_list( $status = 'ALL' ) {
    $output = false;
    $i = 0;
    $comments_file = get_json_content( CONTENTPATH . 'comments.json' );
    $comments =& $comments_file['comments'];
    if ( $comments_file ) {

        $count = count( $comments );

        if ( $status != 'ALL' ) {
            for ($i = 0; $i < $count; $i++) {
                if( $comments[$i]['status'] != $status ) {
                    unset( $comments[$i] );
                }
            }
        }
        
        $output = $comments;
    }

    return $output;

}

function get_comment_data_byID( $comment_id ) {
    $output = false;
    if ( comment_exist( (int)$comment_id ) ) {
        $comments = get_comments_list();

        foreach ( $comments as $comment ) {
            if ( $comment['id'] === (int)$comment_id ) {
                $output = $comment;
                break;
            }
        }
    }

    return $output;
}

/**
 * Display a comment form to post new comments
 * and display the whole comment thread
 * @param int $pos_id
 */
function display_comment_thread( $post_id ) {
    $output = false;
    $count = 0;

    if ( post_exist( (int)$post_id ) ) {
        $coms = get_comments_byPOST( (int)$post_id );
        $count += count( $coms['comment_parent'] );
        $count += count( $coms['comment_children'] );

        $output = '<div id="comments-post-'. $post_id . '" class="post-comments">';
        $output .= '<div class="post-comment-head">';
        $output .= '<h2>Discussion</h2>';
        $output .= ( $count > 0 ) ? sprintf( '<h4>%s Comments</h4>', $count ) : '<h4>Be the first to comment</h4>';
        $output .= '</div>';
        $output .= '<div class="comment-form-box">';
        $output .= display_comment_box( (int)$post_id );
        $output .= '</div>';
        if( is_array( $coms ) && isset( $coms['comment_parent'] ) && ! empty( $coms['comment_parent'] ) ) {
           $output .= markup_comment( $post_id, $coms['comment_parent'], $coms['comment_children'] );
        } else {
            $output .= '<ul class="comment-thread"><li id="post-thread-' . $post_id . '"></li></ul>';
        }
        $output .= '</div>';

    }

    return $output;
}


/**
 * Creates the HTML markup for comments and replys
 * @param array $parent_list
 * @param array $children_list
 */
function markup_comment( $post_id, $parent_list, $children_list ) {
    $output = false;

    if ( ! empty ( $parent_list ) ) {
        $output = '<ul class="comment-thread">';
        $output .= '<li id="post-thread-' . $post_id . '"></li>';
        foreach( $parent_list as $comm  ) {
            $date = date( 'Y-m-d', strtotime( $comm['date'] ) );
            $cp = ( ! empty( $comm['commentparent'] ) ) ? $comm['commentparent'] : '0';

            $output .= '<li id="comment-' . $comm['id'] . '" class="comment-box">';
            $output .= '<div class="comment-meta">'; //Start comment-meta
            $output .= '<h3 class="comment-meta-user">' . $comm['user']['name'] . '</h3>';
            $output .= '<p class="comment-meta-date">';
            $output .= '<a href="' . get_requested_url() . '#comment-' . $comm['id'] . '">#</a>';
            $output .= ' ' . $date . '</p>';
            $output .= '</div>'; //end comment-meta
            $output .= '<div class="comment-body">'; //start comment-body
            $output .= '<p>' . $comm['content'] . '</p>';
            $output .= '</div>'; //end comment-body
            $output .= '<div class="button-wrapper">';
            $output .= '<button class="comment-reply-bt comment-action-bt" data-target="box-comment-' . $comm['id'] . '" onclick="open_comment_box( this, ' . $comm['id'] . ', ' . $cp . ', ' . $comm['postparent'] . ' )">REPLY</button>';
            $output .= '</div>';
            $output .= '<div id="box-comment-' . $comm['id'] . '"></div>';

            if ( ! empty ( $children_list ) ) {
                $output .= markup_comment_children( $children_list, (int)$comm['id'] );
            } else {
                $output .= '<ul class="comment-thread children-comments"><li id="child-thread-' . $comm['id'] . '"></li></ul>';
            }

            $output .='</li>';
        }
        $output .= '</ul>';

    }

    return $output;
    
}


/**
 * Creates markup for the comments that are a reply
 * 
 */
function markup_comment_children( $comment_list, $parent_id ) {
    $output = false;
    if ( $comment_list !== false ) {
        $output = '<ul class="comment-thread children-comments">';
        $output .= '<li id="child-thread-' . $parent_id . '"></li>';
        foreach ( $comment_list as $comm ) {
            $output .= '<li id="comment-' . $comm['id'] . '" class="comment-box">';
            $output .= '<div class="comment-meta">';
            $output .= '<h3 class="comment-meta-user">' . $comm['user']['name'] . '</h3>';
            $output .= '<p class="comment-meta-date">';
            $output .= '<a href="' . get_requested_url() . '#comment-' . $comm['id'] . '">#</a>';
            $output .= ' ' . $comm['date'] . '</p>';
            $output .= '</div>';
            $output .= '<div class="comment-body">';
            $output .= '<p>' . $comm['content'] . '</p>';
            $output .= '</div>';
            $output .= '</li>';
        }
        $output.= '</ul>';
    }

    return $output;
}

function get_comments_byPOST( $post_id ) {
    $output = false;
    $comments = get_comments_list();
    $parents = array();
    $children = array();

    foreach ( $comments as $comm ) {
        if( $comm['status'] === 'APPROVED' ) {
            if( (int)$comm['postparent'] === (int)$post_id && empty( $comm['commentparent'] ) ) {
                array_push( $parents, $comm );
            } else if( (int)$comm['postparent'] === (int)$post_id && ! empty( $comm['commentparent'] ) ) {
                array_push( $children, $comm );
            }
        }
    }

    if ( ! empty( $parents ) ) {
        $output = array();
        $output['comment_parent'] = $parents;
        $output['comment_children'] = $children;
    }

    return $output;

}

function post_has_comments( $post_id ) {
    $output = false;
    $comments = get_comments_list();
    $i = 0;

    while( $i < count( $comments ) ) {
        if ( (int)$comments[$i]['postparent'] === (int)$post_id ) {
            $output = true;
            break;
        }
        $i++;
    }

    return $output;
}

function get_comment_children( $parent_id ) {
    $output = false;
    $comments = get_comments_list();
    $found_comms = array();

    if ( $comments !== false ) {
        foreach ( $comments as $comm ) {
            if ( $comm['commentparent'] != (int)$parent_id ) {
                array_push( $found_comms, $comm );
            }
        }
    }

    if( ! empty( $found_comms ) ) {
        $output = $found_comms;
    }

    return $output;
}

function get_new_commentid() {
    $output = false;
    $ids = array();
    $comments = get_comments_list();
    foreach( $comments as $comm ) {
        array_push( $ids, $comm['id'] );
    }

    asort( $ids );

    $output = end( $ids ) + 1;

    return $output;
}

function submit_comment( $comment_data ) {
    $output = false;
    $user_rg = '/^(user-)/';
    $comments_file = get_json_content( CONTENTPATH . 'comments.json' );
    $comment = array();
    $comment['status'] = 'PENDING';

    if ( isset( $comment_data['usereply'] ) || isset( $comment_data['adminreply'] )  || isset( $comment_data['usersubmit'] ) ) {

        if ( isset( $comment_data['usereply'] ) ) {

            unset( $comment_data['usereply'] );

        } else if ( isset( $comment_data['adminreply'] ) ) {

            unset( $comment_data['adminreply'] );
            $comment['status'] = 'APPROVED';

        } else if ( isset( $comment_data['usersubmit'] ) ) {

            unset( $comment_data['usersubmit'] );

        }

        foreach ( $comment_data as $key => $value ) {
        	
            if ( preg_match( $user_rg, $key ) ) {
                switch ( $key ) {
                    case 'user-name':
                        $comment['user']['name'] = htmlspecialchars( $value );
                        break;
                    case 'user-email':
                        $comment['user']['email'] = htmlspecialchars( $value );
                        break;
                    case 'user-website':
                        $comment['user']['website'] = ( ! empty( $value ) ) ? htmlspecialchars( $value ) : '';
                        break;
                    case 'postparent':
                        $comment['postparent'] = (int)$value;
                        break;
                    case 'commentparent':
                        $comment['commentparent'] = (int)$value;
                }
            } else {
                $comment[$key] = ( ! empty( $value ) || (int)$value === 0 ) ? htmlspecialchars( $value ) : '';
            }
        }   
    }

    $comment['id'] = (int)get_new_commentid();

    if ( ! comment_exist( (int)$comment['id'] ) ) {
        array_push( $comments_file['comments'], $comment );

        if( write_to_json( CONTENTPATH . 'comments.json', $comments_file ) ) {
            $output = $comment;
        }
    }

    return $output;

}

function display_comment_box( $post_id ) {
    $now = date( 'Y-m-d H:i:s', time() );
    //Main Comment box
    $mainbox = '<div id="comment-form-container">';
    $mainbox .= '<form id="post-comment-form" method="POST" data-response="post-thread-' . $post_id . '" onsubmit="submit_comment(this, event)">';
    $mainbox .= '<input id="commentparent" type="hidden" value="0" name="commentparent">';
    $mainbox .= '<input id="postparent" type="hidden" value="%s" name="postparent">';
    $mainbox .= '<p>';
    $mainbox .= '<label>Your name</label>';
    $mainbox .= '<input id="user-name" type="text" name="user-name" placeholder="required" required/>';
    $mainbox .= '</p><p>';
    $mainbox .= '<label>Your email</label>';
    $mainbox .= '<input id="user-mail" type="email" name="user-email" placeholder="required" required/>';
    $mainbox .= '</p><p>';
    $mainbox .= '<label>Your website (if any)</label>';
    $mainbox .= '<input id="user-website" type="url" name="user-website" placeholder="http://yoursite.com"/>';
    $mainbox .= '</p><p>';
    $mainbox .= '<label>Share your thoughts.</label>';
    $mainbox .= '<textarea id="comment-content" name="content" required></textarea>';
    $mainbox .= '</p>';
    $mainbox .= '<div class="button-wrapper">';
    $mainbox .= '<input id="submit-comment-form" type="submit" name="usersubmit" class="half-button" value="SUBMIT"/>';
    $mainbox .= '<input name="cancel" class="half-button" type="reset" value="CANCEL"/>';
    $mainbox .= '</div>';
    $mainbox .= '</form>';
    $mainbox .= '</div>';

    //Template to use for replying comments
    $template = '<div id="comment-box-template" style="display:none;">';
    $template .= '<form method="POST" data-response="" onsubmit="submit_comment(this, event)">';
    $template .= '<p>';
    $template .= '<label>Name: </label>';
    $template .= '<input type="text" name="user-name" required/>';
    $template .= '</p><p>';
    $template .= '<label>Email: </label>';
    $template .= '<input type="email" name="user-email" required/>';
    $template .= '</p><p>';
    $template .= '<label>Website: <i>optional</i></label>';
    $template .= '<input type="url" name="user-website" placeholder="http://yoursite.com"/>';
    $template .= '</p><p>';
    $template .= '<label>Share your thoughts: </label>';
    $template .= '<textarea name="content" required></textarea>';
    $template .= '</p>';
    $template .= '<div class="button-wrapper">';
    $template .= '<input name="usereply" class="half-button" type="submit" value="SUBMIT"/>';
    $template .= '</div>';
    $template .= '</form>';
    $template .= '</div>';

    $output = '<div class="form-wrapper">';
    $output .= sprintf( $mainbox, $post_id );
    $output .= $template;
    $output .= '</div>';

    return $output;

}

function comment_exist( $comment_id ) {
    $output = false;
    $comments = get_comments_list();
    $i = 0;

    while ( $i < count( $comments ) ) {
        if ( $comments[$i]['id'] === (int)$comment_id ) {
            $output = true;
            break;
        }
        $i++;
    }
    return $output;
}


/***************************************/
/*               CLASSES               */
/***************************************/

/**
 * 
 */
class ContactForm {

	private $email_address;
	private $reCaptcha_key;
	private $secret_key;

	/**
	 * [__construct description]
	 * @param array $args email
	 *                    data_sitekey
	 */
	public function __construct ( $args = array() ) {
		if ( isset( $args['email'] ) && ! empty( $args['email'] ) ) {
			$this->email_address = $args['email'];
		} else {
			echo 'No email address has been assigned';
			die;
		}
	}

	/**
	 * Place this in the <head> section
	 * @return [type] [description]
	 */
	public function captcha_dependencies () {

	}

	/**
	 * Place this wherever you want your form displayed
	 * @param  array  $options fields
	 *                         textarea
	 *                         textarea_plcholder
	 *                         
	 * @return [type]          [description]
	 */
	public function render_form ( $options = array() ) {
		$has_label = false;

	}

}

/**
 * Pagination class
 */
class Paginate {
	private $items_page;
	private $item_array;
	private $items_count;
	private $actual_page;
	public $page_content;
	
	public function __construct( $args = array() ) {
		$this->items_page = ( isset( $args['items_per_page'] ) && ! empty ( $args['items_per_page'] ) ) ? $args['items_per_page'] : 5;
		$this->item_array = $args['items'];
		$this->items_count = count( $this->item_array );
		$this->actual_page = ( isset( $_GET['p'] ) && ! empty ( $_GET['p'] ) ) ? $_GET['p'] : 0;
		
		$start_item = $this->actual_page * $this->items_page;
		
		if ( $this->items_count <= $this->items_page ) {
			$this->page_content = $this->item_array;
		} else {
			$this->page_content = array_slice( $this->item_array, $start_item, $this->items_page );
		}
		
	}
	
	private function get_pages () {
		$n = ceil ( $this->items_count / $this->items_page );
		
		$counter = 0;
		
		$pages = array();
		
		while ( $counter < $n ) :
			$pages[] = $counter;
			$counter++;
		endwhile;
		
		return $pages;
	}
	
	public function pages_bar ( $options = array() ) {
		$output = '';
        $url_string = ( preg_match( '/\?/', get_requested_url() ) ) ? get_requested_url() . '&p=' : get_requested_url() . '?p=' ;
		$bar_text = ( isset( $options['bar_text'] ) && ! empty ( $options['bar_text'] ) ) ? $options['bar_text'] : 'Pages';
		
		$ul_class = 'paginator';
		
		if ( $this->items_count > $this->items_page ) {
			$output .= sprintf ( '<ul class="%1$s"><li class="bar-text">%2$s</li>', $ul_class, $bar_text );
			
			
			if ( $this->actual_page > 0 ) {
				$prev_url = 'href="' . $url_string . ( $this->actual_page - 1 ) . '"';
				$output .= sprintf( '<li class="prev-page page-button"><a %s>&lt;</a></li>', $prev_url );
			}
			
			foreach ( $this->get_pages() as $page ) :
				
				$url;
				
				if ( $page == ( $this->actual_page) ) {
					$url = '';
					$li_class = 'page-button current-page';
				} else {
					$url = 'href="' . $url_string . $page . '"';
					$li_class = 'page-button';
				}
				
				$output .= sprintf( '<li class="%1$s"><a %3$s>%2$d</a></li>', $li_class, ( $page + 1 ), $url );
			endforeach;
			
			if ( $this->actual_page < ( count ( $this->get_pages() ) - 1 )  ) {
				$next_url = 'href="' . $url_string . ( $this->actual_page + 1 ) . '"';
				$output .= sprintf( '<li class="next-page page-button"><a %s>&gt;</a></li>', $next_url );
			}
			
			$output .= '</ul>';
		}

		echo $output;
	}
	
}

/**********************************/
/*         ERROR HANDLERS         */
/**********************************/
/**
 * Send the errors to the javascript console, it is recommended to run
 * this function in the footer file of your website right before the
 * closing body tag
 * @return [array] Contains categorize error in the form of
 *                 Error-Description => Error-Type
 */
function get_errors () {
	global $site_errors;

	$output = '<script type="text/javascript">';

	if ( isset ( $site_errors ) && ! empty ( $site_errors ) ) {

		foreach ( $site_errors as $error => $type ) {
			$output .= sprintf( 'console.error( "%1$s : %2$s" );', $type, $error );
		}

	} else {
		$output .= 'console.log("NO ERRORS FOUND!");';
	}

	$output .= '</script>';

	echo $output;		
}

/**
 * Sends the process time to the console log through javascript
 */
function get_process_time () {
	$end_time = microtime( true );
	$time_p = $end_time - START_TIME;
	$output = sprintf( '<script type="text/javascript">console.log( "Your php site ran in: " + %s );</script>', $time_p );

	echo $output;
}

/**********************************/
/*         HELPER FUNCTIONS       */
/**********************************/

/**
 * Gets a file name of file full path and clean it up and explode it
 * to retrieve only the last section after the last forward slash
 * to use it as a name.
 * @param  string $filename filename or filepath
 * @return string           cleaned name
 */
function cleanup_filename( $filename ) {
	$pattern = array( '/[-_]/', '/(.jpg|.gif|.png|.bmp|.tiff|.pdf|.json|.content|.php|.html)/' );
	$replace_patt = array( ' ', '' );
	$cleaned_path = explode ( '/', preg_replace( $pattern, $replace_patt, $filename ) );
	$output = end( $cleaned_path );
	return $output;
}

/**
 * Check if content.json file has any post in its array.
 * @return mixed|bool returns array on success, false on fail.
 */
function content_posts_exist() {
    $output = false;
    $content = get_json_content( CONTENTPATH . 'content.json' );
    if( is_array( $content ) && isset( $content['posts'] ) && ! empty( $content['posts'] ) ) {
        $output = $content;
    }
    return $output;
}

function str_title_sort( $a, $b ) {
    return strcasecmp( $a['title'], $b['title'] );
}

function str_title_rsort( $a, $b ) {
    return strcasecmp( $b['title'], $a['title'] );
}

function int_id_sort( $a, $b ) {
    return (int)$a['id'] - (int)$b['id'];
}

function int_id_rsort( $a, $b ) {
    return (int)$b['id'] - (int)$a['id'];
}

function str_date_sort( $a, $b ) {
    return strtotime( $a['date'] ) - strtotime( $b['date'] );
}

function str_date_rsort( $a, $b ) {
    return strtotime( $b['date'] ) - strtotime( $a['date'] );
}

function pretty_array( $obj, $dump = false ) {
    echo '<pre>';
    ( $dump === true ) ? var_dump($obj) : print_r($obj);
    echo '</pre>';
}

?>