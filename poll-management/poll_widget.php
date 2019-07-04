<?php
if (!defined('ABSPATH')) die('No direct access allowed');

/*
* Class: poll_widget
* Description : Widget definition 
*/
class poll_widget extends WP_Widget {
 
	function __construct() {
		
		parent::__construct(	 
			'poll_widget', 
	 		__("Today's Poll", 'poll'), 
	 		array( 'description' => __("Displays today's poll", 'poll' )) 
		);
	}
	 
	 
	public function widget( $args, $instance ) {

		global $wpdb;
		$poll_options = $wpdb->prefix.'poll_options';
		$poll_response = $wpdb->prefix.'poll_response';

		wp_enqueue_style('style-css', plugins_url('assets/css/style.css',__FILE__));
		wp_enqueue_script('poll-js', plugins_url('assets/js/poll-js.js',__FILE__), array('jquery'));
		
		$today = date('Y-m-d');

		$query = "SELECT ID, post_title FROM $wpdb->posts a JOIN $wpdb->postmeta b
				ON a.id = b.post_id AND b.meta_key='poll_date' 
				WHERE b.meta_value = '$today'";
		$poll = $wpdb->get_row($query);

		$title = apply_filters( 'widget_title', $instance['title'] );
		 	
		echo $args['before_widget'];

		if (!empty($title))
			echo $args['before_title'] . $title . $args['after_title'];
		 
		if($poll == '') {
			echo __("No poll scheduled for the day.",'poll');
		}
		else {
			$question =  $poll->post_title;
			$id = $poll->ID;
			$ip_address = get_user_ip_address();

			$response_id = $wpdb->get_var("SELECT response FROM $poll_response WHERE user_ip = '$ip_address'");

			$total_opt1  = $wpdb->get_var("SELECT COUNT(id) FROM $poll_response WHERE response = '1'");
			$total_opt2  = $wpdb->get_var("SELECT COUNT(id) FROM $poll_response WHERE response = '2'");

			wp_localize_script('poll-js', 'widget_var', array('ajax_url' => admin_url('admin-ajax.php'),
														'widget_nonce' => wp_create_nonce('widget_req'),
														'post_id' => $id));

			$options = $wpdb->get_row("SELECT option_1, option_2 FROM $poll_options WHERE poll_id = '$id'");
			$opt1 = $options->option_1;
			$opt2 = $options->option_2;
			$sel_1 = $sel_2 = '';
			if($response_id == 2)
				$sel_2 ='checked';
			elseif($response_id == 1)
				$sel_1 ='checked';
			$first_per = $second_per ='';
			if($response_id != '')
			{
				$total = $total_opt1 + $total_opt2;
				$first_per ='('. ($total_opt1 / $total) * 100 .'%)';
				$second_per = '('. ($total_opt2 / $total) * 100 .'%)';
			}
			echo "<div class='polling-widget'>
					<div class='question'>
						{$question}
					</div>
					<div class='avail-options'>
						<span> 
							<input type ='radio' class= 'poll-radio' id='poll_one' name='poll_ans' value= '1' {$sel_1}> 
							<label for='poll_one' > {$opt1} </label> {$first_per}
						</span>
						<span> 
							<input type = 'radio' class= 'poll-radio' id='poll_two' name='poll_ans' value= '2' {$sel_2}> 
							<label for='poll_two' > {$opt2} </label> {$second_per}
						</span>
					</div>";
			if($response_id == '')
				echo "<div class='btn-dv'>
						<input type='button' value= 'Vote' class= 'btn-vote'>
					</div>";

			echo "</div>";
		}

		
		echo $args['after_widget'];
	}
	         
	
	public function form( $instance ) {

		if (isset( $instance['title'])) {	
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'poll' );
		}
		// Widget admin form
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}
	     
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
} 