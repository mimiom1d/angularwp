<?php
/**
 * Getcoins functions and definitions
 *
 * Sets up the theme and provides some helper functions, which are used
 * in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 * When using a child theme (see https://codex.wordpress.org/Theme_Development and
 * https://codex.wordpress.org/Child_Themes), you can override certain functions
 * (those wrapped in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before the parent
 * theme's file, so the child theme functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are instead attached
 * to a filter or action hook.
 *
 * For more information on hooks, actions, and filters, @link https://codex.wordpress.org/Plugin_API
 *
 * @package WordPress
 * @subpackage getcoins
 * @since Getcoins 1.0
 */

// require_once(dirname(__FILE__).'/inc/redux-config.php');
// require_once(dirname(__FILE__).'/inc/resizer.php');
// require_once(dirname(__FILE__).'/inc/atm-location-module.php');
require_once(get_template_directory() .'/inc/atm-location-module-rest.php');

add_action('init', 'gc_add_rewrite_rule');
add_action('template_redirect','gc_rewrite_catch');
add_filter('query_vars', 'gc_rewrite_add_var');

// **Update/Customize CSS within in Admin (Added in functions.php)
// function gc_admin_style() {
// 	wp_enqueue_style('admin-styles', get_template_directory_uri().'/css/admin.css');
// }
// add_action('admin_enqueue_scripts', 'gc_admin_style');


function gc_rewrite_add_var( $vars ) {
	$vars[] = 'location_id';
    return $vars;
}
function gc_add_rewrite_rule() {
	    
	add_rewrite_tag( '%location%', '([^&]+)' );
	add_rewrite_tag( '%bitcoin-atm-locations%', '([^&]+)' );
	add_rewrite_tag( '%import-locations%', '([^&]+)' );
	
	add_rewrite_rule(
        '^bitcoin-atm-locations/([0-9]+)$',
        'index.php?bitcoin-atm-locations=bitcoin-atm-locations&location_id=$matches[1]',
        'top'
	);
    
	add_rewrite_rule(
        '^location/([0-9]+)$',
        'index.php?location=location&location_id=$matches[1]',
        'top'
	);

	add_rewrite_rule(
        '^import-locations/?',
        'index.php?import-locations=import-locations',
        'top'
	);
	
    flush_rewrite_rules();
}
function gc_rewrite_catch() {
    global $wp_query;

    if ( array_key_exists( 'location', $wp_query->query_vars ) ) {
    	require_once ( get_template_directory() . '/page-templates/location-detail-view.php');
        die(); // stop default WP behavior
    }
    if ( array_key_exists( 'bitcoin-atm-locations', $wp_query->query_vars ) ) {
    	require_once ( get_template_directory() . '/page-templates/location-detail-view.php');
        die(); // stop default WP behavior
    }

    if ( array_key_exists( 'import-locations', $wp_query->query_vars ) ) {
    	require_once ( get_template_directory() . '/inc/import.php');
        die(); // stop default WP behavior
    }

}


// function gc_load_template($template_domain = null, $partial_template = null, $load_data = null){
// 	set_query_var( 'load_data', $load_data );
// 	if($template_domain != null && $partial_template != null){
// 		get_template_part( $template_domain, $partial_template);
// 	}
	
