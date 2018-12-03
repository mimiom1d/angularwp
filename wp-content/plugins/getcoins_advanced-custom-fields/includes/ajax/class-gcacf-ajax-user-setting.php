<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('GCACF_Ajax_User_Setting') ) :

class GCACF_Ajax_User_Setting extends GCACF_Ajax {
	
	/** @var string The AJAX action name */
	var $action = 'gcacf/ajax/user_setting';
	
	/** @var bool Prevents access for non-logged in users */
	var $public = true;
	
	/**
	*  get_response
	*
	*  The actual logic for this AJAX request.
	*
	*  @date	31/7/18
	*  @since	5.7.2
	*
	*  @param	void
	*  @return	mixed The response data to send back or WP_Error.
	*/
	
	function response() {
		
		// update
		if( $this->has('value') ) {
			return gcacf_update_user_setting( $this->get('name'), $this->get('value') );
		
		// get
		} else {
			return gcacf_get_user_setting( $this->get('name') );
		}
	}
}

gcacf_new_instance('GCACF_Ajax_User_Setting');

endif; // class_exists check

?>