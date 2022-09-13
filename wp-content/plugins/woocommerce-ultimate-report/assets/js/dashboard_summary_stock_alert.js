var popup_open 			= false;
var popup_id 			= null;
var popup_data			= new Array();
var enable_notification = false;
function center() {
	if (popup_open == true) {
		if(popup_id == null) return;
		
		obj = popup_id;
		var $ = jQuery;
		var windowWidth = document.documentElement.clientWidth;
		var windowHeight = document.documentElement.clientHeight;
		var popupHeight = $(obj).height();
		var popupWidth = $(obj).width();
		$(obj).css({
			"position": "fixed",
			"top": windowHeight / 2 - popupHeight / 2,
			"left": windowWidth / 2 - popupWidth / 2
		}).fadeIn();
	}
}

function hidePopup() {
	var $ = jQuery;
    if (popup_open == true) {
        $(".popup_mask").fadeOut("slow");
        $(".popup_box").fadeOut("slow");
        popup_open = false;
		popup_id = null;
    }
}

function get_minimum_level_stock_alert(popupid,product_count){
	var $ = jQuery;	
	
	if(!enable_notification) return ''
	
	var action_data = {
		"action"			: ic_ajax_object.ic_ajax_action,
		"do_action_type"	: "product_stock_alert",
		"product_count"		: product_count,
		"report_name"		: popupid
    }
	
	$.ajax({
		type:	"POST",
		url:	ic_ajax_object.ajaxurl,
		data:	action_data,
		success:function(data) {
			popup_data[popupid] = data;
			$(popup_id).find(".ajax_popup_content").html(popup_data[popupid]);
			center();
		},
		error: function(jqxhr, textStatus, error ){				
			alert(jqxhr.responseText)
		}
	});
}

function set_notification_button(){
	var $ = jQuery;	
	
	$(".open_popup").click(function(){		
		var obj 		= this;	
		var popupid 	= $(obj).attr("data-popupid");
		var prod_count 	= $(obj).attr("data-notifications");
		popup_open 		= true;
		popup_id 		= "#" + popupid;
		$(popup_id).find(".ajax_popup_content").html("Please Wait!");
		
		$(".popup_mask").fadeIn();
		$(popup_id).fadeIn("slow");
		
		if(popup_data[popupid]){
			$(popup_id).find(".ajax_popup_content").html(popup_data[popupid]);
		}else{	
			get_minimum_level_stock_alert(popupid,prod_count);	
		}
		center();
		return false;
	});
}

jQuery(document).ready(function($) {
	
	$( window ).resize(function() {
		center();
	});
	
	$('.popup_close, .popup_mask, .button_popup_close').click(function(){
		hidePopup();
	});
	
	set_notification_button();
	
	var action_data = {
		"action"			: ic_ajax_object.ic_ajax_action,
		"do_action_type"	: "product_stock_alert",
		"report_name"		: 'product_notification_count'
    }
	
	$.ajax({
		type:	"POST",
		url:	ic_ajax_object.ajaxurl,
		data:	action_data,
		dataType: "json",
		success:function(data) {
			var total_min_stock_product = 0;
			if(data.zero_level_popup_button >= 1){
				$("#zero_level_popup_button").attr("data-notifications",data.zero_level_popup_button);
				enable_notification = true;
				total_min_stock_product = total_min_stock_product + data.zero_level_popup_button;
			}else{
				$("#zero_level_popup_button").css({"visibility":"hidden"});
			}
			
			if(data.minimum_level_popup_button >= 1){
				$("#minimum_level_popup_button").attr("data-notifications",data.minimum_level_popup_button);
				enable_notification = true;
				total_min_stock_product = total_min_stock_product + data.minimum_level_popup_button;
			}else{
				$("#minimum_level_popup_button").css({"visibility":"hidden"});
			}
			
			if(enable_notification){
				$(".notification_box").css({"visibility":"visible"});
			}
			
			$("#total_min_stock_product").attr("data-notifications",total_min_stock_product);
		},
		error: function(jqxhr, textStatus, error ){				
			alert(jqxhr.responseText)
		}
	});
});