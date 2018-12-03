<?php

// global
global $field_group;

?>
<div class="gcacf-field">
	<div class="gcacf-label">
		<label><?php _e("Rules",'gcacf'); ?></label>
		<p class="description"><?php _e("Create a set of rules to determine which edit screens will use these advanced custom fields",'gcacf'); ?></p>
	</div>
	<div class="gcacf-input">
		<div class="rule-groups">
			
			<?php foreach( $field_group['location'] as $i => $group ): 
				
				// bail ealry if no group
				if( empty($group) ) return;
				
				
				// view
				gcacf_get_view('html-location-group', array(
					'group'		=> $group,
					'group_id'	=> "group_{$i}"
				));
			
			endforeach;	?>
			
			<h4><?php _e("or",'gcacf'); ?></h4>
			
			<a href="#" class="button add-location-group"><?php _e("Add rule group",'gcacf'); ?></a>
			
		</div>
	</div>
</div>
<script type="text/javascript">
if( typeof gcacf !== 'undefined' ) {
		
	gcacf.newPostbox({
		'id': 'gcacf-field-group-locations',
		'label': 'left'
	});	

}
</script>