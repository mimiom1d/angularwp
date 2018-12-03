<?php

require_once(get_template_directory() .'/inc/awt-functions.php');
class gc_wp_ng_theme {
	
	public $test = ["age", "name", "gc_title", "city"];
	public $custom_field_arr = ["id","lat", "log", "name", "country", "region", "state", "state_name", "city",  "city_name", "street", "zipcode", "img", "html", "zoom", "hours", "title_name",  "title_seo"];
	
	function enqueue_scripts() {
		
		wp_enqueue_style( 'bootstrapCSS', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css', array(), '1.0', 'all' );
		wp_enqueue_script( 'angular-core', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular.min.js', array( 'jquery' ), '1.0', false );
		wp_enqueue_script('angular-resource', "//ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular-resource.js", array('angular-core'), '1.0', false);
		wp_enqueue_script('angular-route', "//ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular-route.js", array('angular-core'), '1.0', false);
		wp_enqueue_script( 'ui-router', 'https://unpkg.com/@uirouter/angularjs@1.0.7/release/angular-ui-router.min.js', array( 'angular-core' ), '1.0', false );
		wp_enqueue_script( 'ngScripts', get_template_directory_uri() . '/assets/js/angular-theme.js', array( 'ui-router' ), '1.0', false );
		wp_localize_script( 'ngScripts', 'appInfo',
			array(
				'api_url'			 => rest_get_url_prefix() . '/wp/v2/',
				'template_directory' => get_template_directory_uri() . '/',
				'nonce'				 => wp_create_nonce( 'wp_rest' ),
				'is_admin'			 => current_user_can('administrator')
			)
		);
		
	}

	
	function get_locations_data($arr_format = false){
		$url = get_site_url();
		$locationJsonUrl = ABSPATH . "json/locations.json";
		$locations = json_decode(file_get_contents($locationJsonUrl));
		return $locations;
	}
	// ** Below was WooCommerce specific deprecated function
	// function register_api_field( $object_type, $attributes, $args = array() ) { 
	// 	wc_deprecated_function( 'register_api_field', 'WPAPI-2.0', 'register_rest_field' ); 
	// 	register_rest_field( $object_type, $attributes, $args ); 
	// } 
	function register_new_field(){
		// if ( !function_exists( 'register_api_field' ) ) { 
		// 	// $link = '/inc/vendor/wp-rest-functions.php'; 
		// 	require_once (get_template_directory() . '/inc/vendor/wp-rest-functions.php');
		// } 
		// ** for testing purpose, use test variable
		foreach($this->test as $key => &$val){
			register_rest_field( // ** register_api_field was WC specific function and never included in WP
				'atm-location',
				// 'post',
				$val, 
				array('get_callback' => array($this, 'gc_custom_field'))
			);
		}
		// register_rest_field( // ** register_api_field was WC specific function and never included in WP
		// 	'post',
		// 	'my_awesome_field', 
		// 	array('get_callback' => array($this, 'awesome_field'))
		// );
	}
	function gc_custom_field($object, $field_name, $request){
		// return 'My awesome string';
		// var_dump($object);
		// echo $object['id'];
		// var_dump(get_post_meta($object['id'], $field_name));
		  return get_post_meta($object['id'], $field_name);
	}

	// ** Creating Rest API dir and retrieve the data with callback
	function gc_location_route() {
		register_rest_route( 'wp/v1', '/mimi', array(
			'methods' => 'GET',
			'callback' => array( $this, 'gc_location_route_callback' )
			)
		);
	}
	function gc_location_route_callback( $data ) {
		$jsonfile = $this->get_locations_data();
		$new_data = $jsonfile;
		// $new_data = array( 'name' => 'Mimi', 'age' => 20, 'location' => 'East Coast' );
		$response = new WP_REST_Response( $new_data );
		return $response;
	}


	function gcacf_route() {
		register_rest_route( 'wp/v1', '/gcacf', array(
			'methods' => 'GET',
			'callback' => array( $this, 'gcacf_route_callback' )
			)
		);
	}
	function gcacf_route_callback( $data ) {
		$jsonfile = $this->get_locations_data();
		$new_data = $jsonfile;
		$response = new WP_REST_Response( $new_data );

		return $response;
	}
	
}

$ngTheme = new gc_wp_ng_theme();
add_action( 'wp_enqueue_scripts', array( $ngTheme, 'enqueue_scripts' ) );
add_action( 'rest_api_init', array( $ngTheme, 'register_new_field' ) );
add_action( 'rest_api_init', array( $ngTheme, 'gc_location_route' ) );
add_action( 'rest_api_init', array( $ngTheme, 'gcacf_route' ) );


// ** ALL COMMENTED BELOW are for alternative way to add WP built in "Cutom Field(s)" to API

// if(!function_exists('gc_api_add_data')) {
// 	function gc_api_add_data($response, $post) {
// 		$response->data['gc_api_category'] = '';
// 		$categories = get_the_category($post->ID);
 
// 		if(count($categories)) {
// 			$category_slug = $categories[0]->slug;
// 			$response->data['gc_api_category'] = $category_slug;
// 		 }
// 	}
// }
 
// add_filter('rest_prepare_post', 'gc_api_add_data', 10, 3);

// function gc_api_add_post_data() {
//     register_rest_field('post',
//         'gc_api_field',
//         array(
//             'get_callback' => 'gc_api_get_field',
//             'update_callback' => 'gc_api_update_field',
//             'schema' => array(
//                                 'description' => 'My special field',
//                                 'type' => 'string',
//                                 'context' => array('view', 'edit')
//                             )
//         )
//     );
// }
 
// add_action('rest_api_init', 'gc_api_add_post_data');
// function awesome_field($object, $field_name, $request){
// 	return 'My awesome string';
// }
// function gc_api_get_field($post, $field_name, $request) {
// 	var_dump($post);
// 	var_dump($field_name);
//   return get_post_meta($post->id, $field_name);
// }
 
// function gc_api_update_field($value, $post, $field_name) {
//   if (!$value || !is_string($value)) {
//     return;
//   }
 
//   return update_post_meta($post->ID, $field_name, strip_tags($value));
// }
?>