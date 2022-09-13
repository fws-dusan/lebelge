import DataService    from './DataService';
import LoggingService from './LoggingService';

declare let clickToAddress: any;

class FetchifyAddressAutocompleteService {
    /**
     * Attach change events to postcode fields
     */
    constructor() {
        if ( !DataService.getSetting( 'enable_fetchify_address_autocomplete' ) ) {
            return;
        }

        if ( typeof clickToAddress === 'undefined' ) {
            // eslint-disable-next-line no-console
            LoggingService.logError( 'CheckoutWC: Could not load Fetchify object.' );
        }

        // eslint-disable-next-line new-cap
        const config = {
            accessToken: DataService.getSetting( 'fetchify_access_token' ), // Replace this with your access token
            gfxMode: 1,
            domMode: 'name', // Use names to find form elements
            countryMatchWith: 'iso_2',
            enabledCountries: this.getAllowedCountries(),
            defaultCountry: DataService.getSetting( 'fetchify_default_country' ),
            getIpLocation: DataService.getSetting( 'fetchify_enable_geolocation' ),
        };

        const fetchify = new clickToAddress( config );

        fetchify.attach( {
            search: 'billing_address_1', // 'search_field' is the name of the search box element
            line_1: 'billing_address_1',
            line_2: 'billing_address_2',
            company: 'billing_company',
            town: 'billing_city',
            postcode: 'billing_postcode',
        }, {
            onResultSelected: this.fillBillingCountryState.bind( this ),
        } );

        if ( DataService.getSetting( 'needs_shipping_address' ) === true ) {
            fetchify.attach( {
                search: 'shipping_address_1', // 'search_field' is the name of the search box element
                line_1: 'shipping_address_1',
                line_2: 'shipping_address_2',
                company: 'shipping_company',
                town: 'shipping_city',
                postcode: 'shipping_postcode',
            }, {
                onResultSelected: this.fillShippingCountryState.bind( this ),
            } );
        }
    }

    fillShippingCountryState( object: any, dom: any, result: any ): void {
        try {
            this.fillCountryState( 'shipping', result );
        } catch ( err ) {
            LoggingService.logError( err );
        }
    }

    fillBillingCountryState( object: any, dom: any, result: any ): void {
        try {
            this.fillCountryState( 'billing', result );
        } catch ( err ) {
            LoggingService.logError( err );
        }
    }

    fillCountryState( prefix: any, result: any ): void {
        if ( !result ) {
            return;
        }

        jQuery( document.body ).one( 'cfw_fetchify_country_changed', () => {
            setTimeout( () => {
                const state = jQuery( `#${prefix}_state` );
                const foundState = result.province_name.replace( 'County ', '' );

                // Special State handling
                if ( !state.is( 'select' ) || state.find( `option[value="${foundState}"]` ).length ) {
                    state.val( foundState );
                } else {
                    state.val( state.find( `option:contains(${foundState})` ).val() );
                }

                state.trigger( 'change', [ 'cfw_store' ] ).trigger( 'keyup' );
            }, 300 );
        } );

        jQuery( `#${prefix}_country` ).val( result.country.iso_3166_1_alpha_2 ).trigger( 'change', [ 'cfw_store' ] ).trigger( 'keyup' );
        jQuery( document.body ).trigger( 'cfw_fetchify_country_changed' );
    }

    getAllowedCountries(): Array<string> {
        const countryNames = [];

        jQuery( '#shipping_country option, #billing_country option' ).each( ( index, elem ) => {
            const countryVal = jQuery( elem ).val();

            if ( countryVal !== '' && countryNames.indexOf( countryVal ) === -1 ) {
                countryNames.push( countryVal );
            }
        } );

        return countryNames;
    }
}

export default FetchifyAddressAutocompleteService;
