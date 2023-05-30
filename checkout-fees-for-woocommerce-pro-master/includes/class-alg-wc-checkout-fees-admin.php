<?php
/**
 * Checkout Fees for WooCommerce - Admin
 *
 * @version 2.5.0
 * @since   2.5.0
 * @author  Tyche Softwares
 *
 * @package checkout-fees-for-woocommerce-pro/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Checkout_Fees_Admin' ) ) :

	/**
	 * Delete plugin data.
	 */
	class Alg_WC_Checkout_Fees_Admin {

		/**
		 * Constructor.
		 *
		 * @version 2.5.0
		 * @since   2.5.0
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'maybe_delete_all_plugin_data' ), PHP_INT_MAX );
		}

		/**
		 * Admin_notice_delete_all_plugin_data_success.
		 *
		 * @version 2.5.0
		 * @since   2.5.0
		 */
		public function admin_notice_delete_all_plugin_data_success() {
			echo wp_kses_post( '<div class="notice notice-info"><p>' . __( 'Plugin data successfully deleted.', 'checkout-fees-for-woocommerce' ) . '</p></div>' );
		}

		/**
		 * Admin_notice_delete_all_plugin_data_error.
		 *
		 * @version 2.5.0
		 * @since   2.5.0
		 */
		public function admin_notice_delete_all_plugin_data_error() {
			echo wp_kses_post( '<div class="notice notice-error"><p>' . __( 'Wrong user role or nonce not verified.', 'checkout-fees-for-woocommerce' ) . '</p></div>' );
		}

		/**
		 * Delete all plugin data.
		 *
		 * @version 2.5.0
		 * @since   2.2.2
		 */
		public function maybe_delete_all_plugin_data() {
			if ( isset( $_GET['alg_woocommerce_checkout_fees_delete_all_data'] ) ) {
				// Checking nonce & user role.
				if ( ! isset( $_GET['alg_woocommerce_checkout_fees_delete_all_data_nonce'] ) ||
				! wp_verify_nonce( sanitize_key( $_GET['alg_woocommerce_checkout_fees_delete_all_data_nonce'] ), 'alg_woocommerce_checkout_fees_delete_all_data' ) ||
				! current_user_can( 'manage_woocommerce' )
				) {
					add_action( 'admin_notices', array( $this, 'admin_notice_delete_all_plugin_data_error' ) );
					return;
				}
				global $wpdb;
				$delete_counter_meta = 0;
				$plugin_meta         = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key LIKE '_alg_checkout_fees_%'" ); // phpcs:ignore
				foreach ( $plugin_meta as $meta ) {
					delete_post_meta( $meta->post_id, $meta->meta_key );
					$delete_counter_meta++;
				}
				$delete_counter_options = 0;
				$plugin_options         = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'alg_woocommerce_checkout_fees_%' OR option_name LIKE 'alg_gateways_fees_%'" ); // phpcs:ignore
				foreach ( $plugin_options as $option ) {
					if ( 'alg_woocommerce_checkout_fees_version' !== $option->option_name ) {
						delete_option( $option->option_name );
						delete_site_option( $option->option_name );
						$delete_counter_options++;
					}
				}
				// The end.
				wp_safe_redirect(
					add_query_arg(
						'alg_woocommerce_checkout_fees_delete_all_data_success',
						$delete_counter_meta . ',' . $delete_counter_options,
						remove_query_arg( 'alg_woocommerce_checkout_fees_delete_all_data' )
					)
				);
				exit;
			} elseif ( isset( $_GET['alg_woocommerce_checkout_fees_delete_all_data_success'] ) ) {
				add_action( 'admin_notices', array( $this, 'admin_notice_delete_all_plugin_data_success' ) );
			}
		}

	}

endif;

return new Alg_WC_Checkout_Fees_Admin();
