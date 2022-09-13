jQuery( document ).ready( function($) {

	$( '#wcap_print, #wcap_csv' ).click( function( e ) {

		e.preventDefault();

		let submitButton = $(this);
		let id           = $(this).prop('id');
		let type         = id.split( '_' );
		type             = type[1];

		$('.wcap-view-abandoned-orders-msg').html( '<div id="wcap_myProgress"><div id="wcap_myBar" data-added="0">0%</div></div>' );

		if ( ! submitButton.hasClass( 'button-disabled' ) ) {
			$('.wcap-export').addClass('button-disabled');
			// start the process
			process_step( 1, type );
		}
	});

	function process_step( step, csv_print ) {
		let total_items = $('.top .displaying-num').text();
		total_items = total_items.split(' ');
		total_items = total_items[0];
		total_items = parseInt( total_items.replace(/,/g, '') );

		let done_items = $('#wcap_myBar').data( 'added' );

		let filter_status = $( '#cart_status' ).val();
		let start_date    = $( '#hidden_start' ).val();
		let end_date      = $( '#hidden_end' ).val();

		const wcapUrlParams = new URLSearchParams( window.location.search );
		let wcap_section    = wcapUrlParams.has( 'wcap_section' ) ? wcapUrlParams.get( 'wcap_section' ) : '';
		
		var data = {
			filter_status,
			start_date,
			end_date,
			wcap_section,
			total_items,
			done_items,
			csv_print,
			action: 'wcap_data_export',
			step: step,
		};

		$.post( ajaxurl, data, function( response ) {
		}).done( function( response ) {
			if ( 'done' == response.step || response.error || response.success ) {
				// We need to get the actual in progress form, not all forms on the page
				$('.wcap-export').removeClass('button-disabled');

				if ( response.error ) {
					var error_message = response.message;
					$('.wcap-view-abandoned-orders-msg').html('<div class="updated error"><p>' + error_message + '</p></div>')

				} else {
					$('#wcap_myBar').animate({
						width: '100%',
					}, 50, function() {
						$('.wcap-view-abandoned-orders-msg').html( '' );
					});
					if ( 'print' == csv_print ) {
						printCarts();
						$( '.wcap-view-cart-data' ).html( '' )
					} else {
						window.location = response.url;
					}
				}

			} else {
				if ( 'print' == csv_print ) {
					let content = response.html_data;

					if ( step == 1 ) {
						$( '.wcap-view-cart-data' ).html( content );
					} else {
						$('#wcap_print_data').append( content );
					}
				 }
				$('#wcap_myBar').data( 'added', response.added );
				$('#wcap_myBar').animate({
					width: response.percentage + '%',
				}, 50, function() {
					// Animation complete.
				});
		
				$('#wcap_myBar').text( response.percentage + '%' );
				console.log( response.step );
				process_step( parseInt( response.step ), csv_print );
			}
		}).fail( function (response) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});		
	}

	function printCarts() {
		let printThis = document.getElementById('wcap_print_data').outerHTML;  
		let win = window.open();
		win.document.open();
		win.document.write('<'+'html'+'><head><title>Print Abandoned Carts</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><'+'body'+'>');
		win.document.write( printThis );
		win.document.write('<'+'/body'+'><'+'/html'+'>');
		win.document.close();
		win.print();
	}
});
