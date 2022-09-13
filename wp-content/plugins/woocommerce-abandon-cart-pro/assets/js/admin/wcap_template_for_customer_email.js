jQuery( function( $ ) {
	
	$(document).on('change', '#wcap_email_action', function() {
        var wcap_selected_value = this.value;
        if ( 'wcap_email_others' == wcap_selected_value) {
        	$( ".wcap_other_emails" ).fadeIn();
        }else {
        	$( ".wcap_other_emails" ).fadeOut();
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

    if ( select_box.nodeName == 'INPUT' && rule_type == 'payment_gateways' ) {
        const selectValue = document.querySelector( select_id );
        const inputField = document.createElement('select');
        inputField.setAttribute( 'class', 'wcap_rule_value' );
        inputField.setAttribute( 'id', select_id );
        inputField.setAttribute( 'name', select_id );
        select_box.parentNode.replaceChild( inputField, select_box );
        select_box = document.getElementById( select_id );
    } 
    
    if ( '' !== rule_type ) {
        // Update the conditions select box.
        while( select_cond_box.options.length > 0 ) {
            select_cond_box.remove(0);
        }
        switch( rule_type ) {
            case 'cart_items_count':
            case 'cart_total':
                var wcap_count = Object.entries( wcap_email_params.wcap_counts );
                for ( const[key, value ] of wcap_count ) {
                    let option1 = new Option( `${value}`, `${key}` );
                    select_cond_box.add( option1, undefined );
                }
                select_cond_box.removeAttribute( 'style' );
                break;
            default:
                var wcap_cond_includes = Object.entries( wcap_email_params.wcap_cond_includes );
                for ( const[key, value ] of wcap_cond_includes ) {
                    let option1 = new Option( `${value}`, `${key}` );
                    select_cond_box.add( option1, undefined );
                }
                select_cond_box.setAttribute( 'style', 'width: 80%;' );
                break;
        }
        if ( select_box.nodeName === 'SELECT' ) {
            while (select_box.options.length > 0) {
                select_box.remove(0);
            }
            select_box.removeAttribute( 'onChange' );
        }
        switch( rule_type ) {
            case 'send_to':
                select_box.setAttribute( 'onChange', 'wcap_rule_value_updated( this.id )' );
                select_box.setAttribute( 'class', 'wcap_rule_value wc-product-search' );
                select_box.setAttribute( 'multiple', 'multiple' );
                select_box.setAttribute( 'data-action', 'wcap_json_find_send_to' );
                select_box.setAttribute( 'name', select_id + '[]' );
                select_box.setAttribute( 'data-placeholder', wcap_email_params.wcap_send_to_select );
                select_box.setAttribute( 'style', 'width:90%;' );
                jQuery('.wc-product-search').selectWoo();
                jQuery( document.body ).trigger( 'wc-enhanced-select-init' );
                break;
            case 'cart_items_count':
            case 'cart_total':
                const selectValue = document.querySelector( select_id );
                const inputField = document.createElement('input');
                inputField.setAttribute( 'class', 'wcap_rule_value' );
                inputField.setAttribute( 'type', 'number' );
                inputField.setAttribute( 'id', select_id );
                inputField.setAttribute( 'name', select_id );
                inputField.min = 1;
                select_box.parentNode.replaceChild( inputField, select_box );
                break;
            case 'payment_gateways':
                var wcap_payment_gateways = Object.entries( wcap_email_params.wcap_payment_gateways );
                for ( const[key, value ] of wcap_payment_gateways ) {
                    let option1 = new Option( `${value}`, `${key}` );
                    select_box.add( option1, undefined );
                }
                break;
            case 'cart_items':
                select_box.setAttribute( 'class', 'wcap_rule_value wc-product-search' );
                select_box.setAttribute( 'multiple', 'multiple' );
                select_box.setAttribute( 'data-action', 'wcap_json_find_products' );
                select_box.setAttribute( 'name', select_id + '[]' );
                select_box.setAttribute( 'data-placeholder', wcap_email_params.wcap_product_select );
                select_box.setAttribute( 'style', 'width:90%;' );
                jQuery('.wc-product-search').selectWoo();
                jQuery( document.body ).trigger( 'wc-enhanced-select-init' );
                break;
            case 'coupons':
                select_box.setAttribute( 'class', 'wcap_rule_value wc-product-search' );
                select_box.setAttribute( 'multiple', 'multiple' );
                select_box.setAttribute( 'data-action', 'wcap_json_find_coupons' );
                select_box.setAttribute( 'name', select_id + '[]' );
                select_box.setAttribute( 'data-placeholder', wcap_email_params.wcap_coupon_select );
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
                select_box.setAttribute( 'data-placeholder', wcap_email_params.wcap_prod_cat_select );
                select_box.setAttribute( 'style', 'width:90%;' );
                jQuery('.wc-product-search').selectWoo();
                jQuery( document.body ).trigger( 'wc-enhanced-select-init' );
                break;
            case 'product_tag':
                select_box.setAttribute( 'class', 'wcap_rule_value wc-product-search' );
                select_box.setAttribute( 'multiple', 'multiple' );
                select_box.setAttribute( 'data-action', 'wcap_json_find_product_tag' );
                select_box.setAttribute( 'name', select_id + '[]' );
                select_box.setAttribute( 'data-placeholder', wcap_email_params.wcap_prod_tag_select );
                select_box.setAttribute( 'style', 'width:90%;' );
                jQuery('.wc-product-search').selectWoo();
                jQuery( document.body ).trigger( 'wc-enhanced-select-init' );
                break;
            case 'cart_status':
                select_box.setAttribute( 'class', 'wcap_rule_value wc-product-search' );
                select_box.setAttribute( 'multiple', 'multiple' );
                select_box.setAttribute( 'data-action', 'wcap_json_find_cart_status' );
                select_box.setAttribute( 'name', select_id + '[]' );
                select_box.setAttribute( 'data-placeholder', wcap_email_params.wcap_status_select );
                select_box.setAttribute( 'style', 'width:90%;' );
                jQuery('.wc-product-search').selectWoo();
                jQuery( document.body ).trigger( 'wc-enhanced-select-init' );
                break;
        }
    }
}

function wcap_delete_rule_row( id ) {
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

function wcap_rule_value_updated( id ) {
    var select_box = document.getElementById( id );
    var rule_value = getSelectValues( select_box );

    // append a text area to the td.
    var td = select_box.parentNode;
    var textArea = document.getElementById( 'wcap_rules_email_addresses' );

    if ( rule_value.indexOf( 'email_addresses' ) != -1 && textArea === null ) {

        var newField = document.createElement( 'textarea' );
        newField.setAttribute( 'rows', 3 );
        newField.setAttribute( 'cols', 35 );
        newField.setAttribute( 'name', 'wcap_rules_email_addresses' );
        newField.setAttribute( 'id', 'wcap_rules_email_addresses' );
        newField.setAttribute( 'style', 'margin-top: 10px;' );
        newField.setAttribute( 'placeholder', 'Please enter email addresses separated by a comma' );
        newField.innerHTML = '';
        td.append( newField );
        
    } else if ( rule_value.indexOf( 'email_addresses' ) == -1 && null !== textArea ) {
        textArea.parentNode.removeChild( textArea );
    }

}

function getSelectValues(select) {
    var result = [];
    var options = select && select.options;
    var opt;
  
    for (var i=0, iLen=options.length; i<iLen; i++) {
      opt = options[i];
  
      if (opt.selected) {
        result.push(opt.value || opt.text);
      }
    }
    return result;
}