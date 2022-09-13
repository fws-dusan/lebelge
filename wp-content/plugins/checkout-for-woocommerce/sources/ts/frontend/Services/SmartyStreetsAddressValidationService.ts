import SmartyStreetsAddressValidationAction from '../Actions/SmartyStreetsAddressValidationAction';
import Main                                 from '../Main';
import DataService                          from './DataService';
import TabService                           from './TabService';

class SmartyStreetsAddressValidationService {
    constructor() {
        if ( DataService.getSetting( 'enable_smartystreets_integration' ) ) {
            this.load();
        }
    }

    load() {
        const fastDeepEqual = require( 'fast-deep-equal' );
        let userAddress;
        let suggestedAddress = {};
        let userHasAcceptedAddress = false;

        const trigger = jQuery( '.cfw-smartystreets-modal-trigger' );

        trigger.modaal( { width: 600, custom_class: 'checkoutwc' } );

        /**
         * Tab Change Intercept
         *
         * Only fires when the current tab is the information tab
         * and the destination tab is to the right
         */
        Main.instance.tabService.tabContainer.bind( 'easytabs:before', ( event, clicked, target ) => {
            const address = {
                address_1: jQuery( '[name="shipping_address_1"]' ).val(),
                address_2: jQuery( '[name="shipping_address_2"]' ).val(),
                city: jQuery( '[name="shipping_city"]' ).val(),
                state: jQuery( '[name="shipping_state"]' ).val(),
                postcode: jQuery( '[name="shipping_postcode"]' ).val(),
                country: jQuery( '[name="shipping_country"]' ).val(),
                company: jQuery( '[name="shipping_company"]' ).val(),
            };

            const addressHasNotChanged = fastDeepEqual( userAddress, address );

            if ( userHasAcceptedAddress && addressHasNotChanged ) {
                return true;
            }

            userHasAcceptedAddress = false;

            const currentTab = Main.instance.tabService.getCurrentTab();
            const destinationTab = jQuery( target );
            const destinationTabIsAfterCurrentTab   = currentTab.nextAll( '.cfw-panel' ).filter( `#${destinationTab.attr( 'id' )}` ).length;
            const currentTabIsInformationTab = currentTab.attr( 'id' ) === TabService.customerInformationTabId;

            // Make sure the next tab is after the current one
            if ( destinationTabIsAfterCurrentTab && currentTabIsInformationTab ) {
                // Fetch address suggestions
                const response = new SmartyStreetsAddressValidationAction( 'cfw_smartystreets_address_validation', DataService.getAjaxInfo(), address ).synchronousLoad();

                if ( response.result ) {
                    jQuery( '.cfw-smartystreets-user-address' ).html( response.original );
                    jQuery( '.cfw-smartystreets-suggested-address' ).html( response.address );

                    // Set to suggested by default
                    jQuery( '.cfw-radio-suggested-address' ).prop( 'checked', true ).trigger( 'change' );

                    trigger.modaal( 'open' );

                    suggestedAddress = response.components;
                    userAddress = address;

                    event.stopImmediatePropagation();
                    return false;
                }
            }

            return true;
        } );

        /**
         * Address Selection Radio Buttons
         */
        const wraps  = jQuery( '.cfw-smartystreets-option-wrap' );
        const radios = jQuery( '.cfw-smartystreets-option-wrap input:radio' );

        const updateButtons = () => {
            wraps.each( ( i, wrap ) => {
                if ( jQuery( wrap ).find( 'input:radio:checked' ).length > 0 ) {
                    jQuery( wrap ).removeClass( 'cfw-smartystreets-hide-buttons' );
                    return; // continue
                }

                jQuery( wrap ).addClass( 'cfw-smartystreets-hide-buttons' );
            } );
        };

        radios.on( 'change', updateButtons );

        updateButtons();

        /**
         * Use Address Buttons
         */
        jQuery( document.body ).on( 'click', '.cfw-smartystreets-suggested-address-button', ( el ) => {
            // Replace address with suggested address
            Object.keys( suggestedAddress ).forEach( ( key: any ) => {
                jQuery( `[name="shipping_${key}"]` ).val(  suggestedAddress[ key ] ).trigger( 'change' );
            } );

            userHasAcceptedAddress = true;
            userAddress = suggestedAddress;

            trigger.modaal( 'close' );
            Main.instance.tabService.tabContainer.easytabs( 'select', jQuery( el.target ).data( 'tab' ) );
        } );

        jQuery( document.body ).on( 'click', '.cfw-smartystreets-user-address-button', ( el ) => {
            userHasAcceptedAddress = true;

            trigger.modaal( 'close' );
            Main.instance.tabService.tabContainer.easytabs( 'select', jQuery( el.target ).data( 'tab' ) );
        } );
    }
}

export default SmartyStreetsAddressValidationService;
