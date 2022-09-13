<?php
call_user_func(static function() {

    $cartTypeOption = 'wnsm_msg_cart_type';
    $cartTextOption = 'wnsm_msg_cart_text';
    $checkoutTypeOption = 'wnsm_msg_checkout_type';
    $checkoutTextOption = 'wnsm_msg_checkout_text';

    $legacyTypeOption = 'wnsm_no_shipping_message_type';
    $legacyTextOption = 'wnsm_no_shipping_message';

    if (get_option($cartTypeOption, null) === null && get_option($cartTextOption, null) === null &&
        get_option($checkoutTypeOption, null) === null && get_option($checkoutTextOption, null) === null) {

        $type = get_option($legacyTypeOption, null);
        if (isset($type) && in_array($type, ['text', 'html'], true)) {
            update_option($cartTypeOption, $type);
            update_option($checkoutTypeOption, $type);
        }

        $text = get_option($legacyTextOption, null);
        if (isset($text)) {
            update_option($cartTextOption, $text);
            update_option($checkoutTextOption, $text);
        }
    }

    delete_option($legacyTypeOption);
    delete_option($legacyTextOption);
});