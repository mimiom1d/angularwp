<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Custom Post Type Atm Location
add_action('init', 'atm_location_register_post_type');
function atm_location_register_post_type() {
    add_rewrite_tag('%city_name%','(.+)');
    add_rewrite_tag('%state_name%','(.+)');
    // add_rewrite_tag('%title_seo%','(.+)');

	register_post_type('atm-location', array(
		'labels' => array(
			'name'               => _x( 'Atm Locations', 'post type general name' ),
			'singular_name'      => _x( 'Atm Location', 'post type singular name' ),
			'add_new'            => _x( 'Add New', 'Atm Location' ),
			'add_new_item'       => __( 'Add New Atm Location' ),
			'edit_item'          => __( 'Edit Atm Location' ),
			'new_item'           => __( 'New Atm Location' ),
			'all_items'          => __( 'All Atm Locations' ),
			'view_item'          => __( 'View Atm Location' ),
			'search_items'       => __( 'Search Atm Location' ),
			'not_found'          => __( 'No Atm Location found' ),
			'not_found_in_trash' => __( 'No Atm Location found in the Trash' ),
			'parent_item_colon'  => '',
			'menu_name'          => 'Atm Locations'
		),
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true, 
		'show_in_menu' => true, 
		'query_var' => true,
		// 'rewrite' => array( 'slug' => 'bitcoin-atm-locations/%state_name%/%city_name%/%title_seo%' ),
		'rewrite' => array( 'slug' => 'bitcoin-atm-locations/%state_name%/%city_name%' ),
		'capability_type' => 'post',
		'capabilities' => array(
                    // 'create_posts' => 'do_not_allow', // false < WP 4.5, credit @Ewout
                ),
        'map_meta_cap' => true,
		'has_archive' => true, 
		'hierarchical' => false,
		'menu_position' => 3,
		'menu_icon'   => 'dashicons-location-alt',
		'supports' => array(
			'title',
			'editor',
			'excerpt',
			'custom-fields',
			'page-attributes',
			'thumbnail'
		),
		'taxonomies' => array('category', 'post_tag'),
		'show_in_rest'       => true,
		'rest_base'          => 'atm-locations-api',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	));
}

  /**
   * Register a genre post type, with REST API support
   *
   * Based on example at: https://codex.wordpress.org/Function_Reference/register_taxonomy
   */
  add_action( 'init', 'gc_atm_taxonomy', 30 );
  function gc_atm_taxonomy() {
  
  	$labels = array(
  		'name'              => _x( 'Stores', 'taxonomy general name' ),
  		'singular_name'     => _x( 'Store', 'taxonomy singular name' ),
  		'search_items'      => __( 'Search Stores' ),
  		'all_items'         => __( 'All Stores' ),
  		'parent_item'       => __( 'Parent Store' ),
  		'parent_item_colon' => __( 'Parent Store:' ),
  		'edit_item'         => __( 'Edit Store' ),
  		'update_item'       => __( 'Update Store' ),
  		'add_new_item'      => __( 'Add New Store' ),
  		'new_item_name'     => __( 'New Store Name' ),
  		'menu_name'         => __( 'Store' ),
  	);
  
  	$args = array(
  		'hierarchical'      => true,
  		'labels'            => $labels,
  		'show_ui'           => true,
  		'show_admin_column' => true,
  		'query_var'         => true,
  		'rewrite'           => array( 'slug' => 'atm' ),
  		'show_in_rest'       => true,
  		'rest_base'          => 'atm-store',
  		'rest_controller_class' => 'WP_REST_Terms_Controller',
  	);
  
  	register_taxonomy( 'store', array( 'atm-location' ), $args );
  
  }


// ### Adding REST API Support To Existing Content Types
// When a custom post type or custom taxonomy has been added by code that you do not control, for example a theme or plugin you are using, you will need to add REST API support, after it is registered. The arguments are the same as in the previous examples, but need to be added to the global `$wp_post_types` and `$wp_taxonomies` arrays.

// Here is an example of adding REST API support to an existing custom post type:

// ```php
  /**
  * Add REST API support to an already registered post type.
  */
  add_action( 'init', 'gc_custom_post_type_rest_support', 25 );
  function gc_custom_post_type_rest_support() {
  	global $wp_post_types;
  
  	//be sure to set this to the name of your post type!
  	$post_type_name = 'atm-location';
  	if( isset( $wp_post_types[ $post_type_name ] ) ) {
  		$wp_post_types[$post_type_name]->show_in_rest = true;
  		$wp_post_types[$post_type_name]->rest_base = $post_type_name;
  		$wp_post_types[$post_type_name]->rest_controller_class = 'WP_REST_Posts_Controller';
  	}
  
  }
