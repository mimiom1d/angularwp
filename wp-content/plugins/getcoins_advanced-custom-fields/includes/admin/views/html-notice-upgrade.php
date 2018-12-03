<?php 

// calculate add-ons (non pro only)
$plugins = array();

if( !gcacf_get_setting('pro') ) {
	
	if( is_plugin_active('gcacf-repeater/gcacf-repeater.php') ) $plugins[] = __("Repeater",'gcacf');
	if( is_plugin_active('gcacf-flexible-content/gcacf-flexible-content.php') ) $plugins[] = __("Flexible Content",'gcacf');
	if( is_plugin_active('gcacf-gallery/gcacf-gallery.php') ) $plugins[] = __("Gallery",'gcacf');
	if( is_plugin_active('gcacf-options-page/gcacf-options-page.php') ) $plugins[] = __("Options Page",'gcacf');
	
}

?>
<div id="gcacf-upgrade-notice" class="notice">
	
	<div class="col-content">
		
		<img src="<?php echo gcacf_get_url('assets/images/gc-logo.png'); ?>" />
		<h2><?php _e("Database Upgrade Required",'gcacf'); ?></h2>
		<p><?php printf(__("Thank you for updating to %s v%s!", 'gcacf'), gcacf_get_setting('name'), gcacf_get_setting('version') ); ?><br /><?php _e("This version contains improvements to your database and requires an upgrade.", 'gcacf'); ?></p>
		<?php if( !empty($plugins) ): ?>
			<p><?php printf(__("Please also check all premium add-ons (%s) are updated to the latest version.", 'gcacf'), implode(', ', $plugins) ); ?></p>
		<?php endif; ?>
	</div>
	
	<div class="col-actions">
		<a id="gcacf-upgrade-button" href="<?php echo $button_url; ?>" class="button button-primary button-hero"><?php echo $button_text; ?></a>
	</div>
	
</div>
<?php if( $confirm ): ?>
<script type="text/javascript">
(function($) {
	
	$("#gcacf-upgrade-button").on("click", function(){
		return confirm("<?php _e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'gcacf' ); ?>");
	});
		
})(jQuery);	
</script>
<?php endif; ?>