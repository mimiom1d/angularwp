<?php
// ** this is to fix notice error for undefined UTF8_ENABLED
define('UTF8_ENABLED', TRUE);

/**
 * 
 */
class gc_import_json_locations{
	private $wpdb;
	private $post_type;
	private $post_author;
	private $messages;
	private $unique_meta_key;
	private $cat_name;
	function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->post_type = 'atm-location';
        $this->post_author = 1;
        $this->unique_meta_key = 'id';
		$this->messages = [];
		$this->cat_name = "Bitcoin ATM location";
    }

	
	// **GCEdit: 
	// ** Reference: https://www.codexworld.com/generate-seo-friendly-url-from-title-string-php/
    function generateSeoURL($string, $wordLimit = 0){
	    $separator = '-';
	    
	    if($wordLimit != 0){
	        $wordArr = explode(' ', $string);
	        $string = implode(' ', array_slice($wordArr, 0, $wordLimit));
	    }

	    $quoteSeparator = preg_quote($separator, '#');

	    $trans = array(
	        '&.+?;'                    => '',
	        '[^\w\d _-]'            => '',
	        '\s+'                    => $separator,
	        '('.$quoteSeparator.')+'=> $separator
	    );

	    $string = strip_tags($string);
	    foreach ($trans as $key => $val){
	        $string = preg_replace('#'.$key.'#i'.(UTF8_ENABLED ? 'u' : ''), $val, $string);
	    }

	    $string = strtolower($string);

	    return trim(trim($string, $separator));
	}

	// function get_guid_meta_id($location_id){
    // 	$sql = "SELECT p.ID, p.guid  FROM ".$this->wpdb->prefix."posts";
	//         $results = $this->wpdb->get_row($sql);
	//         return $results;
	// }

	function check_exists_location($location_id){
		$sql = "SELECT p.*  FROM ".$this->wpdb->prefix."posts as p 
	        INNER JOIN ".$this->wpdb->prefix."postmeta as pm
	        ON p.ID = pm.post_id 
	        WHERE pm.meta_key = '".$this->unique_meta_key."' 
	        AND pm.meta_value = $location_id
	        AND p.post_type = '".$this->post_type."' 
	        GROUP BY p.ID ORDER BY p.post_date DESC";
	        $results = $this->wpdb->get_results($sql);
	        return $results;
	}

	// **GCEdit: to update/create category name upon this post automation.
	/**
	 * 
	 */
	function get_categoryId($cat_name){
		if(get_cat_ID($cat_name) === 0){
			wp_create_category($cat_name);
		}
		$category_id = get_cat_ID($cat_name);
		return $category_id;
	}

	function insert_location($location){
            
        $post_id;
        // ==========insert new item========

		// $post_title = get_html_h3tag($location->html); // **We dont need to get the html value bc we have title property now.
		$post_title =sanitize_text_field(trim($location->title));
		$post_title_seo = $this->generateSeoURL(sanitize_text_field(trim($location->title)));
		$state = $this->generateSeoURL(sanitize_text_field(trim($location->state)));
		$city = $this->generateSeoURL(sanitize_text_field(trim($location->city)));
		// $name = $this->generateSeoURL(sanitize_text_field(trim($location->name)));
		$rootDir = get_site_url();
		$category_id = $this->get_categoryId($this->cat_name);
		$posturl = $rootDir . "/bitcoin-atm-locations/" . $state . "/" . $city . "/" . $post_title_seo;

        $new_post_data = array(
        'post_title'    => $post_title,
        'post_content'  => '',
        'post_status'   => 'publish',
        'post_date'     => date('Y-m-d H:i:s'),
        'post_type'     => $this->post_type,
        'post_author'   => $this->post_author,
        'post_category' => array($category_id), // **GCEdit: Added category to be inserted as well
        'tags_input' => array(),
        'guid'   => $posturl,// **GCEdit: Added guid to be inserted as it is dynamically changed as well
        );
		$post_id = wp_insert_post($new_post_data); // **this function will return the id of newly created post
		// **if the wp_insert_post succeeded, also insert meta data as well
        if($post_id){
            //insert meta
            $this->insert_location_meta($post_id, $location);
            $this->messages['insert_locations'][$location->id] = $post_id; 

        }else{
            $this->messages['fail_locations'][$location->id] = 'failed'; 
        }

	}

	function update_location($post_id, $location){
		$this->update_location_meta($post_id, $location);
		$this->messages['update_locations'][$location->id] = $post_id; 
	}


	function insert_location_meta($post_id, $meta_data){
	    foreach($meta_data as $key=>$value){
	        $meta_key = trim($key);
	        if( $meta_key == 'html'){
				add_post_meta($post_id, $meta_key, trim($value), true);
	        	// update_post_meta($post_id, $meta_key, $value);
	        }elseif($meta_key == 'hours') {
				add_post_meta($post_id, $meta_key, serialize($value), true);
	        	// update_post_meta($post_id, $meta_key, serialize($value));
	        }elseif($meta_key == 'state') {
				$state = sanitize_text_field(trim($value));
				add_post_meta($post_id, $meta_key, $state, true);
				add_post_meta($post_id, 'state_name', $this->generateSeoURL($state), true);// **GCEdit: Added seo version as well
	        	// update_post_meta($post_id, $meta_key, sanitize_text_field($value));
	        	// update_post_meta($post_id, 'state_name', $this->generateSeoURL($value));
        	}elseif($meta_key == 'city') {
				$city = sanitize_text_field(trim($value));
				add_post_meta($post_id, $meta_key, $city, true);
				add_post_meta($post_id, 'city_name', $this->generateSeoURL($city), true);// **GCEdit: Added seo version as well
				// update_post_meta($post_id, $meta_key, sanitize_text_field($value));
        		// update_post_meta($post_id, 'city_name', $this->generateSeoURL($value));
	        }elseif($meta_key == 'title') { // **GCEdit: Added title to have _seo version as well
				$title = sanitize_text_field(trim($value));
				add_post_meta($post_id, $meta_key, $title, true);
        		add_post_meta($post_id, 'title_seo', $this->generateSeoURL($title), true);
	        }else{
				add_post_meta($post_id, $meta_key, sanitize_text_field($value), true);
	        	// update_post_meta($post_id, $meta_key, sanitize_text_field($value));
			}
			// ** here we automatically add the template to this post.
			add_post_meta( $post_id, '_wp_page_template', 'inc/atm-location-template.php', true);
	    }
	    
	}
	// // ** To explicitely update the modified time, this function will do by contacting wpdb
	// // **However, just running wp_update_post() with 'post_modified' and 'post_modified_gmt'did the work, 
	// // ** so now commented
	// function update_modified_date($post_id){
	// 	//eg. time one year ago..
	// 	// $time = time() - DAY_IN_SECONDS * 365;
	// 	$time = time();
	// 	$mysql_time_format= "Y-m-d H:i:s";
	// 	$post_modified = gmdate( $mysql_time_format, $time );
	// 	$post_modified_gmt = gmdate( $mysql_time_format, ( $time + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS )  );
	// 	$wpdb->query("UPDATE $wpdb->posts SET post_modified = '{$post_modified}', post_modified_gmt = '{$post_modified_gmt}'  WHERE ID = {$post_id}" );
	// }

	// ** For testing of update_location_meta not updating the meta correctly, 
	// ** tried to directly update the data into the database, but soon realized that
	// ** the bug was just a mistake of trashing the wrong post and looking at the old post as to compare..
	function update_in_database($post_id, $meta_key, $sanitized_value){
		$this->wpdb->query("UPDATE {$this->wpdb->prefix}postmeta SET meta_key = '{$meta_key}', meta_value = '{$sanitized_value}'  WHERE post_id = {$post_id}" );
	}

	function update_location_meta($post_id, $meta_data){

		$state = '';
		$city = '';
		$title = '';
		$post_title_seo = '';
		$guid = '';
	    foreach($meta_data as $key=>$value){
	        $meta_key = trim($key);
	        if( $meta_key == 'html'){ // ** html includes special tags, so cannot sanitize, hence this is included in this if condition and not at the bottom or else clause.
	        	update_post_meta($post_id, $meta_key, $value);
	        }elseif($meta_key == 'hours') {
	        	update_post_meta($post_id, $meta_key, serialize($value)); // ** the value here is array, so serialize
	        }elseif($meta_key == 'state') {
				$state =  sanitize_text_field($value);
	        	update_post_meta($post_id, $meta_key, $state);
	        	update_post_meta($post_id, 'state_name', $this->generateSeoURL($state));
        	}elseif($meta_key == 'city') {
				$city =  sanitize_text_field($value);
				update_post_meta($post_id, $meta_key, $city);
        		update_post_meta($post_id, 'city_name', $this->generateSeoURL($city));
	        }elseif($meta_key == 'title') {
				$title =  sanitize_text_field($value);
				$post_title_seo = $this->generateSeoURL($title);
				update_post_meta($post_id, $meta_key, $title);
				update_post_meta($post_id, $meta_key, $post_title_seo);
	        }else{
				$sanitized_value = sanitize_text_field($value);
				update_post_meta($post_id, $meta_key, $sanitized_value);
	        }
		}
		// **GCEdit: Added the auto functionality to change the title/time reflected from the json data.

		update_post_meta($post_id, '_wp_page_template','inc/atm-location-template.php');

		$time = current_time('mysql');
		$category_id = $this->get_categoryId($this->cat_name);
		$rootDir = get_site_url();
		$posturl = $rootDir . "/bitcoin-atm-locations/" . $state . "/" . $city . "/" . $post_title_seo;
		echo $posturl;
		$updated = wp_update_post(
			array (
				'ID' => $post_id, // **NOTE: MUST INCLUDE THIS! Without this ID, no way to find the association.
				'post_title'   => $title,
				'post_modified' => $time,
				'post_modified_gmt' => get_gmt_from_date( $time ),
				'post_category' => array($category_id),
				// 'guid'   => $posturl // ** THis didnt work though wp_update_post will go through without errors. Hence, you need to directly manipuate the value using wpdb method as below. 
			)
		);
		// **this part is to emit the error for debugging
		// 	, true
		// );                        
		// if (is_wp_error($post_id)) {
		// 	$errors = $post_id->get_error_messages();
		// 	foreach ($errors as $error) {
		// 		echo $error;
		// 	}
		// }

		
		/*
		// ** DIRECTLY/FORCEFULLY UPDATE GUID VALUE
		// ** Added the auto functionality to change the guid collected from the json data.
		*/

		// (Replacement Update Example: "UPDATE wp_posts SET guid = REPLACE(guid, 'oldurl.com', 'newurl.com') WHERE guid LIKE 'http://oldurl.com/%'";)
		
		// $guid_change = "UPDATE ".$wpdb->prefix."posts SET guid = '".$posturl ."' WHERE ID = '".$post_id ."'";
		$guid_changed = $this->wpdb->query( $this->wpdb->prepare( 
			"
			UPDATE {$this->wpdb->prefix}posts 
			SET guid = %s
			WHERE ID = %d
				AND post_type = %s
			",
				$posturl, $post_id, $this->post_type 
		) );

		// ** TO update explicitely the modified date dynamically, we call wpdb insert
		// update_modified_date($post_id);

		// **if the wpdb update succeeded, logout the message
        if($guid_changed){
            $this->messages['guid_inserted'][$post_id] = $posturl; 

        }else{
            $this->messages['guid_insert_failed'][$post_id] = 'guid value or other updates were not successful; failed'; 
        }
	    
	}

	function import_locations(){

		$results  = get_locations_data();
		$locations = $results->locations;
		

		foreach($locations as $location){
			$post_id;
            // ==========if item already exists update action will perform========

            $location_details = $this->check_exists_location($location->id);
			
            if(count($location_details) > 0 ){
            	$post_id = $location_details[0]->ID;
            	$this->update_location($post_id, $location);

            }else{
            	$this->insert_location($location);
            }

		}
		echo '<code><pre>';
		print_r($this->messages);
		echo '</pre></code>';

		return $this->messages;

	}

}

