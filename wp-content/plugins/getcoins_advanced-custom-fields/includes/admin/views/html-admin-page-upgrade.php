<?php

/**
*  Admin Database Upgrade
*
*  Shows the databse upgrade process. 
*
*  @date	24/8/18
*  @since	5.7.4
*  @param	void
*/

?>
<style type="text/css">
	
	/* hide steps */
	.step-1,
	.step-2,
	.step-3 {
		display: none;
	}		
	
</style>
<div id="gcacf-upgrade-wrap" class="wrap">
	
	<h1><?php _e("Upgrade Database", 'gcacf'); ?></h1>
	
<?php if( gcacf_has_upgrade() ): ?>

	<p><?php _e('Reading upgrade tasks...', 'gcacf'); ?></p>
	<p class="step-1"><i class="gcacf-loading"></i> <?php printf(__('Upgrading data to version %s', 'gcacf'), GCACF_VERSION); ?></p>
	<p class="step-2"></p>
	<p class="step-3"><?php echo sprintf( __('Database upgrade complete. <a href="%s">See what\'s new</a>', 'gcacf' ), admin_url('edit.php?post_type=gcacf-field-group&page=gcacf-settings-info') ); ?></p>
	
	<script type="text/javascript">
	(function($) {
		
		var upgrader = new gcacf.Model({
			initialize: function(){
				
				// allow user to read message for 1 second
				this.setTimeout( this.upgrade, 1000 );
			},
			upgrade: function(){
				
				// show step 1
				$('.step-1').show();
				
				// vars
				var response = '';
				var success = false;
				
				// send ajax request to upgrade DB
			    $.ajax({
			    	url: gcacf.get('ajaxurl'),
					dataType: 'json',
					type: 'post',
					data: gcacf.prepareForAjax({
						action: 'gcacf/ajax/upgrade'
					}),
					success: function( json ){
						
						// success
						if( gcacf.isAjaxSuccess(json) ) {
							
							// update
							success = true;
							
							// set response text
							if( jsonText = gcacf.getAjaxMessage(json) ) {
								response = jsonText;
							}
						
						// error
						} else {
							
							// set response text
							response = '<?php _e('Upgrade failed.', 'gcacf'); ?>';
							if( jsonText = gcacf.getAjaxError(json) ) {
								response += ' <pre>' + jsonText +  '</pre>';
							}
						}			
					},
					error: function( jqXHR, textStatus, errorThrown ){
						
						// set response text
						response = '<?php _e('Upgrade failed.', 'gcacf'); ?>';
						if( errorThrown) {
							response += ' <pre>' + errorThrown +  '</pre>';
						}
					},
					complete: this.proxy(function(){
						
						// remove spinner
						$('.gcacf-loading').hide();
						
						// display response
						if( response ) {
							$('.step-2').show().html( response );
						}
						
						// display success
						if( success ) {
							$('.step-3').show();
						}
					})
				});
			}
		});
				
	})(jQuery);	
	</script>

<?php else: ?>

	<p><?php _e('No updates available.', 'gcacf'); ?></p>
	
<?php endif; ?>
</div>