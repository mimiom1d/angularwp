<?php

if( ! class_exists('gcacf_field_radio') ) :

class gcacf_field_radio extends gcacf_field {
	
	
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
		$this->name = 'radio';
		$this->label = __("Radio Button",'gcacf');
		$this->category = 'choice';
		$this->defaults = array(
			'layout'			=> 'vertical',
			'choices'			=> array(),
			'default_value'		=> '',
			'other_choice'		=> 0,
			'save_other_choice'	=> 0,
			'allow_null' 		=> 0,
			'return_format'		=> 'value'
		);
		
	}
	
		
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
	
	function render_field( $field ) {

		// vars
		$i = 0;
		$e = '';
		$ul = array( 
			'class'				=> 'gcacf-radio-list',
			'data-allow_null'	=> $field['allow_null'],
			'data-other_choice'	=> $field['other_choice']
		);
		
		
		// append to class
		$ul['class'] .= ' ' . ($field['layout'] == 'horizontal' ? 'gcacf-hl' : 'gcacf-bl');
		$ul['class'] .= ' ' . $field['class'];
		
		
		// select value
		$checked = '';
		$value = strval($field['value']);
		
		
		// selected choice
		if( isset($field['choices'][ $value ]) ) {
			
			$checked = $value;
			
		// custom choice
		} elseif( $field['other_choice'] && $value !== '' ) {
			
			$checked = 'other';
			
		// allow null	
		} elseif( $field['allow_null'] ) {
			
			// do nothing
			
		// select first input by default	
		} else {
			
			$checked = key($field['choices']);
			
		}
		
		
		// ensure $checked is a string (could be an int)
		$checked = strval($checked); 
		
				
		// other choice
		if( $field['other_choice'] ) {
			
			// vars
			$input = array(
				'type'		=> 'text',
				'name'		=> $field['name'],
				'value'		=> '',
				'disabled'	=> 'disabled',
				'class'		=> 'gcacf-disabled'
			);
			
			
			// select other choice if value is not a valid choice
			if( $checked === 'other' ) {
				
				unset($input['disabled']);
				$input['value'] = $field['value'];
				
			}
			
			
			// allow custom 'other' choice to be defined
			if( !isset($field['choices']['other']) ) {
				
				$field['choices']['other'] = '';
				
			}
			
			
			// append other choice
			$field['choices']['other'] .= '</label><input type="text" ' . gcacf_esc_attr($input) . ' /><label>';
		
		}
		
		
		// bail early if no choices
		if( empty($field['choices']) ) return;
		
		
		// hiden input
		$e .= gcacf_get_hidden_input( array('name' => $field['name']) );
		
		
		// open
		$e .= '<ul ' . gcacf_esc_attr($ul) . '>';
		
		
		// foreach choices
		foreach( $field['choices'] as $value => $label ) {
			
			// ensure value is a string
			$value = strval($value);
			$class = '';
			
			
			// increase counter
			$i++;
			
			
			// vars
			$atts = array(
				'type'	=> 'radio',
				'id'	=> $field['id'], 
				'name'	=> $field['name'],
				'value'	=> $value
			);
			
			
			// checked
			if( $value === $checked ) {
				
				$atts['checked'] = 'checked';
				$class = ' class="selected"';
				
			}
			
			
			// deisabled
			if( isset($field['disabled']) && gcacf_in_array($value, $field['disabled']) ) {
			
				$atts['disabled'] = 'disabled';
				
			}
			
			
			// id (use crounter for each input)
			if( $i > 1 ) {
			
				$atts['id'] .= '-' . $value;
				
			}
			
			
			// append
			$e .= '<li><label' . $class . '><input ' . gcacf_esc_attr( $atts ) . '/>' . $label . '</label></li>';
			
		}
		
		
		// close
		$e .= '</ul>';
		
		
		// return
		echo $e;
		
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
		
		
		// other_choice
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Other','gcacf'),
			'instructions'	=> '',
			'name'			=> 'other_choice',
			'type'			=> 'true_false',
			'ui'			=> 1,
			'message'		=> __("Add 'other' choice to allow for custom values", 'gcacf'),
		));
		
		
		// save_other_choice
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Save Other','gcacf'),
			'instructions'	=> '',
			'name'			=> 'save_other_choice',
			'type'			=> 'true_false',
			'ui'			=> 1,
			'message'		=> __("Save 'other' values to the field's choices", 'gcacf'),
			'conditions'	=> array(
				'field'		=> 'other_choice',
				'operator'	=> '==',
				'value'		=> 1
			)
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
				'vertical'		=> __("Vertical",'gcacf'), 
				'horizontal'	=> __("Horizontal",'gcacf')
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
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the field group ID (post_type = gcacf)
	*
	*  @return	$field - the modified field
	*/

	function update_field( $field ) {
		
		// decode choices (convert to array)
		$field['choices'] = gcacf_decode_choices($field['choices']);
		
		
		// return
		return $field;
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*  @todo	Fix bug where $field was found via json and has no ID
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		
		// bail early if no value (allow 0 to be saved)
		if( !$value && !is_numeric($value) ) return $value;
		
		
		// save_other_choice
		if( $field['save_other_choice'] ) {
			
			// value isn't in choices yet
			if( !isset($field['choices'][ $value ]) ) {
				
				// get raw $field (may have been changed via repeater field)
				// if field is local, it won't have an ID
				$selector = $field['ID'] ? $field['ID'] : $field['key'];
				$field = gcacf_get_field( $selector, true );
				
				
				// bail early if no ID (JSON only)
				if( !$field['ID'] ) return $value;
				
				
				// unslash (fixes serialize single quote issue)
				$value = wp_unslash($value);
				
				
				// sanitize (remove tags)
				$value = sanitize_text_field($value);
				
				
				// update $field
				$field['choices'][ $value ] = $value;
				
				
				// save
				gcacf_update_field( $field );
				
			}
			
		}		
		
		
		// return
		return $value;
	}
	
	
	/*
	*  load_value()
	*
	*  This filter is appied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	5.2.9
	*  @date	23/01/13
	*
	*  @param	$value - the value found in the database
	*  @param	$post_id - the $post_id from which the value was loaded from
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the value to be saved in te database
	*/
	
	function load_value( $value, $post_id, $field ) {
		
		// must be single value
		if( is_array($value) ) {
			
			$value = array_pop($value);
			
		}
		
		
		// return
		return $value;
		
	}
	
	
	/*
	*  translate_field
	*
	*  This function will translate field settings
	*
	*  @type	function
	*  @date	8/03/2016
	*  @since	5.3.2
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function translate_field( $field ) {
		
		return gcacf_get_field_type('select')->translate_field( $field );
		
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
		
		return gcacf_get_field_type('select')->format_value( $value, $post_id, $field );
		
	}
	
}


// initialize
gcacf_register_field_type( 'gcacf_field_radio' );

endif; // class_exists check

?>