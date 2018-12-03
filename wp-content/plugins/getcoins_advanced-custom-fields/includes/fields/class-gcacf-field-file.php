<?php

if( ! class_exists('gcacf_field_file') ) :

class gcacf_field_file extends gcacf_field {
	
	
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
		$this->name = 'file';
		$this->label = __("File",'gcacf');
		$this->category = 'content';
		$this->defaults = array(
			'return_format'	=> 'array',
			'library' 		=> 'all',
			'min_size'		=> 0,
			'max_size'		=> 0,
			'mime_types'	=> ''
		);
		
		// filters
		add_filter('get_media_item_args', array($this, 'get_media_item_args'));
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
		   	'Select File'	=> __('Select File', 'gcacf'),
			'Edit File'		=> __('Edit File', 'gcacf'),
			'Update File'	=> __('Update File', 'gcacf'),
	   	));
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
		$uploader = gcacf_get_setting('uploader');
		
		
		// allow custom uploader
		$uploader = gcacf_maybe_get($field, 'uploader', $uploader);
		
		
		// enqueue
		if( $uploader == 'wp' ) {
			gcacf_enqueue_uploader();
		}
		
		
		// vars
		$o = array(
			'icon'		=> '',
			'title'		=> '',
			'url'		=> '',
			'filename'	=> '',
			'filesize'	=> ''
		);
		
		$div = array(
			'class'				=> 'gcacf-file-uploader',
			'data-library' 		=> $field['library'],
			'data-mime_types'	=> $field['mime_types'],
			'data-uploader'		=> $uploader
		);
		
		
		// has value?
		if( $field['value'] ) {
			
			$attachment = gcacf_get_attachment($field['value']);
			if( $attachment ) {
				
				// has value
				$div['class'] .= ' has-value';
				
				// update
				$o['icon'] = $attachment['icon'];
				$o['title']	= $attachment['title'];
				$o['url'] = $attachment['url'];
				$o['filename'] = $attachment['filename'];
				if( $attachment['filesize'] ) {
					$o['filesize'] = size_format($attachment['filesize']);
				}
			}		
		}
				
