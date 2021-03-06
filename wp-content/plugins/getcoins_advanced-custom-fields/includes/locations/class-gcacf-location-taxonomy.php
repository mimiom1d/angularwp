<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('gcacf_location_taxonomy') ) :

class gcacf_location_taxonomy extends gcacf_location {
	
	
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
		$this->name = 'taxonomy';
		$this->label = __("Taxonomy",'gcacf');
		$this->category = 'forms';
    	
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
		$taxonomy = gcacf_maybe_get( $screen, 'taxonomy' );
		
		
		// bail early if not taxonomy
		if( !$taxonomy ) return false;
				
		
        // return
        return $this->compare( $taxonomy, $rule );
		
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
		
		// vars
		$choices = array( 'all' => __('All', 'gcacf') );
		$choices = array_merge( $choices, gcacf_get_taxonomy_labels() );
		
		
		// return
		return $choices;
		
	}
	
}

// initialize
gcacf_register_location_rule( 'gcacf_location_taxonomy' );

endif; // class_exists check

?>