<?php
/**
 * Plugin Name: Payment Gateway Based Fees and Discounts for WooCommerce Pro
 * Plugin URI: https://www.tychesoftwares.com/store/premium-plugins/payment-gateway-based-fees-and-discounts-for-woocommerce-plugin/
 * Description: Set payment gateways fees and discounts in WooCommerce.
 * Version: 2.9.0
 * Author: Tyche Softwares
 * Author URI: https://www.tychesoftwares.com/
 * Text Domain: checkout-fees-for-woocommerce
 * Domain Path: /langs
 * Copyright: © 2022 Tyche Softwares
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * WC tested up to: 7.4
 *
 * @package checkout-fees-for-woocommerce-pro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Automattic\WooCommerce\Utilities\OrderUtil;

// Check if WooCommerce is active.
$plugin_name = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin_name, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ), true ) &&
	! ( is_multisite() && array_key_exists( $plugin_name, get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	return;
}

if ( 'checkout-fees-for-woocommerce.php' === basename( __FILE__ ) ) {
	// Check if Pro is active, if so then return.
	$plugin_name = 'checkout-fees-for-woocommerce-pro/checkout-fees-for-woocommerce-pro.php';
	if (
		in_array( $plugin_name, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ), true ) ||
		( is_multisite() && array_key_exists( $plugin_name, get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

if ( ! class_exists( 'Alg_Woocommerce_Checkout_Fees' ) ) :

	/**
	 * Main Alg_Woocommerce_Checkout_Fees Class
	 *
	 * @version 2.5.2
	 * @class   Alg_Woocommerce_Checkout_Fees
	 */
	final class Alg_Woocommerce_Checkout_Fees {

		/**
		 * Plugin version.
		 *
		 * @var   string
		 * @since 2.1.0
		 */
		public $version = '2.9.0';

		/**
		 * Create an instance.
		 *
		 * @var Alg_Woocommerce_Checkout_Fees The single instance of the class.
		 */
		protected static $instance = null;

		/**
		 * Main Alg_Woocommerce_Checkout_Fees Instance
		 *
		 * Ensures only one instance of Alg_Woocommerce_Checkout_Fees is loaded or can be loaded.
		 *
		 * @static
		 * @return Alg_Woocommerce_Checkout_Fees - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Alg_Woocommerce_Checkout_Fees Constructor.
		 *
		 * @version 2.5.2
		 * @todo    [dev] maybe replace all standalone options with option arrays, e.g.: replace `alg_gateways_fees_text_ . $key` with `alg_gateways_fees_text[$key]`
		 */
		public function __construct() {

			add_action( 'alg_get_plugins_list', array( $this, 'cf_remove_plugin_name' ), PHP_INT_MAX );
			add_action( 'admin_notices', array( $this, 'ts_alg_wc_admin_notice' ) );
			add_action( 'before_woocommerce_init', array( &$this, 'pgbf_custom_order_tables_compatibility' ), 999 );

			// Deactivation hook.
			register_deactivation_hook( __FILE__, array( &$this, 'cf_deactivate' ) );

			// Filter.
			add_filter( 'alg_wc_checkout_fees_option', array( $this, 'checkout_fees_option' ), 10, 3 );

			// Include required files.
			$this->includes();

			if ( is_admin() ) {
				add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
			}

			// Admin.
			if ( is_admin() ) {
				add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
				// Admin core.
				require_once 'includes/class-alg-wc-checkout-fees-admin.php';
				// Settings.
				require_once 'includes/settings/class-alg-wc-checkout-fees-settings-section.php';
				$this->settings                     = array();
				$this->settings['general']          = require_once 'includes/settings/class-alg-wc-checkout-fees-settings-general.php';
				$this->settings['info']             = require_once 'includes/settings/class-alg-wc-checkout-fees-settings-info.php';
				$this->settings['global-extra-fee'] = require_once 'includes/settings/class-alg-wc-checkout-fees-settings-global-extra-fee.php';
				$this->settings['gateways']         = require_once 'includes/settings/class-alg-wc-checkout-fees-settings-gateways.php';
				$this->settings['license']          = require_once 'includes/settings/class-alg-wc-checkout-fees-settings-license.php';
				// Settings - Per product meta box.
				$this->meta_box_settings = require_once 'includes/settings/class-alg-wc-checkout-fees-settings-per-product.php';
				// Version.
				if ( get_option( 'alg_woocommerce_checkout_fees_version', '' ) !== $this->version ) {
					add_action( 'admin_init', array( $this, 'version_updated' ) );
				}

				$this->define_constants();

				if ( isset( $_GET['license'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					add_action( 'admin_init', array( $this, 'cf_edd_handle_license' ) );
				}
				require_once 'includes/class-cf-license-activation.php';

				// Data Tracking.
				include_once 'includes/class-cf-data-tracking.php';
				include_once 'includes/class-cf-tracking-functions.php';

				$this->check_for_updates();
			}

		}

		/**
		 * Checkout fees options.
		 *
		 * @param mixed  $value value of the setting.
		 * @param string $type Type of setting.
		 * @param array  $args Extra arguments.
		 * @version 2.5.1
		 * @since   2.0.0
		 */
		public function checkout_fees_option( $value, $type, $args = array() ) {
			switch ( $type ) {
				case 'settings':
					return '';
				case 'per_product':
					return true;
				case 'countries':
					return get_option( 'alg_gateways_fees_countries_' . $args['type'] . '_' . $args['fee_num'] . $args['current_gateway'], '' );
				case 'states':
					return array_filter( array_map( 'trim', explode( ',', get_option( 'alg_gateways_fees_states_' . $args['type'] . '_' . $args['fee_num'] . $args['current_gateway'], '' ) ) ) );
				case 'cats':
					return get_option( 'alg_gateways_fees_cats_' . $args['type'] . '_' . $args['fee_num'] . $args['current_gateway'], '' );
			}
			return $value;
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @version 2.5.0
		 * @param mixed $links Action links.
		 * @return  array
		 */
		public function action_links( $links ) {
			$custom_links   = array();
			$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_checkout_fees' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
			if ( 'checkout-fees-for-woocommerce.php' === basename( __FILE__ ) ) {
				$custom_links[] = '<a href="https://www.tychesoftwares.com/store/premium-plugins/payment-gateway-based-fees-and-discounts-for-woocommerce-plugin/">' .
				__( 'Unlock All', 'checkout-fees-for-woocommerce' ) . '</a>';
			}
			return array_merge( $custom_links, $links );
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 *
		 * @version 2.5.2
		 */
		public function includes() {
			// Functions.
			require_once 'includes/functions/country-functions.php';
			// Core.
			$this->core = require_once 'includes/class-alg-wc-checkout-fees.php';
			require_once 'includes/class-alg-wc-order-fees.php';
		}

		/**
		 * Load Localisation files.
		 *
		 * @version 2.5.2
		 */
		public function load_plugin_textdomain() {
			$locale = determine_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'woocommerce' );
			unload_textdomain( 'checkout-fees-for-woocommerce' );
			load_textdomain( 'checkout-fees-for-woocommerce', WP_LANG_DIR . '/checkout-fees-for-woocommerce/checkout-fees-for-woocommerce-' . $locale . '.mo' );
			load_plugin_textdomain( 'checkout-fees-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
		}
		/**
		 *  * Add admin license active notice.
		 *
		 * @version 2.5.2
		 */
		public function ts_alg_wc_admin_notice() {
			global $current_screen;
			$alg_wc_current_screen = get_current_screen();
			$return_url            = admin_url( 'admin.php?page=wc-settings&tab=alg_checkout_fees&section=license' );

			// Return when we're on any edit screen, as notices are distracting in there.
			if ( ( method_exists( $alg_wc_current_screen, 'is_block_editor' ) && $alg_wc_current_screen->is_block_editor() ) || ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) ) {
				return;
			}
			$license = get_option( 'edd_license_key_cf' );
			$status  = get_option( 'edd_license_key_cf_status' );
			if ( false !== $license && 'valid' !== $status ) {?>
				<div class=''>
					<div class="cf-pro-message cf-pro-tracker notice notice-info is-dismissible" style="position: relative;">
						<p style="margin: 10px 0 10px 10px; font-size: medium;">

							<?php
								printf(
									wp_kses_post(
										// translators: Plugin Name & Documentation Link.
										__( 'The license for Payment Gateway Based Fees and Discounts for WooCommerce Pro is not active. To receive automatic updates & support, please activate the license <a href="' . $return_url . '">here</a>.' )//phpcs:ignore
									)
								);
							?>
						</p>
					</div>
				</div>
					<?php
			}
		}

		/**
		 * Check for version.
		 *
		 * @version 2.5.2
		 * @since   2.5.0
		 */
		public function version_updated() {
			foreach ( $this->settings as $section ) {
				foreach ( $section->get_settings() as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
			update_option( 'alg_woocommerce_checkout_fees_version', $this->version );
		}

		/**
		 * Add Woocommerce settings tab to WooCommerce settings.
		 *
		 * @param array $settings Add settings tab for checkout fees.
		 * @version 2.5.2
		 */
		public function add_woocommerce_settings_tab( $settings ) {
			$settings[] = require_once 'includes/settings/class-alg-wc-settings-checkout-fees.php';
			return $settings;
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Define constants.
		 */
		private function define_constants() {
			define( 'EDD_CF_STORE_URL', 'https://www.tychesoftwares.com/' );
			define( 'EDD_CF_ITEM_NAME', 'Payment Gateway Based Fees and Discounts for WooCommerce' );
		}

		/**
		 * Handle EDD licenses.
		 */
		public function cf_edd_handle_license() {

			if ( isset( $_GET['license'] ) && 'cf_activate' === $_GET['license'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				self::cf_activate_license();
			} elseif ( isset( $_GET['license'] ) && 'cf_deactivate' === $_GET['license'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				self::cf_deactivate_license();
			}
		}

		/**
		 * Activate license.
		 */
		public static function cf_activate_license() {
			// run a quick security check.
			// retrieve the license from the database.
			$license = trim( get_option( 'edd_license_key_cf' ) );
			// data to send in our API request.
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => rawurlencode( EDD_CF_ITEM_NAME ), // the name of our product in EDD.
			);
			// Call the custom API.
			$response = wp_remote_get(
				add_query_arg( $api_params, EDD_CF_STORE_URL ),
				array(
					'timeout'   => 15,
					'sslverify' => false,
				)
			);
			// make sure the response came back okay.
			if ( is_wp_error( $response ) ) {
				return false;
			}
			// decode the license data.
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "active" or "inactive".
			update_option( 'edd_license_key_cf_status', $license_data->license );
		}

		/**
		 * Deactivate license.
		 */
		public static function cf_deactivate_license() {
			// run a quick security check.
			// retrieve the license from the database.
			$license = trim( get_option( 'edd_license_key_cf' ) );
			// data to send in our API request.
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => rawurlencode( EDD_CF_ITEM_NAME ), // the name of our product in EDD.
			);
			// Call the custom API.
			$response = wp_remote_get(
				add_query_arg( $api_params, EDD_CF_STORE_URL ),
				array(
					'timeout'   => 15,
					'sslverify' => false,
				)
			);
			// make sure the response came back okay.
			if ( is_wp_error( $response ) ) {
				return false;
			}
			// decode the license data.
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			// $license_data->license will be either "deactivated" or "failed".
			if ( 'deactivated' === $license_data->license ) {
				delete_option( 'edd_license_key_cf_status' );
			}
		}

		/**
		 * Check for updates.
		 */
		private function check_for_updates() {

			if ( ! class_exists( 'EDD_CF_Plugin_Updater' ) ) {
				// load our custom updater if it doesn't already exist.
				include dirname( __FILE__ ) . '/plugin_updates/EDD_CF_Plugin_Updater.php';
			}
			/**
			 * Retrieve our license key from the DB
			 */
			$license_key = trim( get_option( 'edd_license_key_cf' ) );
			/**
			 * Setup the updater
			 */
			$edd_updater = new EDD_CF_Plugin_Updater(
				EDD_CF_STORE_URL,
				__FILE__,
				array(
					'version'   => $this->version,    // current version number.
					'license'   => $license_key,      // license key (used get_option above to retrieve from DB).
					'item_name' => EDD_CF_ITEM_NAME, // name of this plugin.
					'author'    => 'Ashok Rane',       // author of this plugin.
				)
			);
		}

		/**
		 * Remove WP Factory helper plugin list.
		 */
		public function cf_remove_plugin_name() {

			$plugin_list = get_option( 'alg_wpcodefactory_helper_plugins' );

			if ( '' !== $plugin_list ) {
				$plugin_list = array_diff( $plugin_list, array( 'checkout-fees-for-woocommerce-pro' ) );
				update_option( 'alg_wpcodefactory_helper_plugins', $plugin_list );
			}
		}

		/**
		 * Actions to be performed when the plugin is deactivate.
		 *
		 * @since 2.6.3
		 */
		public function cf_deactivate() {
			if ( false !== as_next_scheduled_action( 'ts_send_data_tracking_usage' ) ) {
				as_unschedule_action( 'ts_send_data_tracking_usage' ); // Remove the scheduled action.
			}
			do_action( 'cf_deactivate' );
		}
		/**
		 * Sets the compatibility with Woocommerce HPOS.
		 *
		 * @since 2.7.0
		 */
		public static function pgbf_custom_order_tables_compatibility() {

			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', 'checkout-fees-for-woocommerce-pro/checkout-fees-for-woocommerce-pro.php', true );
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'orders_cache', 'checkout-fees-for-woocommerce-pro/checkout-fees-for-woocommerce-pro.php', true );
			}
		}
	}

endif;

if ( ! function_exists( 'alg_wc_cf' ) ) {
	/**
	 * Returns the main instance of Alg_Woocommerce_Checkout_Fees to prevent the need to use globals.
	 *
	 * @version 2.3.0
	 * @return  Alg_Woocommerce_Checkout_Fees
	 */
	function alg_wc_cf() {
		return Alg_Woocommerce_Checkout_Fees::instance();
	}
}

alg_wc_cf();
