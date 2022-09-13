<?php
if (! defined('ABSPATH')) {
    exit;
}

if (is_null($wcs_data)) {
  return '';
}
$ec_woo_settings_rtl = get_option('ec_woo_settings_rtl', EC_WOO_BUILDER_RTL);

$txt_direction=$ec_woo_settings_rtl?'right':'left';
$interval=$wcs_data->get_billing_interval()==1?'':$wcs_data->get_billing_interval();

?>

<h2>
  <?php _e('Subscription Information', EC_WOO_BUILDER_TEXTDOMAIN); ?>
</h2>

<table  class="ec-woo-wcs-table-info"
  cellspacing="0"
  cellpadding="0"
  style="width: 100% !important;font-family: 'Avenir Next',Avenir,Roboto,'Century Gothic','Franklin Gothic Medium','Helvetica Neue',Helvetica,Arial,sans-serif;"
  border="0">
  <thead>
    <tr>

      <th class="ec-woo-wcs-column-1" width="20%" style="font-weight:bold;text-align:<?php echo $txt_direction; ?>">
        <?php _e('Subscription', EC_WOO_BUILDER_TEXTDOMAIN); ?>
      </th>
      <th class="ec-woo-wcs-column-2" width="17%" style="font-weight:bold;text-align:<?php echo $txt_direction; ?>">
        <?php _e('Status', EC_WOO_BUILDER_TEXTDOMAIN); ?>
      </th>
      <th class="ec-woo-wcs-column-3" width="20%" style="font-weight:bold;text-align:<?php echo $txt_direction; ?>">
        <?php _e('Start Date', EC_WOO_BUILDER_TEXTDOMAIN); ?>
      </th>
      <th class="ec-woo-wcs-column-4" width="20%" style="font-weight:bold;text-align:<?php echo $txt_direction; ?>">
        <?php _e('End Date', EC_WOO_BUILDER_TEXTDOMAIN); ?>
      </th>
      <th class="ec-woo-wcs-column-5" width="23%" style="font-weight:bold;text-align:<?php echo $txt_direction; ?>">
        <?php _e('Price', EC_WOO_BUILDER_TEXTDOMAIN); ?>
      </th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        <?php echo $wcs_data->get_subscription_hyperlink(); ?>
      </td>

      <td>
        <?php echo $wcs_data->get_subscription_status(); ?>
      </td>

      <td>
        <?php echo $wcs_data->get_subscription_start_date(); ?>
      </td>
      <td>
        <?php echo $wcs_data->get_subscription_end_date(); ?>
      </td>

      <td>
        <?php
          echo $order_total.' ';
          echo  _e(' every ', EC_WOO_BUILDER_TEXTDOMAIN).$interval.' '.$wcs_data->get_billing_period();
         ?>
      </td>


    </tr>
  </tbody>
</table>