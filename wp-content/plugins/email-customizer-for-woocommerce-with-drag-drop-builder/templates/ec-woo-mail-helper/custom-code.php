<?php
/**
 * Custom code shortcode
 *
 * @var $order WooCommerce order
 * @var $email_id WooCommerce email id (new_order, completed_order,etc)
 * @var $attr array custom code attributes
 *
 * IMPORTANT NOTE:
 * After adding custom shortcode, you will not see the result during customizing,
 * If you want to test it, just click 'Preview' button (the first button in the top-right menu in the builder interface)
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Example for the short code [ec_woo_custom_code type="demo-purchase"]
 if(isset($attr['type']) && $attr['type'] == 'thank-you-page'){
    //echo '<a href="' . $order ->get_order_number() . '">CLICK ME</a>';
 
    $order_key = $order->get_order_key();
    $order_id = $order->get_order_number();
    
    $thankyouurl = "https://lebelgechocolatier.com/order-received/thank-you/?key=$order_key&order_id=$order_id&ts=1";
    
    echo '<div class="mj-column-per-100 outlook-group-fix" style="vertical-align: top; direction: ltr; font-size: 13px; text-align: left; width: 100%;">
<table style="vertical-align: top;" role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="font-size: 0px; padding: 0px;" align="center">
<table style="border-collapse: separate; width: 100%;" role="presentation" border="0" cellspacing="0" cellpadding="0" align="center">
<tbody>
<tr>
<td style="border: none; border-radius: 6px; color: #ffffff; cursor: auto; padding: 10px 25px;" align="center" valign="middle" bgcolor="#44c2c4"><a style="text-decoration: none; color: #ffffff; font-family: Arial, sans-serif; font-size: 16px; line-height: 16px; font-weight: 600; text-transform: none; margin: 0px;" href="' . $thankyouurl . '" target="_blank" rel="noopener">Click Here to View Your Order</a></td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>';
}


if(isset($attr['type']) && $attr['type'] == 'shipping-type'){
// Get the shipping method related data (we need the Instance ID)
        $shipping_item        = $order->get_items('shipping');
        $item = reset($shipping_item);
        $instance_id   = $item->get_instance_id();
        $method_id = $item->get_method_id();
        
        // Get the Zone ID and related data
        $shipping_zone_object = WC_Shipping_Zones::get_zone_by( 'instance_id', $instance_id );
        $zone_id              = $shipping_zone_object->get_id();        // Zone ID
        $zone_name            = $shipping_zone_object->get_zone_name(); // Zone name
        
        $countries_obj = new WC_Countries();
        $countries_array = $countries_obj->get_countries();
        $country_states_array = $countries_obj->get_states();
        
        $zone_location_code = $order->get_shipping_state();
        
        $state_name = strtoupper($country_states_array['US'][$zone_location_code]);
        $ship_method = strtoupper($order->get_shipping_method());
        
        $zone_ids = array_keys( array('') + WC_Shipping_Zones::get_zones() );
        
        $insulated_html = '<h1 style="text-align: center;"><span style="background-color: #ffff00; color: #44c2c4;"><strong>INSULATION IS REQUIRED</strong></span></h1>
                <h2 style="text-align: center;"><span style="color: #44c2c4;"><strong>%s</strong></span></h2>
                <h2 style="text-align: center;"><span style="color: #44c2c4;"><strong>SHIP VIA: %s</strong></span></h2>
                <table style="border-color: #44c2c4; height: 78px; margin-left: auto; margin-right: auto;" border="5" width="567">
                <tbody>
                <tr style="height: 119.344px;">
                <td style="width: 549px; height: 119.344px; text-align: left; vertical-align: top;">
                <h2 style="text-align: center;"><span style="color: #44c2c4;">ASTOR SHIPPING DEPARTMENT OVERRIDE:</span></h2>
                </td></tr></tbody></table>';
        
        $non_insulated_html = '<h1 style="text-align: center;"><span style="color: #44c2c4;"><strong>NON-INSULATED</strong></span></h1>
            <h2 style="text-align: center;"><span style="color: #44c2c4;"><strong>%s</strong></span></h2>
            <h2 style="text-align: center;"><span style="color: #44c2c4;"><strong>SHIP VIA: %s</strong></span></h2>
            <table style="border-color: #44c2c4; height: 78px; margin-left: auto; margin-right: auto;" border="5" width="567">
            <tbody>
            <tr style="height: 119.344px;">
            <td style="width: 549px; height: 119.344px; text-align: left; vertical-align: top;">
            <h2 style="text-align: center;"><span style="color: #44c2c4;">ASTOR SHIPPING DEPARTMENT OVERRIDE:</span></h2>
            </td></tr></tbody></table>';

        if ( 'SHIP 1 - Insulated Ground, 2 Day and Next Day' == $zone_name ||
             'SHIP 2 - Insulated 2 Day, Next Day' == $zone_name ||
             'SHIP 3 - Insulated Next Day' == $zone_name ||
             'SHIP 7 - Insulated Hawaii' == $zone_name) {
            echo sprintf($insulated_html, $state_name, $ship_method);
        }
        elseif ('SHIP 4 - Non-Insulated Ground, 2 Day and Next Day' == $zone_name ||
            'SHIP 5 - Non-Insulated 2 Day, Next Day' == $zone_name ||
            'SHIP 6 - Non-Insulated Next Day' == $zone_name ||
            'NJ - Ground, Next Day' == $zone_name ||
            'SHIP 8 - Alaksa' == $zone_name){
            echo sprintf($non_insulated_html, $state_name, $ship_method);
        }
    }


?>