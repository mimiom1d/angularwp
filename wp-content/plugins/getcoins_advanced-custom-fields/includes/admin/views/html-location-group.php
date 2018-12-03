<div class="rule-group" data-id="<?php echo $group_id; ?>">

	<h4><?php echo ($group_id == 'group_0') ? __("Show this field group if",'gcacf') : __("or",'gcacf'); ?></h4>
	
	<table class="gcacf-table -clear">
		<tbody>
			<?php foreach( $group as $i => $rule ):
				
				// validate rule
				$rule = gcacf_validate_location_rule($rule);
				
				// append id and group
				$rule['id'] = "rule_{$i}";
				$rule['group'] = $group_id;
				
				// view
				gcacf_get_view('html-location-rule', array(
					'rule'	=> $rule
				));
				
			 endforeach; ?>
		</tbody>
	</table>
	
</div>