<?php
/*
Plugin Name: WooCommerce Astor Export Format
Plugin URI: http://www.astorchocolate.com/
Description: Astor Chocolate woocommerce plugin
Author: Astor Chocolate
Author URI: http://www.astorchocolate.com/
Version: 1.0.0
*/

function getGUID(){

    if (function_exists('com_create_guid')){

        return com_create_guid();

    } else {

        mt_srand((double)microtime()*10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);
        $uuid = chr(123)
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);
        return $uuid;

    }

}

function log_var($v){
	$to = 'bri@jealousrepublic.com';
	$subject = 'Astor Testing';
	$body = print_r($v, true);
	$headers = array('Content-Type: text/html; charset=UTF-8');
	wp_mail( $to, $subject, $body, $headers );
}


function wc_xml_export_output_custom_format( $output, $order_data ) {

	$originalXML = new SimpleXMLElement($output);
	$guid = getGUID();
	$currentDate = date_create();

	$xml = '<?xml version="1.0" encoding="utf-8"?>
		<!DOCTYPE cXML SYSTEM "http://xml.cxml.org/schemas/cXML/1.2.021/cXML.dtd">
		<cXML payloadID="' . $guid . '" timestamp="' . date_format($currentDate, 'Y-m-d H:i:s') . '" version="1.2.021" xml:lang="en-US">

		  <Header>
		    <From>
		      <Credential domain="NetworkId">
		        <Identity>AWS1001</Identity>
		      </Credential>
		    </From>
		    <To>
		      <Credential domain="NetworkId">
		        <Identity>MTD05132016</Identity>
		      </Credential>
		      <Credential domain="networkid">
		        <Identity>MTD05132016</Identity>
		      </Credential>
		      <Credential domain="partition-psap1">
		        <Identity>MTD05132016</Identity>
		      </Credential>
		    </To>
		    <Sender>
		      <Credential domain="NetworkUserId">
		        <Identity>web@astorchocolate.com</Identity>
		        <SharedSecret>25Ax9z90kSap</SharedSecret>
		      </Credential>
		      <UserAgent>Buyer 9r1</UserAgent>
		    </Sender>
		  </Header>'.PHP_EOL;

	foreach($originalXML as $order) {

	$xml .= '<Request deploymentMode="production">

			    <OrderRequest>
			      <OrderRequestHeader orderDate="' . $order->OrderDate . '" orderID="' . $order->OrderId . '" orderType="regular" orderVersion="1" type="new">
			        <Total>
			          <Money alternateAmount="" alternateCurrency="" currency="' . $order->OrderCurrency . '">' . $order->OrderTotal . '</Money>
			        </Total>

			        <ShipTo>

			          <Address addressID="1" isoCountryCode="' . $order->ShippingCountry . '">

						<Name xml:lang="en">' . $order->ShippingFullName . '</Name>

			            <PostalAddress name="default">
			              <DeliverTo>' . $order->ShippingFullName . '</DeliverTo>
			              <Street>' . $order->ShippingAddress1 . '</Street>
			              <Street2>' .  $order->ShippingAddress2 . '</Street2>
			              <City>' .  $order->ShippingCity . '</City>
			              <State>' . $order->ShippingState . '</State>
			              <PostalCode>' . $order->ShippingPostcode . '</PostalCode>
			              <Country isoCountryCode="' . $order->ShippingCountry . '"></Country>
			            </PostalAddress>

			            <Email name="default" preferredLang="' . $order->ShippingCountry . '">' . $order->BillingEmail . '</Email>

			          </Address>

			        </ShipTo>

			        <BillTo>
			          <Address addressID="1" isoCountryCode="' . $order->BillingCountry . '">

			            <Name xml:lang="en">' . $order->BillingFullName . '</Name>

			            <PostalAddress name="default">
			              <Street>' . $order->BillingAddress1 . '</Street>
			              <Street2>' .  $order->BillingAddress2 . '</Street2>
			              <City>' . $order->BillingCity . '</City>
			              <State>' . $order->BillingState . '</State>
			              <PostalCode>' . $order->BillingPostcode . '</PostalCode>
			              <Country isoCountryCode="' . $order->BillingCountry . '"></Country>
			            </PostalAddress>

			          </Address>
			        </BillTo>

					<Extrinsic name="source">Astor Web Site</Extrinsic>
					<Extrinsic name="vendorIDNo">ASTORCHOCO_001</Extrinsic>

					<Comments></Comments>

			      </OrderRequestHeader>'.PHP_EOL;



			      foreach($order->OrderLineItems as $orderItems) {

				      foreach($orderItems->OrderLineItem as $orderItem) {

					      $pID = (string)$orderItem->ProductId[0];
						  $selluom = get_post_meta($pID, '_uom_sku', true);
						  $mintedsku = get_post_meta($pID, '_minted_sku', true);

						  $xml .= '<ItemOut requestedDeliveryDate="" lineNumber="" quantity="' . $orderItem->Quantity . '">

					        <ItemID>
					          <SupplierPartID>' . $orderItem->OrderLineItem->Sku . '</SupplierPartID>
					        </ItemID>

					        <ItemDetail>
							  <SELLUOM>' . $selluom . '</SELLUOM>
							  <MINTEDSKU>' . $mintedsku . '</MINTEDSKU>
					          <UnitPrice>
					            <Money alternateCurrency="" alternateAmount="" currency="">' . $orderItem->Price . '</Money>
					          </UnitPrice>

					          <Description xml:lang="en">' . $orderItem->Name . '</Description>

							  <Option>' . $orderItem->Meta . '</Option>

					          <UnitOfMeasure>CS</UnitOfMeasure>

					          <Classification domain=""></Classification>

					          <ManufacturerPartID>' . $orderItem->Sku . '</ManufacturerPartID>
					          <Extrinsic name="Requester"></Extrinsic>

					        </ItemDetail>

					      </ItemOut>'.PHP_EOL;

				      }

			      }

			    $xml .= '</OrderRequest>'.PHP_EOL;
			  $xml .= '</Request>'.PHP_EOL;

	}

  	$xml .= '</cXML>'.PHP_EOL;

	return $xml;

}

add_filter( 'wc_customer_order_xml_export_suite_generated_xml', 'wc_xml_export_output_custom_format', 10, 2 );