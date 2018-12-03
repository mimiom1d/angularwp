<?php
/**
 * Plugin Name: GCACF to WP API
 * Description: Puts all GCACF fields from posts, pages, custom post types, attachments and taxonomy terms, into the WP-API output under the 'gcacf' key (ONLY FOR INTERNAL USE ONLY)
 * Author: Mimi
 * Credit: Chris Hutchinson
 * Version: 1.0.0
 */

class GCACFtoWPAPI {

	/**
	 * @var object 	$plugin 			All base plugin configuration is stored here
	 */
	protected $plugin;

	/**
	 * @var string 	$apiVersion 		Stores the version number of the REST API
	 */
	protected $apiVersion;

	/**
	 * Constructor
	 *
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @since 1.3.0 	Updated to support version 2 of the WP-API
	 * @since 1.0.0
	 */
	function __construct() {
		// Setup defaults
		$this->plugin = new StdClass;
		$this->plugin->title = 'GCACF to WP API';
		$this->plugin->name = 'gcacf-to-wp-api';
        $this->plugin->folder = WP_PLUGIN_DIR . '/' . $this->plugin->name;
        $this->plugin->url = WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__), "", plugin_basename(__FILE__));
		$this->plugin->version = '1.0.0';

		$this->apiVersion = get_option( 'rest_api_plugin_version', get_option( 'json_api_plugin_version', null ) );

		// Version One
		if($this->_isAPIVersionOne()) {
			$this->_versionOneSetup();
		}

		// Version Two
		if($this->_isAPIVersionTwo()) {
			$this->_versionTwoSetup();	
		}
	}
	/**
	 * Die and dump
	 *
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @param mixed 	$data 	The data to be dumped to the screen
	 * 
	 * @return void
	 *
	 * @since 1.3.0
	 */
	private function dd($data) {
		if( WP_DEBUG ) {
			echo '<pre>';
			print_r($data);
			echo '</pre>';
			die();
		}
	}

	/**
	 * Adds the required filters and hooks for version 1 of the REST API
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @return void
	 *
	 * @since 1.3.0
	 */
	private function _versionOneSetup() {
		// Filters
		add_filter( 'json_prepare_post', array( $this, 'addGCACFDataPost'), 10, 3 ); // Posts
		add_filter( 'json_prepare_term', array( $this, 'addGCACFDataTerm'), 10, 3 ); // Taxonomy Terms
		add_filter( 'json_prepare_user', array( $this, 'addGCACFDataUser'), 10, 3 ); // Users
		add_filter( 'json_prepare_comment', array( $this, 'addGCACFDataComment'), 10, 3 ); // Comments

		// Endpoints
		add_filter( 'json_endpoints', array( $this, 'registerRoutes' ), 10, 3 );
	}

	/**
	 * Adds the required filters and hooks for version 2 of the REST API
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @return void
	 *
	 * @since 1.3.0
	 */
	private function _versionTwoSetup() {
		// Actions
		add_action( 'rest_api_init', array( $this, 'addGCACFDataPostV2' ) ); // Posts
		add_action( 'rest_api_init', array( $this, 'addGCACFDataTermV2' ) ); // Taxonomy Terms
		add_action( 'rest_api_init', array( $this, 'addGCACFDataUserV2' ) ); // Users
		add_action( 'rest_api_init', array( $this, 'addGCACFDataCommentV2' ) ); // Comments

		add_action( 'rest_api_init', array( $this, 'addGCACFOptionRouteV2') );
	}

	/**
	 * Gets the version number of the WP REST API
	 *
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @return int 	The base version number
	 *
	 * @since 1.3.0
	 */
	private function _getAPIBaseVersion() {
		$version = $this->apiVersion;

		if( is_null( $version ) ) {
			return false;
		}

		$baseNumber = substr( $version, 0, 1 );

		if( $baseNumber === '1' ) {
			return 1;
		}

		if( $baseNumber === '2' ) {
			return 2;
		}

		return false;
	}

	/**
	 * Check if the current API base version is version 1
	 *
	 * @return boolean 	True if the current API version is 1
	 *
	 * @since 1.3.0
	 */
	private function _isAPIVersionOne() {
		if($this->_getAPIBaseVersion() === 1) { 
			return true;
		}

		return false;
	}

	/**
	 * Check if the current API base version is version 2
	 *
	 * @return boolean 	True if the current API version is 2
	 *
	 * @since 1.3.0
	 */
	private function _isAPIVersionTwo() {
		if($this->_getAPIBaseVersion() === 2) { 
			return true;
		}

		return false;
	}

	/**
	 * Add data to users
	 *
	 * @param array 	$data 		The current GCACF data
	 * @param int 		$user 		The ID of the user
	 * @param string	$context 	The context the data is being requested in
	 *
	 * @since 1.1.0
	 */
	function addGCACFDataUser( $data, $user, $context ) {
		$data['gcacf'] = $this->_getData( $user->ID, 'user' );
		return $data;
	}

	/**
	 * Add data to terms
	 *
	 * @param array 	$data 		The current GCACF data
	 * @param int 		$term 		The ID of the term
	 * @param string	$context 	The context the data is being requested in
	 *
	 * @since 1.1.0
	 */
	function addGCACFDataTerm( $data, $term, $context = null ) {
		$data['gcacf'] = get_fields( $term, 'term' );
		return $data;
	}

	/**
	 * Add data to Posts, Custom Post Types, Pages & Attachments
	 *
	 * @param array 	$data 		The current GCACF data
	 * @param int 		$post 		The ID of the record
	 * @param string	$context 	The context the data is being requested in
	 *
	 * @since 1.1.0
	 */
	function addGCACFDataPost( $data, $post, $context ) {
		$data['gcacf'] = $this->_getData( $post['ID'] );
		return $data;
	}

	/**
	 * Registers the `gcacf` field against posts
	 *
	 * @return void
	 *
	 * @since 1.3.2 	Adds support for pages and public custom post types
	 * @since 1.3.0
	 */
	function addGCACFDataPostV2() {
		// Posts
		register_rest_field( 'post',
	        'gcacf',
	        array(
	            'get_callback'    => array( $this, 'addGCACFDataPostV2cb' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );

		// Pages
		register_rest_field( 'page',
	        'gcacf',
	        array(
	            'get_callback'    => array( $this, 'addGCACFDataPostV2cb' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );

		// Public custom post types
		$types = get_post_types(array(
			'public' => true,
			'_builtin' => false
		));
		foreach($types as $key => $type) {
			register_rest_field( $type,
		        'gcacf',
		        array(
		            'get_callback'    => array( $this, 'addGCACFDataPostV2cb' ),
		            'update_callback' => null,
		            'schema'          => null,
		        )
		    );
		}
	}
	
	/**
	 * Returns the ACF data to be added to the JSON response posts
	 * 
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @param array 	$object 		The object to get data for
	 * @param string 	$fieldName 		The name of the field being completed
	 * @param object 	$request 		The WP_REST_REQUEST object
	 * 
	 * @return array 	The data for this object type
	 *
	 * @see GCACFtoWPAPI::addGCACFDataPostV2()
	 *
	 * @since 1.3.0
	 */
	function addGCACFDataPostV2cb($object, $fieldName, $request) {
		return $this->_getData($object['id']);
	}

	/**
	 * Registers the `gcacf` field against taxonomy terms
	 *
	 * @return void
	 *
	 * @since 1.3.0
	 */
	function addGCACFDataTermV2() {
		register_rest_field( 'term',
	        'gcacf',
	        array(
	            'get_callback'    => array( $this, 'addGCACFDataTermV2cb' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	}

	/**
	 * Returns the ACF data to be added to the JSON response for taxonomy terms
	 * 
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @param array 	$object 		The object to get data for
	 * @param string 	$fieldName 		The name of the field being completed
	 * @param object 	$request 		The WP_REST_REQUEST object
	 * 
	 * @return array 	The data for this object type
	 *
	 * @see GCACFtoWPAPI::addGCACFDataTermV2()
	 *
	 * @since 1.3.0
	 */
	function addGCACFDataTermV2cb($object, $fieldName, $request) {
		return $this->_getData($object['id'], 'term', $object);
	}

	/**
	 * Registers the `gcacf` field against users
	 *
	 * @return void
	 *
	 * @since 1.3.0
	 */
	function addGCACFDataUserV2() {
		register_rest_field( 'user',
	        'gcacf',
	        array(
	            'get_callback'    => array( $this, 'addGCACFDataUserV2cb' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	}

	/**
	 * Returns the ACF data to be added to the JSON response for users
	 * 
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @param array 	$object 		The object to get data for
	 * @param string 	$fieldName 		The name of the field being completed
	 * @param object 	$request 		The WP_REST_REQUEST object
	 * 
	 * @return array 	The data for this object type
	 *
	 * @see GCACFtoWPAPI::addGCACFDataUserV2()
	 *
	 * @since 1.3.0
	 */
	function addGCACFDataUserV2cb($object, $fieldName, $request) {
		return $this->_getData( $object['id'], 'user' );
	}

	/**
	 * Registers the `gcacf` field against comments
	 *
	 * @return void
	 *
	 * @since 1.3.0
	 */
	function addGCACFDataCommentV2() {
		register_rest_field( 'comment',
	        'gcacf',
	        array(
	            'get_callback'    => array( $this, 'addGCACFDataCommentV2cb' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	}

	/**
	 * Returns the ACF data to be added to the JSON response for comments
	 * 
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @param array 	$object 		The object to get data for
	 * @param string 	$fieldName 		The name of the field being completed
	 * @param object 	$request 		The WP_REST_REQUEST object
	 * 
	 * @return array 	The data for this object type
	 *
	 * @see GCACFtoWPAPI::addGCACFDataCommentV2()
	 *
	 * @since 1.3.0
	 */
	function addGCACFDataCommentV2cb( $object, $fieldName, $request ) {
		return $this->_getData( $object['id'], 'comment' );
	}

	/**
	 * Returns an array of Advanced Custom Fields data for the given record
	 *
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 * 
	 * @param int 		$id 		The ID of the object to get
	 * @param string 	$type 		The type of the object to get
	 * @param array 	$object 	The full object being requested, only required for specific $types
	 *
	 * @return array 	The Advanced Custom Fields data for the supplied record
	 * 
	 * @since 1.3.0
	 */
	private function _getData($id, $type = 'post', $object = array()) {
		switch($type) {
			case 'post':
			default:
				return get_fields($id);
				break;
			case 'term':
				return get_fields($object['taxonomy'] . '_' . $id);
				break;
			case 'user':
				return get_fields('user_' . $id);
				break;
			case 'comment':
				return get_fields('comment_' . $id);
			 	break;
			case 'options':
				return get_fields('option');
				break;
		}
	}

	/**
	 * Registers the routes for all and single options
	 *
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @return void
	 *
	 * @since 1.3.1 	Switched to array() notation (over [] notation) to support PHP < 5.4
	 * @since 1.3.0
	 */
	function addGCACFOptionRouteV2() {
		register_rest_route( 'wp/v2/gcacf', '/options', array(
			'methods' => array(
				'GET'
			),
			'callback' => array( $this, 'addGCACFOptionRouteV2cb' )
		) );

		register_rest_route( 'wp/v2/gcacf', '/options/(?P<option>.+)', array(
			'methods' => array(
				'GET'
			),
			'callback' => array( $this, 'addGCACFOptionRouteV2cb' )
		) );
	}

	/**
	 * The callback for the `wp/v2/gcacf/options` endpoint
	 * 
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @param WP_REST_Request 	$request 	The WP_REST_Request object
	 *
	 * @return array|string 	The single requested option, or all options 
	 *
	 * @see GCACFtoWPAPI::addGCACFOptionRouteV2()
	 *
	 * @since 1.3.0
	 */
	function addGCACFOptionRouteV2cb( WP_REST_Request $request ) {
		if($request['option']) {
			return get_field($request['option'], 'option');
		}

		return get_fields('option');
	}

	/**
	 * Returns data for comments (WP API v1)
	 *
	 * @author Chris Hutchinson <chris_hutchinson@me.com>
	 *
	 * @param array 	$data 		The response data to be extended
	 * @param object 	$comment 	The comment being requested
	 * @param string	$context 	The context the data is being requested in
	 *
	 * @return array 	The extended $data array, with ACF data
	 *
	 * @since 1.1.0
	 *
	 */
	function addGCACFDataComment($data, $comment, $context) {
		$data['gcacf'] = $this->_getData('comment_' . $comment->comment_ID);
		return $data;
	}

	/**
	 * Returns data for options (WP API v1)
	 *
	 * @author github.com/kokarn
	 *
	 * @return array 	The options data
	 *
	 * @since 1.1.0
	 *
	 */
	function getGCACFOptions() {
		return get_fields('options');
	}

	/**
	 * Returns a single option based on the supplied name (WP API v1)
	 *
	 * @author github.com/asquel
	 *
	 * @param string 	$name 	The option name being requested
	 *
	 * @return mixed 	The data for the supplied option	
	 *
	 * @since 1.3.0
	 */
	function getGCACFOption($name) {
		return get_field($name, 'option');
	}

	/**
	 * Registers additional routes (WP API v1)
	 *
	 * @author github.com/kokarn
	 *
	 * @return array 	The routes data
	 *
	 * @since 1.1.0
	 *
	 */
	function registerRoutes( $routes ) {
		$routes['/option'] = array(
			array( array( $this, 'getGCACFOptions' ), WP_JSON_Server::READABLE )
		);
		$routes['/options'] = array(
			array( array( $this, 'getGCACFOptions' ), WP_JSON_Server::READABLE )
		);

		$routes['/options/(?P<name>[\w-]+)'] = array(
			array( array( $this, 'getGCACFOption' ), WP_JSON_Server::READABLE ),
		);

		return $routes;
	}

}

$GCACFtoWPAPI = new GCACFtoWPAPI();