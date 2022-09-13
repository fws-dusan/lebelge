<p align='center'>
<img src="https://raw.githubusercontent.com/MajeMediaLLC/MajeMedia-WC-No-PO-Boxes/master/assets/banner-772x250.png" alt='WooCommerce: No PO Boxes' title="Maje Media LLC">
</p>

# WooCommerce: No PO Boxes
Disallows the use of PO Boxes for shipping when using WooCommerce

## Description

Please contact us at support@majemedia.com or go to [https://www.majemedia.com](https://majemedia.com/plugins/no-po-boxes) for plugin support.

Restricts the WooCommerce checkout form for allowing PO Boxes for shipping addresses.

- If same billing/shipping address the customer cannot complete checkout with a PO Box as a billing/shipping address.
- If the customer is shipping to a different physical address they cannot use a PO Box as a shipping address, but can use it as a billing address.
- Does not restrict the use of PO Boxes on Digital/Virtual only carts.
- Requires that WooCommerce shipping is enabled.
- Requires that WooCommerce has a shipping method setup (can be free shipping only).

## Installation
1. Have WooCommerce installed and activated
1. Enable shipping
1. Add this plugin or upload the .zip file (downloaded from here)
1. Activate plugin
1. Go to the settings screen (can be found under the WooCommerce menu as "No PO Boxes")
1. Insert your custom messages
1. Click "Save Changes"

## Screenshots

<p align='center'>
<img width="32%" src="https://raw.githubusercontent.com/MajeMediaLLC/MajeMedia-WC-No-PO-Boxes/master/assets/screenshot-1.png" alt='Screenshot of billing only on checkout' title="When shipping to the same billing address the billing address fields are reviewed">
<img width="32%" src="https://raw.githubusercontent.com/MajeMediaLLC/MajeMedia-WC-No-PO-Boxes/master/assets/screenshot-2.png" alt='Screenshot of separate shipping on checkout' title="When shipping to a different address only the shipping side is reviewed">
<img width="32%" src="https://raw.githubusercontent.com/MajeMediaLLC/MajeMedia-WC-No-PO-Boxes/master/assets/screenshot-3.png" alt='Screenshot of settings page' title="Settings page with configurable messaging (no html markup allowed)">
</p>

## Extending

### Filter: `mwnpb_restrict_shipping_method`
[example](https://www.majemedia.com/plugins/no-po-boxes/#mwnpb_restrict_shipping_method)

    add_filter( 'mwnpb_restrict_shipping_method', 'mwnpb_restrict_shipping_method_example', 10, 3 );
    function mwnpb_restrict_shipping_method_example( $restrict_shipping_method = FALSE ) {
    
        /*
         * This filter defaults to FALSE. And only fires when a shipping method is NOT
         * restricted from P.O. Box shipping.
         * 
         * This allows you to have exceptions in specific situations to not allow P.O. Box shipping 
         * to a shipping method that would normally allow it.
        */
    
        // Set $restrict_shipping_method to TRUE to disallow P.O. Boxes
        // (DEFAULT) Set $restrict_shipping_method to FALSE to allow P.O. Boxes
    
        return $restrict_shipping_method;
    
    }

### Filter: `mwnpb_allow_pobox`
[example](https://www.majemedia.com/plugins/no-po-boxes/#mwnpb_allow_pobox)

    add_filter( 'mwnpb_allow_pobox', 'mwnpb_allow_pobox_example', 10, 3 );
    function mwnpb_allow_pobox_example( $allow_po_box = FALSE ) {
    
        /*
         * This filter defaults to FALSE. It is fired immediately before stopping the
         * checkout process from continuing.
        */
        
        // Set $allow_po_box to TRUE to allow a P.O. Box ship to address in specific situations
        // (DEFAULT) Set $allow_po_box to FALSE to disallow a P.O. Box from being shipped to.
    
        return $allow_po_box;
    
    }

### Filter: `mmwc_restricted_message`
[example](https://majemedia.com/plugins/no-po-boxes/#mmwc_restricted_message)

    add_filter( 'mmwc_restricted_message', 'mmwc_restricted_message_example', 10, 3 );
    function mmwc_restricted_message_example( $message, $restricted_string, $field_with_restricted_string ) {
    
        // use $restricted_string to customize $message based on different conditions. 
        // use $field_with_restricted_string to customize the message based on what field the restriction occurred in
    
        $message = 'This is the message I want to display now instead of the saved one from the dashboard';
    
        return $message;
    
    }

### Filter: `mmwc_restricted_words`
[example](https://majemedia.com/plugins/no-po-boxes/#mmwc_restricted_words)

    add_filter( 'mmwc_restricted_words', 'mmwc_restricted_words_example' );
    function mmwc_restricted_words_example( $words ) {
    
        /*
         * You'll need to modify this example function since a number of different filtering options are being used.
         */
    
        // Remove options by word (has to be exact to what is in `MajeMedia_WC_No_Po_Checkout::RestrictedStrings()` );
        if ( ( $key = array_search( 'word I do not want to filter', $words ) ) !== FALSE ) {
            unset( $words[ $key ] );
        }
    
        // Remove strings by array_key from `MajeMedia_WC_No_Po_Checkout::RestrictedStrings()`
        unset( $words[ 0 ] ); // unsets "po box"
    
        // Add an additional string
        $words[] = 'my new restricted string';
        
        // Add multiple additional strings
        $words[] = 'new string 1';
        $words[] = 'new string 2';
    
        // delete all restricted strings from default plugin and define your own
        $words = array( 'my restriction 1', 'my restriction 2' );
    
        return $words;
    
    }

## Changelog
### 2.1.2
* Fixed for working with WordPress 5.5.x
* Fixed for working with WooCommerce 4.5.x
* Updated version

### 2.1.1
* Added "p o box" to restricted strings
* Updated WordPress version tested against to 5.4 branch
* Updated WooCommerce version tested against to 4.3.0

### 2.1.0
* Github #17: Plugin is now semi-comptible with WooCommerce's official USPS shipping plugin. Please see github issue referenced for limits of the compatibility.

### 2.0.9
* Github #16: Updated the settings screen display of shipping methods to display the title of the shipping method instead of the shipping method type title.

### 2.0.8
* Tested to WordPress 5.3
* Tested to WooCommerce 3.8.1
* Fixed issue causing checkouts to go through with PO Boxes in limited cases

### 2.0.7
* Changed .org repo name
* Added new banner image to assets
* Updated README.md
* moved screenshots to assets directory

### 2.0.6
* Remoed mailto link from readme.txt

### 2.0.5
* Removed mailto link cause it doesn't work

### 2.0.4
* Updated screenshots
* Updated readme changelog
* Updated github repo link
* Updated Description with links

### 2.0.3
* Bumped tested to versions of WordPress & WooCommerce

### 2.0.2 
* Fix: Used the wrong function for translation

### 2.0.1
* Fix: Accidentally used a PHP7.0 only argument. Fixed to allow for php 5.3+
* Fix: Settings page was throwing notices when new, unsaved shipping methods were displayed

### 2.0.0
* New: Restrict by Shipping Method per Shipping Zone
* New: Filter: [mwnpb_restrict_shipping_method](https://www.majemedia.com/plugins/no-po-boxes/#mwnpb_restrict_shipping_method)
* New: Filter: [mwnpb_allow_pobox](https://www.majemedia.com/plugins/no-po-boxes/#mwnpb_allow_pobox)
* Update: Updated Plugin Name to "WooCommerce: No PO Boxes"
* Maintenance: Refactored Code and usage in preparation for switch to minimally supporting 5.6
* Meta: readme information to claim 5.6 required. Not yet required. Just setting the stage

### 1.1.12
* Tested up to WordPress 4.9
* Tested up to WooCommerce 3.2.3
* Added the following words to the deny list: PO. Box, PO.Box, P.O Box, P.O

### 1.1.11
* Forgot to update version in main php file... fixing to allow for updates in sites using

### 1.1.10
* Fix: Plugin now puts all restricted strings to lowercase instead of assuming they are
* Tested to WordPress version 4.8.2
* Added WooCommerce version check strings to main php file

### 1.1.9
* Fix: Plugin options getting deleted upon plugin deactivation.
* Updated: Plugin options are deleted upon uninstall.
* Updated: Added link to documentation on settings page
* Updated: Default functionality status: On (previously disabled upon installation)
* Tested to Wordpress veresion 4.8.1

### 1.1.8:
* Tested to WordPress version 4.8

### 1.1.7:
* Tested to WordPress version 4.7.1

### 1.1.6:
* Tested to WordPress version 4.7

### 1.1.5:
* Tested to WordPress version 4.6.1

### 1.1.4:
* Updated tested to WordPress version.

### 1.1.3:
* Updated compatibility to WordPress 4.5
* Tested against WooCommerce 2.5.x

### 1.1.2:
* Added instruction text for cases where WooCommerce shipping is not enabled

### 1.1.1:
* Changed to using plain text text domain instead of class constant

### 1.1.0:
* Added filter `mmwc_restricted_message`
* Added filter `mmwc_restricted_words`

### 1.0.2:
* Added the ability to turn on and off the restriction (off by default)
* Added Settings link to the Plugin listing page

### 1.0.1:
* Updated documentation

### 1.0.0:
* Created plugin
