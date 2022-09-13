jQuery(function( $ ) {

	/* Recovered Orders */
	$( '#duration_select' ).change( function() {
        var group_name  = $( '#duration_select' ).val();
        var today       = new Date();
        var start_date  = "";
        var end_date    = "";

        if ( group_name == "yesterday" ) {
            start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 1 );
            end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 1 );
        } else if ( group_name == "today" ) {
            start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
            end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
        } else if ( group_name == "last_seven" ) {
            start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 7 );
            end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
        } else if ( group_name == "last_fifteen" ) {
            start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 15 );
            end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
        } else if ( group_name == "last_thirty" ) {
            start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 30 );
            end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
        } else if ( group_name == "last_ninety" ) {
            start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 90 );
            end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
        } else if ( group_name == "last_year_days" ) {
            start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 365 );
            end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
        }
		// Populate the Hidden Dates first.
		wcap_populate_hidden_dates( start_date, end_date );
        var monthNames       = [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];
        var start_date_value = start_date.getDate() + " " + monthNames[ start_date.getMonth() ] + " " + start_date.getFullYear();
        var end_date_value   = end_date.getDate() + " " + monthNames[ end_date.getMonth() ] + " " + end_date.getFullYear();
        $( '#start_date' ) . val( start_date_value );
        $( '#end_date' )   . val( end_date_value );
    } );

	function wcap_populate_hidden_dates( start_date, end_date ) {
		if ( $( '#hidden_start' ).length > 0 ) {
			let hidden_start = start_date.getDate() + '-' + ( start_date.getMonth() + 1 ) + '-' + start_date.getFullYear();
			$( '#hidden_start' ).val( hidden_start );
		}
		if ( $( '#hidden_end' ).length > 0 ) {
			let hidden_end   = end_date.getDate() + '-' + ( end_date.getMonth() + 1 ) + '-' + end_date.getFullYear();
			$( '#hidden_end' ).val( hidden_end );
		}
	}

	function wcap_populate_hidden_start( date, inst ) {
		if ( $( '#hidden_start' ).length > 0 ) {
			var monthValue 	= inst.selectedMonth+1;
			var dayValue 	= inst.selectedDay;
			var yearValue 	= inst.selectedYear;

			var current_dt 	= dayValue + "-" + monthValue + "-" + yearValue;
			$( '#hidden_start').val( current_dt );
		}
	}

	function wcap_populate_hidden_end( date, inst ) {
		if ( $( '#hidden_end' ).length > 0 ) {
			var monthValue 	= inst.selectedMonth+1;
			var dayValue 	= inst.selectedDay;
			var yearValue 	= inst.selectedYear;

			var current_dt 	= dayValue + "-" + monthValue + "-" + yearValue;
			$( '#hidden_end').val( current_dt );
		}
	}

	/* Sent Emails */
    $( '#duration_select_email' ).change( function() {
	    var group_name = $( '#duration_select_email' ) . val();
	    var today      = new Date();
	    var start_date = "";
	    var end_date   = "";

	    if ( group_name == "yesterday" ) {
	        start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 1 );
	        end_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 1 );
	    } else if ( group_name == "today" ) {
	        start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
	        end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
	    } else if ( group_name == "last_seven" ) {
	        start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 7 );
	        end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
	    } else if ( group_name == "last_fifteen" ) {
	        start_date = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 15 );
	        end_date   = new Date(today.getFullYear(), today.getMonth(), today.getDate() );
	    } else if ( group_name == "last_thirty") {
	        start_date = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 30 );
	        end_date   = new Date(today.getFullYear(), today.getMonth(), today.getDate() );
	    } else if ( group_name == "last_ninety" ) {
	        start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 90 );
	        end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
	    } else if ( group_name == "last_year_days" ) {
	        start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 365 );
	        end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
	    }
		// Populate the Hidden Dates first.
		wcap_populate_hidden_dates( start_date, end_date );
	    var monthNames       = [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];
	    var start_date_value = start_date.getDate() + " " + monthNames[start_date.getMonth()] + " " + start_date.getFullYear();
	    var end_date_value   = end_date.getDate() + " " + monthNames[end_date.getMonth()] + " " + end_date.getFullYear();
	    $( '#start_date_email' ).val( start_date_value );
	    $( '#end_date_email' ).val( end_date_value );
	} );

	var formats = [ "d.m.y", "d M yy", "MM d, yy" ];
	$.datepicker.setDefaults( $.datepicker.regional[ "en-GB" ] );

	/* Recover orders tab fields */
	$( "#start_date" ).datepicker( { 
		dateFormat: formats[1],
		onSelect: wcap_populate_hidden_start
	} );
	$( "#end_date" ).datepicker( { 
		dateFormat: formats[1],
		onSelect: wcap_populate_hidden_end
	} );
	
	/* Sent emails tab fields */
	$( "#start_date_email" ).datepicker( {
        dateFormat: formats[1],
		onSelect: wcap_populate_hidden_start
    } );

	$( "#end_date_email" ).datepicker( {
    	dateFormat: formats[1],
		onSelect: wcap_populate_hidden_end
    } );
	
	/* Sent SMS */
    $( '#duration_select_sms' ).change( function() {

	    var group_name = $( '#duration_select_sms' ).val();
	    var today      = new Date();
	    var start_date = "";
	    var end_date   = "";

	    if ( group_name == "yesterday" ) {
	        start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 1 );
	        end_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 1 );
	    } else if ( group_name == "today" ) {
	        start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
	        end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
	    } else if ( group_name == "last_seven" ) {
	        start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 7 );
	        end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
	    } else if ( group_name == "last_fifteen" ) {
	        start_date = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 15 );
	        end_date   = new Date(today.getFullYear(), today.getMonth(), today.getDate() );
	    } else if ( group_name == "last_thirty") {
	        start_date = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 30 );
	        end_date   = new Date(today.getFullYear(), today.getMonth(), today.getDate() );
	    } else if ( group_name == "last_ninety" ) {
	        start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 90 );
	        end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
	    } else if ( group_name == "last_year_days" ) {
	        start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 365 );
	        end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
	    }
		// Populate the Hidden Dates first.
		wcap_populate_hidden_dates( start_date, end_date );
	    var monthNames       = [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];
	    var start_date_value = start_date.getDate() + " " + monthNames[start_date.getMonth()] + " " + start_date.getFullYear();
	    var end_date_value   = end_date.getDate() + " " + monthNames[end_date.getMonth()] + " " + end_date.getFullYear();
	    $( '#start_date_sms' ).val( start_date_value );
	    $( '#end_date_sms' ).val( end_date_value );
	} );

	var formats = [ "d.m.y", "d M yy", "MM d, yy" ];
	$.datepicker.setDefaults( $.datepicker.regional[ "en-GB" ] );
	
	/* Sent SMS tab fields */
	$( "#start_date_sms" ).datepicker( {
        dateFormat: formats[1],
		onSelect: wcap_populate_hidden_start
    } );

	$( "#end_date_sms" ).datepicker( {
    	dateFormat: formats[1],
		onSelect: wcap_populate_hidden_end
    } );

	/* Email Templates */
    $( '#duration_select_cart_recovery' ).change( function() {

	    var group_name = $( '#duration_select_cart_recovery' ).val();
	    var today      = new Date();
	    var start_date = "";
	    var end_date   = "";

	    if ( group_name == "yesterday" ) {
	        start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 1 );
	        end_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 1 );
	    } else if ( group_name == "today" ) {
	        start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
	        end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
	    } else if ( group_name == "last_seven" ) {
	        start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 7 );
	        end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
	    } else if ( group_name == "last_fifteen" ) {
	        start_date = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 15 );
	        end_date   = new Date(today.getFullYear(), today.getMonth(), today.getDate() );
	    } else if ( group_name == "last_thirty") {
	        start_date = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 30 );
	        end_date   = new Date(today.getFullYear(), today.getMonth(), today.getDate() );
	    } else if ( group_name == "last_ninety" ) {
	        start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 90 );
	        end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
	    } else if ( group_name == "last_year_days" ) {
	        start_date = new Date( today.getFullYear(), today.getMonth(), today.getDate() - 365 );
	        end_date   = new Date( today.getFullYear(), today.getMonth(), today.getDate() );
	    }
		// Populate the Hidden Dates first.
		wcap_populate_hidden_dates( start_date, end_date );
	    var monthNames       = [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];
	    var start_date_value = start_date.getDate() + " " + monthNames[start_date.getMonth()] + " " + start_date.getFullYear();
	    var end_date_value   = end_date.getDate() + " " + monthNames[end_date.getMonth()] + " " + end_date.getFullYear();
	    
	    $( '#start_date_cart_recovery' ).val( start_date_value );
	    $( '#end_date_cart_recovery' ).val( end_date_value );
	} );

	var formats = [ "d.m.y", "d M yy", "MM d, yy" ];
	$.datepicker.setDefaults( $.datepicker.regional[ "en-GB" ] );

	/* Email Templates tab fields */
	$( "#start_date_cart_recovery" ).datepicker( {
        dateFormat: formats[1],
		onSelect: wcap_populate_hidden_start
    } );

	$( "#end_date_cart_recovery" ).datepicker( {
    	dateFormat: formats[1],
		onSelect: wcap_populate_hidden_end
    } );
});