// }
function get_availableCash_data(){
	$availableCashUrl = "https://analytics.bitexpress.com/api/v1/status/cash-available";
	$availableCash = json_decode(file_get_contents($availableCashUrl));
	return $availableCash;
}
function get_locations_data($arr_format = false){
	$url = get_site_url();
	$locationJsonUrl = ABSPATH . "json/locations.json";
	$locations = json_decode(file_get_contents($locationJsonUrl));
	return $locations;
}
function gc_remove_img_firstBr_p_tag($content){
	$newContent = preg_replace("/<img[^>]+\>/i", " ", $content); 
    $count = 1;
	$newContent = str_replace('<br />','',$newContent, $count);
	$newContent = str_replace('<p>',' ',$newContent);
	$newContent = str_replace('</p>',' ',$newContent);
	$newContent = str_replace("<h3>","<h3><a href='#'>",$newContent);
	$newContent = str_replace('</h3>','</a></h3>',$newContent);
	return $newContent;
}
function gc_get_hours_from_html($content){
	$newContent = preg_replace("/<img[^>]+\>/i", "", $content); 
    $count = 1;
	$newContent = str_replace('<br />',' ',$newContent, $count);
	$newContent = str_replace('<p>',' ',$newContent);
	$newContent = str_replace('</p>',' ',$newContent);
	return $newContent;
}
function gc_strip_tags($content, array $tags){
	$newContent = preg_replace("/<img[^>]+\>/i", " ", $content); 
    $count = 1;

    if(count($tags) > 0 ){
    	foreach ($tags as $key => $tag) {
    		$newContent = str_replace($tag,' ',$newContent);
    	}
    }
	return $newContent;
}
function get_html_img($content = ''){
    $img_arr = [];
    //$html = file_get_contents($permalink);
    $dom = new domDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML($content);
    libxml_clear_errors();
    $dom->preserveWhiteSpace = false;
    $images = $dom->getElementsByTagName('img');
    foreach ($images as $image) {
     $img_arr[] =  $image->getAttribute('src');
    }
    return $img_arr;
}
function get_html_h3tag($content = ''){
    $newcontent = preg_replace("/\s+/",' ',$content);  
	preg_match('/<h3>(.*?)<\/h3>/i', $newcontent , $h3);
    return $h3[1];
}
function gc_get_html_without_img($content){
    $result = preg_replace("/\s+/",' ',$content);  
	$result = preg_replace("/<img[^>]+\>/i", "", $result);
    $count = 1;
	$result = str_replace('<br />','',$result, $count);
	return $result;
}
function gc_get_addresshourscoin_from_html($content){
	$result = gc_get_html_without_h3(gc_get_html_without_img($content));
	$result = gc_strip_tags($result, ['<p>', '</p>']);
	// echo $result;
	$array = explode('<b>', $result); 
	// var_dump($array);
	$newResult;
	$newArray;
	$hoursSet;
	foreach($array as $key=>$value){
		if($key === 0){
			$newResult['address'] = $value; 
		}
		elseif((substr( $value, 0, 3 ) === "Buy" || substr( $value, 0, 3 ) === "buy")){
			$newResult['coin'] = "<b>"  . str_replace('Buy','Buy</b>',$value);
		}
		elseif($key === sizeof($array)-1 && (substr( $value, 0, 3 ) !== "Buy" || substr( $value, 0, 3 ) !== "buy")){
			$newResult['coin2'] = "<b>"  . str_replace('Sell','Sell</b>',$value);
		}
		else{
			$newvalue = "<b>" . str_replace(':','</b>:',$value);
			$hoursSet[] = $newvalue; 
		}
	}
	if(!empty($newResult['coin2'])){
		$newResult['coin'] = $newResult['coin'] . " " .  $newResult['coin2'];
	}
	// $hours = join('<br>', $hoursSet);
	$newResult['hours'] = $hoursSet;
	// var_dump($newResult);
	return $newResult;
}
function gc_insert_h2tags_in_html($content){
	// $result = str_replace('</h3><p>', '</h3><h2 class="gc_location-single_h2">Address:</h2><p>', $content);
	// $array = explode('<b>', $result); 
	// echo "below is the first array";
	// var_dump($array);
	// $array_size = sizeof($array);
	// $coin = $array[$array_size-1];
	// echo "this is coin value: " . $coin;
	// $newArray;
	// foreach($array as $key=>$value){
	// 	if($key !== 0 && $key !== $array_size-1){
	// 		$newArray[] = $value; 
	// 	}
	// }
	// var_dump($newArray);
}
function gc_get_html_without_h3($content){
	if($content){
		$newcontent = preg_replace("/<h3>(.*?)<\/h3>/i",'',$content);
		// echo $newcontent;  
	}
	return $newcontent;
}
function gc_get_address($content){
	$newContent = explode("<br>",  $content, 3);
        print_r($newContent);
	$newContent = preg_replace('#<h3>(.*?)</h3>#', '', $newContent, 1);
	$newContent = [$newContent[0], $newContent[1]];
	$newContent = implode(', ', $newContent);
	$address = trim(strip_tags($newContent));
	return $address;
}
function gc_title($title = '', $len){
	$new_title = '';
	$title_len = strlen($title);

    if($title_len > $len){
    	$new_title = substr($title, 0, ($len -3)).'...';
    }else{
    	$new_title = $title;
    }

    return $new_title;
}
function gc_excerpt($content = '', $len){
	$new_content = '';
	$content_len = strlen($content);

    if($content_len > $len){
    	$new_content = substr($content, 0, ($len -3)).'...';
    }else{
    	$new_content = $content;
    }

    return $new_content;
}

