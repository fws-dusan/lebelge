jQuery.widget("ui.autocomplete", jQuery.ui.autocomplete, {
    options : jQuery.extend({}, this.options, {
        multiselect: false
    }),
    _create: function(){
        this._super();

        var self = this,
            o = self.options;

        if (o.multiselect) {
            console.log('multiselect true');

            self.selectedItems = {};           
            self.multiselect = jQuery("<div></div>")
                .addClass("ui-autocomplete-multiselect ui-state-default ui-widget")
                .css("width", self.element.width())
                .insertBefore(self.element)
                .append(self.element)
                .bind("click.autocomplete", function(){
                    self.element.focus();
                });
				
				var n = jQuery(self.element).attr('name');
				var v = jQuery(self.element).val();
				
				if(v == "-1"){
					v = "";
					jQuery(self.element).val(v);
				}
								
				jQuery(self.element).attr('name',"multiselect_" + n);				
				self.form_field = jQuery("<input name=\""+ n +"\" class=\""+ n +"\" type=\"hidden\" value=\""+ v +"\">").insertBefore(self.element);
            
            var fontSize = parseInt(self.element.css("fontSize"), 10);
            function autoSize(e){
                var $this = jQuery(this);
                $this.width(1).width(this.scrollWidth+fontSize-1);
            };
			
			function set_selected_items(e){
               var  s = new Array(),i = 0;
				jQuery.each(self.selectedItems, function( e_index, e_item ) {
				  s[i] = e_item.id;
				  i++;
				});
				jQuery(self.form_field).val(s.toString());
            };

            var kc = jQuery.ui.keyCode;
            self.element.bind({
                "keydown.autocomplete": function(e){
                    if ((this.value === "") && (e.keyCode == kc.BACKSPACE)) {
                        var prev = self.element.prev();
                        delete self.selectedItems[prev.text()];
                        prev.remove();
						set_selected_items();
                    }
                },
                "focus.autocomplete blur.autocomplete": function(){
                    self.multiselect.toggleClass("ui-state-active");
                },
                "keypress.autocomplete change.autocomplete focus.autocomplete blur.autocomplete": autoSize
            }).trigger("change");

            o.select = function(e, ui) {
				
				/*jQuery.each(self.selectedItems, function( e_index, e_item ) {
				  	if(ui.item.id = e_item.id){
					 
					}
				});*/
				
                jQuery("<div></div>")
                    .addClass("ui-autocomplete-multiselect-item")
                    .text(ui.item.label)
					.attr('data-id',ui.item.id)
                    .append(
                        jQuery("<span></span>")
                            .addClass("ui-icon ui-icon-close")
                            .click(function(){
                                var item = jQuery(this).parent();
                                delete self.selectedItems[item.text()];
                                item.remove();
								set_selected_items();
                            })
                    )
                    .insertBefore(self.element);
                
                self.selectedItems[ui.item.label] = ui.item;
                self._value("");
				set_selected_items();
				return false;
            }
        }

        return this;
    }
});