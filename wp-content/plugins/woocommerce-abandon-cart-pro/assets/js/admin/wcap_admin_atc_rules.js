jQuery(document).ready(function() {
    // On page load.
    var coupon_type = jQuery('#wcap_atc_coupon_type').val();
    if ( 'unique' == coupon_type ) {
        jQuery('.wcap_atc_pre_selected').hide();
        jQuery('.wcap_atc_unique').show();
    } else if ( 'pre-selected' == coupon_type ) {
        jQuery('.wcap_atc_unique').hide();
        jQuery('.wcap_atc_pre_selected').show();
    }
    // On Value change.
    jQuery('#wcap_atc_coupon_type').on( 'change', function() {
		var coupon_type = jQuery('#wcap_atc_coupon_type').val();
		if ( 'unique' == coupon_type ) {
			jQuery('.wcap_atc_pre_selected').hide();
			jQuery('.wcap_atc_unique').show();
		} else if ( 'pre-selected' == coupon_type ) {
			jQuery('.wcap_atc_unique').hide();
			jQuery('.wcap_atc_pre_selected').show();
		}
	});
});

function wcap_add_new_rule_row( id ) {
	document.getElementById('wcap-rule-list-header').style.display = "table-header-group";

    // Remove the Add new icon from the existing row.
    var id = document.getElementById(id);
    id.remove();

    var tableRef = document.getElementsByClassName('wcap-rule-list');
    var table_length = tableRef[0].tBodies[0].rows.length + 1;

    // Store the new row in a variable.
    var data_row = tableRef[0].getAttribute( 'data-row' );
    // Add the row in the table.
    var tableBody = document.getElementById('wcap-rule-list-body');
    var newRow = tableBody.insertRow();
    newRow.id = table_length;
    
    // Edit the id & names to add the row_id.
    var rule_type = 'wcap_rule_type_' + table_length;
    var rule_cond = 'wcap_rule_condition_' + table_length;
    var rule_value = 'wcap_rule_value_' + table_length;
    var delete_id = 'wcap_rule_delete_' + table_length;

    data_row = data_row.replace( /'wcap_rule_type_'/g, rule_type );
    data_row = data_row.replace( /'wcap_rule_condition_'/g, rule_cond );
    data_row = data_row.replace( /'wcap_rule_value_'/g, rule_value );
    data_row = data_row.replace( /'wcap_rule_delete_'/g, delete_id );
    data_row = data_row.replace( /'<td>'/g, '' );
    
    var cells = data_row.split('</td>');
    // Add new cell.
    var newCell0 = newRow.insertCell(0);
    // Insert the HTML.
    newCell0.innerHTML = cells[0];

    // Add new cell.
    var newCell1 = newRow.insertCell(1);
    // Insert the HTML.
    newCell1.innerHTML = cells[1];

    // Add new cell.
    var newCell2 = newRow.insertCell(2);
    // Insert the HTML.
    newCell2.innerHTML = cells[2];

    // Add new cell.
    var newCell3 = newRow.insertCell(3);
    // Insert the HTML.
    newCell3.innerHTML = cells[3];
    newCell0.style.width = '25%';
    newCell1.style.width = '23%';
    newCell2.style.width = '30%';
}

function wcap_rule_values( object_id ) {

    var rule_type = document.getElementById( object_id ).value;
    var id = object_id.substr(-1);
    var select_id = 'wcap_rule_value_' + id;
    var select_box = document.getElementById( select_id );

    var select_cond_id = 'wcap_rule_condition_' + id;
    var select_cond_box = document.getElementById( select_cond_id );

    if ( '' !== rule_type ) {
        if ( select_box.nodeName === 'SELECT' ) {
            while (select_box.options.length > 0) {
                select_box.remove(0);
            }
            select_box.removeAttribute( 'onChange' );
		}
		
		if ( jQuery( '#' + select_id ).hasClass('select2-hidden-accessible') ) {
			jQuery( '#' + select_id ).select2('destroy');
			const selectValue = document.querySelector( select_id );
			const selectNew = document.createElement('select');
			selectNew.setAttribute( 'class', 'wcap_rule_value' );
			selectNew.setAttribute( 'id', select_id );
			selectNew.setAttribute( 'name', select_id );
			select_box.parentNode.replaceChild( selectNew, select_box );
			select_box = document.getElementById( select_id );
		}	
        switch( rule_type ) {
            case 'custom_pages':
                select_box.setAttribute( 'class', 'wcap_rule_value wc-product-search' );
                select_box.setAttribute( 'multiple', 'multiple' );
                select_box.setAttribute( 'data-action', 'wcap_json_find_pages' );
                select_box.setAttribute( 'name', select_id + '[]' );
                select_box.setAttribute( 'data-placeholder', wcap_atc_rules_params.wcap_custom_pages );
                select_box.setAttribute( 'style', 'width:90%;' );
                jQuery('.wc-product-search').selectWoo();
                jQuery( document.body ).trigger( 'wc-enhanced-select-init' );
                break;
            case 'product_cat':
                var tr = document.getElementById(id);
                var td = tr.cells[2];
                console.log( td );
                select_box.setAttribute( 'class', 'wcap_rule_value wc-product-search' );
                select_box.setAttribute( 'multiple', 'multiple' );
                select_box.setAttribute( 'data-action', 'wcap_json_find_product_cat' );
                select_box.setAttribute( 'name', select_id + '[]' );
                select_box.setAttribute( 'data-placeholder', wcap_atc_rules_params.wcap_prod_cat_select );
                select_box.setAttribute( 'style', 'width:90%;' );
                jQuery('.wc-product-search').selectWoo();
                jQuery( document.body ).trigger( 'wc-enhanced-select-init' );
                break;
            case 'products':
                select_box.setAttribute( 'class', 'wcap_rule_value wc-product-search' );
                select_box.setAttribute( 'multiple', 'multiple' );
                select_box.setAttribute( 'data-action', 'wcap_json_find_products' );
                select_box.setAttribute( 'name', select_id + '[]' );
                select_box.setAttribute( 'data-placeholder', wcap_atc_rules_params.wcap_products_select );
                select_box.setAttribute( 'style', 'width:90%;' );
                jQuery('.wc-product-search').selectWoo();
                jQuery( document.body ).trigger( 'wc-enhanced-select-init' );
                break;
        }
    }
}

