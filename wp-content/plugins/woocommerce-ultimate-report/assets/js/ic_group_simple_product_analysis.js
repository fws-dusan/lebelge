// JavaScript Document
// JavaScript Document
jQuery(function($){
	
	 jQuery('._date').datepicker({
        dateFormat : 'yy-mm-dd'
    });
	//alert("a");
	// alert(ajax_object.ic_ajax_url);
	jQuery( "#frm_group_simple_product" ).submit(function( e ) {
		// alert(ajax_object.ic_ajax_url);
		//return false;
		$.ajax({
			
			url:ic_ajax_object.ajaxurl,
			data:$("#frm_group_simple_product").serialize(),
			
			success:function(data) {
				// This outputs the result of the ajax request
				console.log(data);
				//alert("s");
				$(".ajax_content").html(data);
			},
			error: function(errorThrown){
				console.log(errorThrown);
				alert("e");
			}
		}); 
		
		
		return false;
	});
});