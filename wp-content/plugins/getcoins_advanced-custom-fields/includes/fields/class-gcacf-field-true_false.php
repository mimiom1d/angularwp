<?php

if( ! class_exists('gcacf_field_true_false') ) :

class gcacf_field_true_false extends gcacf_field {
	
	
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
		$this->name = 'true_false';
		$this->label = __('True / False','gcacf');
		$this->category = 'choice';
		$this->defaults = array(
			'default_value'	=> 0,
			'message'		=> '',
			'ui'			=> 0,
			'ui_on_text'	=> '',
			'ui_off_text'	=> '',
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
		$input = array(
			'type'		=> 'checkbox',
			'id'		=> $field['id'],
			'name'		=> $field['name'],
			'value'		=> '1',
			'class'		=> $field['class'],
			'autocomplete'	=> 'off'
		);
		
		$hidden = array(
			'name' 		=> $field['name'],
			'value'		=> 0
		);
		
		$active = $field['value'] ? true : false;
		$switch = '';
		
		
		// checked
		if( $active ) $input['checked'] = 'checked';
		
		
		// ui
		if( $field['ui'] ) {
			
			// vars
			if( $field['ui_on_text'] === '' ) $field['ui_on_text'] = __('Yes', 'gcacf');
			if( $field['ui_off_text'] === '' ) $field['ui_off_text'] = __('No', 'gcacf');
			
			
			// update input
			$input['class'] .= ' gcacf-switch-input';
			//$input['style'] = 'display:none;';
			
			$switch .= '<div class="gcacf-switch' . ($active ? ' -on' : '') . '">';
				$switch .= '<span class="gcacf-switch-on">'.$field['ui_on_text'].'</span>';
				$switch .= '<span class="gcacf-switch-off">'.$field['ui_off_text'].'</span>';
				$switch .= '<div class="gcacf-switch-slider"></div>';
			$switch .= '</div>';
			
		}
		
?>
<div class="gcacf-true-false">
	<?php gcacf_hidden_input($hidden); ?>
	<label>
		<input <?php echo gcacf_esc_attr($input); ?>/>
		<?php if( $switch ) echo gcacf_esc_html($switch); ?>
		<?php if( $field['message'] ): ?><span class="message"><?php echo gcacf_esc_html($field['message']); ?></span><?php endif; ?>
	</label>
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
		
		// message
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Message','gcacf'),
			'instructions'	=> __('Displays text alongside the checkbox','gcacf'),
			'type'			=> 'text',
			'name'			=> 'message',
		));
		
		
		// default_value
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Default Value','gcacf'),
			'instructions'	=> '',
			'type'			=> 'true_false',
			'name'			=> 'default_value',
		));
		
		
		// ui
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Stylised UI','gcacf'),
			'instructions'	=> '',
			'type'			=> 'true_false',
			'name'			=> 'ui',
			'ui'			=> 1,
			'class'			=> 'gcacf-field-object-true-false-ui'
		));
		
		
		// on_text
		gcacf_render_field_setting( $field, array(
			'label'			=> __('On Text','gcacf'),
			'instructions'	=> __('Text shown when active','gcacf'),
			'type'			=> 'text',
			'name'			=> 'ui_on_text',
			'placeholder'	=> __('Yes', 'gcacf'),
			'conditions'	=> array(
				'field'		=> 'ui',
				'operator'	=> '==',
				'value'		=> 1
			)
		));
		
		
		// on_text
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Off Text','gcacf'),
			'instructions'	=> __('Text shown when inactive','gcacf'),
			'type'			=> 'text',
			'name'			=> 'ui_off_text',
			'placeholder'	=> __('No', 'gcacf'),
			'conditions'	=> array(
				'field'		=> 'ui',
				'operator'	=> '==',
				'value'		=> 1
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
		
		return empty($value) ? false : true;
		
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
		
		// bail early if not required
		if( ! $field['required'] ) {
			
			return $valid;
			
		}
		
		
		// value may be '0'
		if( !$value ) {
			
			return false;
			
		}
		
		
		// return
		return $valid;
				
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
		
		// translate
		$field['message'] = gcacf_translate( $field['message'] );
		$field['ui_on_text'] = gcacf_translate( $field['ui_on_text'] );
		$field['ui_off_text'] = gcacf_translate( $field['ui_off_text'] );
		
		
		// return
		return $field;
		
	}
	
}


// initialize
gcacf_register_field_type( 'gcacf_field_true_false' );

endif; // class_exists check

?>