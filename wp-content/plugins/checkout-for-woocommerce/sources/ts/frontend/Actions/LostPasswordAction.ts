import { Alert, AlertInfo }    from '../Components/Alert';
import Main                    from '../Main';
import { AjaxInfo }            from '../Types/Types';
import Action                  from './Action';

/**
 *
 */
class LostPasswordAction extends Action {
    /**
     *
     * @param id
     * @param ajaxInfo
     * @param fields
     */
    constructor( id: string, ajaxInfo: AjaxInfo, fields: any ) {
        let data = {
            'wc-ajax': id,
            fields,
        };

        data = {
            ...data,
        };

        super( id, data );
    }

    /**
     *
     * @param resp
     */
    public response( resp: any ): void {
        if ( typeof resp !== 'object' ) {
            // eslint-disable-next-line no-param-reassign
            resp = JSON.parse( resp );
        }

        jQuery( '#cfw_lost_password_form' ).replaceWith( resp.message );
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        const alertInfo: AlertInfo = {
            type: 'error',
            message: `An error occurred during login. Error: ${errorThrown} (${textStatus})`,
            cssClass: 'cfw-alert-error',
        };

        const alert: Alert = new Alert( Main.instance.alertContainer, alertInfo );
        alert.addAlert();
    }
}

export default LostPasswordAction;
