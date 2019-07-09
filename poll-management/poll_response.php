<?php
if (!defined('ABSPATH')) die('No direct access allowed');

/*
* Class: Poll_Response
* Description : Contains everything related to poll response
*/
class Poll_Response {

	public function __construct() {
		
		add_action('wp_ajax_insert_response', array($this, 'insert_response'));
		add_action('wp_ajax_nopriv_insert_response', array($this, 'insert_response'));

	}

	public function insert_response()
	{
		check_ajax_referer('widget_req', 'sec_nonce');
		global $wpdb;
		$poll_response = $wpdb->prefix.'poll_response';

		$pid = $_POST['pid'];
		$response = $_POST['response'];
		$ip_address = get_user_ip_address();
		
		$date = date('Y-m-d');
		
		$wpdb->insert($poll_response,
						array('poll_id' => $pid,
							'response' => $response,
							'user_ip' => $ip_address,
							'response_dt' =>$date),
						array('%d','%s','%s','%s'));
		echo $wpdb->insert_id;
		
		die();
	}
	
	
}
new Poll_Response();
