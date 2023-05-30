<?php
/**
 * Checkout Fees for WooCommerce - Pro
 *
 * Uninstalling Checkout Fees for WooCommerce Plugin delete settings.
 *
 * @author      Tyche Softwares
 * @version     2.5.6
 *
 * @package checkout-fees-for-woocommerce-pro
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// check if the Lite version file is present. If yes, do not delete any settings irrespective of whether the plugin is active or no.
if ( file_exists( WP_PLUGIN_DIR . '/checkout-fees-for-woocommerce/checkout-fees-for-woocommerce.php' ) ) {
	return;
}

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

global $wpdb;
/**
 * Delete the data for the WordPress Multisite.
 */
if ( is_multisite() ) {

	$cf_blog_list = get_sites();

	foreach ( $cf_blog_list as $cf_blog_list_key => $cf_blog_list_value ) {


		$cf_blog_id = $cf_blog_list_value->blog_id;

		/**
		 * It indicates the sub site id.
		 */
		$cf_multisite_prefix = $cf_blog_id > 1 ? $wpdb->prefix . "$cf_blog_id_" : $wpdb->prefix;


		// Product Settings.
		$wpdb->get_results( $wpdb->prepare( 'DELETE FROM `%s` WHERE meta_key LIKE %s', $cf_multisite_prefix . 'postmeta', '_alg_checkout_fees_%' ) );

		// General Settings.
		$wpdb->get_results( $wpdb->prepare( 'DELETE FROM `%s` WHERE option_name LIKE %s OR option_name LIKE %s', $cf_multisite_prefix . 'options', 'alg_woocommerce_checkout_fees_%', 'alg_gateways_fees_%' ) );

		// License.
		delete_blog_option( $cf_blog_id, 'edd_license_key_cf' );
		delete_blog_option( $cf_blog_id, 'edd_license_key_cf_status' );

		// Installation Wizard.
		delete_blog_option( $cf_blog_id, 'cf_installation_wizard_license_key' );

		// Version Number.
		delete_blog_option( $cf_blog_id, 'alg_woocommerce_checkout_fees_version' );

		// Data Tracking.
		delete_blog_option( $cf_blog_id, 'cf_pro_allow_tracking' );

	}
} else {

	// Product Settings.
	$wpdb->query( 'DELETE FROM `' . $wpdb->prefix . 'postmeta` WHERE meta_key LIKE "_alg_checkout_fees_%"' );

	// General Settings.
	$wpdb->query( 'DELETE FROM `' . $wpdb->prefix . 'options` WHERE option_name LIKE "alg_woocommerce_checkout_fees_%" OR option_name LIKE "alg_gateways_fees_%"' );
	
	// License.
	delete_option( 'edd_license_key_cf' );
	delete_option( 'edd_license_key_cf_status' );

	// Installation Wizard.
	delete_option( 'cf_installation_wizard_license_key' );

	// Version Number.
	delete_option( 'alg_woocommerce_checkout_fees_version' );

	// Data Tracking.
	delete_option( 'cf_pro_allow_tracking' );

}
// Clear any cached data that has been removed.
wp_cache_flush();


