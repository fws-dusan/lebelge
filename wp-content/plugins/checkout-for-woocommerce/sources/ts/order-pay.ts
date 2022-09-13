import 'core-js/features/object/assign';
import 'ts-polyfill';
import cfwDomReady            from './_functions';
import Accordion              from './frontend/Components/Accordion';
import Cart                   from './frontend/Components/Cart';
import TermsAndConditions     from './frontend/Components/TermsAndConditions';
import AlertService           from './frontend/Services/AlertService';
import DataService            from './frontend/Services/DataService';
import LoggingService         from './frontend/Services/LoggingService';
import PaymentGatewaysService from './frontend/Services/PaymentGatewaysService';
import UpdateCheckoutService  from './frontend/Services/UpdateCheckoutService';

// eslint-disable-next-line import/prefer-default-export
class OrderPay {
    constructor() {
        cfwDomReady( () => {
            /**
             * Services
             */
            // Init runtime params
            DataService.initRunTimeParams();

            new PaymentGatewaysService();

            // Alert Service
            new AlertService( DataService.getElement( 'alertContainerId' ) );

            /**
             * Components
             */
            // Accordion Component
            new Accordion();

            // Cart Component
            new Cart();

            // Load Terms and Conditions Component
            new TermsAndConditions();

            // Payment Gateway Service
            new PaymentGatewaysService();

            // Trigger updated checkout
            UpdateCheckoutService.triggerUpdatedCheckout();

            // Init checkout ( WooCommerce native event )
            jQuery( document.body ).trigger( 'init_checkout' );
            LoggingService.logEvent( 'Fired init_checkout event.' );
        } );
    }
}

new OrderPay();