function gc_redux_settings($key = null){
	global $getcoins_settings;
	if($key == null) return $getcoins_settings;
	return $getcoins_settings[$key];
}

// function gc_scripts_styles_method() {
// 	$js_dir = get_template_directory_uri().'/js/';
// 	$css_dir = get_template_directory_uri().'/css/';
// 	$fonts_dir = get_template_directory_uri().'/fonts/';

// 	// Themes supportive styles
// 	wp_enqueue_style( 'bite-style-bootstrap', $css_dir.'bootstrap.css', array(), '', false );
// 	wp_enqueue_style( 'bite-style-bootstrap', $fonts_dir.'fonts.css', array(), '', false );
// 	wp_enqueue_style( 'bite-style-font-awesome', $css_dir.'font-awesome.css', array(), '', false );
// 	wp_enqueue_style( 'bite-style-font-awesome5', $css_dir.'font-awesome_v5.css', array(), '', false );
// 	wp_enqueue_style( 'bite-style-themestyle', $css_dir.'style.css', array(), '', false );
// 	wp_enqueue_style( 'bite-style-responsive', $css_dir.'responsive.css', array(), '', false );
// 	wp_enqueue_style( 'bite-style-chosen', $css_dir.'chosen.min.css', array(), '', false );
// 	// Themes supportive scripts
// 	wp_enqueue_script( 'bite-script-bootstrap', $js_dir.'bootstrap.js', array(), '', true );
// 	wp_enqueue_script( 'bite-script-custom', $js_dir.'custom.js', array(), '', true );
// 	wp_enqueue_script( 'bite-script-modernizr', $js_dir.'modernizr-1.0.js', array(), '', true );
// 	wp_enqueue_script( 'bite-script-chosen', $js_dir.'chosen.jquery.min.js', array(), '', true );
// 	if(is_home() || is_front_page()){
// 		wp_enqueue_script( 'bite-script-map', $js_dir.'map-script.js', array(), '', true );
// 	}
	
// 	wp_enqueue_script( 'bite-google-map', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCtKnIFay10qc-ey8eyKiYhOSikapDFcnY&libraries=places', array(), '', true );
	
// }
// add_action( 'wp_enqueue_scripts', 'gc_scripts_styles_method' );


// Set up the content width value based on the theme's design and stylesheet.
// if ( ! isset( $content_width ) )
// 	$content_width = 625;

/**
 * Getcoins setup.
 *
 * Sets up theme defaults and registers the various WordPress features that
 * Getcoins supports.
 *
 * @uses load_theme_textdomain() For translation/localization support.
 * @uses add_editor_style() To add a Visual Editor stylesheet.
 * @uses add_theme_support() To add support for post thumbnails, automatic feed links,
 * 	custom background, and post formats.
 * @uses register_nav_menu() To add support for navigation menus.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 *
 * @since Getcoins 1.0
 */
function getcoins_setup() {
	/*
	 * Makes Getcoins available for translation.
	 *
	 * Translations can be filed at WordPress.org. See: https://translate.wordpress.org/projects/wp-themes/getcoins
	 * If you're building a theme based on Getcoins, use a find and replace
	 * to change 'getcoins' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'getcoins' );

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// Adds RSS feed links to <head> for posts and comments.
	add_theme_support( 'automatic-feed-links' );

	// This theme supports a variety of post formats.
	add_theme_support( 'post-formats', array( 'aside', 'image', 'link', 'quote', 'status' ) );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menu( 'primary', __( 'Primary Menu', 'getcoins' ) );

	/*
	 * This theme supports custom background color and image,
	 * and here we also set up the default background color.
	 */
	add_theme_support( 'custom-background', array(
		'default-color' => 'f79420',
	) );

	// This theme uses a custom image size for featured images, displayed on "standard" posts.
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 624, 9999 ); // Unlimited height, soft crop

	// Indicate widget sidebars can use selective refresh in the Customizer.
	add_theme_support( 'customize-selective-refresh-widgets' );
}
add_action( 'after_setup_theme', 'getcoins_setup' );

/**
 * Add support for a custom header image.
 */
// require( get_template_directory() . '/inc/custom-header.php' );

/**
 * Return the Google font stylesheet URL if available.
 *
 * The use of Open Sans by default is localized. For languages that use
 * characters not supported by the font, the font can be disabled.
 *
 * @since Getcoins 1.2
 *
 * @return string Font stylesheet or empty string if disabled.
 */
