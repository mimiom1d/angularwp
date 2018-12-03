<?php

if( ! class_exists('gcacf_field_tab') ) :

class gcacf_field_tab extends gcacf_field {
	
	
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
		$this->name = 'tab';
		$this->label = __("Tab",'gcacf');
		$this->category = 'layout';
		$this->defaults = array(
			'placement'	=> 'top',
			'endpoint'	=> 0 // added in 5.2.8
		);
		
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
		$atts = array(
			'href'				=> '',
			'class'				=> 'gcacf-tab-button',
			'data-placement'	=> $field['placement'],
			'data-endpoint'		=> $field['endpoint'],
			'data-key'			=> $field['key']
		);
		
		?>
		<a <?php gcacf_esc_attr_e( $atts ); ?>><?php echo gcacf_esc_html($field['label']); ?></a>
		<?php
		
		
	}
	
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @param	$field	- an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field_settings( $field ) {
		
/*
		// message
		$message = '';
		$message .= '<p>' . __( 'Use "Tab Fields" to better organize your edit screen by grouping fields together.', 'gcacf') . '</p>';
		$message .= '<p>' . __( 'All fields following this "tab field" (or until another "tab field" is defined) will be grouped together using this field\'s label as the tab heading.','gcacf') . '</p>';
		
		
		// default_value
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Instructions','gcacf'),
			'instructions'	=> '',
			'name'			=> 'notes',
			'type'			=> 'message',
			'message'		=> $message,
		));
*/
		
		
		// preview_size
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Placement','gcacf'),
			'type'			=> 'select',
			'name'			=> 'placement',
			'choices' 		=> array(
				'top'			=>	__("Top aligned", 'gcacf'),
				'left'			=>	__("Left aligned", 'gcacf'),
			)
		));
		
		
		// endpoint
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Endpoint','gcacf'),
			'instructions'	=> __('Define an endpoint for the previous tabs to stop. This will start a new group of tabs.', 'gcacf'),
			'name'			=> 'endpoint',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
				
	}
	
	
	/*
	*  load_field()
	*
	*  This filter is appied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the field array holding all the field options
	*/
	
	function load_field( $field ) {
		
		// remove name to avoid caching issue
		$field['name'] = '';
		
		// remove required to avoid JS issues
		$field['required'] = 0;
		
		// set value other than 'null' to avoid GCACF loading / caching issue
		$field['value'] = false;
		
		// return
		return $field;
		
	}
	
}


// initialize
gcacf_register_field_type( 'gcacf_field_tab' );

endif; // class_exists check

?>