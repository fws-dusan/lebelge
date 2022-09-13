/**
 * Abandoned cart detail Modal
 */

var Modal;
var wcap_clicked_cart_id;
var $wcap_get_email_address;
var $wcap_customer_details;
var $wcap_cart_total;
var $wcap_abandoned_date;
var $wcap_abandoned_status; 
var email_body;
var $wcap_cart_status;
var $wcap_show_customer_detail;

jQuery(function($) {

    Modal = {
        init: function(){

            $(document.body).on( 'click', '.wcap-js-close-modal', this.close );
            $(document.body).on( 'click', '.wcap-modal-overlay', this.close );
            $(document.body).on( 'click', '.wcap-js-open-modal', this.handle_link );
            $(document.body).on( 'click', '.wcap-js-edit_email', this.edit_email_popup );
            $(document.body).on( 'click', '.wcap-mark-recovered-action', this.mark_recovered_popup );
            $(document.body).on( 'click', '.wcap_search_wc_order', this.search_order );
            $(document.body).on( 'click', '.wcap_mark_recovered_admin', this.mark_recovered_update );
            $(document.body).on( 'click', '.wcap_customer_detail_modal', this.handle_customer_details );
            $(document.body).on( 'mousedown', '.wcap-js-open-modal', this.handle_link_mouse_middle_click );
            $(document.body).on( 'click', '.wcap_admin_unsubscribe', this.wcap_admin_unsubscribe );
            $(document.body).on( 'click', '.wcap_update_email', this.wcap_update_email );

            $(window).resize(function(){
                Modal.position();
            });

            $(document).keydown(function(e) {
                if (e.keyCode == 27) {
                    Modal.close();
                }
            });

        },
        handle_customer_details: function ( event ){
            event.preventDefault();
            var wcap_text_of_event = $(event.target).text();
            if ( wcap_text_of_event.indexOf ('Hide') == -1 ){
                $( ".wcap_modal_customer_all_details" ).fadeIn();
                Modal.position();
                $(event.target).text('Hide Details') ;
            }else{
                $( ".wcap_modal_customer_all_details" ).fadeOut();
                Modal.position();
                $(event.target).text('Show Details') ;
            }
        },
        handle_link_mouse_middle_click: function( e ){
            
           if( e.which == 2 ) {
                var wcap_get_currentpage = window.location.href;    
                this.href = wcap_get_currentpage;
                e.preventDefault();
                return false;
           }
        },
        handle_link: function( e ){
            e.preventDefault();

            var $a = $( this );
            var current_page   = ''; 
            var wcap_get_currentpage = window.location.href;
            var $wcap_get_email_address;
            var $wcap_break_email_text;
            var $email_text;
            var $wcap_row_data;
            
            if ( wcap_get_currentpage.indexOf('action=emailstats') == -1 ){ 
                $wcap_row_data = $a.closest("tr")[0];
                $email_text = $wcap_row_data.getElementsByTagName('td')[1].innerHTML;
                $wcap_break_email_text  = $email_text.split('<div');
                $wcap_get_email_address = $wcap_break_email_text[0];

                $wcap_customer_details = $wcap_row_data.getElementsByTagName('td')[2].innerHTML;
                $wcap_cart_total       = $wcap_row_data.getElementsByTagName('td')[3].innerHTML;
                $wcap_abandoned_date   = $wcap_row_data.getElementsByTagName('td')[4].innerHTML;
                $wcap_abandoned_status = $wcap_row_data.getElementsByClassName('status')[0].firstChild.innerText;
                
                $wcap_cart_status = $wcap_abandoned_status;
            }else{
                current_page            = 'send_email';
                $wcap_get_email_address = '';
                $wcap_customer_details  = '';
                $wcap_cart_total        = '';
                $wcap_abandoned_date    = '';
                $wcap_cart_status       = '';
            }

            $wcap_show_customer_detail = '<br><a href="#" id "wcap_customer_detail_modal"> Show Details </a>';
            
            email_body = '<div class="wcap-modal__body"> <div class="wcap-modal__body-inner"> <table cellspacing="0" cellpadding="6" border="1" class="wcap-cart-table"> <thead><th>Email Address</th><th>Customer Details</th><th>Order Total</th><th> Abandoned Date</th></tr></thead><tbody><tr><td>'+  $wcap_get_email_address+ '</td><td>'+ $wcap_customer_details + $wcap_show_customer_detail +'</td><td>'+ $wcap_cart_total+' </td><td>' +$wcap_abandoned_date+' </td></tr></tbody></table></div> </div>';

            var type = $a.data('modal-type');
            
            if ( type == 'ajax' )
            {
                wcap_clicked_cart_id     = $a.data('wcap-cart-id');
                Modal.open( 'type-ajax' );
                Modal.loading();
                var data = {
                    action                : 'wcap_abandoned_cart_info',
                    wcap_cart_id          : wcap_clicked_cart_id,
                    wcap_email_address    : $wcap_get_email_address,
                    wcap_customer_details : $wcap_customer_details,
                    wcap_cart_total       : $wcap_cart_total,
                    wcap_abandoned_date   : $wcap_abandoned_date,
                    wcap_abandoned_status : $wcap_cart_status,
                    wcap_current_page     : current_page
                }

                $.post( ajaxurl, data , function( response ){

                    Modal.contents( response ); 
                });
            }
        },
        edit_email_popup: function( e ){
            e.preventDefault();

            var $a = $( this );
            var current_page   = ''; 
            var wcap_get_currentpage = window.location.href;
            var $wcap_get_email_address;
            var $wcap_break_email_text;
            var $email_text;
            var $wcap_row_data;

            if ( wcap_get_currentpage.indexOf('action=emailstats') == -1 ){ 
                $wcap_row_data = $a.closest("tr")[0];
                $email_text = $wcap_row_data.getElementsByTagName('td')[1].innerHTML;
                $wcap_break_email_text   = $email_text.split('<div');
                $wcap_break_email_text_1 = $wcap_break_email_text[0].split('<a');
                $wcap_get_email_address  = $wcap_break_email_text_1[0];
                $wcap_user_id = $(this).data('wcap-user-id');

                $wcap_cart_status = '';
            }

            email_body = `
                <div class="wcap-modal__body">
                    <div class="wcap-modal__body-inner">
                        <label for="wcap_edit_guest_email">Update Guest Email:</label>
                        <input
                            type="email"
                            id="wcap_edit_guest_email"
                            name="wcap_edit_guest_email"
                            class="wcap_edit_guest_email"
                            value="${$wcap_get_email_address}"
                            style="width:256px;"/>
                        <div class="wcap_edit_footer" style="padding:25px 0px;">
                            <a
                                class="button wcap_update_email"
                                data-wcap-user-id="${$wcap_user_id}"
                                data-modal-type="ajax">
                                Update Email Address
                            </a>
                        </div>
                    </div>
                </div>`;

            var type = $a.data('modal-type');
            
            if ( type == 'ajax' ) {
                wcap_clicked_cart_id = $a.data('wcap-cart-id');
                Modal.open( 'type-ajax' );

                $( '.wcap-modal-cart-content-hide' ).hide();
            }
        },

        open: function( classes ) {

            $(document.body).addClass('wcap-modal-open').append('<div class="wcap-modal-overlay"></div>');
            var modal_body = '<div class="wcap-modal ' + classes + '"><div class="wcap-modal__contents"> <div class="wcap-modal__header"><h1>Cart #'+wcap_clicked_cart_id+'</h1>'+$wcap_cart_status+'</div>'+ email_body +' <div class = "wcap-modal-cart-content-hide" id ="wcap_remove_class">  </div> </div>  <div class="wcap-icon-close wcap-js-close-modal"></div>    </div>';

            $(document.body).append( modal_body );

            this.position();
        },

        loading: function() {
            $(document.body).addClass('wcap-modal-loading');
        },

        contents: function ( contents ) {
            $(document.body).removeClass('wcap-modal-loading');

            contents = contents.replace(/\\(.)/mg, "$1");

            $('.wcap-modal__contents').html(contents);

            this.position();
        },

        close: function() {
            $(document.body).removeClass('wcap-modal-open wcap-modal-loading');
            
            $('.wcap-modal, .wcap-modal-overlay').remove();
        },

        position: function() {

            $('.wcap-modal__body').removeProp('style');

            var modal_header_height = $('.wcap-modal__header').outerHeight();
            var modal_height = $('.wcap-modal').height();
            var modal_width = $('.wcap-modal').width();
            var modal_body_height = $('.wcap-modal__body').outerHeight();
            var modal_contents_height = modal_body_height + modal_header_height;

            $('.wcap-modal').css({
                'margin-left': -modal_width / 2,
                'margin-top': -modal_height / 2
            });

            if ( modal_height < modal_contents_height - 5 ) {
                $('.wcap-modal__body').height( modal_height - modal_header_height );
            }
        },
        search_order: function(e) {

            var wcap_order_id = parseInt( $( '#wcap_link_wc_order' ).val() );
            if ( isNaN( wcap_order_id ) ) {
                var wcap_order_id = $( '#wcap_hidden_order_id' ).val();
            }

            if ( wcap_order_id > 0 ) {
                var data = {
                    action: 'wcap_json_search_wc_order',
                    order_id : wcap_order_id 
                };
                $.post( ajaxurl, data, function( response ){
                    if ( response === 'failed' ) {
                        $( '#wcap_link_wc_order' ).val( wcap_abandoned_cart_params.order_id_not_found_msg );
                    } else {
                        $( '#wcap_hidden_order_id' ).val( wcap_order_id );
                        $( '#wcap_link_wc_order' ).val( response );
                    }
                });
            } else {
                $( '#wcap_link_wc_order' ).val( wcap_abandoned_cart_params.validation_error_order_id );
            }
        },
        mark_recovered_popup: function(e) {
            e.preventDefault();

            var $a = $( this );
            
            var $cart_id = $a.data( 'wcap-cart-id');
            $wcap_cart_status = '';
            email_body = `
                <div class="wcap-modal__body">
                    <div class="wcap-modal__body-inner">
                        <div>
                        <p>${wcap_abandoned_cart_params.recovered_display_text}</p>
                            <input
                                id="wcap_link_wc_order"
                                name="wcap_link_wc_order"
                                class="wcap_link_wc_order"
                                style="width:60%; height: 30px;"
                                placeholder="${wcap_abandoned_cart_params.order_id_txt_placeholder}" />
                            <a
                                class="button wcap_search_wc_order"
                                data-wcap-cart-id="${$cart_id}"
                                data-modal-type="ajax">
                                ${wcap_abandoned_cart_params.search_button_txt}
                            </a>
                        </div>
                        <input
                            type="hidden"
                            id="wcap_hidden_order_id"
                            class="wcap_hidden_order_id"
                            value="" />
                        <div class="wcap_edit_footer" style="padding:25px 0px;">
                            <a
                                class="button wcap_mark_recovered_admin"
                                data-wcap-cart-id="${$cart_id}"
                                data-modal-type="ajax">
                                ${wcap_abandoned_cart_params.mark_recovered_txt}
                            </a>
                        </div>
                    </div>
                </div>`;
            var type = $a.data('modal-type');
            
            if ( type == 'ajax' ) {
                wcap_clicked_cart_id = $a.data('wcap-cart-id');
                Modal.open( 'type-ajax' );

                $( '.wcap-modal-cart-content-hide' ).hide();
            }
        },
        wcap_admin_unsubscribe: function() {

            var $a                  = $( this );
            wcap_clicked_cart_id    = $a.data('wcap-cart-id');
            var data = {
                action          : 'wcap_admin_unsubscribe_cart',
                wcap_cart_id    : wcap_clicked_cart_id,
            }

            $a.text('Processing...');

            $.post( ajaxurl, data , function( response ){
                $a.text(response.data);
                
                setTimeout( function(){
                    $('.wcap-js-close-modal' ).click();
                    location.reload();    
                },500);
            });

            return false;
        },

        wcap_update_email: function() {
            const $a = $( this );
            const wcap_user_id = $a.data('wcap-user-id');
            const wcap_email = $( '.wcap_edit_guest_email' ).val();
            const edit_data = {
                action : 'wcap_edit_guest_email',
                wcap_user_id,
                wcap_email
            };

            $a.text('Processing...');

            $.post( ajaxurl, edit_data, function( response ){
                $a.text(response.data);
                
                setTimeout( function(){
                    $('.wcap-js-close-modal' ).click();
                    location.reload();
                },500);
            });

            return false;
        },
        mark_recovered_update: function() {
            const order_id = $( '#wcap_hidden_order_id' ).val();
            const cart_id = $(this).data( 'wcap-cart-id' );
            recovered_data = {
                order_id: order_id,
                cart_id : cart_id,
                action  : 'wcap_mark_recovered_admin'
            };
            $.post( ajaxurl, recovered_data, function( response ) {
                
                setTimeout( function(){
                    $('.wcap-js-close-modal' ).click();
                    location.reload();
                },500);
            });
        }
    };
    Modal.init();
});