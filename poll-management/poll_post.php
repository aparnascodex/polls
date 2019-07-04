<?php
if (!defined('ABSPATH')) die('No direct access allowed');

/*
* Class: Poll
* Description : Contains everything related to custom post type Poll (cst_poll) 
*/
class Poll {

	public function __construct() {
		add_action( 'init', array($this,'define_custom_post_poll'));
		add_action('add_meta_boxes', array($this,'polls_answer_metabox'));
		add_action('save_post', array($this,'save_options_and_date'), 10, 2);
		add_action('wp_ajax_check_date_availibility', array($this, 'check_date_availibility'));
		add_filter( 'manage_cst_poll_posts_columns',  array($this, 'set_schedule_date_columns'));
		add_action( 'manage_cst_poll_posts_custom_column' , array($this, 'display_schedule_date_columns'), 10, 2 );

	}

	//Define custom post type
	public function define_custom_post_poll() {
 
	    $labels = array(
	        'name'                => _x( 'Polls',  'poll' ),
	        'singular_name'       => _x( 'Poll',  'poll' ),
	        'menu_name'           => __( 'Polls', 'poll' ),
	        'parent_item_colon'   => __( 'Parent Poll', 'poll' ),
	        'all_items'           => __( 'All Polls', 'poll' ),
	        'view_item'           => __( 'View Pole', 'poll' ),
	        'add_new_item'        => __( 'Add New Poll', 'poll' ),
	        'add_new'             => __( 'Add New', 'poll' ),
	        'edit_item'           => __( 'Edit Poll', 'poll' ),
	        'update_item'         => __( 'Update Poll', 'poll' ),
	        'search_items'        => __( 'Search Poll', 'poll' ),
	        'not_found'           => __( 'Not Found', 'poll' ),
	        'not_found_in_trash'  => __( 'Not found in Trash', 'poll' ),
	    );
	     
	     
	    $args = array(
	        'label'               => __( 'Polls', 'poll' ),
	        'description'         => __( 'Daily Polls', 'poll' ),
	        'labels'              => $labels,      
	        'supports'            => array( 'title', 'author'),
	        'hierarchical'        => false,
	        'public'              => true,
	        'show_ui'             => true,
	        'show_in_menu'        => true,
	        'show_in_nav_menus'   => true,
	        'show_in_admin_bar'   => true,
	        'menu_position'       => 5,
	        'can_export'          => true,
	        'has_archive'         => true,
	        'exclude_from_search' => false,
	        'publicly_queryable'  => true,
	        'capability_type'     => 'page',
	    );
	     
	    register_post_type( 'cst_poll', $args );
 	}

 	//Save meta data of Poll
	public function save_options_and_date($pid, $post) {
		global $wpdb;
		$tbl = $wpdb->prefix.'poll_options';
		$poll_date = isset($_POST['poll_date'])? $_POST['poll_date']:'';
		$opt1 = isset($_POST['opt_one'])? $_POST['opt_one']:'';
		$opt2 = isset($_POST['opt_two'])? $_POST['opt_two']:'';
	    
	    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || $post->post_status == 'auto-draft') 
	    	return $pid;
	   
	    if ( $post->post_type != 'cst_poll' ) return $pid;

	    update_post_meta($pid, 'poll_date', $poll_date);
	    update_post_meta($pid, 'opt_1', $opt1);
	    update_post_meta($pid, 'opt_2', $opt2);

	    $sql = "SELECT id FROM $tbl WHERE poll_id = '$pid'";
	    $id = $wpdb->get_var($sql);
	    if($id != '')
	    {
	    	$wpdb->update($tbl,
	    					array('option_1' => $opt1,
	    						 	'option_2' => $opt2),
	    					array('poll_id' => $pid),
	    					array ('%s', '%s'),
	    					array('%d'));

	    }
	    else
	    {
	    	$wpdb->insert($tbl,
	    					array(
	    						'poll_id' => $pid,
	    						'option_1' => $opt1,
	    						 'option_2' => $opt2),	    					
	    					array ('%d', '%s', '%s')
	    					);
	    }
	}

	//Ajax call for checking date availability. If one poll is already scheduled on the same date then throws error
	public function check_date_availibility()
	{
		global $wpdb;
		check_ajax_referer('validate_req', 'sec_nonce');
		$dt = $_POST['date'];
		$pid = $_POST['pid'];
		$query = "SELECT b.meta_value FROM $wpdb->posts a JOIN $wpdb->postmeta b
					ON a.id = b.post_id 
					WHERE a.post_status='publish' AND a.id != $pid AND b.meta_key='poll_date'";
		$dates = $wpdb->get_col($query);
		if(in_array($dt, $dates))
			echo 1;
		else
			echo 0;
		die();
	}
	//Meta boxes for adding options and poll date
 	public function polls_answer_metabox() {
		   add_meta_box(
		       'anwsers_metabox',       
		       'Options',                 
		       array($this,'create_options_metabox'),  
		       'cst_poll',                 
		       'normal',                  
		       'high'                     
		   );
		   add_meta_box(
		       'poll_dt_metabox',       
		       'Polling Date',                 
		       array($this,'create_date_metabox'),  
		       'cst_poll',                 
		       'normal',                  
		       'high'                     
		   );
	}
	public function create_options_metabox($post) {
		$pid  = $post->ID;
		
	    $opt1 = get_post_meta($pid, 'opt_1', true);
	    $opt2 = get_post_meta($pid, 'opt_2', true);
		echo "<div class='container'>
				<div class='opt'>
					<label> Option 1 : </label>
					<input type='text' class='option' name='opt_one' value='$opt1'>
				</div>
				<div class='opt'>
					<label> Option 2 : </label>
					<input type='text' class='option' name='opt_two' value='$opt2' >
				</div>
			</div>";

	}
	public function create_date_metabox($post) {
		$pid  = $post->ID;
		$date = get_post_meta($pid, 'poll_date', true);

		wp_enqueue_style('admin-css', plugins_url('assets/css/calendar.css',__FILE__));
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('admin-js', plugins_url('assets/js/admin-js.js',__FILE__), array('jquery'));
		wp_localize_script('admin-js', 'data_var' , array('ajax_url' => admin_url('admin-ajax.php'),
														'valid_nonce' => wp_create_nonce('validate_req'),
														'post_id' => $pid));
		echo "<div class='container'>
				<div class='dt'>
					<label> Select Date : </label>
					<input type='text' class='poll-date' name='poll_date' value='$date'>
				</div>
			</div>";
	}
	public function set_schedule_date_columns($columns) {
 
    	$columns['poll_date'] = __( 'Scheduled Date', 'poll' );
       	return $columns;
	}
	
	public function display_schedule_date_columns( $column, $post_id ) {
    	switch ( $column ) {

        case 'poll_date' :
            $date = get_post_meta( $post_id , 'poll_date' ,true);
           	echo $date;
            break;

        }
	}

}
new Poll();
