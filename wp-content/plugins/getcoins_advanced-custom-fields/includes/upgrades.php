<?php

/**
*  gcacf_has_upgrade
*
*  Returns true if this site has an upgrade avaialble.
*
*  @date	24/8/18
*  @since	5.7.4
*
*  @param	void
*  @return	bool
*/
function gcacf_has_upgrade() {
	
	// vars
	$db_version = gcacf_get_db_version();
	
	// return true if DB version is < latest upgrade version // **GCEdit: THis value was 5.5.0, which is not dynamic value.., so NEED TO REFACTOR THIS LATER TO AUTOMATICALLY RETRIEVE THE CURRENT VERSION.
	if( $db_version && gcacf_version_compare($db_version, '<', '1.0.0') ) {
		return true;
	}
	
	// update DB version if needed
	if( $db_version !== GCACF_VERSION ) {
		gcacf_update_db_version( GCACF_VERSION );
	}
	
	// return
	return false;
}

/**
*  gcacf_upgrade_all
*
*  Returns true if this site has an upgrade avaialble.
*
*  @date	24/8/18
*  @since	5.7.4
*
*  @param	void
*  @return	bool
*/
function gcacf_upgrade_all() {
	
	// increase time limit
	@set_time_limit(600);
	
	// start timer
	timer_start();
	
	// log
	gcacf_dev_log('GCACF Upgrade Begin.');
	
	// vars
	$db_version = gcacf_get_db_version();
	
	// 5.0.0
	if( gcacf_version_compare($db_version, '<', '5.0.0') ) {
		gcacf_upgrade_500();
	}
	
	// 5.5.0
	if( gcacf_version_compare($db_version, '<', '5.5.0') ) {
		gcacf_upgrade_550();
	}
	
	// upgrade DB version once all updates are complete
	gcacf_update_db_version( GCACF_VERSION );
	
	// log
	global $wpdb;
	gcacf_dev_log('GCACF Upgrade Complete.', $wpdb->num_queries, timer_stop(0));
}

/**
*  gcacf_get_db_version
*
*  Returns the GCACF DB version.
*
*  @date	10/09/2016
*  @since	5.4.0
*
*  @param	void
*  @return	string
*/
function gcacf_get_db_version() {
	return get_option('gcacf_version');
}

/*
*  gcacf_update_db_version
*
*  Updates the GCACF DB version.
*
*  @date	10/09/2016
*  @since	5.4.0
*
*  @param	string $version The new version.
*  @return	void
*/
function gcacf_update_db_version( $version = '' ) {
	update_option('gcacf_version', $version );
}

/**
*  gcacf_upgrade_500
*
*  Version 5 introduces new post types for field groups and fields.
*
*  @date	23/8/18
*  @since	5.7.4
*
*  @param	void
*  @return	void
*/
function gcacf_upgrade_500() {
	
	// log
	gcacf_dev_log('GCACF Upgrade 5.0.0.');
	
	// action
	do_action('gcacf/upgrade_500');
	
	// do tasks
	gcacf_upgrade_500_field_groups();
	
	// update version
	gcacf_update_db_version('5.0.0');
}

/**
*  gcacf_upgrade_500_field_groups
*
*  Upgrades all GCACF4 field groups to GCACF5
*
*  @date	23/8/18
*  @since	5.7.4
*
*  @param	void
*  @return	void
*/
function gcacf_upgrade_500_field_groups() {
	
	// log
	gcacf_dev_log('GCACF Upgrade 5.0.0 Field Groups.');
	
	// get old field groups
	$ofgs = get_posts(array(
		'numberposts' 		=> -1,
		'post_type' 		=> 'gcacf',
		'orderby' 			=> 'menu_order title',
		'order' 			=> 'asc',
		'suppress_filters'	=> true,
	));
	
	// loop
	if( $ofgs ) {
		foreach( $ofgs as $ofg ){
			gcacf_upgrade_500_field_group( $ofg );
		}
	}
}

