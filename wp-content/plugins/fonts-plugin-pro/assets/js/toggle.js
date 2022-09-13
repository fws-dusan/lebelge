( function( $, api ) {
	api.controlConstructor.toggle = api.Control.extend( {

		ready: function() {
			const control = this;

			this.container.on( 'change', 'input:checkbox', function() {
				value = this.checked ? true : false;
				control.setting.set( value );
			} );
		},

	} );
}( jQuery, wp.customize ) );
