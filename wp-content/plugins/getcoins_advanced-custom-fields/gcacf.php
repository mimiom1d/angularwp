<?php
/*
Plugin Name: GetCoins' Advanced Custom Fields
Plugin URI: 
Description: Customise GetCoins WordPress Site with powerful, professional and intuitive fields. (ONLY FOR INTERNAL USE ONLY)
Version: 1.0.0
Author: EvergreenATM (Mimi Maldonado)
Credit: Elliot Condon (This is a clone of his work; NO COMMERCIAL USE! ONLY FOR INTERNAL USE!)
Text Domain: gcacf
Domain Path: /lang
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('GCACF') ) :

class GCACF {
	
	/** @var string The plugin version number */
	var $version = '1.0.0';
	
	/** @var array The plugin settings array */
	var $settings = array();
	
	/** @var array The plugin data array */
	var $data = array();
	
	/** @var array Storage for class instances */
	var $instances = array();
	
	
	/*
	*  __construct
	*
	*  A dummy constructor to ensure GCACF is only initialized once
	*
	*  @type	function
	*  @date	10/31/2018
	*  @since	1.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct() {
		
		/* Do nothing here */
		
	}
	
	
	/*
	*  initialize
	*
	*  The real constructor to initialize GCACF
	*
	*  @type	function
	*  @date	10/31/2018
	*  @since	1.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
		
	function initialize() {
		
		// vars
		$version = $this->version;
		$basename = plugin_basename( __FILE__ );
		$path = plugin_dir_path( __FILE__ );
		$url = plugin_dir_url( __FILE__ );
		$slug = dirname($basename);
		
		
		// settings
		$this->settings = array(
			
			// basic
			'name'				=> __('GC Advanced Custom Fields', 'gcacf'),
			'version'			=> $version,
			'required'           => true,   // **GCEdit: added required true so automatically warm users
						
			// urls
			'file'				=> __FILE__,
			'basename'			=> $basename,
			'path'				=> $path,
			'url'				=> $url,
			'slug'				=> $slug,
			
			// options
			'show_admin'				=> true,
			'show_updates'				=> true,
			'stripslashes'				=> false,
			'local'						=> true,
			'json'						=> true,
			'save_json'					=> '',
			'load_json'					=> array(),
			'default_language'			=> '',
			'current_language'			=> '',
			'capability'				=> 'manage_options',
			'uploader'					=> 'wp',
			'autoload'					=> false,
			'l10n'						=> true,
			'l10n_textdomain'			=> '',
			'google_api_key'			=> '',
			'google_api_client'			=> '',
			'enqueue_google_maps'		=> true,
			'enqueue_select2'			=> true,
			'enqueue_datepicker'		=> true,
			'enqueue_datetimepicker'	=> true,
			'select2_version'			=> 4,
			'row_index_offset'			=> 1,
			// 'remove_wp_meta_box'		=> true // **GCEdit: original
			'remove_wp_meta_box'		=> false // **GCEdit: made it falase so that users can control from admin backend
		);
		
		
		// constants
		$this->define( 'GCACF', 			true );
		$this->define( 'GCACF_VERSION', 	$version );
		$this->define( 'GCACF_PATH', 		$path );
		//$this->define( 'GCACF_DEV', 		true );
		
		
		// api
		include_once( GCACF_PATH . 'includes/api/api-helpers.php');
		gcacf_include('includes/api/api-input.php');
		gcacf_include('includes/api/api-value.php');
		gcacf_include('includes/api/api-field.php');
		gcacf_include('includes/api/api-field-group.php');
		gcacf_include('includes/api/api-template.php');
		gcacf_include('includes/api/api-term.php');
		
		// fields
		gcacf_include('includes/fields.php');
		gcacf_include('includes/fields/class-gcacf-field.php');
				
		
		// locations
		gcacf_include('includes/locations.php');
		gcacf_include('includes/locations/class-gcacf-location.php');
		
		
		// core
		gcacf_include('includes/assets.php');
		gcacf_include('includes/cache.php');
		gcacf_include('includes/compatibility.php');
		gcacf_include('includes/deprecated.php');
		gcacf_include('includes/form.php');
		gcacf_include('includes/json.php');
		gcacf_include('includes/local.php');
		gcacf_include('includes/loop.php');
		gcacf_include('includes/media.php');
		gcacf_include('includes/revisions.php');
		gcacf_include('includes/updates.php');
		gcacf_include('includes/upgrades.php');
		gcacf_include('includes/validation.php');
		
		// ajax
		gcacf_include('includes/ajax/class-gcacf-ajax.php');
		gcacf_include('includes/ajax/class-gcacf-ajax-check-screen.php');
		gcacf_include('includes/ajax/class-gcacf-ajax-user-setting.php');
		gcacf_include('includes/ajax/class-gcacf-ajax-upgrade.php');
		gcacf_include('includes/ajax/class-gcacf-ajax-query.php');
		gcacf_include('includes/ajax/class-gcacf-ajax-query-terms.php');
		
		// forms
		gcacf_include('includes/forms/form-attachment.php');
		gcacf_include('includes/forms/form-comment.php');
		gcacf_include('includes/forms/form-customizer.php');
		gcacf_include('includes/forms/form-front.php');
		gcacf_include('includes/forms/form-nav-menu.php');
		gcacf_include('includes/forms/form-post.php');
		gcacf_include('includes/forms/form-taxonomy.php');
		gcacf_include('includes/forms/form-user.php');
		gcacf_include('includes/forms/form-widget.php');
		
		
		// admin
		if( is_admin() ) {
			gcacf_include('includes/admin/admin.php');
			gcacf_include('includes/admin/admin-field-group.php');
			gcacf_include('includes/admin/admin-field-groups.php');
			gcacf_include('includes/admin/admin-tools.php');
			gcacf_include('includes/admin/admin-upgrade.php');
			gcacf_include('includes/admin/settings-info.php');
		}
		
		
		// pro
		gcacf_include('pro/gcacf-pro.php');
		
		
		// actions
		add_action('init',	array($this, 'init'), 5);
		add_action('init',	array($this, 'register_post_types'), 5);
		add_action('init',	array($this, 'register_post_status'), 5);
		
		
		// filters
		add_filter('posts_where',		array($this, 'posts_where'), 10, 2 );
		//add_filter('posts_request',	array($this, 'posts_request'), 10, 1 );
	}
	
	
	/*
	*  init
	*
	*  This function will run after all plugins and theme functions have been included
	*
	*  @type	action (init)
	*  @date	10/31/2018
	*  @since	1.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function init() {
		
		// bail early if too early
		// ensures all plugins have a chance to add fields, etc
		if( !did_action('plugins_loaded') ) return;
		
		
		// bail early if already init
		if( gcacf_has_done('init') ) return;
		
		
		// vars
		$major = intval( gcacf_get_setting('version') );
		
		
		// update url
		// - allow another plugin to modify dir (maybe force SSL)
		gcacf_update_setting('url', plugin_dir_url( __FILE__ ));
		
		
		// textdomain
		$this->load_plugin_textdomain();
		
		// include 3rd party support
		gcacf_include('includes/third-party.php');
		
		// include wpml support
		if( defined('ICL_SITEPRESS_VERSION') ) {
			gcacf_include('includes/wpml.php');
		}
		
		// early access
		if( defined('GCACF_EARLY_ACCESS') ) {
			gcacf_include('includes/early-access.php');
		}
		
		// include gutenberg
		if( defined('GUTENBERG_VERSION') ) {
			gcacf_include('includes/forms/form-gutenberg.php');
		}
		
		// fields
		gcacf_include('includes/fields/class-gcacf-field-text.php');
		gcacf_include('includes/fields/class-gcacf-field-textarea.php');
		gcacf_include('includes/fields/class-gcacf-field-number.php');
		gcacf_include('includes/fields/class-gcacf-field-range.php');
		gcacf_include('includes/fields/class-gcacf-field-email.php');
		gcacf_include('includes/fields/class-gcacf-field-url.php');
		gcacf_include('includes/fields/class-gcacf-field-password.php');
		
		gcacf_include('includes/fields/class-gcacf-field-image.php');
		gcacf_include('includes/fields/class-gcacf-field-file.php');
		gcacf_include('includes/fields/class-gcacf-field-wysiwyg.php');
		gcacf_include('includes/fields/class-gcacf-field-oembed.php');
		
		gcacf_include('includes/fields/class-gcacf-field-select.php');
		gcacf_include('includes/fields/class-gcacf-field-checkbox.php');
		gcacf_include('includes/fields/class-gcacf-field-radio.php');
		gcacf_include('includes/fields/class-gcacf-field-button-group.php');
		gcacf_include('includes/fields/class-gcacf-field-true_false.php');
		
		gcacf_include('includes/fields/class-gcacf-field-link.php');
		gcacf_include('includes/fields/class-gcacf-field-post_object.php');
		gcacf_include('includes/fields/class-gcacf-field-page_link.php');
		gcacf_include('includes/fields/class-gcacf-field-relationship.php');
		gcacf_include('includes/fields/class-gcacf-field-taxonomy.php');
		gcacf_include('includes/fields/class-gcacf-field-user.php');
		
		gcacf_include('includes/fields/class-gcacf-field-google-map.php');
		gcacf_include('includes/fields/class-gcacf-field-date_picker.php');
		gcacf_include('includes/fields/class-gcacf-field-date_time_picker.php');
		gcacf_include('includes/fields/class-gcacf-field-time_picker.php');
		gcacf_include('includes/fields/class-gcacf-field-color_picker.php');
		
		gcacf_include('includes/fields/class-gcacf-field-message.php');
		gcacf_include('includes/fields/class-gcacf-field-accordion.php');
		gcacf_include('includes/fields/class-gcacf-field-tab.php');
		gcacf_include('includes/fields/class-gcacf-field-group.php');
		do_action('gcacf/include_field_types', $major);
		
		
		// locations
		gcacf_include('includes/locations/class-gcacf-location-post-type.php');
		gcacf_include('includes/locations/class-gcacf-location-post-template.php');
		gcacf_include('includes/locations/class-gcacf-location-post-status.php');
		gcacf_include('includes/locations/class-gcacf-location-post-format.php');
		gcacf_include('includes/locations/class-gcacf-location-post-category.php');
		gcacf_include('includes/locations/class-gcacf-location-post-taxonomy.php');
		gcacf_include('includes/locations/class-gcacf-location-post.php');
		gcacf_include('includes/locations/class-gcacf-location-page-template.php');
		gcacf_include('includes/locations/class-gcacf-location-page-type.php');
		gcacf_include('includes/locations/class-gcacf-location-page-parent.php');
		gcacf_include('includes/locations/class-gcacf-location-page.php');
		gcacf_include('includes/locations/class-gcacf-location-current-user.php');
		gcacf_include('includes/locations/class-gcacf-location-current-user-role.php');
		gcacf_include('includes/locations/class-gcacf-location-user-form.php');
		gcacf_include('includes/locations/class-gcacf-location-user-role.php');
		gcacf_include('includes/locations/class-gcacf-location-taxonomy.php');
		gcacf_include('includes/locations/class-gcacf-location-attachment.php');
		gcacf_include('includes/locations/class-gcacf-location-comment.php');
		gcacf_include('includes/locations/class-gcacf-location-widget.php');
		gcacf_include('includes/locations/class-gcacf-location-nav-menu.php');
		gcacf_include('includes/locations/class-gcacf-location-nav-menu-item.php');
		do_action('gcacf/include_location_rules', $major);
		
		
		// local fields
		do_action('gcacf/include_fields', $major);
		
		
		// action for 3rd party
		do_action('gcacf/init');
			
	}
	
	
	/*
	*  load_plugin_textdomain
	*
	*  This function will load the textdomain file
	*
	*  @type	function
	*  @date	10/31/2018
	*  @since	1.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function load_plugin_textdomain() {
		
		// vars
		$domain = 'gcacf';
		$locale = apply_filters( 'plugin_locale', gcacf_get_locale(), $domain );
		$mofile = $domain . '-' . $locale . '.mo';
		
		
		// load from the languages directory first
		load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile );
		
		
		// redirect missing translations
		$mofile = str_replace('fr_CA', 'fr_FR', $mofile);
		
		
		// load from plugin lang folder
		load_textdomain( $domain, gcacf_get_path( 'lang/' . $mofile ) );
		
	}
	
	
	/*
	*  register_post_types
	*
	*  This function will register post types and statuses
	*
	*  @type	function
	*  @date	10/31/2018
	*  @since	1.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function register_post_types() {
		
		// vars
		$cap = gcacf_get_setting('capability');
		
		
		// register post type 'gcacf-field-group'
		register_post_type('gcacf-field-group', array(
			'labels'			=> array(
			    'name'					=> __( 'GC Field Groups', 'gcacf' ),
				'singular_name'			=> __( 'GC Field Group', 'gcacf' ),
			    'add_new'				=> __( 'Add New' , 'gcacf' ),
			    'add_new_item'			=> __( 'Add New GC Field Group' , 'gcacf' ),
			    'edit_item'				=> __( 'Edit GC Field Group' , 'gcacf' ),
			    'new_item'				=> __( 'New GC Field Group' , 'gcacf' ),
			    'view_item'				=> __( 'View GC Field Group', 'gcacf' ),
			    'search_items'			=> __( 'Search GC Field Groups', 'gcacf' ),
			    'not_found'				=> __( 'No GC Field Groups found', 'gcacf' ),
			    'not_found_in_trash'	=> __( 'No GC Field Groups found in Trash', 'gcacf' ), 
			),
			'public'			=> false,
			'show_ui'			=> true,
			'_builtin'			=> false,
			'capability_type'	=> 'post',	
			'capabilities'		=> array(
				'edit_post'			=> $cap,
				'delete_post'		=> $cap,
				'edit_posts'		=> $cap,
				'delete_posts'		=> $cap,
			),
			'hierarchical'		=> true,
			'rewrite'			=> false,
			'query_var'			=> false,
			'supports' 			=> array('title'),
			'show_in_menu'		=> false,
		));
		
		
		// register post type 'gcacf-field'
		register_post_type('gcacf-field', array(
			'labels'			=> array(
			    'name'					=> __( 'Fields', 'gcacf' ),
				'singular_name'			=> __( 'Field', 'gcacf' ),
			    'add_new'				=> __( 'Add New' , 'gcacf' ),
			    'add_new_item'			=> __( 'Add New Field' , 'gcacf' ),
			    'edit_item'				=> __( 'Edit Field' , 'gcacf' ),
			    'new_item'				=> __( 'New Field' , 'gcacf' ),
			    'view_item'				=> __( 'View Field', 'gcacf' ),
			    'search_items'			=> __( 'Search Fields', 'gcacf' ),
			    'not_found'				=> __( 'No Fields found', 'gcacf' ),
			    'not_found_in_trash'	=> __( 'No Fields found in Trash', 'gcacf' ), 
			),
			'public'			=> false,
			'show_ui'			=> false,
			'_builtin'			=> false,
			'capability_type'	=> 'post',
			'capabilities'		=> array(
				'edit_post'			=> $cap,
				'delete_post'		=> $cap,
				'edit_posts'		=> $cap,
				'delete_posts'		=> $cap,
			),
			'hierarchical'		=> true,
			'rewrite'			=> false,
			'query_var'			=> false,
			'supports' 			=> array('title'),
			'show_in_menu'		=> false,
		));
		
	}
	
	
	/*
	*  register_post_status
	*
	*  This function will register custom post statuses
	*
	*  @type	function
	*  @date	10/31/2018
	*  @since	1.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function register_post_status() {
		
		// gcacf-disabled
		register_post_status('gcacf-disabled', array(
			'label'                     => __( 'Inactive', 'gcacf' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'gcacf' ),
		));
		
	}
	
	
	/*
	*  posts_where
	*
	*  This function will add in some new parameters to the WP_Query args allowing fields to be found via key / name
	*
	*  @type	filter
	*  @date	10/31/2018
	*  @since	1.0.0
	*
	*  @param	$where (string)
	*  @param	$wp_query (object)
	*  @return	$where (string)
	*/
	
	function posts_where( $where, $wp_query ) {
		
		// global
		global $wpdb;
		
		
		// gcacf_field_key
		if( $field_key = $wp_query->get('gcacf_field_key') ) {
			$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_name = %s", $field_key );
	    }
	    
	    // gcacf_field_name
	    if( $field_name = $wp_query->get('gcacf_field_name') ) {
			$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_excerpt = %s", $field_name );
	    }
	    
	    // gcacf_group_key
		if( $group_key = $wp_query->get('gcacf_group_key') ) {
			$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_name = %s", $group_key );
	    }
	    
	    
	    // return
	    return $where;
	    
	}
	
	
	/*
	*  define
	*
	*  This function will safely define a constant
	*
	*  @type	function
	*  @date	10/31/2018
	*  @since	1.0.0
	*
	*  @param	$name (string)
	*  @param	$value (mixed)
	*  @return	n/a
	*/
	
	function define( $name, $value = true ) {
		
		if( !defined($name) ) {
			define( $name, $value );
		}
		
	}
	
	/**
	*  has_setting
	*
	*  Returns true if has setting.
	*
	*  @date	10/31/2018
	*  @since	1.0.0
	*
	*  @param	string $name
	*  @return	boolean
	*/
	
	function has_setting( $name ) {
		return isset($this->settings[ $name ]);
	}
	
	/**
	*  get_setting
	*
	*  Returns a setting.
	*
	*  @date	10/31/2018
	*  @since	1.0.0
	*
	*  @param	string $name
	*  @return	mixed
	*/
	
	function get_setting( $name ) {
		return isset($this->settings[ $name ]) ? $this->settings[ $name ] : null;
	}
	
	/**
	*  update_setting
	*
	*  Updates a setting.
	*
	*  @date	10/31/2018
	*  @since	1.0.0
	*
	*  @param	string $name
	*  @param	mixed $value
	*  @return	n/a
	*/
	
	function update_setting( $name, $value ) {
		$this->settings[ $name ] = $value;
		return true;
	}
	
	/**
	*  get_data
	*
	*  Returns data.
	*
	*  @date	10/31/2018
	*  @since	1.0.0
	*
	*  @param	string $name
	*  @return	mixed
	*/
	
	function get_data( $name ) {
		return isset($this->data[ $name ]) ? $this->data[ $name ] : null;
	}
	
	
	/**
	*  set_data
	*
	*  Sets data.
	*
	*  @date	10/31/2018
	*  @since	1.0.0
	*
	*  @param	string $name
	*  @param	mixed $value
	*  @return	n/a
	*/
	
	function set_data( $name, $value ) {
		$this->data[ $name ] = $value;
	}
	
	
	/**
	*  get_instance
	*
	*  Returns an instance.
	*
	*  @date	10/31/2018
	*  @since	1.0.0
	*
	*  @param	string $class The instance class name.
	*  @return	object
	*/
	
	function get_instance( $class ) {
		$name = strtolower($class);
		return isset($this->instances[ $name ]) ? $this->instances[ $name ] : null;
	}
	
	/**
	*  new_instance
	*
	*  Creates and stores an instance.
	*
	*  @date	10/31/2018
	*  @since	1.0.0
	*
	*  @param	string $class The instance class name.
	*  @return	object
	*/
	
	function new_instance( $class ) {
		$instance = new $class();
		$name = strtolower($class);
		$this->instances[ $name ] = $instance;
		return $instance;
	}
	
}


/*
*  gcacf
*
*  The main function responsible for returning the one true gcacf Instance to functions everywhere.
*  Use this function like you would a global variable, except without needing to declare the global.
*
*  Example: <?php $gcacf = gcacf(); ?>
*
*  @type	function
*  @date	10/31/2018
*  @since	1.0.0
*
*  @param	N/A
*  @return	(object)
*/

function gcacf() {
	
	// globals
	global $gcacf;
	
	
	// initialize
	if( !isset($gcacf) ) {
		$gcacf = new GCACF();
		$gcacf->initialize();
	}
	
	
	// return
	return $gcacf;
	
}


// initialize
gcacf();


endif; // class_exists check

?>
