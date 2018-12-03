<?php

if( ! class_exists('gcacf_field_url') ) :

class gcacf_field_url extends gcacf_field {
	
	
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
		$this->name = 'url';
		$this->label = __("Url",'gcacf');
		$this->defaults = array(
			'default_value'	=> '',
			'placeholder'	=> '',
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
		$keys2 = array( 'readonly', 'disabled', 'required' );
		$html = '';
		
		
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
		$html .= '<div class="gcacf-input-wrap gcacf-url">';
		$html .= '<i class="gcacf-icon -globe -small"></i>' . gcacf_get_text_input( $atts ) ;
		$html .= '</div>';
		
		
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
		
		// bail early if empty		
		if( empty($value) ) {
				
			return $valid;
			
		}
		
		
		if( strpos($value, '://') !== false ) {
			
			// url
			
		} elseif( strpos($value, '//') === 0 ) {
			
			// protocol relative url
			
		} else {
			
			$valid = __('Value must be a valid URL', 'gcacf');
			
		}
		
		
		// return		
		return $valid;
		
	}
	
}


// initialize
gcacf_register_field_type( 'gcacf_field_url' );

endif; // class_exists check

?>