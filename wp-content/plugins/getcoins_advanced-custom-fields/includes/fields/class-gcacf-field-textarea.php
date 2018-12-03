<?php

if( ! class_exists('gcacf_field_textarea') ) :

class gcacf_field_textarea extends gcacf_field {
	
	
	/*
	*  initialize
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
		$this->name = 'textarea';
		$this->label = __("Text Area",'gcacf');
		$this->defaults = array(
			'default_value'	=> '',
			'new_lines'		=> '',
			'maxlength'		=> '',
			'placeholder'	=> '',
			'rows'			=> ''
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
		$atts = array();
		$keys = array( 'id', 'class', 'name', 'value', 'placeholder', 'rows', 'maxlength' );
		$keys2 = array( 'readonly', 'disabled', 'required' );
		
		
		// rows
		if( !$field['rows'] ) {
			$field['rows'] = 8;
		}
		
		
		// atts (value="123")
		foreach( $keys as $k ) {
			if( isset($field[ $k ]) ) $atts[ $k ] = $field[ $k ];
		}
		
		
		// atts2 (disabled="disabled")
		foreach( $keys2 as $k ) {
			if( !empty($field[ $k ]) ) $atts[ $k ] = $k;
		}
		
		
		// remove empty atts
		$atts = gcacf_clean_atts( $atts );
		
		
		// return
		gcacf_textarea_input( $atts );
		
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
		
		// default_value
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Default Value','gcacf'),
			'instructions'	=> __('Appears when creating a new post','gcacf'),
			'type'			=> 'textarea',
			'name'			=> 'default_value',
		));
		
		
		// placeholder
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Placeholder Text','gcacf'),
			'instructions'	=> __('Appears within the input','gcacf'),
			'type'			=> 'text',
			'name'			=> 'placeholder',
		));
		
		
		// maxlength
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Character Limit','gcacf'),
			'instructions'	=> __('Leave blank for no limit','gcacf'),
			'type'			=> 'number',
			'name'			=> 'maxlength',
		));
		
		
		// rows
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Rows','gcacf'),
			'instructions'	=> __('Sets the textarea height','gcacf'),
			'type'			=> 'number',
			'name'			=> 'rows',
			'placeholder'	=> 8
		));
		
		
		// formatting
		gcacf_render_field_setting( $field, array(
			'label'			=> __('New Lines','gcacf'),
			'instructions'	=> __('Controls how new lines are rendered','gcacf'),
			'type'			=> 'select',
			'name'			=> 'new_lines',
			'choices'		=> array(
				'wpautop'		=> __("Automatically add paragraphs",'gcacf'),
				'br'			=> __("Automatically add &lt;br&gt;",'gcacf'),
				''				=> __("No Formatting",'gcacf')
			)
		));
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is applied to the $value after it is loaded from the db and before it is returned to the template
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
		
		// bail early if no value or not for template
		if( empty($value) || !is_string($value) ) {
			
			return $value;
		
		}
				
		
		// new lines
		if( $field['new_lines'] == 'wpautop' ) {
			
			$value = wpautop($value);
			
		} elseif( $field['new_lines'] == 'br' ) {
			
			$value = nl2br($value);
			
		}
		
		
		// return
		return $value;
	}
	
}


// initialize
gcacf_register_field_type( 'gcacf_field_textarea' );

endif; // class_exists check

?>