<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('gcacf_location_page') ) :

class gcacf_location_page extends gcacf_location {
	
	
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
		$this->name = 'page';
		$this->label = __("Page",'gcacf');
		$this->category = 'page';
    	
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
		
		return gcacf_get_location_rule('post')->rule_match( $result, $rule, $screen );
		
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
		
		// get posts grouped by post type
		$groups = gcacf_get_grouped_posts(array(
			'post_type' => 'page'
		));
		
		
		// pop
		$choices = array_pop( $groups );
		
		
		// convert posts to titles
		foreach( $choices as &$item ) {
			
			$item = gcacf_get_post_title( $item );
			
		}
					
		
		// return
		return $choices;
		
	}
	
}

// initialize
gcacf_register_location_rule( 'gcacf_location_page' );

endif; // class_exists check

?>