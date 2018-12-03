<?php

/*
*  GCACF Admin Field Groups Class
*
*  All the logic for editing a list of field groups
*
*  @class 		gcacf_admin_field_groups
*  @package		GCACF
*  @subpackage	Admin
*/

if( ! class_exists('gcacf_admin_field_groups') ) :

class gcacf_admin_field_groups {
	
	// vars
	var $url = 'edit.php?post_type=gcacf-field-group',
		$sync = array();
		
	
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
	
		// actions
		add_action('current_screen',		array($this, 'current_screen'));
		add_action('trashed_post',			array($this, 'trashed_post'));
		add_action('untrashed_post',		array($this, 'untrashed_post'));
		add_action('deleted_post',			array($this, 'deleted_post'));
		add_action('load-edit.php',			array($this, 'maybe_redirect_edit'));
	}
	
	/**
	*  maybe_redirect_edit
	*
	*  Redirects the user from the old GCACF4 edit page to the new GCACF5 edit page
	*
	*  @date	17/9/18
	*  @since	5.7.6
	*
	*  @param	void
	*  @return	void
	*/
	function maybe_redirect_edit() {
		if( gcacf_maybe_get_GET('post_type') == 'gcacf' ) {
			wp_redirect( admin_url($this->url) );
			exit;
		}
	}
	
	/*
	*  current_screen
	*
	*  This function is fired when loading the admin page before HTML has been rendered.
	*
	*  @type	action (current_screen)
	*  @date	21/07/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function current_screen() {
		
		// validate screen
		if( !gcacf_is_screen('edit-gcacf-field-group') ) {
			return;
		}
		

		// customize post_status
		global $wp_post_statuses;
		
		
		// modify publish post status
		$wp_post_statuses['publish']->label_count = _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'gcacf' );
		
		
		// reorder trash to end
		$wp_post_statuses['trash'] = gcacf_extract_var( $wp_post_statuses, 'trash' );

		
		// check stuff
		$this->check_duplicate();
		$this->check_sync();
		
		
		// actions
		add_action('admin_enqueue_scripts',							array($this, 'admin_enqueue_scripts'));
		add_action('admin_footer',									array($this, 'admin_footer'));
		
		
		// columns
		add_filter('manage_edit-gcacf-field-group_columns',			array($this, 'field_group_columns'), 10, 1);
		add_action('manage_gcacf-field-group_posts_custom_column',	array($this, 'field_group_columns_html'), 10, 2);
		
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
		
		wp_enqueue_script('gcacf-input');
		
	}
	
	
	/*
	*  check_duplicate
	*
	*  This function will check for any $_GET data to duplicate
	*
	*  @type	function
	*  @date	17/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function check_duplicate() {
		
		// message
		if( $ids = gcacf_maybe_get_GET('gcacfduplicatecomplete') ) {
			
			// explode
			$ids = explode(',', $ids);
			$total = count($ids);
			
			if( $total == 1 ) {
				
				gcacf_add_admin_notice( sprintf(__('Field group duplicated. %s', 'gcacf'), '<a href="' . get_edit_post_link($ids[0]) . '">' . get_the_title($ids[0]) . '</a>') );
				
			} else {
				
				gcacf_add_admin_notice( sprintf(_n( '%s field group duplicated.', '%s field groups duplicated.', $total, 'gcacf' ), $total) );
				
			}
			
		}
		
		
		// vars
		$ids = array();
		
		
		// check single
		if( $id = gcacf_maybe_get_GET('gcacfduplicate') ) {
			
			$ids[] = $id;
		
		// check multiple
		} elseif( gcacf_maybe_get_GET('action2') === 'gcacfduplicate' ) {
			
			$ids = gcacf_maybe_get_GET('post');
			
		}
		
		
		// sync
		if( !empty($ids) ) {
			
			// validate
			check_admin_referer('bulk-posts');
			
			
			// vars
			$new_ids = array();
			
			
			// loop
			foreach( $ids as $id ) {
				
				// duplicate
				$field_group = gcacf_duplicate_field_group( $id );
				
				
				// increase counter
				$new_ids[] = $field_group['ID'];
				
			}
			
			
			// redirect
			wp_redirect( admin_url( $this->url . '&gcacfduplicatecomplete=' . implode(',', $new_ids)) );
			exit;
				
		}
		
	}
	
	
	/*
	*  check_sync
	*
	*  This function will check for any $_GET data to sync
	*
	*  @type	function
	*  @date	9/12/2014
	*  @since	5.1.5
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function check_sync() {
		
		// message
		if( $ids = gcacf_maybe_get_GET('gcacfsynccomplete') ) {
			
			// explode
			$ids = explode(',', $ids);
			$total = count($ids);
			
			if( $total == 1 ) {
				
				gcacf_add_admin_notice( sprintf(__('Field group synchronised. %s', 'gcacf'), '<a href="' . get_edit_post_link($ids[0]) . '">' . get_the_title($ids[0]) . '</a>') );
				
			} else {
				
				gcacf_add_admin_notice( sprintf(_n( '%s field group synchronised.', '%s field groups synchronised.', $total, 'gcacf' ), $total) );
				
			}
			
		}
		
		
		// vars
		$groups = gcacf_get_field_groups();
		
		
		// bail early if no field groups
		if( empty($groups) ) return;
		
		
		// find JSON field groups which have not yet been imported
		foreach( $groups as $group ) {
			
			// vars
			$local = gcacf_maybe_get($group, 'local', false);
			$modified = gcacf_maybe_get($group, 'modified', 0);
			$private = gcacf_maybe_get($group, 'private', false);
			
			
			// ignore DB / PHP / private field groups
			if( $local !== 'json' || $private ) {
				
				// do nothing
				
			} elseif( !$group['ID'] ) {
				
				$this->sync[ $group['key'] ] = $group;
				
			} elseif( $modified && $modified > get_post_modified_time('U', true, $group['ID'], true) ) {
				
				$this->sync[ $group['key'] ]  = $group;
				
			}
						
		}
		
		
		// bail if no sync needed
		if( empty($this->sync) ) return;
		
		
		// maybe sync
		$sync_keys = array();
		
		
		// check single
		if( $key = gcacf_maybe_get_GET('gcacfsync') ) {
			
			$sync_keys[] = $key;
		
		// check multiple
		} elseif( gcacf_maybe_get_GET('action2') === 'gcacfsync' ) {
			
			$sync_keys = gcacf_maybe_get_GET('post');
			
		}
		
		
		// sync
		if( !empty($sync_keys) ) {
			
			// validate
			check_admin_referer('bulk-posts');
			
			
			// disable filters to ensure GCACF loads raw data from DB
			gcacf_disable_filters();
			gcacf_enable_filter('local');
			
			
			// disable JSON
			// - this prevents a new JSON file being created and causing a 'change' to theme files - solves git anoyance
			gcacf_update_setting('json', false);
			
			
			// vars
			$new_ids = array();
				
			
			// loop
			foreach( $sync_keys as $key ) {
				
				// append fields
				if( gcacf_have_local_fields($key) ) {
					
					$this->sync[ $key ]['fields'] = gcacf_get_local_fields( $key );
					
				}
				
				
				// import
				$field_group = gcacf_import_field_group( $this->sync[ $key ] );
									
				
				// append
				$new_ids[] = $field_group['ID'];
				
			}
			
			
			// redirect
			wp_redirect( admin_url( $this->url . '&gcacfsynccomplete=' . implode(',', $new_ids)) );
			exit;
			
		}
		
		
		// filters
		add_filter('views_edit-gcacf-field-group', array($this, 'list_table_views'));
		
	}
	
	
	/*
	*  list_table_views
	*
	*  This function will add an extra link for JSON in the field group list table
	*
	*  @type	function
	*  @date	3/12/2014
	*  @since	5.1.5
	*
	*  @param	$views (array)
	*  @return	$views
	*/
	
	function list_table_views( $views ) {
		
		// vars
		$class = '';
		$total = count($this->sync);
		
		// active
		if( gcacf_maybe_get_GET('post_status') === 'sync' ) {
			
			// actions
			add_action('admin_footer', array($this, 'sync_admin_footer'), 5);
			
			
			// set active class
			$class = ' class="current"';
			
			
			// global
			global $wp_list_table;
			
			
			// update pagination
			$wp_list_table->set_pagination_args( array(
				'total_items' => $total,
				'total_pages' => 1,
				'per_page' => $total
			));
			
		}
		
		
		// add view
		$views['json'] = '<a' . $class . ' href="' . admin_url($this->url . '&post_status=sync') . '">' . __('Sync available', 'gcacf') . ' <span class="count">(' . $total . ')</span></a>';
		
		
		// return
		return $views;
		
	}
	
	
	/*
	*  trashed_post
	*
	*  This function is run when a post object is sent to the trash
	*
	*  @type	action (trashed_post)
	*  @date	8/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	n/a
	*/
	
	function trashed_post( $post_id ) {
		
		// validate post type
		if( get_post_type($post_id) != 'gcacf-field-group' ) {
		
			return;
		
		}
		
		
		// trash field group
		gcacf_trash_field_group( $post_id );
		
	}
	
	
	/*
	*  untrashed_post
	*
	*  This function is run when a post object is restored from the trash
	*
	*  @type	action (untrashed_post)
	*  @date	8/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	n/a
	*/
	
	function untrashed_post( $post_id ) {
		
		// validate post type
		if( get_post_type($post_id) != 'gcacf-field-group' ) {
		
			return;
			
		}
		
		
		// trash field group
		gcacf_untrash_field_group( $post_id );
		
	}
	
	
	/*
	*  deleted_post
	*
	*  This function is run when a post object is deleted from the trash
	*
	*  @type	action (deleted_post)
	*  @date	8/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	n/a
	*/
	
	function deleted_post( $post_id ) {
		
		// validate post type
		if( get_post_type($post_id) != 'gcacf-field-group' ) {
		
			return;
			
		}
		
		
		// trash field group
		gcacf_delete_field_group( $post_id );
		
	}
	
	
	/*
	*  field_group_columns
	*
	*  This function will customize the columns for the field group table
	*
	*  @type	filter (manage_edit-gcacf-field-group_columns)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	$columns (array)
	*  @return	$columns (array)
	*/
	
	function field_group_columns( $columns ) {
		
		return array(
			'cb'	 				=> '<input type="checkbox" />',
			'title' 				=> __('Title', 'gcacf'),
			'gcacf-fg-description'	=> __('Description', 'gcacf'),
			'gcacf-fg-status' 		=> '<i class="gcacf-icon -dot-3 small gcacf-js-tooltip" title="' . esc_attr__('Status', 'gcacf') . '"></i>',
			'gcacf-fg-count' 			=> __('Fields', 'gcacf'),
		);
		
	}
	
	
	/*
	*  field_group_columns_html
	*
	*  This function will render the HTML for each table cell
	*
	*  @type	action (manage_gcacf-field-group_posts_custom_column)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	$column (string)
	*  @param	$post_id (int)
	*  @return	n/a
	*/
	
	function field_group_columns_html( $column, $post_id ) {
		
		// vars
		$field_group = gcacf_get_field_group( $post_id );
		
		
		// render
		$this->render_column( $column, $field_group );
	    
	}
	
	function render_column( $column, $field_group ) {
		
		// description
		if( $column == 'gcacf-fg-description' ) {
			
			if( $field_group['description'] ) {
				
				echo '<span class="gcacf-description">' . gcacf_esc_html($field_group['description']) . '</span>';
				
			}
        
        // status
	    } elseif( $column == 'gcacf-fg-status' ) {
			
			if( isset($this->sync[ $field_group['key'] ]) ) {
				
				echo '<i class="gcacf-icon -sync grey small gcacf-js-tooltip" title="' . esc_attr__('Sync available', 'gcacf') .'"></i> ';
				
			}
			
			if( $field_group['active'] ) {
				
				//echo '<i class="gcacf-icon -check small gcacf-js-tooltip" title="' . esc_attr__('Active', 'gcacf') .'"></i> ';
				
			} else {
				
				echo '<i class="gcacf-icon -minus yellow small gcacf-js-tooltip" title="' . esc_attr__('Inactive', 'gcacf') . '"></i> ';
				
			}
	    
        // fields
	    } elseif( $column == 'gcacf-fg-count' ) {
			
			echo esc_html( gcacf_get_field_count( $field_group ) );
        
        }
		
	}
	
	
	/*
	*  admin_footer
	*
	*  This function will render extra HTML onto the page
	*
	*  @type	action (admin_footer)
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_footer() {
		
		// vars
		// $url_home = 'https://www.advancedcustomfields.com'; // **GCEdit: Original author's url
		// $url_support = 'https://support.advancedcustomfields.com';// **GCEdit: Original author's url
		$url_home = 'https://www.getcoins.com';
		$url_support = 'https://www.getcoins.com';
		$icon = '<i aria-hidden="true" class="dashicons dashicons-external"></i>';
		
?>
<script type="text/html" id="tmpl-gcacf-column-2">
<div class="gcacf-column-2">
	<div class="gcacf-box">
		<div class="inner">
			<h2><?php echo gcacf_get_setting('name'); ?></h2>
			<p><?php _e('Customise WordPress with powerful, professional and intuitive fields.','gcacf'); ?></p>
			
			<h3><?php _e("Changelog",'gcacf'); ?></h3>
			<p><?php 
			
			$gcacf_changelog = admin_url('edit.php?post_type=gcacf-field-group&page=gcacf-settings-info&tab=changelog');
			$gcacf_version = gcacf_get_setting('version');
			printf( __('See what\'s new in <a href="%s">version %s</a>.','gcacf'), esc_url($gcacf_changelog), $gcacf_version );
			
			?></p>
			<h3><?php _e("Resources",'gcacf'); ?></h3>
			<ul>
				<li><a href="<?php echo esc_url( $url_home ); ?>" target="_blank"><?php echo $icon; ?> <?php _e("Website",'gcacf'); ?></a></li>
				<!-- // **GCEdit: TEMPORAL COMMENTATION. 
				<li><a href="<?php echo esc_url( $url_home . '/resources/' ); ?>" target="_blank"><?php echo $icon; ?> <?php _e("Documentation",'gcacf'); ?></a></li>
				<li><a href="<?php echo esc_url( $url_support ); ?>" target="_blank"><?php echo $icon; ?> <?php _e("Support",'gcacf'); ?></a></li>
				<?php if( !gcacf_get_setting('pro') ): ?>
				<li><a href="<?php echo esc_url( $url_home . '/pro/' ); ?>" target="_blank"><?php echo $icon; ?> <?php _e("Pro",'gcacf'); ?></a></li> -->
				<?php endif; ?>
			</ul>
		</div>
		<div class="footer">
			<p><?php printf( __('Thank you for creating with <a href="%s">GCACF</a>.','gcacf'), esc_url($url_home) ); ?></p>
		</div>
	</div>
</div>
</script>
<script type="text/javascript">
(function($){
	
	// wrap
	$('#wpbody .wrap').attr('id', 'gcacf-field-group-wrap');
	
	
	// wrap form
	$('#posts-filter').wrap('<div class="gcacf-columns-2" />');
	
	
	// add column main
	$('#posts-filter').addClass('gcacf-column-1');
	
	
	// add column side
	$('#posts-filter').after( $('#tmpl-gcacf-column-2').html() );
	
	
	// modify row actions
	$('#the-list tr').each(function(){
		
		// vars
		var $tr = $(this),
			id = $tr.attr('id'),
			description = $tr.find('.column-gcacf-fg-description').html();
		
		
		// replace Quick Edit with Duplicate (sync page has no id attribute)
		if( id ) {
			
			// vars
			var post_id	= id.replace('post-', '');
			var url = '<?php echo esc_url( admin_url( $this->url . '&gcacfduplicate=__post_id__&_wpnonce=' . wp_create_nonce('bulk-posts') ) ); ?>';
			var $span = $('<span class="gcacf-duplicate-field-group"><a title="<?php _e('Duplicate this item', 'gcacf'); ?>" href="' + url.replace('__post_id__', post_id) + '"><?php _e('Duplicate', 'gcacf'); ?></a> | </span>');
			
			
			// replace
			$tr.find('.column-title .row-actions .inline').replaceWith( $span );
			
		}
		
		
		// add description to title
		$tr.find('.column-title .row-title').after( description );
		
	});
	
	
	// modify bulk actions
	$('#bulk-action-selector-bottom option[value="edit"]').attr('value','gcacfduplicate').text('<?php _e( 'Duplicate', 'gcacf' ); ?>');
	
	
	// clean up table
	$('#adv-settings label[for="gcacf-fg-description-hide"]').remove();
	
	
	// mobile compatibility
	var status = $('.gcacf-icon.-dot-3').first().attr('title');
	$('td.column-gcacf-fg-status').attr('data-colname', status);
	
	
	// no field groups found
	$('#the-list tr.no-items td').attr('colspan', 4);
	
	
	// search
	$('.subsubsub').append(' | <li><a href="#" class="gcacf-toggle-search"><?php _e('Search', 'gcacf'); ?></a></li>');
	
	
	// events
	$(document).on('click', '.gcacf-toggle-search', function( e ){
		
		// prevent default
		e.preventDefault();
		
		
		// toggle
		$('.search-box').slideToggle();
		
	});
	
})(jQuery);
</script>
<?php
		
	}
	
	
	/*
	*  sync_admin_footer
	*
	*  This function will render extra HTML onto the page
	*
	*  @type	action (admin_footer)
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function sync_admin_footer() {
		
		// vars
		$i = -1;
		$columns = array(
			'gcacf-fg-description',
			'gcacf-fg-status',
			'gcacf-fg-count'
		);
		$nonce = wp_create_nonce('bulk-posts');
		
?>
<script type="text/html" id="tmpl-gcacf-json-tbody">
<?php foreach( $this->sync as $field_group ): 
	
	// vars
	$i++; 
	$key = $field_group['key'];
	$title = $field_group['title'];
	$url = admin_url( $this->url . '&post_status=sync&gcacfsync=' . $key . '&_wpnonce=' . $nonce );
	
	?>
	<tr <?php if($i%2 == 0): ?>class="alternate"<?php endif; ?>>
		<th class="check-column" scope="row">
			<label for="cb-select-<?php echo esc_attr($key); ?>" class="screen-reader-text"><?php echo esc_html(sprintf(__('Select %s', 'gcacf'), $title)); ?></label>
			<input type="checkbox" value="<?php echo esc_attr($key); ?>" name="post[]" id="cb-select-<?php echo esc_attr($key); ?>">
		</th>
		<td class="post-title page-title column-title">
			<strong>
				<span class="row-title"><?php echo esc_html($title); ?></span><span class="gcacf-description"><?php echo esc_html($key); ?>.json</span>
			</strong>
			<div class="row-actions">
				<span class="import"><a title="<?php echo esc_attr( __('Synchronise field group', 'gcacf') ); ?>" href="<?php echo esc_url($url); ?>"><?php _e( 'Sync', 'gcacf' ); ?></a></span>
			</div>
		</td>
		<?php foreach( $columns as $column ): ?>
			<td class="column-<?php echo esc_attr($column); ?>"><?php $this->render_column( $column, $field_group ); ?></td>
		<?php endforeach; ?>
	</tr>
<?php endforeach; ?>
</script>
<script type="text/html" id="tmpl-gcacf-bulk-actions">
	<?php // source: bulk_actions() wp-admin/includes/class-wp-list-table.php ?>
	<select name="action2" id="bulk-action-selector-bottom"></select>
	<?php submit_button( __( 'Apply' ), 'action', '', false, array( 'id' => "doaction2" ) ); ?>
</script>
<script type="text/javascript">
(function($){
	
	// update table HTML
	$('#the-list').html( $('#tmpl-gcacf-json-tbody').html() );
	
	
	// bulk may not exist if no field groups in DB
	if( !$('#bulk-action-selector-bottom').exists() ) {
		
		$('.tablenav.bottom .actions.alignleft').html( $('#tmpl-gcacf-bulk-actions').html() );
		
	}
	
	
	// set only options
	$('#bulk-action-selector-bottom').html('<option value="-1"><?php _e('Bulk Actions'); ?></option><option value="gcacfsync"><?php _e('Sync', 'gcacf'); ?></option>');
		
})(jQuery);
</script>
<?php
		
	}
			
}

new gcacf_admin_field_groups();

endif;

?>