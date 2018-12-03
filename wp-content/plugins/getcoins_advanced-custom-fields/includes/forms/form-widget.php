<?php

/*
*  GCACF Widget Form Class
*
*  All the logic for adding fields to widgets
*
*  @class 		gcacf_form_widget
*  @package		GCACF
*  @subpackage	Forms
*/

if( ! class_exists('gcacf_form_widget') ) :

class gcacf_form_widget {
	
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// vars
		$this->preview_values = array();
		$this->preview_reference = array();
		$this->preview_errors = array();
		
		
		// actions
		add_action('admin_enqueue_scripts',		array($this, 'admin_enqueue_scripts'));
		add_action('in_widget_form', 			array($this, 'edit_widget'), 10, 3);
		add_action('gcacf/validate_save_post',	array($this, 'gcacf_validate_save_post'), 5);
		
		
		// filters
		add_filter('widget_update_callback', 	array($this, 'save_widget'), 10, 4);
		
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This action is run after post query but before any admin script / head actions. 
	*  It is a good place to register all actions.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @date	26/01/13
	*  @since	3.6.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function admin_enqueue_scripts() {
		
		// validate screen
		if( gcacf_is_screen('widgets') || gcacf_is_screen('customize') ) {
		
			// valid
			
		} else {
			
			return;
			
		}
		
		
		// load gcacf scripts
		gcacf_enqueue_scripts();
		
		
		// actions
		add_action('gcacf/input/admin_footer', array($this, 'admin_footer'), 1);

	}
	
	
	/*
	*  gcacf_validate_save_post
	*
	*  This function will loop over $_POST data and validate
	*
	*  @type	action 'gcacf/validate_save_post' 5
	*  @date	7/09/2016
	*  @since	5.4.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function gcacf_validate_save_post() {
		
		// bail ealry if not widget
		if( !isset($_POST['_gcacf_widget_id']) ) return;
		
		
		// vars
		$id = $_POST['_gcacf_widget_id'];
		$number = $_POST['_gcacf_widget_number'];
		$prefix = $_POST['_gcacf_widget_prefix'];
		
		
		// validate
		gcacf_validate_values( $_POST[ $id ][ $number ]['gcacf'], $prefix );
				
	}
	
	
	/*
	*  edit_widget
	*
	*  This function will render the fields for a widget form
	*
	*  @type	function
	*  @date	11/06/2014
	*  @since	5.0.0
	*
	*  @param	$widget (object)
	*  @param	$return (null)
	*  @param	$instance (object)
	*  @return	$post_id (int)
	*/
	
	function edit_widget( $widget, $return, $instance ) {
		
		// vars
		$post_id = 0;
		$prefix = 'widget-' . $widget->id_base . '[' . $widget->number . '][gcacf]';
		
		
		// get id
		if( $widget->number !== '__i__' ) {
		
			$post_id = "widget_{$widget->id}";
			
		}
		
		
		// get field groups
		$field_groups = gcacf_get_field_groups(array(
			'widget' => $widget->id_base
		));
		
		
		// render
		if( !empty($field_groups) ) {
			
			// render post data
			gcacf_form_data(array( 
				'screen'		=> 'widget',
				'post_id'		=> $post_id,
				'widget_id'		=> 'widget-' . $widget->id_base,
				'widget_number'	=> $widget->number,
				'widget_prefix'	=> $prefix
			));
			
			
			// wrap
			echo '<div class="gcacf-widget-fields gcacf-fields -clear">';
			
			// loop
			foreach( $field_groups as $field_group ) {
				
				// load fields
				$fields = gcacf_get_fields( $field_group );
				
				
				// bail if not fields
				if( empty($fields) ) continue;
				
				
				// change prefix
				gcacf_prefix_fields( $fields, $prefix );
				
				
				// render
				gcacf_render_fields( $fields, $post_id, 'div', $field_group['instruction_placement'] );
				
			}
			
			//wrap
			echo '</div>';
			
			
			// jQuery selector looks odd, but is necessary due to WP adding an incremental number into the ID
			// - not possible to find number via PHP parameters
			if( $widget->updated ): ?>
			<script type="text/javascript">
			(function($) {
				
				gcacf.doAction('append', $('[id^="widget"][id$="<?php echo $widget->id; ?>"]') );
				
			})(jQuery);	
			</script>
			<?php endif;
				
		}
		
	}
	
	
	/*
	*  save_widget
	*
	*  This function will hook into the widget update filter and save GCACF data
	*
	*  @type	function
	*  @date	27/05/2015
	*  @since	5.2.3
	*
	*  @param	$instance (array) widget settings
	*  @param	$new_instance (array) widget settings
	*  @param	$old_instance (array) widget settings
	*  @param	$widget (object) widget info
	*  @return	$instance
	*/
	
	function save_widget( $instance, $new_instance, $old_instance, $widget ) {
		
		// bail ealry if not valid (!customize + gcacf values + nonce)
		if( isset($_POST['wp_customize']) || !isset($new_instance['gcacf']) || !gcacf_verify_nonce('widget') ) return $instance;
		
		
		// save
		gcacf_save_post( "widget_{$widget->id}", $new_instance['gcacf'] );
		
		
		// return
		return $instance;
		
	}
	
	
	/*
	*  admin_footer
	*
	*  This function will add some custom HTML to the footer of the edit page
	*
	*  @type	function
	*  @date	11/06/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_footer() {
?>
<script type="text/javascript">
(function($) {
	
	// vars
	gcacf.set('post_id', 'widgets');
	
	// Only initialize visible fields.
	gcacf.addFilter('find_fields', function( $fields ){
		
		// not templates
		$fields = $fields.not('#available-widgets .gcacf-field');
		
		// not widget dragging in
		$fields = $fields.not('.widget.ui-draggable-dragging .gcacf-field');
		
		// return
		return $fields;
	});
	
	// on publish
	$('#widgets-right').on('click', '.widget-control-save', function( e ){
		
		// vars
		var $button = $(this);
		var $form = $button.closest('form');
		
		// validate
		var valid = gcacf.validateForm({
			form: $form,
			event: e,
			reset: true
		});
		
		// if not valid, stop event and allow validation to continue
		if( !valid ) {
			e.preventDefault();
			e.stopImmediatePropagation();
		}
	});
	
	// show
	$('#widgets-right').on('click', '.widget-top', function(){
		var $widget = $(this).parent();
		if( $widget.hasClass('open') ) {
			gcacf.doAction('hide', $widget);
		} else {
			gcacf.doAction('show', $widget);
		}
	});
	
	$(document).on('widget-added', function( e, $widget ){
		
		// - use delay to avoid rendering issues with customizer (ensures div is visible)
		setTimeout(function(){
			gcacf.doAction('append', $widget );
		}, 100);
	});
	
})(jQuery);	
</script>
<?php
		
	}
}

new gcacf_form_widget();

endif;

?>
