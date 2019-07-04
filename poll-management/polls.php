<?php
/*
* Plugin Name: Poll management
* Plugin URI: https://github.com/aparnascodex/polls
* Description: This plugin adds a Daily Poll feature to any WordPress website.
* Author: Aparna Gawade
* Author URI: http://aparnascodex.com
* Version: 1.0
* Text Domain: poll
*/

define('POLL_DIR', dirname(__FILE__));

if (is_file(POLL_DIR.'/poll_post.php')) require_once(POLL_DIR.'/poll_post.php');

/*
* Hook : register_activation_hook
* Description : Creates tables to save poll option and poll response in database
*/
register_activation_hook(__FILE__, 'create_required_tables');
function create_required_tables()
{

    global $wpdb;
    $poll_options = $wpdb->prefix.'poll_options';
    $poll_response = $wpdb->prefix.'poll_response';

    
    if ($wpdb->get_var("SHOW tables LIKE '$poll_response'") != $poll_response) {
        $sql = "CREATE TABLE " . $poll_response . " (
	        id int NOT NULL AUTO_INCREMENT,
	        poll_id int,
	        response varchar(500),
	        user_ip varchar(15),
	        response_dt varchar(25),
	        Primary Key (id)
	        );";
 
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    if ($wpdb->get_var("SHOW tables LIKE '$poll_options'") != $poll_options) {
        $sql = "CREATE TABLE " . $poll_options . " (
	        id int NOT NULL AUTO_INCREMENT,
	        poll_id int,
	        option_1 varchar(500),
	        option_2 varchar(500),
	        Primary Key (id)
	        );";
 
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
}




