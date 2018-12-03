<?php

if( ! class_exists('gcacf_field_user') ) :

class gcacf_field_user extends gcacf_field {
	
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize() {
		
		// vars
		$this->name = 'user';
		$this->label = __("User",'gcacf');
		$this->category = 'relational';
		$this->defaults = array(
			'role' 			=> '',
			'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'return_format'	=> 'array',
		);
		
		
		// extra
		add_action('wp_ajax_gcacf/fields/user/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_gcacf/fields/user/query',	array($this, 'ajax_query'));
    	
	}

	
	/*
	*  ajax_query
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_query() {
		
		// validate
		if( !gcacf_verify_ajax() ) die();
		
		
		// get choices
		$response = $this->get_ajax_query( $_POST );
		
		
		// return
		gcacf_send_ajax_results($response);
			
	}
	
	
	/*
	*  get_ajax_query
	*
	*  This function will return an array of data formatted for use in a select2 AJAX response
	*
	*  @type	function
	*  @date	15/10/2014
	*  @since	5.0.9
	*
	*  @param	$options (array)
	*  @return	(array)
	*/
	
	function get_ajax_query( $options = array() ) {
		
		// defaults
   		$options = gcacf_parse_args($options, array(
			'post_id'		=> 0,
			's'				=> '',
			'field_key'		=> '',
			'paged'			=> 1
		));
		
		
		// load field
		$field = gcacf_get_field( $options['field_key'] );
		if( !$field ) return false;
		
		
   		// vars
   		$results = array();
   		$args = array();
   		$s = false;
   		$is_search = false;
   		
		
		// paged
   		$args['users_per_page'] = 20;
   		$args['paged'] = $options['paged'];
   		
   		
   		// search
		if( $options['s'] !== '' ) {
			
			// strip slashes (search may be integer)
			$s = wp_unslash( strval($options['s']) );
			
			
			// update vars
			$args['s'] = $s;
			$is_search = true;
			
		}
		
		
		// role
		if( !empty($field['role']) ) {
		
			$args['role'] = gcacf_get_array( $field['role'] );
			
		}
		
		
		// search
		if( $is_search ) {
			
			// append to $args
			$args['search'] = '*' . $options['s'] . '*';
			
			
			// add reference
			$this->field = $field;
			
			
			// add filter to modify search colums
			add_filter('user_search_columns', array($this, 'user_search_columns'), 10, 3);
			
		}
		
		
		// filters
		$args = apply_filters("gcacf/fields/user/query",							$args, $field, $options['post_id']);
		$args = apply_filters("gcacf/fields/user/query/name={$field['_name']}",	$args, $field, $options['post_id']);
		$args = apply_filters("gcacf/fields/user/query/key={$field['key']}",		$args, $field, $options['post_id']);
		
		
		// get users
		$groups = gcacf_get_grouped_users( $args );
		
		
		// loop
		if( !empty($groups) ) {
			
			foreach( array_keys($groups) as $group_title ) {
				
				// vars
				$users = gcacf_extract_var( $groups, $group_title );
				$data = array(
					'text'		=> $group_title,
					'children'	=> array()
				);
				
				
				// append users
				foreach( array_keys($users) as $user_id ) {
					
					$users[ $user_id ] = $this->get_result( $users[ $user_id ], $field, $options['post_id'] );
					
				};
				
				
				// order by search
				if( $is_search && empty($args['orderby']) ) {
					
					$users = gcacf_order_by_search( $users, $args['s'] );
					
				}
				
				
				// append to $data
				foreach( $users as $id => $title ) {
					
					$data['children'][] = array(
						'id'	=> $id,
						'text'	=> $title
					);
					
				}
				
				
				// append to $r
				$results[] = $data;
				
			}
			
			
		}
		
		// optgroup or single
		if( !empty($args['role']) && count($args['role']) == 1 ) {
			
			$results = $results[0]['children'];
			
		}
		
		
		// vars
		$response = array(
			'results'	=> $results,
			'limit'		=> $args['users_per_page']
		);
		
		
		// return
		return $response;
		
	}
	
	
	
	/*
	*  get_result
	*
	*  This function returns the HTML for a result
	*
	*  @type	function
	*  @date	1/11/2013
	*  @since	5.0.0
	*
	*  @param	$post (object)
	*  @param	$field (array)
	*  @param	$post_id (int) the post_id to which this value is saved to
	*  @return	(string)
	*/
	
