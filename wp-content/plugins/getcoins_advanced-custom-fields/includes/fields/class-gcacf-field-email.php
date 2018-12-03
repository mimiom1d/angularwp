<?php

if( ! class_exists('gcacf_field_email') ) :

class gcacf_field_email extends gcacf_field {
	
	
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
		$this->name = 'email';
		$this->label = __("Email",'gcacf');
		$this->defaults = array(
			'default_value'	=> '',
			'placeholder'	=> '',
			'prepend'		=> '',
			'append'		=> ''
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
		$keys = array( 'type', 'id', 'class', 'name', 'value', 'placeholder', 'pattern' );
		$keys2 = array( 'readonly', 'disabled', 'required', 'multiple' );
		$html = '';
		
		
		// prepend
		if( $field['prepend'] !== '' ) {
		
			$field['class'] .= ' gcacf-is-prepended';
			$html .= '<div class="gcacf-input-prepend">' . gcacf_esc_html($field['prepend']) . '</div>';
			
		}
		
		
		// append
		if( $field['append'] !== '' ) {
		
			$field['class'] .= ' gcacf-is-appended';
			$html .= '<div class="gcacf-input-append">' . gcacf_esc_html($field['append']) . '</div>';
			
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
		
		
		// render
		$html .= '<div class="gcacf-input-wrap">' . gcacf_get_text_input( $atts ) . '</div>';
		
		
		// return
		echo $html;
		
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
		
		// default_value
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Default Value','gcacf'),
			'instructions'	=> __('Appears when creating a new post','gcacf'),
			'type'			=> 'text',
			'name'			=> 'default_value',
		));
		
		
		// placeholder
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Placeholder Text','gcacf'),
			'instructions'	=> __('Appears within the input','gcacf'),
			'type'			=> 'text',
			'name'			=> 'placeholder',
		));
		
		
		// prepend
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Prepend','gcacf'),
			'instructions'	=> __('Appears before the input','gcacf'),
			'type'			=> 'text',
			'name'			=> 'prepend',
		));
		
		
		// append
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Append','gcacf'),
			'instructions'	=> __('Appears after the input','gcacf'),
			'type'			=> 'text',
			'name'			=> 'append',
		));

	}	
	
}


// initialize
gcacf_register_field_type( 'gcacf_field_email' );

endif; // class_exists check

?>