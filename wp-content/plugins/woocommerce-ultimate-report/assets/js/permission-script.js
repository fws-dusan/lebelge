jQuery(document).ready(function($) {
	var submitButton = null;
	var all_checked = true;
	
	$(".btn_save_permission").hide();
	$(".check_all").hide();
	jQuery("#user_role").change(function(){
		var user_role = $(this).val();
		var data = {};		
		data['action'] 			= ic_ajax_object.ic_ajax_action;
		data['plugin_key'] 		= 'icwoocommerceultimatereport';
		data['parent_menu'] 	= 'icwoocommerceultimatereport_page';
		data['do_action_type'] 	= 'permission_setting_pages';
		data['user_role']		= user_role;
		
		if(user_role == '-1'){
			$(".ic_report_pages").html('');
			$(".btn_save_permission").hide();
			return '';	
		}
		
		block_content();
		
		jQuery.ajax({
			type	: "POST",
			url		: ic_ajax_object.ajaxurl,
			data	: data,
			success:function(data) {				
				$(".ic_report_pages").html(data);
				unblock_content();
				$(".btn_save_permission").show();
				$(".check_all").show();
				
				all_checked = true;
				jQuery("input:checkbox").each(function(index, element) {
					if(!jQuery(this).is(":checked")){
						all_checked = false;
					}
				});
			},
			error: function(jqxhr, textStatus, error ){
				unblock_content()
			}
		});
		
	});
	
  	jQuery(document).on('submit','form#frm_save_permission',  function(){		
		block_content();		
		jQuery.ajax({
			type: "POST",
			url: ic_ajax_object.ajaxurl,
			data:  jQuery("#frm_save_permission").serialize(),
			success:function(data) {				
				$(".ic_report_pages").html(data);
				unblock_content();
				all_checked = true;
				jQuery("input:checkbox").each(function(index, element) {
					if(!jQuery(this).is(":checked")){
						all_checked = false;
					}
				});
			},
			error: function(jqxhr, textStatus, error ){
				unblock_content()
			}
		});
		return false;
	});
	
	 jQuery(".check_all").click(function(){
		if(all_checked){
			jQuery('input:checkbox').not(this).prop('checked', false);
			all_checked = false;
		}else{
			jQuery('input:checkbox').not(this).prop('checked', true);
			all_checked = true;
		}
	});
	
});
function block_content(){
	jQuery('div.ic_block_content').block({ 
		message: null,
		css: {
			backgroundColor			: '#fff',
			'-webkit-border-radius'	: '10px',
			'-moz-border-radius'	: '10px',
			border		:'1px solid #5AB6DF',
			//border		:'none',
			padding		:'15px',
			paddingTop	:'19px',
			opacity		:.9,
			color		:'#fff'
		},
		overlayCSS: {
			backgroundColor: '#fff',
			opacity		: 0.6
		}
	});
}

function unblock_content(){
	jQuery('div.ic_block_content').unblock();
}