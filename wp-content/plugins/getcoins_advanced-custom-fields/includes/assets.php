<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('GCACF_Assets') ) :

class GCACF_Assets {
	
	/** @var array Storage for translations */
	var $text = array();
	
	/** @var array Storage for data */
	var $data = array();
	
	
	/**
	*  __construct
	*
	*  description
	*
	*  @date	10/4/18
	*  @since	5.6.9
	*
	*  @param	void
	*  @return	void
	*/
		
	function __construct() {
		
		// actions
		add_action('init',	array($this, 'register_scripts'));
	}
	
	
	/**
	*  add_text
	*
	*  description
	*
	*  @date	13/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function add_text( $text ) {
		foreach( (array) $text as $k => $v ) {
			$this->text[ $k ] = $v;
		}
	}
	
	
	/**
	*  add_data
	*
	*  description
	*
	*  @date	13/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function add_data( $data ) {
		foreach( (array) $data as $k => $v ) {
			$this->data[ $k ] = $v;
		}
	}
	
	
	/**
	*  register_scripts
	*
	*  description
	*
	*  @date	13/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function register_scripts() {
		
		// vars
		$version = gcacf_get_setting('version');
		$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		
		// scripts
		wp_register_script('gcacf-input', gcacf_get_url("assets/js/gcacf-input{$min}.js"), array('jquery', 'jquery-ui-sortable', 'jquery-ui-resizable'), $version );
		wp_register_script('gcacf-field-group', gcacf_get_url("assets/js/gcacf-field-group{$min}.js"), array('gcacf-input'), $version );
		
		// styles
		wp_register_style('gcacf-global', gcacf_get_url('assets/css/gcacf-global.css'), array(), $version );
		wp_register_style('gcacf-input', gcacf_get_url('assets/css/gcacf-input.css'), array('gcacf-global'), $version );
		wp_register_style('gcacf-field-group', gcacf_get_url('assets/css/gcacf-field-group.css'), array('gcacf-input'), $version );
		
		// action
		do_action('gcacf/register_scripts', $version, $min);
	}
	
	
	/**
	*  enqueue_scripts
	*
	*  Enqueue scripts for input
	*
	*  @date	13/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function enqueue_scripts( $args = array() ) {
		
		// run only once
		if( gcacf_has_done('enqueue_scripts') ) {
			return;
		}
		
		// defaults
		$args = wp_parse_args($args, array(
			
			// force tinymce editor to be enqueued
			'uploader'			=> false,
			
			// priority used for action callbacks, defaults to 20 which runs after defaults
			'priority'			=> 20,
			
			// action prefix 
			'context'			=> is_admin() ? 'admin' : 'wp'
		));
		
		// define actions
		$actions = array(
			'admin_enqueue_scripts'			=> $args['context'] . '_enqueue_scripts',
			'admin_print_scripts'			=> $args['context'] . '_print_scripts',
			'admin_head'					=> $args['context'] . '_head',
			'admin_footer'					=> $args['context'] . '_footer',
			'admin_print_footer_scripts'	=> $args['context'] . '_print_footer_scripts',
		);
		
		// fix customizer actions where head and footer are not available
		if( $args['context'] == 'customize_controls' ) {
			$actions['admin_head'] = $actions['admin_print_scripts'];
			$actions['admin_footer'] = $actions['admin_print_footer_scripts'];
		}
		
		// add actions
		foreach( $actions as $function => $action ) {
			gcacf_maybe_add_action( $action, array($this, $function), $args['priority'] );
		}
		
		// enqueue uploader
		// WP requires a lot of JS + inline scripes to create the media modal and should be avoioded when possible.
		// - priority must be less than 10 to allow WP to enqueue
		if( $args['uploader'] ) {
			add_action($actions['admin_footer'], 'gcacf_enqueue_uploader', 5);
		}
		
		// localize text
		gcacf_localize_text(array(
			
			// unload
			'The changes you made will be lost if you navigate away from this page'	=> __('The changes you made will be lost if you navigate away from this page', 'gcacf'),
			
			// media
			'Select.verb'			=> _x('Select', 'verb', 'gcacf'),
			'Edit.verb'				=> _x('Edit', 'verb', 'gcacf'),
			'Update.verb'			=> _x('Update', 'verb', 'gcacf'),
			'Uploaded to this post'	=> __('Uploaded to this post', 'gcacf'),
			'Expand Details' 		=> __('Expand Details', 'gcacf'),
			'Collapse Details' 		=> __('Collapse Details', 'gcacf'),
			'Restricted'			=> __('Restricted', 'gcacf'),
			'All images'			=> __('All images', 'gcacf'),
			
			// validation
			'Validation successful'			=> __('Validation successful', 'gcacf'),
			'Validation failed'				=> __('Validation failed', 'gcacf'),
			'1 field requires attention'	=> __('1 field requires attention', 'gcacf'),
			'%d fields require attention'	=> __('%d fields require attention', 'gcacf'),
			
			// tooltip
			'Are you sure?'			=> __('Are you sure?','gcacf'),
			'Yes'					=> __('Yes','gcacf'),
			'No'					=> __('No','gcacf'),
			'Remove'				=> __('Remove','gcacf'),
			'Cancel'				=> __('Cancel','gcacf'),
			
			// conditions
			'Has any value'				=> __('Has any value', 'gcacf'),
			'Has no value'				=> __('Has no value', 'gcacf'),
			'Value is equal to'			=> __('Value is equal to', 'gcacf'),
			'Value is not equal to'		=> __('Value is not equal to', 'gcacf'),
			'Value matches pattern'		=> __('Value matches pattern', 'gcacf'),
			'Value contains'			=> __('Value contains', 'gcacf'),
			'Value is greater than'		=> __('Value is greater than', 'gcacf'),
			'Value is less than'		=> __('Value is less than', 'gcacf'),
			'Selection is greater than'	=> __('Selection is greater than', 'gcacf'),
			'Selection is less than'	=> __('Selection is less than', 'gcacf'),
			
			// misc
			'Edit field group'	=> __('Edit field group', 'gcacf'),
		));
	}
	
	
	/**
	*  admin_enqueue_scripts
	*
	*  description
	*
	*  @date	16/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function admin_enqueue_scripts() {
		
		// enqueue
		wp_enqueue_script('gcacf-input');
		wp_enqueue_style('gcacf-input');
		
		// vars
		$text = array();
		
		// actions
		do_action('gcacf/enqueue_scripts');
		do_action('gcacf/admin_enqueue_scripts');
		do_action('gcacf/input/admin_enqueue_scripts');
		
		// only include translated strings
		foreach( $this->text as $k => $v ) {
			if( str_replace('.verb', '', $k) !== $v ) {
				$text[ $k ] = $v;
			}
		}
		
		// localize text
		if( $text ) {
			wp_localize_script( 'gcacf-input', 'gcacfL10n', $text );
		}
	}
	
	
	/**
	*  admin_print_scripts
	*
	*  description
	*
	*  @date	18/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function admin_print_scripts() {
		do_action('gcacf/admin_print_scripts');
	}
	
	
	/**
	*  admin_head
	*
	*  description
	*
	*  @date	16/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function admin_head() {

		// actions
		do_action('gcacf/admin_head');
		do_action('gcacf/input/admin_head');
	}
	
	
	/**
	*  admin_footer
	*
	*  description
	*
	*  @date	16/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function admin_footer() {
		
		// global
		global $wp_version;
		
		// get data
		$data = wp_parse_args($this->data, array(
			'screen'		=> gcacf_get_form_data('screen'),
			'post_id'		=> gcacf_get_form_data('post_id'),
			'nonce'			=> wp_create_nonce( 'gcacf_nonce' ),
			'admin_url'		=> admin_url(),
			'ajaxurl'		=> admin_url( 'admin-ajax.php' ),
			'validation'	=> gcacf_get_form_data('validation'),
			'wp_version'	=> $wp_version,
			'gcacf_version'	=> gcacf_get_setting('version'),
			'browser'		=> gcacf_get_browser(),
			'locale'		=> gcacf_get_locale(),
			'rtl'			=> is_rtl()
		));
		
		// get l10n (old)
		$l10n = apply_filters( 'gcacf/input/admin_l10n', array() );
		
		// todo: force 'gcacf-input' script enqueue if not yet included
		// - fixes potential timing issue if gcacf_enqueue_assest() was called during body
		
		// localize data
		?>
<script type="text/javascript">
gcacf.data = <?php echo wp_json_encode($data); ?>;
gcacf.l10n = <?php echo wp_json_encode($l10n); ?>;
</script>
<?php 
		
		// actions
		do_action('gcacf/admin_footer');
		do_action('gcacf/input/admin_footer');
		
		// trigger prepare
		?>
<script type="text/javascript">
gcacf.doAction('prepare');
</script>
<?php
	
	}
	
	
	/**
	*  admin_print_footer_scripts
	*
	*  description
	*
	*  @date	18/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function admin_print_footer_scripts() {
		do_action('gcacf/admin_print_footer_scripts');
	}
	
	/*
	*  enqueue_uploader
	*
	*  This function will render a WP WYSIWYG and enqueue media
	*
	*  @type	function
	*  @date	27/10/2014
	*  @since	5.0.9
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function enqueue_uploader() {
		
		// run only once
		if( gcacf_has_done('enqueue_uploader') ) {
			return;
		}
		
		// bail early if doing ajax
		if( gcacf_is_ajax() ) {
			return;
		}
		
		// enqueue media if user can upload
		if( current_user_can('upload_files') ) {
			wp_enqueue_media();
		}
		
		// create dummy editor
		?>
		<div id="gcacf-hidden-wp-editor" class="gcacf-hidden">
			<?php wp_editor( '', 'gcacf_content' ); ?>
		</div>
		<?php
			
		// action
		do_action('gcacf/enqueue_uploader');
	}
}

// instantiate
gcacf_new_instance('GCACF_Assets');

endif; // class_exists check


/**
*  gcacf_localize_text
*
*  description
*
*  @date	13/4/18
*  @since	5.6.9
*
*  @param	type $var Description. Default.
*  @return	type Description.
*/

function gcacf_localize_text( $text ) {
	return gcacf_get_instance('GCACF_Assets')->add_text( $text );
}


/**
*  gcacf_localize_data
*
*  description
*
*  @date	13/4/18
*  @since	5.6.9
*
*  @param	type $var Description. Default.
*  @return	type Description.
*/

function gcacf_localize_data( $data ) {
	return gcacf_get_instance('GCACF_Assets')->add_data( $data );
}


/*
*  gcacf_enqueue_scripts
*
*  
*
*  @type	function
*  @date	6/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function gcacf_enqueue_scripts( $args = array() ) {
	return gcacf_get_instance('GCACF_Assets')->enqueue_scripts( $args );
}


/*
*  gcacf_enqueue_uploader
*
*  This function will render a WP WYSIWYG and enqueue media
*
*  @type	function
*  @date	27/10/2014
*  @since	5.0.9
*
*  @param	n/a
*  @return	n/a
*/

function gcacf_enqueue_uploader() {
	return gcacf_get_instance('GCACF_Assets')->enqueue_uploader();
}

?>