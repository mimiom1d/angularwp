<?php 

// vars
$prefix = 'gcacf_fields[' . $field['ID'] . ']';
$id = gcacf_idify( $prefix );

// add prefix
$field['prefix'] = $prefix;

// div
$div = array(
	'class' 	=> 'gcacf-field-object gcacf-field-object-' . gcacf_slugify($field['type']),
	'data-id'	=> $field['ID'],
	'data-key'	=> $field['key'],
	'data-type'	=> $field['type'],
);

$meta = array(
	'ID'			=> $field['ID'],
	'key'			=> $field['key'],
	'parent'		=> $field['parent'],
	'menu_order'	=> $i,
	'save'			=> ''
);

?>
<div <?php echo gcacf_esc_attr( $div ); ?>>
	
	<div class="meta">
		<?php foreach( $meta as $k => $v ):
			gcacf_hidden_input(array( 'name' => $prefix . '[' . $k . ']', 'value' => $v, 'id' => $id . '-' . $k ));
		endforeach; ?>
	</div>
	
	<div class="handle">
		<ul class="gcacf-hl gcacf-tbody">
			<li class="li-field-order">
				<span class="gcacf-icon gcacf-sortable-handle" title="<?php _e('Drag to reorder','gcacf'); ?>"><?php echo ($i + 1); ?></span>
			</li>
			<li class="li-field-label">
				<strong>
					<a class="edit-field" title="<?php _e("Edit field",'gcacf'); ?>" href="#"><?php echo gcacf_get_field_label($field, 'admin'); ?></a>
				</strong>
				<div class="row-options">
					<a class="edit-field" title="<?php _e("Edit field",'gcacf'); ?>" href="#"><?php _e("Edit",'gcacf'); ?></a>
					<a class="duplicate-field" title="<?php _e("Duplicate field",'gcacf'); ?>" href="#"><?php _e("Duplicate",'gcacf'); ?></a>
					<a class="move-field" title="<?php _e("Move field to another group",'gcacf'); ?>" href="#"><?php _e("Move",'gcacf'); ?></a>
					<a class="delete-field" title="<?php _e("Delete field",'gcacf'); ?>" href="#"><?php _e("Delete",'gcacf'); ?></a>
				</div>
			</li>
			<?php // whitespace before field name looks odd but fixes chrome bug selecting all text in row ?>
			<li class="li-field-name"> <?php echo $field['name']; ?></li>
			<li class="li-field-key"> <?php echo $field['key']; ?></li>
			<li class="li-field-type"> <?php echo gcacf_get_field_type_label($field['type']); ?></li>
		</ul>
	</div>
	
	<div class="settings">			
		<table class="gcacf-table">
			<tbody class="gcacf-field-settings">
				<?php 
				
				// label
				gcacf_render_field_setting($field, array(
					'label'			=> __('Field Label','gcacf'),
					'instructions'	=> __('This is the name which will appear on the EDIT page','gcacf'),
					'name'			=> 'label',
					'type'			=> 'text',
					'class'			=> 'field-label'
				), true);
				
				
				// name
				gcacf_render_field_setting($field, array(
					'label'			=> __('Field Name','gcacf'),
					'instructions'	=> __('Single word, no spaces. Underscores and dashes allowed','gcacf'),
					'name'			=> 'name',
					'type'			=> 'text',
					'class'			=> 'field-name'
				), true);
				
				
				// type
				gcacf_render_field_setting($field, array(
					'label'			=> __('Field Type','gcacf'),
					'instructions'	=> '',
					'type'			=> 'select',
					'name'			=> 'type',
					'choices' 		=> gcacf_get_grouped_field_types(),
					'class'			=> 'field-type'
				), true);
				
				
				// instructions
				gcacf_render_field_setting($field, array(
					'label'			=> __('Instructions','gcacf'),
					'instructions'	=> __('Instructions for authors. Shown when submitting data','gcacf'),
					'type'			=> 'textarea',
					'name'			=> 'instructions',
					'rows'			=> 5
				), true);
				
				
				// required
				gcacf_render_field_setting($field, array(
					'label'			=> __('Required?','gcacf'),
					'instructions'	=> '',
					'type'			=> 'true_false',
					'name'			=> 'required',
					'ui'			=> 1,
					'class'			=> 'field-required'
				), true);
				
				
				// 3rd party settings
				do_action('gcacf/render_field_settings', $field);
				
				
				// type specific settings
				do_action("gcacf/render_field_settings/type={$field['type']}", $field);
				
				
				// conditional logic
				gcacf_get_view('field-group-field-conditional-logic', array( 'field' => $field ));
				
				
				// wrapper
				gcacf_render_field_wrap(array(
					'label'			=> __('Wrapper Attributes','gcacf'),
					'instructions'	=> '',
					'type'			=> 'number',
					'name'			=> 'width',
					'prefix'		=> $field['prefix'] . '[wrapper]',
					'value'			=> $field['wrapper']['width'],
					'prepend'		=> __('width', 'gcacf'),
					'append'		=> '%',
					'wrapper'		=> array(
						'data-name' => 'wrapper',
						'class' => 'gcacf-field-setting-wrapper'
					)
				), 'tr');
				
				gcacf_render_field_wrap(array(
					'label'			=> '',
					'instructions'	=> '',
					'type'			=> 'text',
					'name'			=> 'class',
					'prefix'		=> $field['prefix'] . '[wrapper]',
					'value'			=> $field['wrapper']['class'],
					'prepend'		=> __('class', 'gcacf'),
					'wrapper'		=> array(
						'data-append' => 'wrapper'
					)
				), 'tr');
				
				gcacf_render_field_wrap(array(
					'label'			=> '',
					'instructions'	=> '',
					'type'			=> 'text',
					'name'			=> 'id',
					'prefix'		=> $field['prefix'] . '[wrapper]',
					'value'			=> $field['wrapper']['id'],
					'prepend'		=> __('id', 'gcacf'),
					'wrapper'		=> array(
						'data-append' => 'wrapper'
					)
				), 'tr');
				
				?>
				<tr class="gcacf-field gcacf-field-save">
					<td class="gcacf-label"></td>
					<td class="gcacf-input">
						<ul class="gcacf-hl">
							<li>
								<a class="button edit-field" title="<?php _e("Close Field",'gcacf'); ?>" href="#"><?php _e("Close Field",'gcacf'); ?></a>
							</li>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	
</div>