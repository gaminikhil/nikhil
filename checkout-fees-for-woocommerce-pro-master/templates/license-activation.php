<?php
/**
 * Welcome page on activate or updation of the plugin
 *
 * @package checkout-fees-for-woocommerce-pro/templates
 */

?>
<form method="post">
	<div class="wrap about-wrap">
		<?php echo wp_kses_post( $get_welcome_header ); // phpcs:ignore ?>

	<hr>
		<div>
			<p style="font-size:28pt; text-align:center;"><?php esc_html_e( 'License Key', 'checkout-fees-for-woocommerce' ); ?></p>
			<p> <?php _e( "Enter your $plugin_name License Key below. Your key unlocks access to automatic updates and support. You can find your key on the $purchase_history page on the $site_name site.", 'checkout-fees-for-woocommerce' );// phpcs:ignore ?>
			</p>
			<p>
				<input id='license_key' name='license_key' type='text' class='regular-text' style='font-size:14pt;' placeholder='Enter Your License Key' />
				<input type='hidden' id='cf_license_display' name='cf_license_display' value='1' />
				<input type='hidden' id='edd_license_cf_hidden_button' name='edd_license_cf_hidden_button' value="<?php esc_html_e( 'Activate License', 'checkout-fees-for-woocommerce' ); ?>" />
				<?php wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ); ?>
				</p>

			<p><button type='submit' class='button-primary'><?php esc_html_e( 'Next', 'checkout-fees-for-woocommerce' ); ?></button></p>
		</div>
	</div>
</form>
