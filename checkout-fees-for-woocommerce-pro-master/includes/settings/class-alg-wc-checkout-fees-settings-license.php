<?php
/**
 * Currency Per Product for WooCommerce - License Settings Section
 *
 * @version 1.4.3
 * @since   1.4.3
 * @author  Tyche Softwares
 *
 * @package checkout-fees-for-woocommerce-pro/settings/license
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Checkout_Fees_Settings_License' ) ) :

	/**
	 * License settings.
	 */
	class Alg_WC_Checkout_Fees_Settings_License extends Alg_WC_Checkout_Fees_Settings_Section {

		/**
		 * Constructor.
		 *
		 * @version 1.4.3
		 * @since   1.4.3
		 */
		public function __construct() {
			$this->id   = 'license';
			$this->desc = __( 'License', 'checkout-fees-for-woocommerce' );
			parent::__construct();
		}

		/**
		 * Add License settings section.
		 *
		 * @param array $sections Sections array.
		 * @version 1.4.3
		 * @since   1.4.3
		 */
		public function settings_section( $sections ) {
			$sections[ $this->id ] = $this->desc;
			return $sections;
		}

		/**
		 * Add settings.
		 *
		 * @version 1.4.3
		 * @since   1.4.3
		 */
		public function get_settings() {

			$license      = get_option( 'edd_license_key_cf' );
			$status       = get_option( 'edd_license_key_cf_status' );
			$http 		  = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http'; // phpcs:ignore
			$http_host    = isset( $_SERVER['HTTP_HOST'] ) ? esc_url( $http . '://' . $_SERVER['HTTP_HOST'] ) : ''; // phpcs:ignore
			$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore
			$current_link = $http_host . $request_uri;

			$link = '';
			if ( false !== $license ) {
				if ( false !== $status && 'valid' === $status ) {
					$link = '<span style="color:green;">active</span>' .
					wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ) .
					'<a href="' . $current_link . '&license=cf_deactivate" class="button-secondary" name="edd_cf_license_deactivate">Deactivate<a/>';
				} else {
					$link = wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ) .
					'<a href="' . $current_link . '&license=cf_activate" class="button-secondary" name="edd_cf_license_activate">Activate<a/>';
				}
			}

			$license_settings = array(
				array(
					'title' => __( 'Plugin License Options', 'checkout-fees-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'alg_wc_cf_license_options',
				),
				array(
					'title'   => __( 'License Key	', 'checkout-fees-for-woocommerce' ),
					'desc'    => __( 'Enter your license key.', 'checkout-fees-for-woocommerce' ),
					'id'      => 'edd_license_key_cf',
					'default' => '',
					'type'    => 'text',
				),
				array(
					'title'   => __( 'Activate License', 'checkout-fees-for-woocommerce' ),
					'desc'    => __( $link, 'checkout-fees-for-woocommerce' ),// phpcs:ignore
					'id'      => 'edd_license_cf_hidden_button',
					'default' => '',
					'type'    => 'text',
					'css'     => 'display:none;',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'alg_wc_cf_license_options',
				),
			);

			return $license_settings;
		}

	}

endif;

return new Alg_WC_Checkout_Fees_Settings_License();