add_filter( 'post_updated_messages', 'atm_location_updated_messages' );
function atm_location_updated_messages( $messages ) {
	global $post, $post_ID;
	$messages['atm-location'] = array(
		0 => '', 
		1 => sprintf( __('Atm Location updated. <a href="%s">View Atm Location</a>'), esc_url( get_permalink($post_ID) ) ),
		2 => __('Custom field updated.'),
		3 => __('Custom field deleted.'),
		4 => __('Atm Location updated.'),
		5 => isset($_GET['revision']) ? sprintf( __('Atm Location restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Atm Location published. <a href="%s">View Atm Location</a>'), esc_url( get_permalink($post_ID) ) ),
		7 => __('Atm Location saved.'),
		8 => sprintf( __('Atm Location submitted. <a target="_blank" href="%s">Preview Atm Location</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __('Atm Location scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Atm Location</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __('Atm Location draft updated. <a target="_blank" href="%s">Preview Atm Location</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	);
	return $messages;
}

add_filter( 'post_type_link', 'gc_atm_post_link', 10, 3 ); // Where $priority is 10, $accepted_args is 3.
function gc_atm_post_link( $permalink, $post, $leavename ) {
    if ( stripos( $permalink, '%state_name%' ) === false )
        return $permalink;

    if ( is_object( $post ) && 'atm-location' === $post->post_type ) {

        $default_state = 'state';
		$default_city = 'city';
		$default_title = get_the_title($post->ID);
		// **IF state_name is indeed provided/autogenerated
        if( $state_name = get_post_meta( $post->ID, 'state_name', true ) ){// ** third param ($single) is to get the actual value of the meta data. SO in this case, we want to assign the value to the variable and not an array, so put true
            $permalink = str_replace( '%state_name%', $state_name, $permalink );
        } else {
            $permalink = str_replace( '%state_name%', $default_state, $permalink );
        }

        if( $city_name = get_post_meta( $post->ID, 'city_name', true ) ){
            $permalink = str_replace( '%city_name%', $city_name, $permalink );
        } else {
            $permalink = str_replace( '%city_name%', $default_city, $permalink );
		}
		
		// $title_name = get_post_meta( $post->ID, 'title_seo', false ); // ** here with title, with $single = true, it returned Undefined variable notice error. So had to make it false, meaning returning an array, and then just use the first value like below.
        // if($title_name[0]){
        //     $permalink = str_replace( '%title_seo%', $title_name[0], $permalink );
        // } else {
        //     $permalink = str_replace( '%title_seo%', $default_title, $permalink );
        // }

    }

    return $permalink;
}


/**
 * Adds a submenu page under a custom post type parent.
 */

add_action('admin_menu', 'atm_location_synchronization_page');

function atm_location_synchronization_page() {
    add_submenu_page(
        'edit.php?post_type=atm-location',
        __( 'Atm Locations Synchronization', 'getcoins' ),
        __( '<i class="dashicons 
		dashicons-image-rotate dashicons-submenu"></i>Location Sync', 'getcoins' ),
        'manage_options',
        'locations-sync-page',
        'locations_sync_page_callback'
    );
}
 
/**
 * Display callback for the submenu page.
 */
function locations_sync_page_callback() { 
	$result = array();
	if(isset($_POST['do_sync']) && $_POST['do_sync'] == 1){
		if ( !class_exists( 'gc_import_json_locations' ) ) {
			require_once( dirname( __FILE__ ) . '/import-json-locations.php' );
		}

		$import_json = new gc_import_json_locations();
		$result = $import_json->import_locations();
	}
    ?>
    <div class="wrap">
    	<div class="location-sync-area">
	        <h1><?php _e( 'Atm Locations Synchronization', 'getcoins' ); ?></h1>
	        <form method="POST" action="" enctype="multipart/form-data">
	        	<input type="hidden" name="do_sync" value="1">
	        	<button type="submit" class="sync-btn"> Sync</button>
	        </form>

	        <?php if(count($result) > 0) {?>
	        	<div class="results">
	        		<?php if(isset($result['insert_locations'])) {?>
		        		<div class="insert-msg">
		        			<p>
		        				<sapn><?php  echo count($result['insert_locations']);?> locations inserted!</sapn>
		        			</p>
		        		</div>
	        		<?php }?>
	        		<?php if(isset($result['update_locations'])) {?>
		        		<div class="update-msg">
		        			<p>
		        				<sapn><?php  echo count($result['update_locations']);?> locations updated!</sapn>
		        			</p>
		        		</div>
	        		<?php }?>
	        		<?php if(isset($result['fail_locations'])) {?>
		        		<div class="update-msg">
		        			<p>
		        				<sapn><?php  echo count($result['fail_locations']);?> locations failed!</sapn>
		        			</p>
		        		</div>
	        		<?php }?>
	        	</div>
	    	<?php } ?>
    	</div>
    </div>
    <?php
}


function get_permalink_meta_id($location_id){
	global $wpdb;
	$unique_meta_key = 'id';
	$post_type = 'atm-location';
	$sql = "SELECT p.ID, p.guid   FROM ".$wpdb->prefix."posts as p 
        INNER JOIN ".$wpdb->prefix."postmeta as pm
        ON p.ID = pm.post_id 
        WHERE pm.meta_key = '".$unique_meta_key."' 
        AND pm.meta_value = $location_id
        AND p.post_type = '".$post_type."' 
        GROUP BY p.ID ORDER BY p.post_date DESC";
        $results = $wpdb->get_row($sql);
        return $results;
}