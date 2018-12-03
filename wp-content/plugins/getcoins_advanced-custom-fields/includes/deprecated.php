<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('gcacf_deprecated') ) :

class gcacf_deprecated {
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	30/1/17
	*  @since	5.5.6
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// settings
		add_filter('gcacf/settings/show_admin',			array($this, 'gcacf_settings_show_admin'), 5, 1);				// 5.0.0
		add_filter('gcacf/settings/l10n_textdomain',		array($this, 'gcacf_settings_l10n_textdomain'), 5, 1);		// 5.3.3
		add_filter('gcacf/settings/l10n_field',			array($this, 'gcacf_settings_l10n_field'), 5, 1);				// 5.3.3
		add_filter('gcacf/settings/l10n_field_group',		array($this, 'gcacf_settings_l10n_field'), 5, 1);				// 5.3.3
		add_filter('gcacf/settings/url',					array($this, 'gcacf_settings_url'), 5, 1);					// 5.6.8
		add_filter('gcacf/validate_setting',				array($this, 'gcacf_validate_setting'), 5, 1);				// 5.6.8
		

		// filters
		add_filter('gcacf/validate_field', 				array($this, 'gcacf_validate_field'), 10, 1); 				// 5.5.6
		add_filter('gcacf/validate_field_group', 			array($this, 'gcacf_validate_field_group'), 10, 1); 			// 5.5.6
		add_filter('gcacf/validate_post_id', 				array($this, 'gcacf_validate_post_id'), 10, 2); 			// 5.5.6
		
	}
	
	
	/*
	*  gcacf_settings_show_admin
	*
	*  This function will add compatibility for previously named hooks
	*
	*  @type	function
	*  @date	19/05/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function gcacf_settings_show_admin( $setting ) {
		
		// 5.0.0 - removed GCACF_LITE
		return ( defined('GCACF_LITE') && GCACF_LITE ) ? false : $setting;
		
	}
	
	
	/*
	*  gcacf_settings_l10n_textdomain
	*
	*  This function will add compatibility for previously named hooks
	*
	*  @type	function
	*  @date	19/05/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function gcacf_settings_l10n_textdomain( $setting ) {
		
		// 5.3.3 - changed filter name
		return gcacf_get_setting( 'export_textdomain', $setting );
		
	}
	
	
	/*
	*  gcacf_settings_l10n_field
	*
	*  This function will add compatibility for previously named hooks
	*
	*  @type	function
	*  @date	19/05/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function gcacf_settings_l10n_field( $setting ) {
		
		// 5.3.3 - changed filter name
		return gcacf_get_setting( 'export_translate', $setting );
		
	}
	
	
	/**
	*  gcacf_settings_url
	*
	*  This function will add compatibility for previously named hooks
	*
	*  @date	12/12/17
	*  @since	5.6.8
	*
	*  @param	n/a
	*  @return	n/a
	*/
		
	function gcacf_settings_url( $value ) {
		return apply_filters( "gcacf/settings/dir", $value );
	}
	
	/**
	*  gcacf_validate_setting
	*
	*  description
	*
	*  @date	2/2/18
	*  @since	5.6.5
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	function gcacf_validate_setting( $name ) {
		
		// vars
		$changed = array(
			'dir' => 'url'	// 5.6.8
		);
		
		// check
		if( isset($changed[ $name ]) ) {
			return $changed[ $name ];
		}
		
		//return
		return $name;
	}
	
	
	/*
	*  gcacf_validate_field
	*
	*  This function will add compatibility for previously named hooks
	*
	*  @type	function
	*  @date	30/1/17
	*  @since	5.5.6
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function gcacf_validate_field( $field ) {
		
		// 5.5.6 - changed filter name
		$field = apply_filters( "gcacf/get_valid_field/type={$field['type']}", $field );
		$field = apply_filters( "gcacf/get_valid_field", $field );
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  gcacf_validate_field_group
	*
	*  This function will add compatibility for previously named hooks
	*
	*  @type	function
	*  @date	30/1/17
	*  @since	5.5.6
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function gcacf_validate_field_group( $field_group ) {
		
		// 5.5.6 - changed filter name
		$field_group = apply_filters('gcacf/get_valid_field_group', $field_group);
		
		
		// return
		return $field_group;
		
	}
	
	
	/*
	*  gcacf_validate_post_id
	*
	*  This function will add compatibility for previously named hooks
	*
	*  @type	function
	*  @date	6/2/17
	*  @since	5.5.6
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function gcacf_validate_post_id( $post_id, $_post_id ) {
		
		// 5.5.6 - changed filter name
		$post_id = apply_filters('gcacf/get_valid_post_id', $post_id, $_post_id);
		
		
		// return
		return $post_id;
		
	}
	
}


// initialize
gcacf()->deprecated = new gcacf_deprecated();

endif; // class_exists check

?>