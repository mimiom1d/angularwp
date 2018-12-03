<div class="gcacf-field-list-wrap">
	
	<ul class="gcacf-hl gcacf-thead">
		<li class="li-field-order"><?php _e('Order','gcacf'); ?></li>
		<li class="li-field-label"><?php _e('Label','gcacf'); ?></li>
		<li class="li-field-name"><?php _e('Name','gcacf'); ?></li>
		<li class="li-field-key"><?php _e('Key','gcacf'); ?></li>
		<li class="li-field-type"><?php _e('Type','gcacf'); ?></li>
	</ul>
	
	<div class="gcacf-field-list<?php if( !$fields ){ echo ' -empty'; } ?>">
		
		<div class="no-fields-message">
			<?php _e("No fields. Click the <strong>+ Add Field</strong> button to create your first field.",'gcacf'); ?>
		</div>
		
		<?php if( $fields ):
			
			foreach( $fields as $i => $field ):
				
				gcacf_get_view('field-group-field', array( 'field' => $field, 'i' => $i ));
				
			endforeach;
		
		endif; ?>
		
	</div>
	
	<ul class="gcacf-hl gcacf-tfoot">
		<li class="gcacf-fr">
			<a href="#" class="button button-primary button-large add-field"><?php _e('+ Add Field','gcacf'); ?></a>
		</li>
	</ul>
	
<?php if( !$parent ):
	
	// get clone
	$clone = gcacf_get_valid_field(array(
		'ID'		=> 'gcacfcloneindex',
		'key'		=> 'gcacfcloneindex',
		'label'		=> __('New Field','gcacf'),
		'name'		=> 'new_field',
		'type'		=> 'text'
	));
	
	?>
	<script type="text/html" id="tmpl-gcacf-field">
	<?php gcacf_get_view('field-group-field', array( 'field' => $clone, 'i' => 0 )); ?>
	</script>
<?php endif;?>
	
</div>