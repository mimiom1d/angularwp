<?php

if( ! class_exists('gcacf_field_time_picker') ) :

class gcacf_field_time_picker extends gcacf_field {
	
	
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
		$this->name = 'time_picker';
		$this->label = __("Time Picker",'gcacf');
		$this->category = 'jquery';
		$this->defaults = array(
			'display_format'		=> 'g:i a',
			'return_format'			=> 'g:i a'
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
		
		// format value
		$display_value = '';
		
		if( $field['value'] ) {
			$display_value = gcacf_format_date( $field['value'], $field['display_format'] );
		}
		
		
		// vars
		$div = array(
			'class'					=> 'gcacf-time-picker gcacf-input-wrap',
			'data-time_format'		=> gcacf_convert_time_to_js($field['display_format'])
		);
		$hidden_input = array(
			'id'					=> $field['id'],
			'class' 				=> 'input-alt',
			'type'					=> 'hidden',
			'name'					=> $field['name'],
			'value'					=> $field['value'],
		);
		$text_input = array(
			'class' 				=> 'input',
			'type'					=> 'text',
			'value'					=> $display_value,
		);
		
		
		// html
		?>
		<div <?php gcacf_esc_attr_e( $div ); ?>>
			<?php gcacf_hidden_input( $hidden_input ); ?>
			<?php gcacf_text_input( $text_input ); ?>
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
		$g_i_a = date('g:i a');
		$H_i_s = date('H:i:s');
		
		
		// display_format
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Display Format','gcacf'),
			'instructions'	=> __('The format displayed when editing a post','gcacf'),
			'type'			=> 'radio',
			'name'			=> 'display_format',
			'other_choice'	=> 1,
			'choices'		=> array(
				'g:i a'	=> '<span>' . $g_i_a . '</span><code>g:i a</code>',
				'H:i:s'	=> '<span>' . $H_i_s . '</span><code>H:i:s</code>',
				'other'	=> '<span>' . __('Custom:','gcacf') . '</span>'
			)
		));
				
		
		// return_format
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Return Format','gcacf'),
			'instructions'	=> __('The format returned via template functions','gcacf'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'other_choice'	=> 1,
			'choices'		=> array(
				'g:i a'	=> '<span>' . $g_i_a . '</span><code>g:i a</code>',
				'H:i:s'	=> '<span>' . $H_i_s . '</span><code>H:i:s</code>',
				'other'	=> '<span>' . __('Custom:','gcacf') . '</span>'
			)
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
		
		return gcacf_format_date( $value, $field['return_format'] );
		
	}
	
}


// initialize
gcacf_register_field_type( 'gcacf_field_time_picker' );

endif; // class_exists check

?>