// function getcoins_get_font_url() {
// 	$font_url = '';

// 	/* translators: If there are characters in your language that are not supported
// 	 * by Open Sans, translate this to 'off'. Do not translate into your own language.
// 	 */
// 	if ( 'off' !== _x( 'on', 'Open Sans font: on or off', 'getcoins' ) ) {
// 		$subsets = 'latin,latin-ext';

// 		/* translators: To add an additional Open Sans character subset specific to your language,
// 		 * translate this to 'greek', 'cyrillic' or 'vietnamese'. Do not translate into your own language.
// 		 */
// 		$subset = _x( 'no-subset', 'Open Sans font: add new subset (greek, cyrillic, vietnamese)', 'getcoins' );

// 		if ( 'cyrillic' == $subset )
// 			$subsets .= ',cyrillic,cyrillic-ext';
// 		elseif ( 'greek' == $subset )
// 			$subsets .= ',greek,greek-ext';
// 		elseif ( 'vietnamese' == $subset )
// 			$subsets .= ',vietnamese';

// 		$query_args = array(
// 			'family' => 'Open+Sans:400italic,700italic,400,700',
// 			'subset' => $subsets,
// 		);
// 		$font_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
// 	}

// 	return $font_url;
// }

/**
 * Enqueue scripts and styles for front end.
 *
 * @since Getcoins 1.0
 */
// function getcoins_scripts_styles() {
// 	global $wp_styles;

// 	/*
// 	 * Adds JavaScript to pages with the comment form to support
// 	 * sites with threaded comments (when in use).
// 	 */
// 	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
// 		wp_enqueue_script( 'comment-reply' );

// 	// Adds JavaScript for handling the navigation menu hide-and-show behavior.
// 	wp_enqueue_script( 'getcoins-navigation', get_template_directory_uri() . '/js/navigation.js', array( 'jquery' ), '20140711', true );

// 	$font_url = getcoins_get_font_url();
// 	if ( ! empty( $font_url ) )
// 		wp_enqueue_style( 'getcoins-fonts', esc_url_raw( $font_url ), array(), null );

// 	// Loads our main stylesheet.
// 	wp_enqueue_style( 'getcoins-style', get_stylesheet_uri() );

// 	// Loads the Internet Explorer specific stylesheet.
// 	wp_enqueue_style( 'getcoins-ie', get_template_directory_uri() . '/css/ie.css', array( 'getcoins-style' ), '20121010' );
// 	$wp_styles->add_data( 'getcoins-ie', 'conditional', 'lt IE 9' );
// }
// add_action( 'wp_enqueue_scripts', 'getcoins_scripts_styles' );

/**
 * Add preconnect for Google Fonts.
 *
 * @since Getcoins 2.2
 *
 * @param array   $urls          URLs to print for resource hints.
 * @param string  $relation_type The relation type the URLs are printed.
 * @return array URLs to print for resource hints.
 */
// function getcoins_resource_hints( $urls, $relation_type ) {
// 	if ( wp_style_is( 'getcoins-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
// 		if ( version_compare( $GLOBALS['wp_version'], '4.7-alpha', '>=' ) ) {
// 			$urls[] = array(
// 				'href' => 'https://fonts.gstatic.com',
// 				'crossorigin',
// 			);
// 		} else {
// 			$urls[] = 'https://fonts.gstatic.com';
// 		}
// 	}

// 	return $urls;
// }
// add_filter( 'wp_resource_hints', 'getcoins_resource_hints', 10, 2 );

/**
 * Filter TinyMCE CSS path to include Google Fonts.
 *
 * Adds additional stylesheets to the TinyMCE editor if needed.
 *
 * @uses getcoins_get_font_url() To get the Google Font stylesheet URL.
 *
 * @since Getcoins 1.2
 *
 * @param string $mce_css CSS path to load in TinyMCE.
 * @return string Filtered CSS path.
 */
// function getcoins_mce_css( $mce_css ) {
// 	$font_url = getcoins_get_font_url();

// 	if ( empty( $font_url ) )
// 		return $mce_css;

// 	if ( ! empty( $mce_css ) )
// 		$mce_css .= ',';

// 	$mce_css .= esc_url_raw( str_replace( ',', '%2C', $font_url ) );

// 	return $mce_css;
// }
// add_filter( 'mce_css', 'getcoins_mce_css' );

