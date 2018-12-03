<?php

/*
*  GCACF Admin Field Group Class
*
*  All the logic for editing a field group
*
*  @class 		gcacf_admin_field_group
*  @package		GCACF
*  @subpackage	Admin
*/

if( ! class_exists('gcacf_admin_field_group') ) :

class gcacf_admin_field_group {
	
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// actions
		add_action('current_screen',									array($this, 'current_screen'));
		add_action('save_post',											array($this, 'save_post'), 10, 2);
		
		
		// ajax
		add_action('wp_ajax_gcacf/field_group/render_field_settings',		array($this, 'ajax_render_field_settings'));
		add_action('wp_ajax_gcacf/field_group/render_location_rule',		array($this, 'ajax_render_location_rule'));
		add_action('wp_ajax_gcacf/field_group/move_field',				array($this, 'ajax_move_field'));
		
		
		// filters
		add_filter('post_updated_messages',								array($this, 'post_updated_messages'));
		
	}
	
	
	/*
	*  post_updated_messages
	*
	*  This function will customize the message shown when editing a field group
	*
	*  @type	action (post_updated_messages)
	*  @date	30/04/2014
	*  @since	5.0.0
	*
	*  @param	$messages (array)
	*  @return	$messages
	*/
	
	function post_updated_messages( $messages ) {
		
		// append to messages
		$messages['gcacf-field-group'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => __('Field group updated.', 'gcacf'),
			2 => __('Field group updated.', 'gcacf'),
			3 => __('Field group deleted.', 'gcacf'),
			4 => __('Field group updated.', 'gcacf'),
			5 => false, // field group does not support revisions
			6 => __('Field group published.', 'gcacf'),
			7 => __('Field group saved.', 'gcacf'),
			8 => __('Field group submitted.', 'gcacf'),
			9 => __('Field group scheduled for.', 'gcacf'),
			10 => __('Field group draft updated.', 'gcacf')
		);
		
		
		// return
		return $messages;
	}
	
	
	/*
	*  current_screen
	*
	*  This function is fired when loading the admin page before HTML has been rendered.
	*
	*  @type	action (current_screen)
	*  @date	21/07/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function current_screen() {
		
		// validate screen
		if( !gcacf_is_screen('gcacf-field-group') ) return;
		
		
		// disable filters to ensure GCACF loads raw data from DB
		gcacf_disable_filters();
		
		
		// enqueue scripts
		gcacf_enqueue_scripts();
		
		
		// actions
		add_action('gcacf/input/admin_enqueue_scripts',		array($this, 'admin_enqueue_scripts'));
		add_action('gcacf/input/admin_head', 					array($this, 'admin_head'));
		add_action('gcacf/input/form_data', 					array($this, 'form_data'));
		add_action('gcacf/input/admin_footer', 				array($this, 'admin_footer'));
		add_action('gcacf/input/admin_footer_js',				array($this, 'admin_footer_js'));
		
		
		// filters
		add_filter('gcacf/input/admin_l10n',					array($this, 'admin_l10n'));
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This action is run after post query but before any admin script / head actions. 
	*  It is a good place to register all actions.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @date	30/06/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_enqueue_scripts() {
		
		// no autosave
		wp_dequeue_script('autosave');
		
		
		// custom scripts
		wp_enqueue_style('gcacf-field-group');
		wp_enqueue_script('gcacf-field-group');
		
		
		// localize text
		gcacf_localize_text(array(
			'The string "field_" may not be used at the start of a field name'	=> __('The string "field_" may not be used at the start of a field name', 'gcacf'),
			'This field cannot be moved until its changes have been saved'		=> __('This field cannot be moved until its changes have been saved', 'gcacf'),
			'Field group title is required'										=> __('Field group title is required', 'gcacf'),
			'Move to trash. Are you sure?'										=> __('Move to trash. Are you sure?', 'gcacf'),
			'No toggle fields available'										=> __('No toggle fields available', 'gcacf'),
			'Move Custom Field'													=> __('Move Custom Field', 'gcacf'),
			'Checked'															=> __('Checked', 'gcacf'),
			'(no label)'														=> __('(no label)', 'gcacf'),
			'(this field)'														=> __('(this field)', 'gcacf'),
			'copy'																=> __('copy', 'gcacf'),
			'or'																=> __('or', 'gcacf'),
			'Null'																=> __('Null', 'gcacf'),
		));
		
		// localize data
		gcacf_localize_data(array(
		   	'fieldTypes' => gcacf_get_field_types_info()
	   	));
	   	
		// 3rd party hook
		do_action('gcacf/field_group/admin_enqueue_scripts');
		
	}
	
	
	/*
	*  admin_head
	*
	*  This function will setup all functionality for the field group edit page to work
	*
	*  @type	action (admin_head)
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_head() {
		
		// global
		global $post, $field_group;
		
		
		// set global var
		$field_group = gcacf_get_field_group( $post );
		
		
		// metaboxes
		add_meta_box('gcacf-field-group-fields', __("Fields",'gcacf'), array($this, 'mb_fields'), 'gcacf-field-group', 'normal', 'high');
		add_meta_box('gcacf-field-group-locations', __("Location",'gcacf'), array($this, 'mb_locations'), 'gcacf-field-group', 'normal', 'high');
		add_meta_box('gcacf-field-group-options', __("Settings",'gcacf'), array($this, 'mb_options'), 'gcacf-field-group', 'normal', 'high');
		
		
		// actions
		add_action('post_submitbox_misc_actions',	array($this, 'post_submitbox_misc_actions'), 10, 0);
		add_action('edit_form_after_title',			array($this, 'edit_form_after_title'), 10, 0);
		
		
		// filters
		add_filter('screen_settings',				array($this, 'screen_settings'), 10, 1);
		
		
		// 3rd party hook
		do_action('gcacf/field_group/admin_head');
		
	}
	
	
	/*
	*  edit_form_after_title
	*
	*  This action will allow GCACF to render metaboxes after the title
	*
	*  @type	action
	*  @date	17/08/13
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function edit_form_after_title() {
		
		// globals
		global $post;
		
		
		// render post data
		gcacf_form_data(array(
			'screen'		=> 'field_group',
			'post_id'		=> $post->ID,
			'delete_fields'	=> 0,
			'validation'	=> 0
		));

	}
	
	
	/*
	*  form_data
	*
	*  This function will add extra HTML to the gcacf form data element
	*
	*  @type	function
	*  @date	31/05/2016
	*  @since	5.3.8
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function form_data( $args ) {
		
		// do action	
		do_action('gcacf/field_group/form_data', $args);
		
	}
	
	
	/*
	*  admin_l10n
	*
	*  This function will append extra l10n strings to the gcacf JS object
	*
	*  @type	function
	*  @date	31/05/2016
	*  @since	5.3.8
	*
	*  @param	$l10n (array)
	*  @return	$l10n
	*/
	
	function admin_l10n( $l10n ) {
		return apply_filters('gcacf/field_group/admin_l10n', $l10n);
	}
	
	
	
	/*
	*  admin_footer
	*
	*  description
	*
	*  @type	function
	*  @date	11/01/2016
	*  @since	5.3.2
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_footer() {
		
		// 3rd party hook
		do_action('gcacf/field_group/admin_footer');
		
	}
	
	
	/*
	*  admin_footer_js
	*
	*  description
	*
	*  @type	function
	*  @date	31/05/2016
	*  @since	5.3.8
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_footer_js() {
		
		// 3rd party hook
		do_action('gcacf/field_group/admin_footer_js');
		
	}
	
	
	/*
	*  screen_settings
	*
	*  description
	*
	*  @type	function
	*  @date	26/01/13
	*  @since	3.6.0
	*
	*  @param	$current (string)
	*  @return	$current
	*/
	
	function screen_settings( $html ) {
		
		// vars
		$checked = gcacf_get_user_setting('show_field_keys') ? 'checked="checked"' : '';
		
		
		// append
	    $html .= '<div id="gcacf-append-show-on-screen" class="gcacf-hidden">';
	    $html .= '<label for="gcacf-field-key-hide"><input id="gcacf-field-key-hide" type="checkbox" value="1" name="show_field_keys" ' . $checked . ' /> ' . __('Field Keys','gcacf') . '</label>';
		$html .= '</div>';
	    
	    
	    // return
	    return $html;
	    
	}
	
	
	/*
	*  post_submitbox_misc_actions
	*
	*  This function will customize the publish metabox
	*
	*  @type	function
	*  @date	17/07/2015
	*  @since	5.2.9
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function post_submitbox_misc_actions() {
		
		// global
		global $field_group;
		
		
		// vars
		$status = $field_group['active'] ? __("Active",'gcacf') : __("Inactive",'gcacf');
		
?>
<script type="text/javascript">
(function($) {
	
	// modify status
	$('#post-status-display').html('<?php echo $status; ?>');
	
	
	// remove edit links
	$('#misc-publishing-actions a').remove();
	
	
	// remove editables (fixes status text changing on submit)
	$('#misc-publishing-actions .hide-if-js').remove();
	
})(jQuery);	
</script>
<?php	
		
	}
	
	
	/*
	*  save_post
	*
	*  This function will save all the field group data
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	1.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function save_post( $post_id, $post ) {
		
		// do not save if this is an auto save routine
		if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return $post_id;
		}
		
		// bail early if not gcacf-field-group
		if( $post->post_type !== 'gcacf-field-group' ) {
			return $post_id;
		}
		
		// only save once! WordPress save's a revision as well.
		if( wp_is_post_revision($post_id) ) {
	    	return $post_id;
        }
        
		// verify nonce
		if( !gcacf_verify_nonce('field_group') ) {
			return $post_id;
		}
        
        // disable filters to ensure GCACF loads raw data from DB
		gcacf_disable_filters();
		
        // save fields
        if( !empty($_POST['gcacf_fields']) ) {
			
			// loop
			foreach( $_POST['gcacf_fields'] as $field ) {
				
				// vars
				$specific = false;
				$save = gcacf_extract_var( $field, 'save' );
				
				
				// only saved field if has changed
				if( $save == 'meta' ) {
					$specific = array(
						'menu_order',
						'post_parent',
					);
				}
				
				// set parent
				if( !$field['parent'] ) {
					$field['parent'] = $post_id;
				}
				
				// save field
				gcacf_update_field( $field, $specific );
				
			}
		}
		
		
		// delete fields
        if( $_POST['_gcacf_delete_fields'] ) {
        	
        	// clean
	    	$ids = explode('|', $_POST['_gcacf_delete_fields']);
	    	$ids = array_map( 'intval', $ids );
	    	
	    	
	    	// loop
			foreach( $ids as $id ) {
				
				// bai early if no id
				if( !$id ) continue;
				
				
				// delete
				gcacf_delete_field( $id );
				
			}
			
        }
		
		
		// add args
        $_POST['gcacf_field_group']['ID'] = $post_id;
        $_POST['gcacf_field_group']['title'] = $_POST['post_title'];
        
        
		// save field group
        gcacf_update_field_group( $_POST['gcacf_field_group'] );
		
		
        // return
        return $post_id;
	}
	
	
	/*
	*  mb_fields
	*
	*  This function will render the HTML for the medtabox 'gcacf-field-group-fields'
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function mb_fields() {
		
		// global
		global $field_group;
		
		
		// get fields
		$view = array(
			'fields'	=> gcacf_get_fields_by_id( $field_group['ID'] ),
			'parent'	=> 0
		);
		
		
		// load view
		gcacf_get_view('field-group-fields', $view);
		
	}
	
	
	/*
	*  mb_options
	*
	*  This function will render the HTML for the medtabox 'gcacf-field-group-options'
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function mb_options() {
		
		// global
		global $field_group;
		
		
		// field key (leave in for compatibility)
		if( !gcacf_is_field_group_key( $field_group['key']) ) {
			
			$field_group['key'] = uniqid('group_');
			
		}
		
		
		// view
		gcacf_get_view('field-group-options');
		
	}
	
	
	/*
	*  mb_locations
	*
	*  This function will render the HTML for the medtabox 'gcacf-field-group-locations'
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function mb_locations() {
		
		// global
		global $field_group;
		
		
		// UI needs at lease 1 location rule
		if( empty($field_group['location']) ) {
			
			$field_group['location'] = array(
				
				// group 0
				array(
					
					// rule 0
					array(
						'param'		=>	'post_type',
						'operator'	=>	'==',
						'value'		=>	'post',
					)
				)
				
			);
		}
		
		
		// view
		gcacf_get_view('field-group-locations');
		
	}
	
	
	/*
	*  ajax_render_location_rule
	*
	*  This function can be accessed via an AJAX action and will return the result from the render_location_value function
	*
	*  @type	function (ajax)
	*  @date	30/09/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function ajax_render_location_rule() {
		
		// validate
		if( !gcacf_verify_ajax() ) die();
		
		// validate rule
		$rule = gcacf_validate_location_rule($_POST['rule']);
			
		// view
		gcacf_get_view( 'html-location-rule', array(
			'rule' => $rule
		));
		
		// die
		die();						
	}
	
	
	/*
	*  ajax_render_field_settings
	*
	*  This function will return HTML containing the field's settings based on it's new type
	*
	*  @type	function (ajax)
	*  @date	30/09/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function ajax_render_field_settings() {
		
		// validate
		if( !gcacf_verify_ajax() ) die();
		
		// vars
		$field = gcacf_maybe_get_POST('field');
		
		// check
		if( !$field ) die();
		
		// set prefix
		$field['prefix'] = gcacf_maybe_get_POST('prefix');
		
		// validate
		$field = gcacf_get_valid_field( $field );
		
		// render
		do_action("gcacf/render_field_settings/type={$field['type']}", $field);
		
		// return
		die();
								
	}
	
	/*
	*  ajax_move_field
	*
	*  description
	*
	*  @type	function
	*  @date	20/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_move_field() {
		
		// disable filters to ensure GCACF loads raw data from DB
		gcacf_disable_filters();
		
		
		$args = gcacf_parse_args($_POST, array(
			'nonce'				=> '',
			'post_id'			=> 0,
			'field_id'			=> 0,
			'field_group_id'	=> 0
		));
		
		
		// verify nonce
		if( !wp_verify_nonce($args['nonce'], 'gcacf_nonce') ) die();
		
		
		// confirm?
		if( $args['field_id'] && $args['field_group_id'] ) {
			
			// vars 
			$field = gcacf_get_field($args['field_id']);
			$field_group = gcacf_get_field_group($args['field_group_id']);
			
			
			// update parent
			$field['parent'] = $field_group['ID'];
			
			
			// remove conditional logic
			$field['conditional_logic'] = 0;
			
			
			// update field
			gcacf_update_field($field);
			
			
			// message
			$a = '<a href="' . admin_url("post.php?post={$field_group['ID']}&action=edit") . '" target="_blank">' . $field_group['title'] . '</a>';
			echo '<p><strong>' . __('Move Complete.', 'gcacf') . '</strong></p>';
			echo '<p>' . sprintf( __('The %s field can now be found in the %s field group', 'gcacf'), $field['label'], $a ). '</p>';
			echo '<a href="#" class="button button-primary gcacf-close-popup">' . __("Close Window",'gcacf') . '</a>';
			die();
			
		}
		
		
		// get all field groups
		$field_groups = gcacf_get_field_groups();
		$choices = array();
		
		
		// check
		if( !empty($field_groups) ) {
			
			// loop
			foreach( $field_groups as $field_group ) {
				
				// bail early if no ID
				if( !$field_group['ID'] ) continue;
				
				
				// bail ealry if is current
				if( $field_group['ID'] == $args['post_id'] ) continue;
				
				
				// append
				$choices[ $field_group['ID'] ] = $field_group['title'];
				
			}
			
		}
		
		
		// render options
		$field = gcacf_get_valid_field(array(
			'type'		=> 'select',
			'name'		=> 'gcacf_field_group',
			'choices'	=> $choices
		));
		
		
		echo '<p>' . __('Please select the destination for this field', 'gcacf') . '</p>';
		
		echo '<form id="gcacf-move-field-form">';
		
			// render
			gcacf_render_field_wrap( $field );
			
			echo '<button type="submit" class="button button-primary">' . __("Move Field",'gcacf') . '</button>';
			
		echo '</form>';
		
		
		// die
		die();
		
	}
	
}

// initialize
new gcacf_admin_field_group();

endif;

?>