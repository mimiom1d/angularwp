<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('gcacf_admin') ) :

class gcacf_admin {
	
	// vars
	var $notices = array();
	
	
	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
	
		// actions
		add_action('admin_menu', 			array($this, 'admin_menu'));
		add_action('admin_enqueue_scripts',	array($this, 'admin_enqueue_scripts'), 0);
		add_action('admin_notices', 		array($this, 'admin_notices'));
		
	}
	
	
	/*
	*  add_notice
	*
	*  This function will add the notice data to a setting in the gcacf object for the admin_notices action to use
	*
	*  @type	function
	*  @date	17/10/13
	*  @since	5.0.0
	*
	*  @param	$text (string)
	*  @param	$class (string)
	*  @param	wrap (string)
	*  @return	n/a
	*/
	
	function add_notice( $text = '', $class = '', $wrap = 'p' ) {
		
		// append
		$this->notices[] = array(
			'text'	=> $text,
			'class'	=> 'updated ' . $class,
			'wrap'	=> $wrap
		);
		
	}
	
	
	/*
	*  get_notices
	*
	*  This function will return an array of admin notices
	*
	*  @type	function
	*  @date	17/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	(array)
	*/
	
	function get_notices() {
		
		// bail early if no notices
		if( empty($this->notices) ) return false;
		
		
		// return
		return $this->notices;
		
	}
	
	
	/*
	*  admin_menu
	*
	*  This function will add the GCACF menu item to the WP admin
	*
	*  @type	action (admin_menu)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_menu() {
		
		// bail early if no show_admin
		if( !gcacf_get_setting('show_admin') ) return;
		
		
		// vars
		$slug = 'edit.php?post_type=gcacf-field-group';
		$cap = gcacf_get_setting('capability');
		
		
		// add parent
		add_menu_page(__("GC Custom Fields",'gcacf'), __("GC Custom Fields",'gcacf'), $cap, $slug, false, 'dashicons-editor-table', '3.5'); // **GCEdit: specify the menu position with decimal just in case to avoid any conflict with other post being the same 3.
		
		
		// add children
		add_submenu_page($slug, __('Field Groups','gcacf'), __('Field Groups','gcacf'), $cap, $slug );
		add_submenu_page($slug, __('Add New','gcacf'), __('Add New','gcacf'), $cap, 'post-new.php?post_type=gcacf-field-group' );
		
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This function will add the already registered css
	*
	*  @type	function
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_enqueue_scripts() {
		
		wp_enqueue_style( 'gcacf-global' );
		
	}
	
	
	/*
	*  admin_notices
	*
	*  This function will render any admin notices
	*
	*  @type	function
	*  @date	17/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_notices() {
		
		// vars
		$notices = $this->get_notices();
		
		
		// bail early if no notices
		if( !$notices ) return;
		
		
		// loop
		foreach( $notices as $notice ) {
			
			$open = '';
			$close = '';
				
			if( $notice['wrap'] ) {
				
				$open = "<{$notice['wrap']}>";
				$close = "</{$notice['wrap']}>";
				
			}
				
			?>
			<div class="gcacf-admin-notice notice is-dismissible <?php echo esc_attr($notice['class']); ?>"><?php echo $open . $notice['text'] . $close; ?></div>
			<?php
				
		}
		
	}
	
}

// initialize
gcacf()->admin = new gcacf_admin();

endif; // class_exists check


/*
*  gcacf_add_admin_notice
*
*  This function will add the notice data to a setting in the gcacf object for the admin_notices action to use
*
*  @type	function
*  @date	17/10/13
*  @since	5.0.0
*
*  @param	$text (string)
*  @param	$class (string)
*  @return	(int) message ID (array position)
*/

function gcacf_add_admin_notice( $text, $class = '', $wrap = 'p' ) {
	
	return gcacf()->admin->add_notice($text, $class, $wrap);
	
}


/*
*  gcacf_get_admin_notices
*
*  This function will return an array containing any admin notices
*
*  @type	function
*  @date	17/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function gcacf_get_admin_notices() {
	
	return gcacf()->admin->get_notices();
	
}

?>