/**
 * Filter the page title.
 *
 * Creates a nicely formatted and more specific title element text
 * for output in head of document, based on current view.
 *
 * @since Getcoins 1.0
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string Filtered title.
 */
// function getcoins_wp_title( $title, $sep ) {
// 	global $paged, $page;

// 	if ( is_feed() )
// 		return $title;

// 	// Add the site name.
// 	$title .= get_bloginfo( 'name', 'display' );

// 	// Add the site description for the home/front page.
// 	$site_description = get_bloginfo( 'description', 'display' );
// 	if ( $site_description && ( is_home() || is_front_page() ) )
// 		$title = "$title $sep $site_description";

// 	// Add a page number if necessary.
// 	if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() )
// 		$title = "$title $sep " . sprintf( __( 'Page %s', 'getcoins' ), max( $paged, $page ) );

// 	return $title;
// }
// add_filter( 'wp_title', 'getcoins_wp_title', 10, 2 );

/**
 * Filter the page menu arguments.
 *
 * Makes our wp_nav_menu() fallback -- wp_page_menu() -- show a home link.
 *
 * @since Getcoins 1.0
 */
// function getcoins_page_menu_args( $args ) {
// 	if ( ! isset( $args['show_home'] ) )
// 		$args['show_home'] = true;
// 	return $args;
// }
// add_filter( 'wp_page_menu_args', 'getcoins_page_menu_args' );

/**
 * Register sidebars.
 *
 * Registers our main widget area and the front page widget areas.
 *
 * @since Getcoins 1.0
 */
// function getcoins_widgets_init() {
// 	register_sidebar( array(
// 		'name' => __( 'Main Sidebar', 'getcoins' ),
// 		'id' => 'sidebar-1',
// 		'description' => __( 'Appears on posts and pages except the optional Front Page template, which has its own widgets', 'getcoins' ),
// 		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
// 		'after_widget' => '</aside>',
// 		'before_title' => '<h3 class="widget-title">',
// 		'after_title' => '</h3>',
// 	) );

// 	register_sidebar( array(
// 		'name' => __( 'First Front Page Widget Area', 'getcoins' ),
// 		'id' => 'sidebar-2',
// 		'description' => __( 'Appears when using the optional Front Page template with a page set as Static Front Page', 'getcoins' ),
// 		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
// 		'after_widget' => '</aside>',
// 		'before_title' => '<h3 class="widget-title">',
// 		'after_title' => '</h3>',
// 	) );

// 	register_sidebar( array(
// 		'name' => __( 'Second Front Page Widget Area', 'getcoins' ),
// 		'id' => 'sidebar-3',
// 		'description' => __( 'Appears when using the optional Front Page template with a page set as Static Front Page', 'getcoins' ),
// 		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
// 		'after_widget' => '</aside>',
// 		'before_title' => '<h3 class="widget-title">',
// 		'after_title' => '</h3>',
// 	) );
// }
// add_action( 'widgets_init', 'getcoins_widgets_init' );

/**
 * Displays navigation to next/previous pages when applicable.
 *
 * @since Getcoins 1.0
 */
/*
if ( ! function_exists( 'getcoins_content_nav' ) ) :
function getcoins_content_nav( $html_id ) {
	global $wp_query;

	if ( $wp_query->max_num_pages > 1 ) : ?>
		<nav id="<?php echo esc_attr( $html_id ); ?>" class="navigation" role="navigation">
			<h3 class="assistive-text"><?php _e( 'Post navigation', 'getcoins' ); ?></h3>
			<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'getcoins' ) ); ?></div>
			<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'getcoins' ) ); ?></div>
		</nav><!-- .navigation -->
	<?php endif;
}
endif;
*/
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own techarcis_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since techarcis 1.0
 */

 /*
if ( ! function_exists( 'getcoins_comment' ) ) :
function getcoins_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
		// Display trackbacks differently than normal comments.
	?>
	<?php
		break;
		default :
		// Proceed with normal comments.
		global $post;
	?>
	<li id="li-comment-<?php comment_ID(); ?>">
	<article id="comment-<?php comment_ID(); ?>" class="comment1">
	 <ul class="comment-metadata" id="comment-<?php comment_ID(); ?>">
        <li><h4><i class="fa fa-user green"></i> 
		&nbsp;<?php printf( ' %1$s %2$s',
						get_comment_author_link(),
						// If current post author is also comment author, make it known visually.
						( $comment->user_id === $post->post_author ) ? '' : ''
					);?>
		</h4></li>
    	<li class="right"><i class="fa fa-clock-o green"></i> &nbsp;
		<?php printf( '%3$s',
						esc_url( get_comment_link( $comment->comment_ID ) ),
						get_comment_time( 'c' ),
						// translators: 1: date, 2: time 
						sprintf( __( '%1$s at %2$s', 'naked' ), get_comment_date(), get_comment_time() )
					);?>
		</li>
    </ul>
	<div class="p-clear">
    <?php comment_text(); ?>
	</div>
	<p class="blog-reply">
				<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'myiconix' ), 'after' => ' <i data-unicode="f122" class="fa fa-mail-reply-all">&nbsp;</i>', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</p>
   </article>
  
	<?php
		break;
	endswitch; // end comment_type check
}
endif;
*/


