<?php

if( ! class_exists('gcacf_field_button_group') ) :

class gcacf_field_button_group extends gcacf_field {
	
	
	/**
	*  initialize()
	*
	*  This function will setup the field type data
	*
	*  @date	18/9/17
	*  @since	5.6.3
	*
	*  @param	n/a
	*  @return	n/a
	*/
	 
	function initialize() {
		
		// vars
		$this->name = 'button_group';
		$this->label = __("Button Group",'gcacf');
		$this->category = 'choice';
		$this->defaults = array(
			'choices'			=> array(),
			'default_value'		=> '',
			'allow_null' 		=> 0,
			'return_format'		=> 'value',
			'layout'			=> 'horizontal',
		);
		
	}
	
	
	/**
	*  render_field()
	*
	*  Creates the field's input HTML
	*
	*  @date	18/9/17
	*  @since	5.6.3
	*
	*  @param	array $field The field settings array
	*  @return	n/a
	*/
	
	function render_field( $field ) {
		
		// vars
		$html = '';
		$selected = null;
		$buttons = array();
		$value = esc_attr( $field['value'] );
		
		
		// bail ealrly if no choices
		if( empty($field['choices']) ) return;
		
		
		// buttons
		foreach( $field['choices'] as $_value => $_label ) {
			
			// checked
			$checked = ( $value === esc_attr($_value) );
			if( $checked ) $selected = true;
			
			
			// append
			$buttons[] = array(
				'name'		=> $field['name'],
				'value'		=> $_value,
				'label'		=> $_label,
				'checked'	=> $checked
			);
							
		}
		
		
		// maybe select initial value
		if( !$field['allow_null'] && $selected === null ) {
			$buttons[0]['checked'] = true;
		}
		
		
		// div
		$div = array( 'class' => 'gcacf-button-group' );
		
		if( $field['layout'] == 'vertical' )	{ $div['class'] .= ' -vertical'; }
		if( $field['class'] )					{ $div['class'] .= ' ' . $field['class']; }
		if( $field['allow_null'] )				{ $div['data-allow_null'] = 1; }
		
		
		// hdden input
		$html .= gcacf_get_hidden_input( array('name' => $field['name']) );
			
			
		// open
		$html .= '<div ' . gcacf_esc_attr($div) . '>';
			
			// loop
			foreach( $buttons as $button ) {
				
				// checked
				if( $button['checked'] ) {
					$button['checked'] = 'checked';
				} else {
					unset($button['checked']);
				}
				
				
				// append
				$html .= gcacf_get_radio_input( $button );
				
			}
			
					
		// close
		$html .= '</div>';
		
		
		// return
		echo $html;
		
	}
	
	
	/**
	*  render_field_settings()
	*
	*  Creates the field's settings HTML
	*
	*  @date	18/9/17
	*  @since	5.6.3
	*
	*  @param	array $field The field settings array
	*  @return	n/a
	*/
	
	function render_field_settings( $field ) {
		
		// encode choices (convert from array)
		$field['choices'] = gcacf_encode_choices($field['choices']);
		
		
		// choices
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Choices','gcacf'),
			'instructions'	=> __('Enter each choice on a new line.','gcacf') . '<br /><br />' . __('For more control, you may specify both a value and label like this:','gcacf'). '<br /><br />' . __('red : Red','gcacf'),
			'type'			=> 'textarea',
			'name'			=> 'choices',
		));
		
		
		// allow_null
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Allow Null?','gcacf'),
			'instructions'	=> '',
			'name'			=> 'allow_null',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
		
		
		// default_value
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Default Value','gcacf'),
			'instructions'	=> __('Appears when creating a new post','gcacf'),
			'type'			=> 'text',
			'name'			=> 'default_value',
		));
		
		
		// layout
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Layout','gcacf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'layout',
			'layout'		=> 'horizontal', 
			'choices'		=> array(
				'horizontal'	=> __("Horizontal",'gcacf'),
				'vertical'		=> __("Vertical",'gcacf'), 
			)
		));
		
		
		// return_format
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Return Value','gcacf'),
			'instructions'	=> __('Specify the returned value on front end','gcacf'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'value'			=> __('Value','gcacf'),
				'label'			=> __('Label','gcacf'),
				'array'			=> __('Both (Array)','gcacf')
			)
		));
		
	}
	
	
	/*
	*  update_field()
	*
	*  This filter is appied to the $field before it is saved to the database
	*
	*  @date	18/9/17
	*  @since	5.6.3
	*
	*  @param	array $field The field array holding all the field options
	*  @return	$field
	*/

	function update_field( $field ) {
		
		return gcacf_get_field_type('radio')->update_field( $field );
	}
	
	
	/*
	*  load_value()
	*
	*  This filter is appied to the $value after it is loaded from the db
	*
	*  @date	18/9/17
	*  @since	5.6.3
	*
	*  @param	mixed	$value		The value found in the database
	*  @param	mixed	$post_id	The post ID from which the value was loaded from
	*  @param	array	$field		The field array holding all the field options
	*  @return	$value
	*/
	
	function load_value( $value, $post_id, $field ) {
		
		return gcacf_get_field_type('radio')->load_value( $value, $post_id, $field );
		
	}
	
	
	/*
	*  translate_field
	*
	*  This function will translate field settings
	*
	*  @date	18/9/17
	*  @since	5.6.3
	*
	*  @param	array $field The field array holding all the field options
	*  @return	$field
	*/
	
	function translate_field( $field ) {
		
		return gcacf_get_field_type('radio')->translate_field( $field );
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @date	18/9/17
	*  @since	5.6.3
	*
	*  @param	mixed	$value		The value found in the database
	*  @param	mixed	$post_id	The post ID from which the value was loaded from
	*  @param	array	$field		The field array holding all the field options
	*  @return	$value
	*/
	
	function format_value( $value, $post_id, $field ) {
		
		return gcacf_get_field_type('radio')->format_value( $value, $post_id, $field );
		
	}
	
}


// initialize
gcacf_register_field_type( 'gcacf_field_button_group' );

endif; // class_exists check

?>