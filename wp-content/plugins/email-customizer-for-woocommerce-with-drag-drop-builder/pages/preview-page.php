<?php
if ( ! current_user_can( 'manage_woocommerce' ) )
	die();

global $wp_scripts, $woocommerce, $wpdb, $current_user, $order, $ec_woo_preview_mail;
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="<?php echo 'Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'); ?>" />
	<title>
		EC WOO Prevew Email
	</title>

	<?php

	print_head_scripts(); //This is the main one
	print_admin_styles();
	?>
</head>
<body id="ecwoo-template" class="ecwoo-template" >

	<?php
	$mails = $woocommerce->mailer()->get_emails();

	$woocommerce->payment_gateways();
	$woocommerce->shipping();

	if ( isset( $_REQUEST['ecwoo_email_type'] ) && $_REQUEST['ecwoo_email_type'] == sanitize_text_field( $_REQUEST['ecwoo_email_type'] ) ) {
		$email_type = $_REQUEST['ecwoo_email_type'];
	}
	else {
		$email_type = current( $mails )->id;
	}

	if ( isset( $_REQUEST['ecwoo_email_order'] ) ) {

		$order_id_to_show = $_REQUEST['ecwoo_email_order'];
	}
	else{

		$order_collection = new WP_Query(array(
			'post_type'			=> 'shop_order',
			'post_status'		=> array_keys( wc_get_order_statuses() ),
			'posts_per_page'	=> 1,
		));
		$order_collection = $order_collection->posts;
		$latest_order = current($order_collection)->ID;
		$order_id_to_show = $latest_order;
	}

	if ( ! get_post( $order_id_to_show ) ) :

		?>
		<div class="email-template-preview pe-in-admin-page">
			<div class="main-content">
				<div class="compatability-warning-text">
					<span class="dashicons dashicons-welcome-comments"></span>
					<h6><?php _e( "You'll need at least one order to preview all the email types correctly", EC_WOO_BUILDER_TEXTDOMAIN ) ?></h6>
					<p>
						<?php _e( "Simply follow your store's checkout process to create at least one order, then return here to preview all the possible email types.", EC_WOO_BUILDER_TEXTDOMAIN ) ?>
					</p>
				</div>
			</div>
		</div>
		<?php

	else :

		/**
		 * Display the chosen email.
		 */

		// prep the order.
		$order = wc_get_order( $order_id_to_show );

		if ( ! empty( $mails ) ) {
			foreach ( $mails as $mail ) {

				if ( $mail->id == $email_type ) {

					// Important Step: populates the $mail object with the necessary properties for it to Preview (or Send a test).
					// It also returns a BOOLEAN for whether we have checked this email types preview with our plugin.
					$compat_warning = $ec_woo_preview_mail->populate_mail_object( $order, $mail );

					// Info Meta Swicth on /off
					$header = ( get_user_meta( $current_user->ID, 'header_info_userspecifc', true) ) ? get_user_meta( $current_user->ID, 'header_info_userspecifc', true ) : 'off' ;
					?>

					<div class="email-template-preview pe-in-admin-page">
						<div class="main-content">

							<?php if ( $compat_warning && ( $mail->id !== $_REQUEST['ecwoo_approve_preview'] ) ) : ?>

								<?php echo 'We cannot show third-party email templates in email preview. If you want to see the result please send email from your website according to email type ' ?>

							<?php else: ?>

								<!-- EMAIL TEMPLATE -->

								<?php
								// Get the email contents. using @ to block ugly error messages showing in the preview.
								// The following mimics what WooCommerce does in it's `->send` method of WC_Email.
								@ $email_message = $mail->get_content();
								$email_message   = $mail->style_inline( $email_message );
								$email_message   = apply_filters( 'woocommerce_mail_content', $email_message );

								// Convert line breaks to <br>'s if the mail is type 'plain'.
								if ( 'plain' === $mail->email_type )
									$email_message = '<div style="padding: 35px 40px; background-color: white;">' . str_replace( "\n", '<br/>', $email_message ) . '</div>';

								// Display the email.
								echo $email_message;
								?>

								<!-- / EMAIL TEMPLATE -->

							<?php endif; ?>

						</div>
					</div>

					<?php
				}
			}
		}

	endif;
	?>
</body>
</html>
<?php exit; ?>