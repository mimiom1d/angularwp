<?php

if( ! class_exists('gcacf_field_relationship') ) :

class gcacf_field_relationship extends gcacf_field {
	
	
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
		$this->name = 'relationship';
		$this->label = __("Relationship",'gcacf');
		$this->category = 'relational';
		$this->defaults = array(
			'post_type'			=> array(),
			'taxonomy'			=> array(),
			'min' 				=> 0,
			'max' 				=> 0,
			'filters'			=> array('search', 'post_type', 'taxonomy'),
			'elements' 			=> array(),
			'return_format'		=> 'object'
		);
		
		// extra
		add_action('wp_ajax_gcacf/fields/relationship/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_gcacf/fields/relationship/query',	array($this, 'ajax_query'));
    	
	}
	
	
	/*
	*  input_admin_enqueue_scripts
	*
	*  description
	*
	*  @type	function
	*  @date	16/12/2015
	*  @since	5.3.2
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function input_admin_enqueue_scripts() {
		
		// localize
		gcacf_localize_text(array(
			//'Minimum values reached ( {min} values )'	=> __('Minimum values reached ( {min} values )', 'gcacf'),
			'Maximum values reached ( {max} values )'	=> __('Maximum values reached ( {max} values )', 'gcacf'),
			'Loading'									=> __('Loading', 'gcacf'),
			'No matches found'							=> __('No matches found', 'gcacf'),
	   	));
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
			'paged'			=> 1,
			'post_type'		=> '',
			'taxonomy'		=> ''
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
   		$args['posts_per_page'] = 20;
   		$args['paged'] = $options['paged'];
   		
   		
   		// search
		if( $options['s'] !== '' ) {
			
			// strip slashes (search may be integer)
			$s = wp_unslash( strval($options['s']) );
			
			
			// update vars
			$args['s'] = $s;
			$is_search = true;
			
		}
		
		
		// post_type
		if( !empty($options['post_type']) ) {
			
			$args['post_type'] = gcacf_get_array( $options['post_type'] );
		
		} elseif( !empty($field['post_type']) ) {
		
			$args['post_type'] = gcacf_get_array( $field['post_type'] );
			
		} else {
			
			$args['post_type'] = gcacf_get_post_types();
			
		}
		
		
		// taxonomy
		if( !empty($options['taxonomy']) ) {
			
			// vars
			$term = gcacf_decode_taxonomy_term($options['taxonomy']);
			
			
			// tax query
			$args['tax_query'] = array();
			
			
			// append
			$args['tax_query'][] = array(
				'taxonomy'	=> $term['taxonomy'],
				'field'		=> 'slug',
				'terms'		=> $term['term'],
			);
			
			
		} elseif( !empty($field['taxonomy']) ) {
			
			// vars
			$terms = gcacf_decode_taxonomy_terms( $field['taxonomy'] );
			
			
			// append to $args
			$args['tax_query'] = array(
				'relation' => 'OR',
			);
			
			
			// now create the tax queries
			foreach( $terms as $k => $v ) {
			
				$args['tax_query'][] = array(
					'taxonomy'	=> $k,
					'field'		=> 'slug',
					'terms'		=> $v,
				);
				
			}
			
		}	
		
		
		// filters
		$args = apply_filters('gcacf/fields/relationship/query', $args, $field, $options['post_id']);
		$args = apply_filters('gcacf/fields/relationship/query/name=' . $field['name'], $args, $field, $options['post_id'] );
		$args = apply_filters('gcacf/fields/relationship/query/key=' . $field['key'], $args, $field, $options['post_id'] );
		
		
		// get posts grouped by post type
		$groups = gcacf_get_grouped_posts( $args );
		
		
		// bail early if no posts
		if( empty($groups) ) return false;
		
		
		// loop
		foreach( array_keys($groups) as $group_title ) {
			
			// vars
			$posts = gcacf_extract_var( $groups, $group_title );
			
			
			// data
			$data = array(
				'text'		=> $group_title,
				'children'	=> array()
			);
			
			
			// convert post objects to post titles
			foreach( array_keys($posts) as $post_id ) {
				
				$posts[ $post_id ] = $this->get_post_title( $posts[ $post_id ], $field, $options['post_id'] );
				
			}
			
			
			// order posts by search
			if( $is_search && empty($args['orderby']) ) {
				
				$posts = gcacf_order_by_search( $posts, $args['s'] );
				
			}
			
			
			// append to $data
			foreach( array_keys($posts) as $post_id ) {
				
				$data['children'][] = $this->get_post_result( $post_id, $posts[ $post_id ]);
				
			}
			
			
			// append to $results
			$results[] = $data;
			
		}
		
		
		// add as optgroup or results
		if( count($args['post_type']) == 1 ) {
			
			$results = $results[0]['children'];
			
		}
		
		
		// vars
		$response = array(
			'results'	=> $results,
			'limit'		=> $args['posts_per_page']
		);
		
		
		// return
		return $response;
			
	}
	
	
	/*
	*  get_post_result
	*
	*  This function will return an array containing id, text and maybe description data
	*
	*  @type	function
	*  @date	7/07/2016
	*  @since	5.4.0
	*
	*  @param	$id (mixed)
	*  @param	$text (string)
	*  @return	(array)
	*/
	
	function get_post_result( $id, $text ) {
		
		// vars
		$result = array(
			'id'	=> $id,
			'text'	=> $text
		);
		
		
		// return
		return $result;
			
	}
	
	
	/*
	*  get_post_title
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
	
	function get_post_title( $post, $field, $post_id = 0, $is_search = 0 ) {
		
		// get post_id
		if( !$post_id ) $post_id = gcacf_get_form_data('post_id');
		
		
		// vars
		$title = gcacf_get_post_title( $post, $is_search );
		
		
		// featured_image
		if( gcacf_in_array('featured_image', $field['elements']) ) {
			
			// vars
			$class = 'thumbnail';
			$thumbnail = gcacf_get_post_thumbnail($post->ID, array(17, 17));
			
			
			// icon
			if( $thumbnail['type'] == 'icon' ) {
				
				$class .= ' -' . $thumbnail['type'];
				
			}
			
			
			// append
			$title = '<div class="' . $class . '">' . $thumbnail['html'] . '</div>' . $title;
			
		}
		
		
		// filters
		$title = apply_filters('gcacf/fields/relationship/result', $title, $post, $field, $post_id);
		$title = apply_filters('gcacf/fields/relationship/result/name=' . $field['_name'], $title, $post, $field, $post_id);
		$title = apply_filters('gcacf/fields/relationship/result/key=' . $field['key'], $title, $post, $field, $post_id);
		
		
		// return
		return $title;
		
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ) {
		
		// vars
		$post_type = gcacf_get_array( $field['post_type'] );
		$taxonomy = gcacf_get_array( $field['taxonomy'] );
		$filters = gcacf_get_array( $field['filters'] );
		
		// filters
		$filter_count = count($filters);
		$filter_post_type_choices = array();
		$filter_taxonomy_choices = array();
		
		// post_type filter
		if( in_array('post_type', $filters) ) {
			
			$filter_post_type_choices = array(
				''	=> __('Select post type', 'gcacf')
			) + gcacf_get_pretty_post_types( $post_type );
		}
		
		// taxonomy filter
		if( in_array('taxonomy', $filters) ) {
			
			$term_choices = array();
			$filter_taxonomy_choices = array(
				''	=> __('Select taxonomy', 'gcacf')
			);
			
			// check for specific taxonomy setting
			if( $taxonomy ) {
				$terms = gcacf_get_encoded_terms( $taxonomy );
				$term_choices = gcacf_get_choices_from_terms( $terms, 'slug' );
			
			// if no terms were specified, find all terms
			} else {
				
				// restrict taxonomies by the post_type selected
				$term_args = array();
				if( $post_type ) {
					$term_args['taxonomy'] = gcacf_get_taxonomies(array(
						'post_type'	=> $post_type
					));
				}
				
				// get terms
				$terms = gcacf_get_grouped_terms( $term_args );
				$term_choices = gcacf_get_choices_from_grouped_terms( $terms, 'slug' );
			}
			
			// append term choices
			$filter_taxonomy_choices = $filter_taxonomy_choices + $term_choices;
			
		}
		
		// div attributes
		$atts = array(
			'id'				=> $field['id'],
			'class'				=> "gcacf-relationship {$field['class']}",
			'data-min'			=> $field['min'],
			'data-max'			=> $field['max'],
			'data-s'			=> '',
			'data-paged'		=> 1,
			'data-post_type'	=> '',
			'data-taxonomy'		=> '',
		);
		
		?>
<div <?php gcacf_esc_attr_e($atts); ?>>
	
	<?php gcacf_hidden_input( array('name' => $field['name'], 'value' => '') ); ?>
	
	<?php 
	
	/* filters */	
	if( $filter_count ): ?>
	<div class="filters -f<?php echo esc_attr($filter_count); ?>">
		<?php 
	
		/* search */	
		if( in_array('search', $filters) ): ?>
		<div class="filter -search">
			<span>
				<?php gcacf_text_input( array('placeholder' => __("Search...",'gcacf'), 'data-filter' => 's') ); ?>
			</span>
		</div>
		<?php endif; 
		
		
		/* post_type */	
		if( in_array('post_type', $filters) ): ?>
		<div class="filter -post_type">
			<span>
				<?php gcacf_select_input( array('choices' => $filter_post_type_choices, 'data-filter' => 'post_type') ); ?>
			</span>
		</div>
		<?php endif; 
		
		
		/* post_type */	
		if( in_array('taxonomy', $filters) ): ?>
		<div class="filter -taxonomy">
			<span>
				<?php gcacf_select_input( array('choices' => $filter_taxonomy_choices, 'data-filter' => 'taxonomy') ); ?>
			</span>
		</div>
		<?php endif; ?>		
	</div>
	<?php endif; ?>
	
	<div class="selection">
		<div class="choices">
			<ul class="gcacf-bl list choices-list"></ul>
		</div>
		<div class="values">
			<ul class="gcacf-bl list values-list">
			<?php if( !empty($field['value']) ): 
				
				// get posts
				$posts = gcacf_get_posts(array(
					'post__in' => $field['value'],
					'post_type'	=> $field['post_type']
				));
				
				
				// loop
				foreach( $posts as $post ): ?>
					<li>
						<?php gcacf_hidden_input( array('name' => $field['name'].'[]', 'value' => $post->ID) ); ?>
						<span data-id="<?php echo esc_attr($post->ID); ?>" class="gcacf-rel-item">
							<?php echo $this->get_post_title( $post, $field ); ?>
							<a href="#" class="gcacf-icon -minus small dark" data-name="remove_item"></a>
						</span>
					</li>
				<?php endforeach; ?>
			<?php endif; ?>
			</ul>
		</div>
	</div>
