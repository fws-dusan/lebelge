import DataService from './DataService';

class LoggingService {
    static logError( message: string ) {
        LoggingService.log( `${message} ⚠️`, true );
    }

    static logNotice( message: string ) {
        LoggingService.log( `${message} ℹ️` );
    }

    static logEvent( message: string ) {
        LoggingService.log( `${message} 🔈` );
    }

    static log( message: string, force: boolean = false ) {
        if ( force || DataService.getCheckoutParam( 'cfw_debug_mode' ) ) {
            // eslint-disable-next-line no-console
            console.log( `CheckoutWC: ${message}` );
        }
    }
}

export default LoggingService;
