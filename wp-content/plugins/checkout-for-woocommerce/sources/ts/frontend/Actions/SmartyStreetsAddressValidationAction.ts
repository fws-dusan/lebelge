import { AjaxInfo, SmartyStreetsValidationData } from '../Types/Types';
import Action                                    from './Action';

/**
 *
 */
class SmartyStreetsAddressValidationAction extends Action {
    /**
     *
     * @param id
     * @param ajaxInfo
     * @param address
     */
    constructor( id: string, ajaxInfo: AjaxInfo, address: object ) {
        const data: SmartyStreetsValidationData = {
            'wc-ajax': id,
            address,
        };

        super( id, data );
    }

    /**
     *
     * @param resp
     */
    public response( resp: any ): void {
        // Silence is golden
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        // eslint-disable-next-line no-console
        console.log( `SmartyStreets Address Validation Error: ${errorThrown} (${textStatus})` );
    }
}

export default SmartyStreetsAddressValidationAction;