// function getcoins_comment_form( $args = array(), $post_id = null ) {
// 	global $id;
// 	if ( null === $post_id )
// 		$post_id = $id;
// 	else
// 		$id = $post_id;
// 	$commenter = wp_get_current_commenter();
// 	$user = wp_get_current_user();
// 	$user_identity = $user->exists() ? $user->display_name : '';
// 	$req = get_option( 'require_name_email' );
// 	$aria_req = ( $req ? " aria-required='true'" : '' );
// 	$fields =  array(
// 		'author' => ''.
// 		            '<div class="form-group"><label for="post_comment_user" class="required form_lbl">Name: <span class="red">*</span></label><div class="input-box"><input id="author post_comment_user" class="name input-text required-entry  form-control" name="author" type="text" value="Name" onfocus="if (this.value ==\'Name\') {this.value = \'\'}" onblur="if (this.value == \'\') {this.value=\'Name\'}" size="30" /></div></div>',
// 		'email'  => '' .
// 		            '<div class="form-group"><label for="post_comment_user" class="required form_lbl">Email: <span class="red">*</span></label><div class="input-box"><input id="email" class="email input-text required-entry form-control" name="email" type="text"  value="Email ID" onfocus="if (this.value ==\'Email ID\') {this.value = \'\'}" onblur="if (this.value == \'\') {this.value=\'Email ID\'}" size="30"' . $aria_req . ' /></div></div>',
// 		/*'url'    => '' .
// 		            '<input id="url" class="website" name="url" type="text" value="Website" onfocus="if (this.value ==\'Website\') {this.value = \'\'}" onblur="if (this.value == \'\') {this.value=\'Website\'}" size="30" /></p>',*/
// 	);
// 	$required_text = sprintf( ' ' . __('Required fields are marked %s'), '<span class="required">*</span>' );
// 	$defaults = array(
// 		'fields'               => apply_filters( 'comment_form_default_fields', $fields ),
// 		'comment_field'        => '<div class="form-group"><label for="post_comment" class="required  form_lbl">Comment: <span class="red">*</span></label><div class="input-box"><textarea id="comment" onfocus="if (this.value ==\'Post Comment\') {this.value = \'\'}" onblur="if (this.value == \'\') {this.value=\'Post Comment\'}" class="input-text required-entry form-control" name="comment" rows="4" aria-required="true"></textarea></div></div>',
// 		'must_log_in'          => '<p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
// 		'logged_in_as'         => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>' ), get_edit_user_link(), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
// 		/*'comment_notes_before' => '<p class="comment-notes">' . __( 'Your email address will not be published.' ) . ( $req ? $required_text : '' ) . '</p>',*/
// 		'comment_notes_after'  => '',
// 		'id_form'              => 'commentform',
// 		'id_submit'            => 'submit',
// 		'title_reply'          => __( 'Leave a Reply' ),
// 		'title_reply_to'       => __( 'Leave a Reply to %s' ),
// 		'cancel_reply_link'    => __( 'Cancel reply' ),
// 		'label_submit'         => __( 'Post Comment' ),
// 	);
// 	$args = wp_parse_args( $args, apply_filters( 'comment_form_defaults', $defaults ) );
/*	?>

		<?php if ( comments_open( $post_id ) ) : ?>
			<?php do_action( 'comment_form_before' ); ?>
			<div id="respond">
			<div class="comment-form">
    	<div class="reply_area">
       <!-- <h2 class="legend">Add new comment</h2>-->
        <ul class="form-list">
				<h3 class="comment-reply-title reply-title"><?php comment_form_title( $args['title_reply'], $args['title_reply_to'] ); ?> <span class="cancel-comment-reply-link">-&nbsp;&nbsp;&nbsp;<?php cancel_comment_reply_link( $args['cancel_reply_link'] ); ?></span></h3>
				<?php if ( get_option( 'comment_registration' ) && !is_user_logged_in() ) : ?>
					<?php echo $args['must_log_in']; ?>
					<?php do_action( 'comment_form_must_log_in_after' ); ?>
				<?php else : ?>
					<form action="<?php echo site_url( '/wp-comments-post.php' ); ?>" method="post" id="<?php echo esc_attr( $args['id_form'] ); ?>" class="entry-post" >
						<?php do_action( 'comment_form_top' ); ?>
						<?php if ( is_user_logged_in() ) : ?>
							<?php echo apply_filters( 'comment_form_logged_in', $args['logged_in_as'], $commenter, $user_identity ); ?>
							<?php do_action( 'comment_form_logged_in_after', $commenter, $user_identity ); ?>
						<?php else : ?>
							<?php echo $args['comment_notes_before']; ?>
							<?php
							do_action( 'comment_form_before_fields' );
							foreach ( (array) $args['fields'] as $name => $field ) {
								echo apply_filters( "comment_form_field_{$name}", $field ) . "\n";
							}
							do_action( 'comment_form_after_fields' );
							?>
						<?php endif; ?>
						<?php echo apply_filters( 'comment_form_field_comment', $args['comment_field'] ); ?>
						</ul>
						</div>
						<?php //echo $args['comment_notes_after']; ?>
						<div class="buttons-set">
						<p class="required"><span class="red">*</span>Required Fields</p>
							<div class="form-group">
							<input name="submit" type="submit" id="<?php echo esc_attr( $args['id_submit'] ); ?>" value="<?php echo esc_attr( $args['label_submit'] ); ?>" class="openstore" />
							</div>
						</div>
							<?php comment_id_fields( $post_id ); ?>
						</p>
						<?php do_action( 'comment_form', $post_id ); ?>
					</form>
					</div>
				<?php endif; ?>
			</div><!-- #respond -->
			<?php do_action( 'comment_form_after' ); ?>
		<?php else : ?>
			<?php do_action( 'comment_form_comments_closed' ); ?>
		<?php endif; ?>
	
	<?php
	
}
*/

