jQuery(document).ready(function(){
	jQuery('.poll-date').datepicker({
		 minDate: +1,
		 dateFormat: 'yy-mm-dd'
	});

	jQuery('#post').submit(function(){
		err = '';
		jQuery('.option').each(function(){
			if(jQuery(this).val() == '')
				err = 1;
		});

		if(err == 1 || jQuery('.poll-date').val() == '')
		{
			alert('Please enter values for both the options and polling date');
			return false;
		}
	});

	jQuery('.poll-date').change(function(){
		dt = jQuery(this).val();
		jQuery.ajax({
			url : data_var.ajax_url,
			type: 'POST',
			data : { action : 'check_date_availibility',
					sec_nonce: data_var.valid_nonce,
					date : dt,
					pid : data_var.post_id },
			success:function(msg) {
				console.log(msg);
				if(msg == 1) {
					alert('There already one Poll scheduled on the same date. Please try different date.');
					jQuery('.poll-date').val('');
				}
				
			},
			error: function(err) {
				console.log(err);
			}
		})
	})
})