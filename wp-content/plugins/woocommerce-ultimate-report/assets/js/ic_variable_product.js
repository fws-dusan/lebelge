// JavaScript Document
jQuery(function($){
	
	 jQuery('._date').datepicker({
        dateFormat : 'yy-mm-dd'
    });
	
	jQuery( "#frm_variable_product" ).submit(function( e ) {
		//alert(ic_ajax_object.ajaxurl);
		
		$.ajax({
			
			url:ic_ajax_object.ajaxurl,
			data:$("#frm_variable_product").serialize(),
			
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