/**
 * Set up post entry meta.
 *
 * Prints HTML with meta information for current post: categories, tags, permalink, author, and date.
 *
 * Create your own getcoins_entry_meta() to override in a child theme.
 *
 * @since Getcoins 1.0
 */
// if ( ! function_exists( 'getcoins_entry_meta' ) ) :
// function getcoins_entry_meta() {
// 	// Translators: used between list items, there is a space after the comma.
// 	$categories_list = get_the_category_list( __( ', ', 'getcoins' ) );

// 	// Translators: used between list items, there is a space after the comma.
// 	$tag_list = get_the_tag_list( '', __( ', ', 'getcoins' ) );

// 	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
// 		esc_url( get_permalink() ),
// 		esc_attr( get_the_time() ),
// 		esc_attr( get_the_date( 'c' ) ),
// 		esc_html( get_the_date() )
// 	);

// 	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
// 		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
// 		esc_attr( sprintf( __( 'View all posts by %s', 'getcoins' ), get_the_author() ) ),
// 		get_the_author()
// 	);

// 	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
// 	if ( $tag_list ) {
// 		$utility_text = __( 'This entry was posted in %1$s and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', 'getcoins' );
// 	} elseif ( $categories_list ) {
// 		$utility_text = __( 'This entry was posted in %1$s on %3$s<span class="by-author"> by %4$s</span>.', 'getcoins' );
// 	} else {
// 		$utility_text = __( 'This entry was posted on %3$s<span class="by-author"> by %4$s</span>.', 'getcoins' );
// 	}

// 	printf(
// 		$utility_text,
// 		$categories_list,
// 		$tag_list,
// 		$date,
// 		$author
// 	);
// }
// endif;

/**
 * Extend the default WordPress body classes.
 *
 * Extends the default WordPress body class to denote:
 * 1. Using a full-width layout, when no active widgets in the sidebar
 *    or full-width template.
 * 2. Front Page template: thumbnail in use and number of sidebars for
 *    widget areas.
 * 3. White or empty background color to change the layout and spacing.
 * 4. Custom fonts enabled.
 * 5. Single or multiple authors.
 *
 * @since Getcoins 1.0
 *
 * @param array $classes Existing class values.
 * @return array Filtered class values.
 */