/**
*  gcacf_upgrade_500_field_group
*
*  Upgrades a GCACF4 field group to GCACF5
*
*  @date	23/8/18
*  @since	5.7.4
*
*  @param	object $ofg	The old field group post object.
*  @return	array $nfg	The new field group array.
*/
function gcacf_upgrade_500_field_group( $ofg ) {
	
	// log
	gcacf_dev_log('GCACF Upgrade 5.0.0 Field Group.', $ofg);
	
	// vars
	$nfg = array(
		'ID'			=> 0,
		'title'			=> $ofg->post_title,
		'menu_order'	=> $ofg->menu_order,
	);
	
	// construct the location rules
	$rules = get_post_meta($ofg->ID, 'rule', false);
	$anyorall = get_post_meta($ofg->ID, 'allorany', true);
	if( is_array($rules) ) {
		
		// if field group was duplicated, rules may be a serialized string!
		$rules = array_map('maybe_unserialize', $rules);
		
		// convert rules to groups
		$nfg['location'] = gcacf_convert_rules_to_groups( $rules, $anyorall );
	}
	
	// settings
	if( $position = get_post_meta($ofg->ID, 'position', true) ) {
		$nfg['position'] = $position;
	}
	
	if( $layout = get_post_meta($ofg->ID, 'layout', true) ) {
		$nfg['layout'] = $layout;
	}
	
	if( $hide_on_screen = get_post_meta($ofg->ID, 'hide_on_screen', true) ) {
		$nfg['hide_on_screen'] = maybe_unserialize($hide_on_screen);
	}
	
	// save field group
	// gcacf_upgrade_field_group will call the gcacf_get_valid_field_group function and apply 'compatibility' changes
	$nfg = gcacf_update_field_group( $nfg );
	
	// log
	gcacf_dev_log('> Complete.', $nfg);
	
	// action for 3rd party
	do_action('gcacf/upgrade_500_field_group', $nfg, $ofg);
	
	// upgrade fields
	gcacf_upgrade_500_fields( $ofg, $nfg );
	
	// trash?
	if( $ofg->post_status == 'trash' ) {
		gcacf_trash_field_group( $nfg['ID'] );
	}
	
	// return
	return $nfg;
}

/**
*  gcacf_upgrade_500_fields
*
*  Upgrades all GCACF4 fields to GCACF5 from a specific field group 
*
*  @date	23/8/18
*  @since	5.7.4
*
*  @param	object $ofg	The old field group post object.
*  @param	array $nfg	The new field group array.
*  @return	void
*/
function gcacf_upgrade_500_fields( $ofg, $nfg ) {
	
	// log
	gcacf_dev_log('GCACF Upgrade 5.0.0 Fields.');
	
	// global
	global $wpdb;
	
	// get field from postmeta
	$rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s", $ofg->ID, 'field_%'), ARRAY_A);
	
	// check
	if( $rows ) {
		
		// vars
		$checked = array();
		
		// loop
		foreach( $rows as $row ) {
			
			// vars
			$field = $row['meta_value'];
			$field = maybe_unserialize( $field );
			$field = maybe_unserialize( $field ); // run again for WPML
			
			// bail early if key already migrated (potential duplicates in DB)
			if( isset($checked[ $field['key'] ]) ) continue;
			$checked[ $field['key'] ] = 1;
			
			// add parent
			$field['parent'] = $nfg['ID'];
			
			// migrate field
			$field = gcacf_upgrade_500_field( $field );
		}
 	}
}

/**
*  gcacf_upgrade_500_field
*
*  Upgrades a GCACF4 field to GCACF5
*
*  @date	23/8/18
*  @since	5.7.4
*
*  @param	array $field The old field.
*  @return	array $field The new field.
*/
function gcacf_upgrade_500_field( $field ) {
	
	// log
	gcacf_dev_log('GCACF Upgrade 5.0.0 Field.', $field);
	
	// order_no is now menu_order
	$field['menu_order'] = gcacf_extract_var( $field, 'order_no', 0 );
	
	// correct very old field keys (field2 => field_2)
	if( substr($field['key'], 0, 6) !== 'field_' ) {
		$field['key'] = 'field_' . str_replace('field', '', $field['key']);
	}
	
	// extract sub fields
	$sub_fields = array();
	if( $field['type'] == 'repeater' ) {
		
		// loop over sub fields
		if( !empty($field['sub_fields']) ) {
			foreach( $field['sub_fields'] as $sub_field ) {
				$sub_fields[] = $sub_field;
			}
		}
		
		// remove sub fields from field
		unset( $field['sub_fields'] );
	
	} elseif( $field['type'] == 'flexible_content' ) {
		
		// loop over layouts
		if( is_array($field['layouts']) ) {
			foreach( $field['layouts'] as $i => $layout ) {
				
				// generate key
				$layout['key'] = uniqid('layout_');
				
				// loop over sub fields
				if( !empty($layout['sub_fields']) ) {
					foreach( $layout['sub_fields'] as $sub_field ) {
						$sub_field['parent_layout'] = $layout['key'];
						$sub_fields[] = $sub_field;
					}
				}
				
				// remove sub fields from layout
				unset( $layout['sub_fields'] );
				
				// update
				$field['layouts'][ $i ] = $layout;
				
			}
		}
	}
	
	// save field
	$field = gcacf_update_field( $field );
	
	// log
	gcacf_dev_log('> Complete.', $field);
	
	// sub fields
	if( $sub_fields ) {
		foreach( $sub_fields as $sub_field ) {
			$sub_field['parent'] = $field['ID'];
			gcacf_upgrade_500_field($sub_field);
		}
	}
	
	// action for 3rd party
	do_action('gcacf/update_500_field', $field);
	
	// return
	return $field;
}

