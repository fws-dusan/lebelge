/**
 * Common JS
 */
window.addEventListener( 'load', function() {
	// Dismiss buttons.
	const dismissBtns = document.querySelectorAll( '.wfcm-admin-notice .notice-dismiss' );

	// Add Exclude Item.
	[ ...dismissBtns ].forEach( dismissBtn => {
		dismissBtn.addEventListener( 'click', wfcmDismissAdminNotice );
	});

	// Dismiss buttons.
	const excludeLargeFileLinks = document.querySelectorAll( 'a[href="#wfcm_exclude_large_file"]' );

	// Add Exclude Item.
	[ ...excludeLargeFileLinks ].forEach( excludeLargeFileLink => {
		excludeLargeFileLink.addEventListener( 'click', wfcmExcludeLargeFile );
	});
});

/**
 * Send dismiss request to remove admin notice.
 *
 * @param {Event} e Event object.
 */
 function wfcmDismissAdminNotice( e ) {
 	const noticeKey = e.target.parentNode.id.substring( 18 ); // Get notice key from id of the notice.
 	// Rest request object.
 	const request = new Request( `${wfcmData.restAdminEndpoint}/${noticeKey}`, {
 		method: 'GET',
 		headers: {
 			'X-WP-Nonce': wfcmData.restNonce
 		}
 	});

 	// Send the request.
 	fetch( request )
 		.then( response => response.json() )
 		.then( data => {
 			if ( data.success ) {
 				document.getElementById( `wfcm-admin-notice-${noticeKey}` ).style.display = 'none';
 			}
 		})
 		.catch( error => {
 			console.log( error );
 		});
 }

 function wfcmExcludeLargeFile( e ) {
	 e.preventDefault();
	 var itemToExlcude       = jQuery(this).parent( 'li' ).find( 'strong' ).text();
	 var itemToExlcudeParent = jQuery(this).parent( 'li' );
	 var nonceValue          = jQuery(this).attr( 'data-nonce' );
	 jQuery.ajax(
		 {
			 url: wfcmData.adminAjax,
			 method: "POST",
			 data: {
				 action: 'wfcm_exclude_file_from_notice',
				 file: itemToExlcude,
				 _wpnonce: nonceValue
			 },
			 success: function( data ) {
				 jQuery(itemToExlcudeParent).fadeOut();
      }
		 }
	 );
 }
