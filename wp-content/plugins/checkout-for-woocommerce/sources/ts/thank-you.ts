import 'core-js/features/object/assign';
import 'ts-polyfill';
import cfwDomReady     from './_functions';
import Cart            from './frontend/Components/Cart';
import DataService     from './frontend/Services/DataService';
import MapEmbedService from './frontend/Services/MapEmbedService';

class ThankYou {
    constructor() {
        const map_embed_service = new MapEmbedService();

        cfwDomReady( () => {
            map_embed_service.setMapEmbedHandlers();

            jQuery( '.status-step-selected' ).prevAll().addClass( 'status-step-selected' );

            // Init runtime params
            DataService.initRunTimeParams();

            // Cart Service
            new Cart();

            jQuery( window ).on( 'load', () => {
                jQuery( '#wpadminbar' ).appendTo( 'html' );
            } );
        } );
    }
}

new ThankYou();
