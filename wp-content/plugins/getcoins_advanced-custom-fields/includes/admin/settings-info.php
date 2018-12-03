<?php

class gcacf_settings_info {

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
		add_action('admin_menu',	array($this, 'admin_menu'));
		
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
		if( !gcacf_get_setting('show_admin') ) {
		
			return;
			
		}


		// add page
		add_submenu_page('edit.php?post_type=gcacf-field-group', __('Info','gcacf'), __('Info','gcacf'), gcacf_get_setting('capability'),'gcacf-settings-info', array($this,'html'));

	}


	/*
	*  html
	*
	*  description
	*
	*  @type	function
	*  @date	7/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/

	function html() {
		
		// vars
		$view = array(
			'version'		=> gcacf_get_setting('version'),
			'have_pro'		=> gcacf_get_setting('pro'),
			'tabs'			=> array(
				'new'			=> __("What's New", 'gcacf'),
				'changelog'		=> __("Changelog", 'gcacf')
			),
			'active'		=> 'new'
		);
		
		
		// set active tab
		$tab = gcacf_maybe_get_GET('tab');
		if( $tab && isset($view['tabs'][ $tab ]) ) {
			
			$view['active'] = $tab;
			
		}
		
		
		// load view
		gcacf_get_view('settings-info', $view);

	}

}


// initialize
new gcacf_settings_info();

?>