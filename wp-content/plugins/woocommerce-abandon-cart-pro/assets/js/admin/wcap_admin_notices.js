jQuery(document).ready(function() {

	jQuery( '#wcap_atc_rule_notice' ).on( 'click', '.notice-dismiss', function() {
		var data = {
			notice: 'wcap_atc_rule_notice_dismiss',
			action: "wcap_dismiss_admin_notice"
		};

		var admin_url = wcap_dismiss_params.ajax_url;
			jQuery.post( admin_url, data, function( response ) {
		});

	});
});