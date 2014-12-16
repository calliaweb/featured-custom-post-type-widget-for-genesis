function tax_term_postback( select_id, post_type ) {
  var data = {
    action: 'tax_term_action',
	post_type: post_type
  };
  jQuery.post(ajax_object.ajax_url, data, function(response) {
	// Decode the data received.
	var terms = jQuery.parseJSON(response);

	// Keep track of what was previously selected
	var select_ctrl = jQuery('#' + select_id);
	var old_term = select_ctrl.val();

	// Clear out the old options, build up the new
	select_ctrl.empty();
	jQuery.each(terms, function(key, value) {
		var new_option = jQuery('<option></option>')
		    .attr('value', key).text(value);
		if (value == old_term) {
			new_option.attr('selected', true);
		}
		select_ctrl.append(new_option);
	});
  });
}
