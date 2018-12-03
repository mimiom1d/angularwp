<?php

// global
global $field_group;
		
		
// active
gcacf_render_field_wrap(array(
	'label'			=> __('Active','gcacf'),
	'instructions'	=> '',
	'type'			=> 'true_false',
	'name'			=> 'active',
	'prefix'		=> 'gcacf_field_group',
	'value'			=> $field_group['active'],
	'ui'			=> 1,
	//'ui_on_text'	=> __('Active', 'gcacf'),
	//'ui_off_text'	=> __('Inactive', 'gcacf'),
));


// style
gcacf_render_field_wrap(array(
	'label'			=> __('Style','gcacf'),
	'instructions'	=> '',
	'type'			=> 'select',
	'name'			=> 'style',
	'prefix'		=> 'gcacf_field_group',
	'value'			=> $field_group['style'],
	'choices' 		=> array(
		'default'			=>	__("Standard (WP metabox)",'gcacf'),
		'seamless'			=>	__("Seamless (no metabox)",'gcacf'),
	)
));


// position
gcacf_render_field_wrap(array(
	'label'			=> __('Position','gcacf'),
	'instructions'	=> '',
	'type'			=> 'select',
	'name'			=> 'position',
	'prefix'		=> 'gcacf_field_group',
	'value'			=> $field_group['position'],
	'choices' 		=> array(
		'gcacf_after_title'	=> __("High (after title)",'gcacf'),
		'normal'			=> __("Normal (after content)",'gcacf'),
		'side' 				=> __("Side",'gcacf'),
	),
	'default_value'	=> 'normal'
));


// label_placement
gcacf_render_field_wrap(array(
	'label'			=> __('Label placement','gcacf'),
	'instructions'	=> '',
	'type'			=> 'select',
	'name'			=> 'label_placement',
	'prefix'		=> 'gcacf_field_group',
	'value'			=> $field_group['label_placement'],
	'choices' 		=> array(
		'top'			=>	__("Top aligned",'gcacf'),
		'left'			=>	__("Left aligned",'gcacf'),
	)
));


// instruction_placement
gcacf_render_field_wrap(array(
	'label'			=> __('Instruction placement','gcacf'),
	'instructions'	=> '',
	'type'			=> 'select',
	'name'			=> 'instruction_placement',
	'prefix'		=> 'gcacf_field_group',
	'value'			=> $field_group['instruction_placement'],
	'choices' 		=> array(
		'label'		=>	__("Below labels",'gcacf'),
		'field'		=>	__("Below fields",'gcacf'),
	)
));


// menu_order
gcacf_render_field_wrap(array(
	'label'			=> __('Order No.','gcacf'),
	'instructions'	=> __('Field groups with a lower order will appear first','gcacf'),
	'type'			=> 'number',
	'name'			=> 'menu_order',
	'prefix'		=> 'gcacf_field_group',
	'value'			=> $field_group['menu_order'],
));


// description
gcacf_render_field_wrap(array(
	'label'			=> __('Description','gcacf'),
	'instructions'	=> __('Shown in field group list','gcacf'),
	'type'			=> 'text',
	'name'			=> 'description',
	'prefix'		=> 'gcacf_field_group',
	'value'			=> $field_group['description'],
));


// hide on screen
$choices = array(
	'permalink'			=>	__("Permalink", 'gcacf'),
	'the_content'		=>	__("Content Editor",'gcacf'),
	'excerpt'			=>	__("Excerpt", 'gcacf'),
	'custom_fields'		=>	__("Custom Fields", 'gcacf'),
	'discussion'		=>	__("Discussion", 'gcacf'),
	'comments'			=>	__("Comments", 'gcacf'),
	'revisions'			=>	__("Revisions", 'gcacf'),
	'slug'				=>	__("Slug", 'gcacf'),
	'author'			=>	__("Author", 'gcacf'),
	'format'			=>	__("Format", 'gcacf'),
	'page_attributes'	=>	__("Page Attributes", 'gcacf'),
	'featured_image'	=>	__("Featured Image", 'gcacf'),
	'categories'		=>	__("Categories", 'gcacf'),
	'tags'				=>	__("Tags", 'gcacf'),
	'send-trackbacks'	=>	__("Send Trackbacks", 'gcacf'),
);
if( gcacf_get_setting('remove_wp_meta_box') ) {
	unset( $choices['custom_fields'] );	// **GCEdit: just a comment here, but if remove_wp_meta_box is set to true in the settings in gcacf.php, then Custom Field checkbox will automatically dissapear. So this time, we set it to false so it wont hide from the beginning. 
}

gcacf_render_field_wrap(array(
	'label'			=> __('Hide on screen','gcacf'),
	'instructions'	=> __('<b>Select</b> items to <b>hide</b> them from the edit screen.','gcacf') . '<br /><br />' . __("If multiple field groups appear on an edit screen, the first field group's options will be used (the one with the lowest order number)",'gcacf'),
	'type'			=> 'checkbox',
	'name'			=> 'hide_on_screen',
	'prefix'		=> 'gcacf_field_group',
	'value'			=> $field_group['hide_on_screen'],
	'toggle'		=> true,
	'choices' 		=> $choices
));


// 3rd party settings
do_action('gcacf/render_field_group_settings', $field_group);
		
?>
<div class="gcacf-hidden">
	<input type="hidden" name="gcacf_field_group[key]" value="<?php echo $field_group['key']; ?>" />
</div>
<script type="text/javascript">
if( typeof gcacf !== 'undefined' ) {
		
	gcacf.newPostbox({
		'id': 'gcacf-field-group-options',
		'label': 'left'
	});	

}
</script>