</div>
		<?php
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
		
		// vars
		$field['min'] = empty($field['min']) ? '' : $field['min'];
		$field['max'] = empty($field['max']) ? '' : $field['max'];
		
		
		// post_type
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Filter by Post Type','gcacf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'post_type',
			'choices'		=> gcacf_get_pretty_post_types(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All post types",'gcacf'),
		));
		
		
		// taxonomy
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Filter by Taxonomy','gcacf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'taxonomy',
			'choices'		=> gcacf_get_taxonomy_terms(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All taxonomies",'gcacf'),
		));
		
		
		// filters
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Filters','gcacf'),
			'instructions'	=> '',
			'type'			=> 'checkbox',
			'name'			=> 'filters',
			'choices'		=> array(
				'search'		=> __("Search",'gcacf'),
				'post_type'		=> __("Post Type",'gcacf'),
				'taxonomy'		=> __("Taxonomy",'gcacf'),
			),
		));
		
		
		// filters
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Elements','gcacf'),
			'instructions'	=> __('Selected elements will be displayed in each result','gcacf'),
			'type'			=> 'checkbox',
			'name'			=> 'elements',
			'choices'		=> array(
				'featured_image'	=> __("Featured Image",'gcacf'),
			),
		));
		
		
		// min
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Minimum posts','gcacf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'min',
		));
		
		
		// max
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Maximum posts','gcacf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'max',
		));
		
		
		
		
		// return_format
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Return Format','gcacf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'choices'		=> array(
				'object'		=> __("Post Object",'gcacf'),
				'id'			=> __("Post ID",'gcacf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
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
		
			return $value;
			
		}
		
		
		// force value to array
		$value = gcacf_get_array( $value );
		
		
		// convert to int
		$value = array_map('intval', $value);
		
		
		// load posts if needed
		if( $field['return_format'] == 'object' ) {
			
			// get posts
			$value = gcacf_get_posts(array(
				'post__in' => $value,
				'post_type'	=> $field['post_type']
			));
			
		}
		
		
		// return
		return $value;
		
	}
	
	
	/*
	*  validate_value
	*
	*  description
	*
	*  @type	function
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function validate_value( $valid, $value, $field, $input ){
		
		// default
		if( empty($value) || !is_array($value) ) {
		
			$value = array();
			
		}
		
		
		// min
		if( count($value) < $field['min'] ) {
		
			$valid = _n( '%s requires at least %s selection', '%s requires at least %s selections', $field['min'], 'gcacf' );
			$valid = sprintf( $valid, $field['label'], $field['min'] );
			
		}
		
		
		// return		
		return $valid;
		
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
		
		// validate
		if( empty($value) ) {
			
			return $value;
			
		}
		
		
		// force value to array
		$value = gcacf_get_array( $value );
		
					
		// array
		foreach( $value as $k => $v ){
		
			// object?
			if( is_object($v) && isset($v->ID) ) {
			
				$value[ $k ] = $v->ID;
				
			}
			
		}
		
		
		// save value as strings, so we can clearly search for them in SQL LIKE statements
		$value = array_map('strval', $value);
		
	
		// return
		return $value;
		
	}
		
}


// initialize
gcacf_register_field_type( 'gcacf_field_relationship' );

endif; // class_exists check

?>