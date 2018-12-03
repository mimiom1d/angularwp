<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('GCACF_Form_Gutenberg') ) :

class GCACF_Form_Gutenberg {
	
	/**
	*  __construct
	*
	*  Setup for class functionality.
	*
	*  @date	13/2/18
	*  @since	5.6.9
	*
	*  @param	n/a
	*  @return	n/a
	*/
		
	function __construct() {
		
		// filters
		add_filter( 'replace_editor', array($this, 'replace_editor'), 99, 2 );
	}
	
	
	/**
	*  replace_editor
	*
	*  Check if Gutenberg is replacing the editor.
	*
	*  @date	13/2/18
	*  @since	5.6.9
	*
	*  @param	boolean $replace True if the editor is being replaced by Gutenberg.
	*  @param	object $post The WP_Post being edited.
	*  @return	boolean
	*/
	
	function replace_editor( $replace, $post ) {
		
		// check if Gutenberg is replacing
		if( $replace ) {
			
			// actions
			add_action('admin_footer', array($this, 'admin_footer'));
		}
		
		// return
		return $replace;
	}
	
	/**
	*  admin_footer
	*
	*  Append missing HTML to Gutenberg editor.
	*
	*  @date	13/2/18
	*  @since	5.6.9
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_footer() {
		
		// edit_form_after_title is not run due to missing action, call this manually
		?>
		<div id="gcacf-form-after-title">
			<?php gcacf_get_instance('GCACF_Form_Post')->edit_form_after_title(); ?>
		</div>
		<?php
		
		
		// move #gcacf-form-after-title
		?>
		<script type="text/javascript">
			$('#normal-sortables').before( $('#gcacf-form-after-title') );
		</script>
		<?php
	}		
}

gcacf_new_instance('GCACF_Form_Gutenberg');

endif;

?>