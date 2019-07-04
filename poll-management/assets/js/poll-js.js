jQuery(document).ready(function() {

	jQuery('.btn-vote').click(function() {

		console.log(widget_var.post_id);
		if(jQuery('.poll-radio').is(':checked')) { 
			response = jQuery('.poll-radio:checked').val();
			jQuery.ajax({
				url : widget_var.ajax_url,
				type: 'POST',
				data : { action : 'insert_response',
						sec_nonce: widget_var.widget_nonce,
						response : response,
						pid : widget_var.post_id },
				success:function(msg) {
					console.log(msg);
					if(msg > 0) {
						alert('Your vote is captured');
						location.reload();
						
					}
					
				},
				error: function(err) {
					console.log(err);
				}
			})
		}
		else
		{
			alert('Please select one of the available options');
		}
	})
})