?>
<div <?php gcacf_esc_attr_e( $div ); ?>>
	<?php gcacf_hidden_input(array( 'name' => $field['name'], 'value' => $field['value'], 'data-name' => 'id' )); ?>
	<div class="show-if-value file-wrap">
		<div class="file-icon">
			<img data-name="icon" src="<?php echo esc_url($o['icon']); ?>" alt=""/>
		</div>
		<div class="file-info">
			<p>
				<strong data-name="title"><?php echo esc_html($o['title']); ?></strong>
			</p>
			<p>
				<strong><?php _e('File name', 'gcacf'); ?>:</strong>
				<a data-name="filename" href="<?php echo esc_url($o['url']); ?>" target="_blank"><?php echo esc_html($o['filename']); ?></a>
			</p>
			<p>
				<strong><?php _e('File size', 'gcacf'); ?>:</strong>
				<span data-name="filesize"><?php echo esc_html($o['filesize']); ?></span>
			</p>
		</div>
		<div class="gcacf-actions -hover">
			<?php 
			if( $uploader != 'basic' ): 
			?><a class="gcacf-icon -pencil dark" data-name="edit" href="#" title="<?php _e('Edit', 'gcacf'); ?>"></a><?php 
			endif;
			?><a class="gcacf-icon -cancel dark" data-name="remove" href="#" title="<?php _e('Remove', 'gcacf'); ?>"></a>
		</div>
	</div>
	<div class="hide-if-value">
		<?php if( $uploader == 'basic' ): ?>
			
			<?php if( $field['value'] && !is_numeric($field['value']) ): ?>
				<div class="gcacf-error-message"><p><?php echo gcacf_esc_html($field['value']); ?></p></div>
			<?php endif; ?>
			
			<label class="gcacf-basic-uploader">
				<?php gcacf_file_input(array( 'name' => $field['name'], 'id' => $field['id'] )); ?>
			</label>
			
		<?php else: ?>
			
			<p><?php _e('No file selected','gcacf'); ?> <a data-name="add" class="gcacf-button button" href="#"><?php _e('Add File','gcacf'); ?></a></p>
			
		<?php endif; ?>
		
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
		
		// clear numeric settings
		$clear = array(
			'min_size',
			'max_size'
		);
		
		foreach( $clear as $k ) {
			
			if( empty($field[$k]) ) {
				
				$field[$k] = '';
				
			}
			
		}
		
		
		// return_format
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Return Value','gcacf'),
			'instructions'	=> __('Specify the returned value on front end','gcacf'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'array'			=> __("File Array",'gcacf'),
				'url'			=> __("File URL",'gcacf'),
				'id'			=> __("File ID",'gcacf')
			)
		));
		
		
		// library
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Library','gcacf'),
			'instructions'	=> __('Limit the media library choice','gcacf'),
			'type'			=> 'radio',
			'name'			=> 'library',
			'layout'		=> 'horizontal',
			'choices' 		=> array(
				'all'			=> __('All', 'gcacf'),
				'uploadedTo'	=> __('Uploaded to post', 'gcacf')
			)
		));
		
		
		// min
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Minimum','gcacf'),
			'instructions'	=> __('Restrict which files can be uploaded','gcacf'),
			'type'			=> 'text',
			'name'			=> 'min_size',
			'prepend'		=> __('File size', 'gcacf'),
			'append'		=> 'MB',
		));
		
		
		// max
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Maximum','gcacf'),
			'instructions'	=> __('Restrict which files can be uploaded','gcacf'),
			'type'			=> 'text',
			'name'			=> 'max_size',
			'prepend'		=> __('File size', 'gcacf'),
			'append'		=> 'MB',
		));
		
		
		// allowed type
		gcacf_render_field_setting( $field, array(
			'label'			=> __('Allowed file types','gcacf'),
			'instructions'	=> __('Comma separated list. Leave blank for all types','gcacf'),
			'type'			=> 'text',
			'name'			=> 'mime_types',
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
		if( empty($value) ) return false;
		
		
		// bail early if not numeric (error message)
		if( !is_numeric($value) ) return false;
		
		
		// convert to int
		$value = intval($value);
		
		
		// format
		if( $field['return_format'] == 'url' ) {
		
			return wp_get_attachment_url($value);
			
		} elseif( $field['return_format'] == 'array' ) {
			
			return gcacf_get_attachment( $value );
		}
		
		
		// return
		return $value;
	}
	
	
	/*
	*  get_media_item_args
	*
	*  description
	*
	*  @type	function
	*  @date	27/01/13
	*  @since	3.6.0
	*
	*  @param	$vars (array)
	*  @return	$vars
	*/
	
	function get_media_item_args( $vars ) {
	
	    $vars['send'] = true;
	    return($vars);
	    
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
		
		// bail early if is empty
		if( empty($value) ) return false;
		
		
		// validate
		if( is_array($value) && isset($value['ID']) ) { 
			
			$value = $value['ID'];
			
		} elseif( is_object($value) && isset($value->ID) ) { 
			
			$value = $value->ID;
			
		}
		
		
		// bail early if not attachment ID
		if( !$value || !is_numeric($value) ) return false;
		
		
		// confirm type
		$value = (int) $value;
		
		
		// maybe connect attacment to post 
		gcacf_connect_attachment_to_post( $value, $post_id );
		
		
		// return
		return $value;
		
	}
		
	
	
	/*
	*  validate_value
	*
	*  This function will validate a basic file input
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
		if( empty($value) ) return $valid;
		
		
		// bail ealry if is numeric
		if( is_numeric($value) ) return $valid;
		
		
		// bail ealry if not basic string
		if( !is_string($value) ) return $valid;
		
		
		// decode value
		$file = null;
		parse_str($value, $file);
		
		
		// bail early if no attachment
		if( empty($file) ) return $valid;
		
		
		// get errors
		$errors = gcacf_validate_attachment( $file, $field, 'basic_upload' );
		
		
		// append error
		if( !empty($errors) ) {
			
			$valid = implode("\n", $errors);
			
		}
		
		
		// return		
		return $valid;
		
	}
	
}


// initialize
gcacf_register_field_type( 'gcacf_field_file' );

endif; // class_exists check

?>