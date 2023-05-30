<?php
/**
 * Checkout Fees for WooCommerce - Admin
 *
 * @version 2.5.6
 * @since   2.5.6
 * @author  Tyche Softwares
 *
 * @package checkout-fees-for-woocommerce-pro/license
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CF_License_Activation' ) ) :

	/**
	 * License Activation Wizard.
	 */
	class CF_License_Activation {

		/**
		 * Plugin Prefix.
		 *
		 * @var $plugin_prefix
		 */
		public $plugin_prefix = 'cf';

		/**
		 * Plugin context.
		 *
		 * @var $plugin_context
		 */
		public $plugin_context = 'checkout-fees-for-woocommerce';

		/**
		 * Plugin name
		 *
		 * @var $plugin_name
		 */
		public $plugin_name = '';

		/**
		 * Minimum Capability for user.
		 *
		 * @var $minimum_capability
		 */
		public $minimum_capability = 'manage_options';

		/**
		 * License id
		 *
		 * @var $license_key
		 */
		public $license_key = 'edd_license_key_cf';

		/**
		 * License status
		 *
		 * @var $license_status
		 */
		public $license_status = 'edd_license_key_cf_status';

		/**
		 * Plugin folder name
		 *
		 * @var $plugin_folder
		 */
		public $plugin_folder = 'checkout-fees-for-woocommerce-pro/';

		/**
		 * Template base
		 *
		 * @var $template_base
		 */
		public $template_base = '';

		/**
		 * Plugin URL
		 *
		 * @var $plugin_url
		 */
		public $plugin_url = '';

		/**
		 * Plugin file path
		 *
		 * @var $plugin_file_path
		 */
		public $plugin_file_path = '';
		/**
		 * Constructor.
		 *
		 * @version 2.5.6
		 * @since   2.5.6
		 */
		public function __construct() {

			$this->plugin_file_path = untrailingslashit( plugin_dir_path( __DIR__ ) ) . '/checkout-fees-for-woocommerce-pro.php';

			$this->plugin_url    = $this->ts_get_plugin_url();
			$this->template_base = $this->ts_get_template_path();
			$this->plugin_name   = $this->ts_get_plugin_name();

			register_activation_hook( $this->plugin_file_path, array( $this, 'alg_wc_cif_pro_installation_completed' ) );

			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );

			add_action( 'admin_init', array( $this, 'ts_cf_license_activation' ), PHP_INT_MAX );

		}

		/**
		 * Flag for installation completion.
		 */
		public function alg_wc_cif_pro_installation_completed() {
			set_transient( 'alg_wc_cf_pro_activated', 1 );
		}

		/**
		 * Display the activation page for the first time.
		 */
		public function ts_cf_license_activation() {
			if ( get_transient( 'alg_wc_cf_pro_activated' ) && ! get_option( 'cf_installation_wizard_license_key' ) ) {
				delete_transient( 'alg_wc_cf_pro_activated' );
				wp_safe_redirect( admin_url( 'index.php?page=' . $this->plugin_prefix . '-pro-license' ) );
				exit;
			}
		}

		/**
		 * Add an installation wizard page.
		 */
		public function admin_menus() {
			$display_version = get_option( 'alg_woocommerce_checkout_fees_version' );

			// License Page.
			add_dashboard_page(
				/* translators: %1$s:Plugin context, %2$s: Plugin name */
				sprintf( esc_html__( 'Welcome to %1$s %2$s', 'checkout-fees-for-woocommerce' ), $this->plugin_name, $display_version ),
				/* translators: %1$s: Plugin name */
				sprintf( esc_html__( 'Welcome to %1$s', 'checkout-fees-for-woocommerce' ), $this->plugin_name ),
				$this->minimum_capability,
				$this->plugin_prefix . '-pro-license',
				array( $this, 'maybe_activate_license' )
			);

		}

		/**
		 * Remove activation wizard page.
		 */
		public function admin_head() {
			remove_submenu_page( 'index.php', $this->plugin_prefix . '-pro-license' );
		}
		/**
		 * Activate license
		 *
		 * @version 2.5.6
		 * @since   2.5.6
		 */
		public function maybe_activate_license() {

			// license is not active & terms have not been accepted.
			$installation_wizard_license_details = get_option( 'cf_installation_wizard_license_key' );

			$license_key    = get_option( $this->license_key ) ? get_option( $this->license_key ) : '';
			$license_status = get_option( $this->license_status ) ? get_option( $this->license_status ) : '';
			$plugin_name    = $this->plugin_name;
			$plugin_context = $this->plugin_context;

			$site_name        = "<a href='https://www.tychesoftwares.com/' target='_blank'>Tyche Softwares</a>";
			$purchase_history = "<a href='https://www.tychesoftwares.com/checkout/purchase-history' target='_blank'>Account->Purchase History</a>";

			$accept               = false;
			$display_failed       = false;
			$redirect_to_settings = false;

			ob_start();
			if ( isset( $_POST['edd_sample_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['edd_sample_nonce'] ), 'edd_sample_nonce' ) ) {

				if ( isset( $_POST['cf_license_display'] ) && '2' === $_POST['cf_license_display'] ) { // phpcs:ignore WordPress.Security.NonceVerification
					// the license activation failed the first time round.

					$insert      = false;
					$license_key = '';
					// check if a license key is entered.
					if ( isset( $_POST['license_key'] ) && '' !== $_POST['license_key'] ) { // phpcs:ignore WordPress.Security.NonceVerification
						$license_key = sanitize_text_field( wp_unslash( $_POST['license_key'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
						update_option( $this->license_key, $license_key );
						Alg_Woocommerce_Checkout_Fees::cf_activate_license();
					}

					$license_details = array( 'license_key' => $license_key );

					if ( isset( $_POST['cf_accept_terms'] ) && '1' === $_POST['cf_accept_terms'] ) { // phpcs:ignore WordPress.Security.NonceVerification
						$license_details['cf_accept_terms'] = '1';
						$accept                             = true;
						$insert                             = true;
						$redirect_to_settings               = true;
					}

					if ( 'valid' === get_option( $this->license_status ) ) {
						$license_details['is_valid'] = true;
						$license_status              = get_option( $this->license_status );
						$insert                      = true;
					}

					// if accept terms is enabled or the license was valid, save and move on to the welcome page.
					if ( $insert ) {
						add_option( 'cf_installation_wizard_license_key', wp_json_encode( $license_details ) );
					}
				} elseif ( isset( $_POST['cf_license_display'] ) && '1' === $_POST['cf_license_display'] ) {// phpcs:ignore WordPress.Security.NonceVerification
					// only for first time.
					update_option( $this->license_key, sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
					Alg_Woocommerce_Checkout_Fees::cf_activate_license();

					if ( 'valid' === get_option( $this->license_status ) ) { // license key validation was successful.
						$license_status = get_option( $this->license_status );
						add_option(
							'cf_installation_wizard_license_key',
							wp_json_encode(
								array(
									'license_key' => sanitize_text_field( wp_unslash( $_POST['license_key'] ) ), // phpcs:ignore WordPress.Security.NonceVerification
									'is_valid'    => true,
								)
							)
						);

						$redirect_to_settings = true;
					} else { // license key validation failed.
						$display_failed = true;

						// load scripts on the page.
						wp_enqueue_style( 'cf-font-awesome', $this->plugin_url . 'assets/css/font-awesome.css', array(), alg_wc_cf()->version );
						wp_enqueue_style( 'cf-font-awesome-min', $this->plugin_url . 'assets/css/font-awesome.min.css', array(), alg_wc_cf()->version );

						// display the template that allows them to proceed without the license key.
						wc_get_template(
							'license-activation-failed.php',
							array(
								'plugin_name'        => $plugin_name,
								'plugin_context'     => $plugin_context,
								'get_welcome_header' => $this->get_welcome_header(),
								'site_name'          => $site_name,
								'purchase_history'   => $purchase_history,
							),
							$this->plugin_folder,
							$this->template_base
						);

					}
				}
			}

			if ( ( '' === $license_key || 'valid' !== $license_status ) && ! $display_failed ) {
				wc_get_template(
					'license-activation.php',
					array(
						'plugin_name'        => $plugin_name,
						'plugin_context'     => $plugin_context,
						'get_welcome_header' => $this->get_welcome_header(),
						'site_name'          => $site_name,
						'purchase_history'   => $purchase_history,
					),
					$this->plugin_folder,
					$this->template_base
				);

			} elseif ( 'valid' === $license_status && '' !== $license_key ) { // if for some reason no conditions have been satisfied and an active license is present, it should redirect to the settings page.
				$redirect_to_settings = true;
			}

			if ( $redirect_to_settings ) {
				wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=alg_checkout_fees' ) );
				exit;
			}
			echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Get templates folder path.
		 */
		public function ts_get_template_path() {

			return untrailingslashit( plugin_dir_path( __DIR__ ) ) . '/templates/';
		}

		/**
		 * Get Plugin folder path.
		 */
		public function ts_get_plugin_url() {
			return plugins_url() . '/' . $this->plugin_folder;
		}

		/**
		 * Get plugin name.
		 */
		public static function ts_get_plugin_name() {
			$ts_plugin_dir  = dirname( dirname( __FILE__ ) );
			$ts_plugin_dir .= '/checkout-fees-for-woocommerce-pro.php';

			$ts_plugin_name = '';
			$plugin_data    = get_file_data( $ts_plugin_dir, array( 'name' => 'Plugin Name' ) );
			if ( ! empty( $plugin_data['name'] ) ) {
				$ts_plugin_name = $plugin_data['name'];
			}
			return $ts_plugin_name;
		}

		/**
		 * Welcome pahe header.
		 */
		public function get_welcome_header() {

			?>
		<h1 class="welcome-h1"><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php
			$this->social_media_elements();
		}

		/**
		 * Social media elements template.
		 */
		public function social_media_elements() {
			ob_start();
			wc_get_template(
				'/social-media-elements.php',
				array(),
				$this->plugin_folder,
				$this->template_base
			);
			ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}


	}

endif;

return new CF_License_Activation();