	function get_result( $user, $field, $post_id = 0 ) {
		
		// get post_id
		if( !$post_id ) $post_id = gcacf_get_form_data('post_id');
		
		
		// vars
		$result = $user->user_login;
		
		
		// append name
		if( $user->first_name ) {
			
			$result .= ' (' .  $user->first_name;
			
			if( $user->last_name ) {
				
				$result .= ' ' . $user->last_name;
				
			}
			
			$result .= ')';
			
		}
		
		
		// filters
		$result = apply_filters("gcacf/fields/user/result",							$result, $user, $field, $post_id);
		$result = apply_filters("gcacf/fields/user/result/name={$field['_name']}",	$result, $user, $field, $post_id);
		$result = apply_filters("gcacf/fields/user/result/key={$field['key']}",		$result, $user, $field, $post_id);
		
		
		// return
		return $result;
		
	}
	
	
	/*
	*  user_search_columns
	*
	*  This function will modify the columns which the user AJAX search looks in
	*
	*  @type	function
	*  @date	17/06/2014
	*  @since	5.0.0
	*
	*  @param	$columns (array)
	*  @return	$columns
	*/
	
	function user_search_columns( $columns, $search, $WP_User_Query ) {
		
		// bail early if no field
		if( empty($this->field) ) {
			
			return $columns;
			
		}
		
		
		// vars
		$field = $this->field;
		
		
		// filter for 3rd party customization
		$columns = apply_filters("gcacf/fields/user/search_columns", 							$columns, $search, $WP_User_Query, $field);
		$columns = apply_filters("gcacf/fields/user/search_columns/name={$field['_name']}",	$columns, $search, $WP_User_Query, $field);
		$columns = apply_filters("gcacf/fields/user/search_columns/key={$field['key']}",		$columns, $search, $WP_User_Query, $field);
		
		
		// return
		return $columns;
		
	}
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - an array holding all the field's data
	*/
	
	function render_field( $field ) {
		
		// Change Field into a select
		$field['type'] = 'select';
		$field['ui'] = 1;
		$field['ajax'] = 1;
		$field['choices'] = array();
		
		
		// populate choices
		if( !empty($field['value']) ) {
			
			// force value to array
			$field['value'] = gcacf_get_array( $field['value'] );
			
			
			// convert values to int
			$field['value'] = array_map('intval', $field['value']);
			
			
			$users = get_users(array(
				'include' => $field['value']
			));
			
			
			if( !empty($users) ) {
			
				foreach( $users as $user ) {
				
					$field['choices'][ $user->ID ] = $this->get_result( $user, $field );
					
				}
				
			}
			
		}
		
		
		// render
		gcacf_render_field( $field );
		
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_settings( $field ) {
		
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Filter by role','gcacf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'role',
			'choices'		=> gcacf_get_pretty_user_roles(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All user roles",'gcacf'),
		));
		
		
		
		// allow_null
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Allow Null?','gcacf'),
			'instructions'	=> '',
			'name'			=> 'allow_null',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		
		// multiple
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Select multiple values?','gcacf'),
			'instructions'	=> '',
			'name'			=> 'multiple',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		// return_format
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Return Format','gcacf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'choices'		=> array(
				'array'			=> __("User Array",'gcacf'),
				'object'		=> __("User Object",'gcacf'),
				'id'			=> __("User ID",'gcacf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
	
		// array?
		if( is_array($value) && isset($value['ID']) ) {
		
			$value = $value['ID'];	
			
		}
		
		// object?
		if( is_object($value) && isset($value->ID) ) {
		
			$value = $value->ID;
			
		}
		
		
		// return
		return $value;
	}
	
	
	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	function load_value( $value, $post_id, $field ) {
		
		// GCACF4 null
		if( $value === 'null' ) {
		
			return false;
			
		}
		
		
		// return
		return $value;
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/
	
	function format_value( $value, $post_id, $field ) {
		
		// bail early if no value
		if( empty($value) ) {
			return false;
		}
		
		// ensure array
		$value = gcacf_get_array( $value );
		
		// update value
		foreach( array_keys($value) as $i ) {
			$value[ $i ] = $this->format_value_single( $value[ $i ], $post_id, $field );
		}
		
		// convert to single
		if( !$field['multiple'] ) {
			$value = array_shift($value);
		}
		
		// return value
		return $value;
		
	}
	
	function format_value_single( $value, $post_id, $field ) {
		
		// vars
		$user_id = (int) $value;
		
		// object
		if( $field['return_format'] == 'object' ) {
			$value = get_userdata( $user_id );
		
		// array	
		} elseif( $field['return_format'] == 'array' ) {
			$wp_user = get_userdata( $user_id );
			$value = array(
				'ID'				=> $user_id,
				'user_firstname'	=> $wp_user->user_firstname,
				'user_lastname'		=> $wp_user->user_lastname,
				'nickname'			=> $wp_user->nickname,
				'user_nicename'		=> $wp_user->user_nicename,
				'display_name'		=> $wp_user->display_name,
				'user_email'		=> $wp_user->user_email,
				'user_url'			=> $wp_user->user_url,
				'user_registered'	=> $wp_user->user_registered,
				'user_description'	=> $wp_user->user_description,
				'user_avatar'		=> get_avatar( $user_id ),
			);
			
		// id		
		} else {
			$value = $user_id;
		}
		
		// return
		return $value;
		
	}
		
}


// initialize
gcacf_register_field_type( 'gcacf_field_user' );

endif; // class_exists check

?>