// function getcoins_body_class( $classes ) {
// 	$background_color = get_background_color();
// 	$background_image = get_background_image();

// 	if ( ! is_active_sidebar( 'sidebar-1' ) || is_page_template( 'page-templates/full-width.php' ) )
// 		$classes[] = 'full-width';

// 	if ( is_page_template( 'page-templates/front-page.php' ) ) {
// 		$classes[] = 'template-front-page';
// 		if ( has_post_thumbnail() )
// 			$classes[] = 'has-post-thumbnail';
// 		if ( is_active_sidebar( 'sidebar-2' ) && is_active_sidebar( 'sidebar-3' ) )
// 			$classes[] = 'two-sidebars';
// 	}

// 	if ( empty( $background_image ) ) {
// 		if ( empty( $background_color ) )
// 			$classes[] = 'custom-background-empty';
// 		elseif ( in_array( $background_color, array( 'fff', 'ffffff' ) ) )
// 			$classes[] = 'custom-background-white';
// 	}

// 	// Enable custom font class only if the font CSS is queued to load.
// 	if ( wp_style_is( 'getcoins-fonts', 'queue' ) )
// 		$classes[] = 'custom-font-enabled';

// 	if ( ! is_multi_author() )
// 		$classes[] = 'single-author';

// 	return $classes;
// }
// add_filter( 'body_class', 'getcoins_body_class' );

/**
 * Adjust content width in certain contexts.
 *
 * Adjusts content_width value for full-width and single image attachment
 * templates, and when there are no active widgets in the sidebar.
 *
 * @since Getcoins 1.0
 */
// function getcoins_content_width() {
// 	if ( is_page_template( 'page-templates/full-width.php' ) || is_attachment() || ! is_active_sidebar( 'sidebar-1' ) ) {
// 		global $content_width;
// 		$content_width = 960;
// 	}
// }
// add_action( 'template_redirect', 'getcoins_content_width' );

/**
 * Register postMessage support.
 *
 * Add postMessage support for site title and description for the Customizer.
 *
 * @since Getcoins 1.0
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 */
// function getcoins_customize_register( $wp_customize ) {
// 	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
// 	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
// 	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

// 	if ( isset( $wp_customize->selective_refresh ) ) {
// 		$wp_customize->selective_refresh->add_partial( 'blogname', array(
// 			'selector' => '.site-title > a',
// 			'container_inclusive' => false,
// 			'render_callback' => 'getcoins_customize_partial_blogname',
// 		) );
// 		$wp_customize->selective_refresh->add_partial( 'blogdescription', array(
// 			'selector' => '.site-description',
// 			'container_inclusive' => false,
// 			'render_callback' => 'getcoins_customize_partial_blogdescription',
// 		) );
// 	}
// }
// add_action( 'customize_register', 'getcoins_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @since Getcoins 2.0
 * @see getcoins_customize_register()
 *
 * @return void
 */
// function getcoins_customize_partial_blogname() {
// 	bloginfo( 'name' );
// }

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @since Getcoins 2.0
 * @see getcoins_customize_register()
 *
 * @return void
 */
// function getcoins_customize_partial_blogdescription() {
// 	bloginfo( 'description' );
// }

/**
 * Enqueue Javascript postMessage handlers for the Customizer.
 *
 * Binds JS handlers to make the Customizer preview reload changes asynchronously.
 *
 * @since Getcoins 1.0
 */
// function getcoins_customize_preview_js() {
// 	wp_enqueue_script( 'getcoins-customizer', get_template_directory_uri() . '/js/theme-customizer.js', array( 'customize-preview' ), '20141120', true );
// }
// add_action( 'customize_preview_init', 'getcoins_customize_preview_js' );


/**
 * Modifies tag cloud widget arguments to display all tags in the same font size
 * and use list format for better accessibility.
 *
 * @since Getcoins 2.4
 *
 * @param array $args Arguments for tag cloud widget.
 * @return array The filtered arguments for tag cloud widget.
 */
// function getcoins_widget_tag_cloud_args( $args ) {
// 	$args['largest']  = 22;
// 	$args['smallest'] = 8;
// 	$args['unit']     = 'pt';
// 	$args['format']   = 'list';

// 	return $args;
// }
// add_filter( 'widget_tag_cloud_args', 'getcoins_widget_tag_cloud_args' );