/**
*  gcacf_upgrade_550
*
*  Version 5.5 adds support for the wp_termmeta table added in WP 4.4.
*
*  @date	23/8/18
*  @since	5.7.4
*
*  @param	void
*  @return	void
*/
function gcacf_upgrade_550() {
	
	// log
	gcacf_dev_log('GCACF Upgrade 5.5.0.');
	
	// action
	do_action('gcacf/upgrade_550');
	
	// do tasks
	gcacf_upgrade_550_termmeta();
	
	// update version
	gcacf_update_db_version('5.5.0');
}

/**
*  gcacf_upgrade_550_termmeta
*
*  Upgrades all GCACF4 termmeta saved in wp_options to the wp_termmeta table.
*
*  @date	23/8/18
*  @since	5.7.4
*
*  @param	void
*  @return	void
*/
function gcacf_upgrade_550_termmeta() {
	
	// log
	gcacf_dev_log('GCACF Upgrade 5.5.0 Termmeta.');
	
	// bail early if no wp_termmeta table
	if( get_option('db_version') < 34370 ) {
		return;
	}
	
	// get all taxonomies
	$taxonomies = get_taxonomies(false, 'objects');
	
	// loop
	if( $taxonomies ) {
	foreach( $taxonomies as $taxonomy ) {
		gcacf_upgrade_550_taxonomy( $taxonomy->name );
	}}
	
	// action for 3rd party
	do_action('gcacf/upgrade_550_termmeta');
}

/*
*  gcacf_wp_upgrade_550_termmeta
*
*  When the database is updated to support term meta, migrate GCACF term meta data across.
*
*  @date	23/8/18
*  @since	5.7.4
*
*  @param	string $wp_db_version The new $wp_db_version.
*  @param	string $wp_current_db_version The old (current) $wp_db_version.
*  @return	void
*/
function gcacf_wp_upgrade_550_termmeta( $wp_db_version, $wp_current_db_version ) {
	if( $wp_db_version >= 34370 && $wp_current_db_version < 34370 ) {
		if( gcacf_version_compare(gcacf_get_db_version(), '>', '5.5.0') ) {
			gcacf_upgrade_550_termmeta();
		}				
	}
}
add_action( 'wp_upgrade', 'gcacf_wp_upgrade_550_termmeta', 10, 2 );

/**
*  gcacf_upgrade_550_taxonomy
*
*  Upgrades all GCACF4 termmeta for a specific taxonomy.
*
*  @date	24/8/18
*  @since	5.7.4
*
*  @param	string $taxonomy The taxonomy name.
*  @return	void
*/
function gcacf_upgrade_550_taxonomy( $taxonomy ) {
	
	// log
	gcacf_dev_log('GCACF Upgrade 5.5.0 Taxonomy.', $taxonomy);
	
	// global
	global $wpdb;
	
	// vars
	$search = $taxonomy . '_%';
	$_search = '_' . $search;
	
	// escape '_'
	// http://stackoverflow.com/questions/2300285/how-do-i-escape-in-sql-server
	$search = str_replace('_', '\_', $search);
	$_search = str_replace('_', '\_', $_search);
	
	// search
	// results show faster query times using 2 LIKE vs 2 wildcards
	$rows = $wpdb->get_results($wpdb->prepare(
		"SELECT * 
		FROM $wpdb->options 
		WHERE option_name LIKE %s 
		OR option_name LIKE %s",
		$search,
		$_search 
	), ARRAY_A);
	
	// loop
	if( $rows ) {
	foreach( $rows as $row ) {
		
		/*
		Use regex to find "(_)taxonomy_(term_id)_(field_name)" and populate $matches:
		Array
		(
		    [0] => _category_3_color
		    [1] => _
		    [2] => 3
		    [3] => color
		)
		*/
		if( !preg_match("/^(_?){$taxonomy}_(\d+)_(.+)/", $row['option_name'], $matches) ) {
			continue;
		}
		
		// vars
		$term_id = $matches[2];
		$meta_key = $matches[1] . $matches[3];
		$meta_value = $row['option_value'];
		
		// update
		// memory usage reduced by 50% by using a manual insert vs update_metadata() function. 
		//update_metadata( 'term', $term_id, $meta_name, $meta_value );
		$wpdb->insert( $wpdb->termmeta, array(
	        'term_id'		=> $term_id,
	        'meta_key'		=> $meta_key,
	        'meta_value'	=> $meta_value
	    ));
	    
	    // log
		gcacf_dev_log('GCACF Upgrade 5.5.0 Term.', $term_id, $meta_key);
		
		// action
		do_action('gcacf/upgrade_550_taxonomy_term', $term_id);
	}}
	
	// action for 3rd party
	do_action('gcacf/upgrade_550_taxonomy', $taxonomy);
}

?>