function wcap_delete_rule_row( id ) {
    console.log('clicked');
    var delete_id_number = id.substr(-1);
    var tableRef = document.getElementsByClassName('wcap-rule-list');
    var table_length = tableRef[0].tBodies[0].rows.length;

    if ( table_length == 1 ) {

        var newField = document.createElement( 'a' );
        newField.href = 'javascript:void(0)';
        newField.classList.add( 'wcap_add_rule_button' );
        newField.id = 'add_new';
        newField.onclick = function() { wcap_add_new_rule_row('add_new'); };
        newField.innerHTML = "<i class='fa fa-plus fa-lg fa-fw'></i> Add Rule";
        var foot_row = document.getElementById( 'wcap_rule_list_footer' );
        foot_row.append( newField );

        // Hide the table header
        document.getElementById('wcap-rule-list-header').style.display = 'none';
    } else if ( delete_id_number == table_length ) {
        // Add the add row icon to the row above.
        var prev_row = document.getElementById( delete_id_number - 1 );
        var td = prev_row.cells[3];
        var newField = document.createElement( 'a' );
        newField.classList.add( 'wcap_add_rule_button' );
        newField.id = 'add_new';
        newField.onclick = function() { wcap_add_new_rule_row('add_new'); };
        newField.href = 'javascript:void(0)';
        newField.innerHTML = "<i class='fa fa-plus fa-lg fa-fw'></i> Add Rule";
        td.append( newField );
        
    }
    // Delete the row.
    var row = document.getElementById( delete_id_number );
    row.parentNode.removeChild( row );
}

function wcap_button_choice( element, data_attr ) {
    var state = element.getAttribute( data_attr );
    new_state = 'on' === state ? 'off' : 'on';
    element.setAttribute( data_attr, new_state );
    switch( data_attr ) {
        case 'wcap-atc-switch-modal-mandatory':
            var hidden_input = document.getElementById( 'wcap_switch_atc_modal_mandatory' );
            var non_mandatory_text = document.getElementById( 'wcap_non_mandatory_modal_section_fields_input_text' );
            if ( 'off' == new_state ){
                non_mandatory_text.removeAttribute( 'disabled' );
            } else if ( 'on' == new_state ){
                non_mandatory_text.setAttribute( 'disabled', 'disabled' );
            }
            break;
        case 'wcap-atc-switch-coupon-enable':
            var hidden_input = document.getElementById( 'wcap_auto_apply_coupons_atc' );
            break;
        case 'wcap-atc-countdown-timer-cart-enable':
            var hidden_input = document.getElementById( 'wcap_countdown_timer_cart' );
            break;
		case 'wcap-atc-capture-phone':
			if ( 'off' == new_state ){
				jQuery( '.atc_phone_field').hide();
			} else if ( 'on' == new_state ){
				jQuery( '.atc_phone_field').show();
			}
			var hidden_input = document.getElementById( 'wcap_switch_atc_capture_phone' );
			break;
    }
    hidden_input.setAttribute( 'value', new_state );
}

function wcap_atc_template_status( element ) {
    var state = element.getAttribute( 'wcap-atc-switch-modal-enable' );
    new_state = 'on' === state ? 'off' : 'on';
    element.setAttribute( 'wcap-atc-switch-modal-enable', new_state );

    jQuery.post( ajaxurl, {
        action    : 'wcap_toggle_atc_enable_status',
        id        : element.getAttribute( 'wcap-template-id' ),
        new_state : new_state
    }, function( wcap_atc_enable_response ) {
    });
}