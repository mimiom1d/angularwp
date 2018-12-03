<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('gcacf_form_front') ) :

class gcacf_form_front {
	
	/** @var array An array of registered form settings */
	private $forms = array();
	
	/** @var array An array of default fields */
	public $fields = array();
	
	
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
		
		// vars
		$this->fields = array(
						
			'_post_title' => array(
				'prefix'	=> 'gcacf',
				'name'		=> '_post_title',
				'key'		=> '_post_title',
				'label'		=> __('Title', 'gcacf'),
				'type'		=> 'text',
				'required'	=> true,
			),
			
			'_post_content' => array(
				'prefix'	=> 'gcacf',
				'name'		=> '_post_content',
				'key'		=> '_post_content',
				'label'		=> __('Content', 'gcacf'),
				'type'		=> 'wysiwyg',
			),
			
			'_validate_email' => array(
				'prefix'	=> 'gcacf',
				'name'		=> '_validate_email',
				'key'		=> '_validate_email',
				'label'		=> __('Validate Email', 'gcacf'),
				'type'		=> 'text',
				'value'		=> '',
				'wrapper'	=> array('style' => 'display:none !important;')
			)
			
		);
		
		
		// actions
		add_action('gcacf/validate_save_post', array($this, 'validate_save_post'), 1);
		
		
		// filters
		add_filter('gcacf/pre_save_post', array($this, 'pre_save_post'), 5, 2);
		
	}
	
	
	/*
	*  validate_form
	*
	*  description
	*
	*  @type	function
	*  @date	28/2/17
	*  @since	5.5.8
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function validate_form( $args ) {
		
		// defaults
		$args = wp_parse_args( $args, array(
			'id'					=> 'gcacf-form',
			'post_id'				=> false,
			'new_post'				=> false,
			'field_groups'			=> false,
			'fields'				=> false,
			'post_title'			=> false,
			'post_content'			=> false,
			'form'					=> true,
			'form_attributes'		=> array(),
			'return'				=> add_query_arg( 'updated', 'true', gcacf_get_current_url() ),
			'html_before_fields'	=> '',
			'html_after_fields'		=> '',
			'submit_value'			=> __("Update", 'gcacf'),
			'updated_message'		=> __("Post updated", 'gcacf'),
			'label_placement'		=> 'top',
			'instruction_placement'	=> 'label',
			'field_el'				=> 'div',
			'uploader'				=> 'wp',
			'honeypot'				=> true,
			'html_updated_message'	=> '<div id="message" class="updated"><p>%s</p></div>', // 5.5.10
			'html_submit_button'	=> '<input type="submit" class="gcacf-button button button-primary button-large" value="%s" />', // 5.5.10
			'html_submit_spinner'	=> '<span class="gcacf-spinner"></span>', // 5.5.10
			'kses'					=> true // 5.6.5
		));
		
		$args['form_attributes'] = wp_parse_args( $args['form_attributes'], array(
			'id'					=> $args['id'],
			'class'					=> 'gcacf-form',
			'action'				=> '',
			'method'				=> 'post',
		));
		
		
		// filter post_id
		$args['post_id'] = gcacf_get_valid_post_id( $args['post_id'] );
		
		
		// new post?
		if( $args['post_id'] === 'new_post' ) {
			
			$args['new_post'] = wp_parse_args( $args['new_post'], array(
				'post_type' 	=> 'post',
				'post_status'	=> 'draft',
			));
			
		}
		
		
		// filter
		$args = apply_filters('gcacf/validate_form', $args);
		
		
		// return
		return $args;
		
	}
	
	
	/*
	*  add_form
	*
	*  description
	*
	*  @type	function
	*  @date	28/2/17
	*  @since	5.5.8
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function add_form( $args = array() ) {
		
		// validate
		$args = $this->validate_form( $args );
		
		
		// append
		$this->forms[ $args['id'] ] = $args;
		
	}
	
	
	/*
	*  get_form
	*
	*  description
	*
	*  @type	function
	*  @date	28/2/17
	*  @since	5.5.8
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function get_form( $id = '' ) {
		
		// bail early if not set
		if( !isset($this->forms[ $id ]) ) return false;
		
		
		// return
		return $this->forms[ $id ];
		
	}
	
	
	/*
	*  validate_save_post
	*
	*  This function will validate fields from the above array
	*
	*  @type	function
	*  @date	7/09/2016
	*  @since	5.4.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function validate_save_post() {
		
		// register field if isset in $_POST
		foreach( $this->fields as $k => $field ) {
			
			// bail early if no in $_POST
			if( !isset($_POST['gcacf'][ $k ]) ) continue;
			
			
			// register
			gcacf_add_local_field($field);
			
		}
		
		
		// honeypot
		if( !empty($_POST['gcacf']['_validate_email']) ) {
			
			gcacf_add_validation_error( '', __('Spam Detected', 'gcacf') );
			
		}
		
	}
	
	
	/*
	*  pre_save_post
	*
	*  description
	*
	*  @type	function
	*  @date	7/09/2016
	*  @since	5.4.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function pre_save_post( $post_id, $form ) {
		
		// vars
		$save = array(
			'ID' => 0
		);
		
		
		// determine save data
		if( is_numeric($post_id) ) {
			
			// update post
			$save['ID'] = $post_id;
			
		} elseif( $post_id == 'new_post' ) {
			
			// merge in new post data
			$save = array_merge($save, $form['new_post']);
					
		} else {
			
			// not post
			return $post_id;
			
		}
		
		
		// save post_title
		if( isset($_POST['gcacf']['_post_title']) ) {
			
			$save['post_title'] = gcacf_extract_var($_POST['gcacf'], '_post_title');
		
		}
		
		
		// save post_content
		if( isset($_POST['gcacf']['_post_content']) ) {
			
			$save['post_content'] = gcacf_extract_var($_POST['gcacf'], '_post_content');
			
		}
		
		
		// honeypot
		if( !empty($_POST['gcacf']['_validate_email']) ) return false;
		
		
		// validate
		if( count($save) == 1 ) {
			
			return $post_id;
			
		}
		
		
		// save
		if( $save['ID'] ) {
			
			wp_update_post( $save );
			
		} else {
			
			$post_id = wp_insert_post( $save );
			
		}
			
		
		// return
		return $post_id;
		
	}
	
	
	/*
	*  enqueue
	*
	*  This function will enqueue a form
	*
	*  @type	function
	*  @date	7/09/2016
	*  @since	5.4.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function enqueue_form() {
		
		// check
		$this->check_submit_form();
		
		
		// load gcacf scripts
		gcacf_enqueue_scripts();
		
	}
	
	
	/*
	*  check_submit_form
	*
	*  This function will maybe submit form data
	*
	*  @type	function
	*  @date	3/3/17
	*  @since	5.5.10
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function check_submit_form() {
		
		// verify nonce
		if( !gcacf_verify_nonce('gcacf_form') ) return;
		
		
		// bail ealry if form not submit
		if( empty($_POST['_gcacf_form']) ) return;
		
		
		// load form
    	$form = json_decode( gcacf_decrypt($_POST['_gcacf_form']), true );
		
		
		// bail ealry if form is corrupt
    	if( empty($form) ) return;
    	
    	
    	// kses
    	if( $form['kses'] && isset($_POST['gcacf']) ) {
	    	$_POST['gcacf'] = wp_kses_post_deep( $_POST['gcacf'] );
    	}
    	
		
		// validate data
		gcacf_validate_save_post(true);
		
		
		// submit
		$this->submit_form( $form );
		
	}
	
	
	/*
	*  submit_form
	*
	*  This function will submit form data
	*
	*  @type	function
	*  @date	3/3/17
	*  @since	5.5.10
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function submit_form( $form ) {
		
		// filter
    	$form = apply_filters('gcacf/pre_submit_form', $form);
    	
    	
    	// vars
    	$post_id = gcacf_maybe_get($form, 'post_id', 0);
		
		
		// add global for backwards compatibility
		$GLOBALS['gcacf_form'] = $form;
		
		
		// allow for custom save
		$post_id = apply_filters('gcacf/pre_save_post', $post_id, $form);
		
		
		// save
		gcacf_save_post( $post_id );
		
		
		// restore form (potentially modified)
		$form = $GLOBALS['gcacf_form'];
		
		
		// action
		do_action('gcacf/submit_form', $form, $post_id);
		
		
		// vars
		$return = gcacf_maybe_get($form, 'return', '');
		
		
		// redirect
		if( $return ) {
			
			// update %placeholders%
			$return = str_replace('%post_id%', $post_id, $return);
			$return = str_replace('%post_url%', get_permalink($post_id), $return);
			
			
			// redirect
			wp_redirect( $return );
			exit;
			
		}
		
	}
	
	
	/*
	*  render
	*
	*  description
	*
	*  @type	function
	*  @date	7/09/2016
	*  @since	5.4.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function render_form( $args = array() ) {
		
		// array
		if( is_array($args) ) {
			
			$args = $this->validate_form( $args );
			
		// id
		} else {
			
			$args = $this->get_form( $args );
			
		}
		
		
		// bail early if no args
		if( !$args ) return false;
		
		
		// load values from this post
		$post_id = $args['post_id'];
		
		
		// dont load values for 'new_post'
		if( $post_id === 'new_post' ) $post_id = false;
		
		
		// register local fields
		foreach( $this->fields as $k => $field ) {
			
			gcacf_add_local_field($field);
			
		}
		
		
		// vars
		$field_groups = array();
		$fields = array();
		
		
		// post_title
		if( $args['post_title'] ) {
			
			// load local field
			$_post_title = gcacf_get_field('_post_title');
			$_post_title['value'] = $post_id ? get_post_field('post_title', $post_id) : '';
			
			
			// append
			$fields[] = $_post_title;
			
		}
		
		
		// post_content
		if( $args['post_content'] ) {
			
			// load local field
			$_post_content = gcacf_get_field('_post_content');
			$_post_content['value'] = $post_id ? get_post_field('post_content', $post_id) : '';
			
			
			// append
			$fields[] = $_post_content;
					
		}
		
		
		// specific fields
		if( $args['fields'] ) {
			
			foreach( $args['fields'] as $selector ) {
				
				// append field ($strict = false to allow for better compatibility with field names)
				$fields[] = gcacf_maybe_get_field( $selector, $post_id, false );
				
			}
			
		} elseif( $args['field_groups'] ) {
			
			foreach( $args['field_groups'] as $selector ) {
			
				$field_groups[] = gcacf_get_field_group( $selector );
				
			}
			
		} elseif( $args['post_id'] == 'new_post' ) {
			
			$field_groups = gcacf_get_field_groups( $args['new_post'] );
		
		} else {
			
			$field_groups = gcacf_get_field_groups(array(
				'post_id' => $args['post_id']
			));
			
		}
		
		
		//load fields based on field groups
		if( !empty($field_groups) ) {
			
			foreach( $field_groups as $field_group ) {
				
				$field_group_fields = gcacf_get_fields( $field_group );
				
				if( !empty($field_group_fields) ) {
					
					foreach( array_keys($field_group_fields) as $i ) {
						
						$fields[] = gcacf_extract_var($field_group_fields, $i);
					}
					
				}
			
			}
		
		}
		
		
		// honeypot
		if( $args['honeypot'] ) {
			
			$fields[] = gcacf_get_field('_validate_email');
			
		}
		
		
		// updated message
		if( !empty($_GET['updated']) && $args['updated_message'] ) {
			
			printf( $args['html_updated_message'], $args['updated_message'] );
			
		}
		
		
		// uploader (always set incase of multiple forms on the page)
		gcacf_update_setting('uploader', $args['uploader']);
		
		
		// display form
		if( $args['form'] ): ?>
		
		<form <?php gcacf_esc_attr_e( $args['form_attributes']); ?>>
			
		<?php endif; 
			
		// render post data
		gcacf_form_data(array( 
			'screen'	=> 'gcacf_form',
			'post_id'	=> $args['post_id'],
			'form'		=> gcacf_encrypt(json_encode($args))
		));
		
		?>
		
		<div class="gcacf-fields gcacf-form-fields -<?php echo $args['label_placement']; ?>">
			<?php
				
			
			// html before fields
			echo $args['html_before_fields'];
			
			
			// render
			gcacf_render_fields( $fields, $post_id, $args['field_el'], $args['instruction_placement'] );
			
			
			// html after fields
			echo $args['html_after_fields'];
			
			
			?>
		</div>
		
		<?php if( $args['form'] ): ?>
		
		<div class="gcacf-form-submit">
			
			<?php printf( $args['html_submit_button'], $args['submit_value'] ); ?>
			<?php echo $args['html_submit_spinner']; ?>
			
		</div>
		
		</form>
		<?php endif;
		
	}
	
}

// initialize
gcacf()->form_front = new gcacf_form_front();

endif; // class_exists check


/*
*  Functions
*
*  alias of gcacf()->form->functions
*
*  @type	function
*  @date	11/06/2014
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/


function gcacf_form_head() {
	
	gcacf()->form_front->enqueue_form();
	
}

function gcacf_form( $args = array() ) {
	
	gcacf()->form_front->render_form( $args );
	
}

function gcacf_get_form( $id = '' ) {
	
	gcacf()->form_front->get_form( $id );
	
}

function gcacf_register_form( $args ) {
	
	gcacf()->form_front->add_form( $args );
	
}

?>