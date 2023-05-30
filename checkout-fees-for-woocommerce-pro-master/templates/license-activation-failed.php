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

		<?php /* translators: %1$s: plugin name %2$s: purchase history %3$s: site name */ ?>
		<p><?php sprintf( esc_html_e( 'Enter your %1$s License Key below. Your key unlocks access to automatic updates and support. You can find your key on the %2$s page on the %3$s site.', 'checkout-fees-for-woocommerce' ), $plugin_name, $purchase_history, $site_name ); ?></p>

		<p>
			<input id='license_key' name='license_key' type='text' class='regular-text' style='font-size:14pt;' placeholder='Enter Your License Key' />
			<br>
			<span style='font-size:8pt; color:#b00606;'><i class='fa fa-times'></i>&nbsp;<?php esc_html_e( 'Invalid or Expired License Key. Please make sure you have entered the correct value and that your key is not expired.', 'checkout-fees-for-woocommerce' ); ?></span>
			<input type='hidden' id='cf_license_display' name='cf_license_display' value='2' />
			<input type='hidden' id='edd_license_cf_hidden_button' name='edd_license_cf_hidden_button' value="<?php esc_html_e( 'Activate License' ); ?>" />
			<?php wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ); ?>
		</p>
		<p>
			<?php /* translators: %1$s: plugin_name */ ?>
			<?php sprintf( esc_html_e( 'If you don\'t enter a valid license key, you will not able to update %1$s when important bug fixes and security enhancements are released. This can be a serious security risk for your site.', 'checkout-fees-for-woocommerce' ), $plugin_name ); ?>  
		</p>
		<p style='font-size:14pt;'>
			<input type='checkbox' value='1' id='cf_accept_terms' name='cf_accept_terms' /><?php esc_html_e( 'I understand the risks', 'checkout-fees-for-woocommerce' ); ?>
			<span style='color:#b00606;'>*</span><br>
			<span style='font-size:8pt; color:#b00606;' ><?php esc_html_e( 'Please accept the terms.', 'checkout-fees-for-woocommerce' ); ?></span>
		</p>
		<p><button type='submit' class='button-primary'><?php esc_html_e( 'Next', 'checkout-fees-for-woocommerce' ); ?></button></p>
	</div>
</div>
</form>
