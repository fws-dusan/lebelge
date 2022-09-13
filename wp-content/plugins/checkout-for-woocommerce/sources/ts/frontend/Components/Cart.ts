import Main                  from '../Main';
import DataService           from '../Services/DataService';

class Cart {
    constructor() {
        this.setUpMobileCartDetailsReveal();
        this.setQuantityStepperTriggers();
        this.setQuantityPromptTriggers();
        this.setManualFireTriggers();
        this.setRemoveItemTriggers();
    }

    setQuantityStepperTriggers(): void {
        jQuery( document.body ).on( 'click', '.cfw-quantity-stepper-btn-minus', ( event ) => {
            const element = jQuery( event.currentTarget );
            const quantityValue = element.siblings( '.cfw-edit-item-quantity-value' ).first();
            const quantityLabel = element.parents( '.cart-item-row' ).find( '.cfw-cart-item-quantity-bubble' ).first();
            let newQuantity = Number( quantityValue.val() ) - Number( jQuery( quantityValue ).data( 'step' ) );
            const minQuantity = Number( jQuery( quantityValue ).data( 'min-value' ) );

            if ( newQuantity > 0 && newQuantity < minQuantity ) {
                newQuantity = minQuantity;
            }

            if ( newQuantity > 0 || ( <any>window ).confirm( DataService.getSetting( 'delete_confirm_message' ) ) ) {
                quantityValue.val( newQuantity );
                quantityLabel.text( newQuantity );

                this.setUpdateCartFlag();

                Main.instance.updateCheckoutService.queueUpdateCheckout();
            }
        } );

        jQuery( document.body ).on( 'click', '.cfw-quantity-stepper-btn-plus:not(.maxed)', ( event ) => {
            const element = jQuery( event.currentTarget );
            const quantityValue = element.siblings( '.cfw-edit-item-quantity-value' ).first();
            const quantityLabel = element.parents( '.cart-item-row' ).find( '.cfw-cart-item-quantity-bubble' ).first();
            const maxQuantity = Number( jQuery( quantityValue ).data( 'max-quantity' ) );
            let newQuantity = Number( quantityValue.val() ) + Number( jQuery( quantityValue ).data( 'step' ) );

            if ( newQuantity > maxQuantity ) {
                newQuantity = maxQuantity;
            }

            if ( newQuantity <= maxQuantity ) {
                quantityValue.val( newQuantity );
                quantityLabel.text( newQuantity );

                this.setUpdateCartFlag();

                Main.instance.updateCheckoutService.queueUpdateCheckout();
            }
        } );
    }

    /**
     *
     */
    setQuantityPromptTriggers(): void {
        jQuery( document.body ).on( 'click', '.cfw-quantity-bulk-edit', ( event ) => {
            const element = jQuery( event.currentTarget );
            const response = ( <any>window ).prompt( DataService.getSetting( 'quantity_prompt_message' ), element.data( 'quantity' ) );

            // If we have input
            if ( response !== null ) {
                const newQuantity = Number( response );

                if ( newQuantity > 0 || ( <any>window ).confirm( DataService.getSetting( 'delete_confirm_message' ) ) ) {
                    element.siblings( '.cfw-quantity-stepper' ).find( '.cfw-edit-item-quantity-value' ).val( newQuantity );

                    this.setUpdateCartFlag();

                    Main.instance.updateCheckoutService.queueUpdateCheckout();
                }
            }
        } );
    }

    setRemoveItemTriggers(): void {
        jQuery( document.body ).on( 'click', '.cfw-quantity-remove-item', ( event ) => {
            const element = jQuery( event.currentTarget );
            const response = ( <any>window ).confirm( DataService.getSetting( 'delete_confirm_message' ) );

            // If we have input
            if ( response !== null ) {
                const newQuantity = Number( 0 );

                element.parent().find( '.cfw-edit-item-quantity-value' ).val( newQuantity );

                this.setUpdateCartFlag();

                Main.instance.updateCheckoutService.queueUpdateCheckout();
            }
        } );
    }

    /**
     *
     */
    setUpMobileCartDetailsReveal(): void {
        const showCartDetails = jQuery( '#cfw-mobile-cart-header' );

        showCartDetails.on( 'click', ( e ) => {
            e.preventDefault();
            jQuery( '#cfw-cart-summary-content' ).slideToggle( 300 );
            jQuery( '#cfw-expand-cart' ).toggleClass( 'active' );
        } );
    }

    setManualFireTriggers(): void {
        jQuery( document.body ).on( 'cfw-fire-udpate-cart-action', () => {
            this.setUpdateCartFlag();
            Main.instance.updateCheckoutService.queueUpdateCheckout();
        } );
    }

    setUpdateCartFlag(): void {
        jQuery( '[name="cfw_update_cart"]' ).val( 'true' );
    }
}

export default Cart;
