jQuery(function(){
	
	var report_name = jQuery("input[name=report_name]").val();
	
	var serach_url = ic_ajax_object.ajaxurl+'?do_action_type=ic_autocomplete&action='+ic_ajax_object.ic_ajax_action+"&=report_name="+report_name;
	var cache = {};
	
	function ic_split( val ) {
	  return val.split( /,\s*/ );
	}
	
	function extractLast( term ) {
	  return ic_split( term ).pop();
	}
	
	
	var ic_autocomplete  = jQuery('.ic_autocomplete');
	
	ic_autocomplete.autocomplete({multiselect: true});
	
	ic_autocomplete.focus(function(){
	//jQuery(document).on("focus.autocomplete", ".ic_autocomplete", function () {
		var search_type = jQuery(this).attr('data-search_type');
		var report_name = jQuery("input[name=report_name]").val();
		
		serach_url = serach_url +"&search_type="+search_type;
		serach_url = serach_url +"&report_name="+report_name;
		jQuery(this).autocomplete({
			source: function(request, response){
				var term = extractLast( request.term );
				if ( term in cache ) {
				  response( cache[ term ] );
				  return;
				}
			   
			   jQuery.getJSON(serach_url, {term: term}, function( data, status, xhr ) {cache[ term ] = data;response( data );});
			},
			search: function() {
			  // custom minLength
			  var term = extractLast( this.value );
			  if ( term.length < 2 ) {
				return false;
			  }
			},
			multiselect: true
		});																
		
	});//autocomplete({multiselect: true});
	
	
})