import cfwDomReady            from '../_functions';
import FieldToggler           from './components/FieldToggler';
import FontSelector           from './components/FontSelector';
import ImagePicker            from './components/ImagePicker';
import OrderBumpsAdmin        from './components/OrderBumpsAdmin';
import RichEditor             from './components/RichEditor';
import SettingsExporterButton from './components/SettingsExporterButton';
import SettingsImporterButton from './components/SettingsImporterButton';
import TrustBadgeRepeater     from './components/TrustBadgeRepeater';

cfwDomReady( () => {
    /**
     * Code Editors
     */
    // Header Scripts
    new RichEditor( '#_cfw__settingheader_scriptsstring' );

    // Footer Scripts
    new RichEditor( '#_cfw__settingfooter_scriptsstring' );

    // Custom CSS
    new RichEditor( '#cfw_css_editor textarea.wp-editor-area', 'css' );

    // PHP Snippets
    new RichEditor( '#_cfw__settingphp_snippetsstring', 'php' );

    /**
     * Color Pickers
     */
    jQuery( '.cfw-admin-color-picker' ).wpColorPicker();

    /**
     * Font Selectors
     */
    new FontSelector( '#cfw-body-font-selector' );
    new FontSelector( '#cfw-heading-font-selector' );

    /**
     * Settings Export / Import
     */
    new SettingsExporterButton( '#export_settings_button' );
    new SettingsImporterButton( '#import_settings_button' );

    /**
     * Toggled Field Sections
     */
    new FieldToggler( '#cfw_checkbox_enable_cart_editing', '#cart_edit_empty_cart_redirect' );
    new FieldToggler( '#cfw_checkbox_enable_thank_you_page', '#cfw_checkbox_enable_map_embed, #thank_you_order_statuses, #cfw_checkbox_override_view_order_template' );
    new FieldToggler( '#cfw_checkbox_enable_trust_badges', '#trust_badges_title, .cfw-admin-trust-badge-row:not(.cfw-admin-trust-badge-template-row), .cfw-admin-add-trust-badge-row-button' );
    new FieldToggler( '#cfw_checkbox_enable_smartystreets_integration', '#smartystreets_auth_id, #smartystreets_auth_token' );
    new FieldToggler( '#cfw_checkbox_enable_fetchify_address_autocomplete', '#fetchify_access_token' );

    /**
     * Image Pickers
     */
    new ImagePicker( '.cfw-admin-image-picker-button' );

    /**
     * Trust Badge Repeater
     */
    new TrustBadgeRepeater();

    /**
     * Order Bumps Metaboxes
     */
    new OrderBumpsAdmin();

    // Enable Select2
    jQuery( document.body ).trigger( 'wc-enhanced-select-init' );

    /**
     * Order Bumps Form Validation
     */
    // Initialize form validation on the registration form.
    // It has the name attribute "registration"
    jQuery( '.post-type-cfw_order_bumps form#post' ).validate( {
        // Specify validation rules
        rules: {
            // The key name on the left side is the name attribute
            // of an input field. Validation rules are defined
            // on the right side
            'cfw_ob_categories[]': {
                required() {
                    return jQuery( '#cfw_ob_display_for option:selected' ).val() === 'specific_categories';
                },
            },
            'cfw_ob_products[]': {
                required() {
                    return jQuery( '#cfw_ob_display_for option:selected' ).val() === 'specific_products';
                },
            },
            cfw_ob_offer_discount: {
                required: true,
                number: true,
            },
            cfw_ob_offer_product: {
                required: true,
            },
            cfw_ob_offer_language: {
                required: true,
            },
            cfw_ob_offer_description: {
                required: true,
            },
            // lastname: 'required',
            // email: {
            //     required: true,
            //     // Specify that email should be validated
            //     // by the built-in "email" rule
            //     email: true,
            // },
            // password: {
            //     required: true,
            //     minlength: 5,
            // },
        },
        // Specify validation error messages
        messages: {
            'cfw_ob_categories[]': 'You must specify at least one category.',
            'cfw_ob_products[]': 'You must specify at least one product.',
            cfw_ob_offer_discount: 'Discount value must be a number. Example: 10, or 10.00',
            cfw_ob_offer_product: 'You must specify an offer product.',
            // password: {
            //     required: 'Please provide a password',
            //     minlength: 'Your password must be at least 5 characters long',
            // },
            // email: 'Please enter a valid email address',
        },
        focusInvalid: false,
        invalidHandler( form, validator ) {
            if ( !validator.numberOfInvalids() ) return;

            jQuery( 'html, body' ).animate( {
                scrollTop: jQuery( validator.errorList[ 0 ].element ).offset().top,
            }, 300 );
        },
        errorPlacement( error, element ) {
            error.appendTo( element.closest( 'td' ) );
        },
        // Make sure the form is submitted to the destination defined
        // in the "action" attribute of the form when valid
        submitHandler( form ) {
            form.submit();
        },
    } );
} );
