<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('gcacf_location_user_form') ) :

class gcacf_location_user_form extends gcacf_location {
	
	
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
	
	function initialize() {
		
		// vars
		$this->name = 'user_form';
		$this->label = __("User Form",'gcacf');
		$this->category = 'user';
    	
	}
	
	
	/*
	*  rule_match
	*
	*  This function is used to match this location $rule to the current $screen
	*
	*  @type	function
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match( $result, $rule, $screen ) {
		
		// vars
		$user_form = gcacf_maybe_get( $screen, 'user_form' );
		
		
		// bail early if no user form
		if( !$user_form ) return false;
		
		
		// add is treated the same as edit
		if( $user_form === 'add' ) {

			$user_form = 'edit';
			
		}
		
		
		// match
		return $this->compare( $user_form, $rule );
		
	}
	
	
	/*
	*  rule_operators
	*
	*  This function returns the available values for this rule type
	*
	*  @type	function
	*  @date	30/5/17
	*  @since	5.6.0
	*
	*  @param	n/a
	*  @return	(array)
	*/
	
	function rule_values( $choices, $rule ) {
		
		return array(
			'all' 		=> __('All', 'gcacf'),
			'edit' 		=> __('Add / Edit', 'gcacf'),
			'register' 	=> __('Register', 'gcacf')
		);
		
/*
		
		// global
		global $wp_roles;
		
		
		// vars
		$choices = array( 'all' => __('All', 'gcacf') );
		$choices = array_merge( $choices, $wp_roles->get_names() );
		
		
		// return
		return $choices;
*/
		
	}
	
}

// initialize
gcacf_register_location_rule( 'gcacf_location_user_form' );

endif; // class_exists check

?>