<?php
/**
 * Checkout Fees for WooCommerce - Settings
 *
 * @version 2.5.0
 * @since   1.0.0
 * @author  Tyche Softwares
 *
 * @package checkout-fees-for-woocommerce-pro/settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Settings_Checkout_Fees' ) ) :

	/**
	 * Add settings tab in WooCommerce settings.
	 */
	class Alg_WC_Settings_Checkout_Fees extends WC_Settings_Page {

		/**
		 * Constructor.
		 *
		 * @version 2.5.0
		 */
		public function __construct() {

			$this->id    = 'alg_checkout_fees';
			$this->label = __( 'Payment Gateway Based Fees and Discounts', 'checkout-fees-for-woocommerce' );

			parent::__construct();

			add_action( 'woocommerce_update_options_' . $this->id, array( $this, 'maybe_reset_settings' ) );
			add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'maybe_unclean_option' ), PHP_INT_MAX, 3 );
			add_action( 'woocommerce_admin_field_alg_woocommerce_checkout_fees_custom_link', array( $this, 'output_custom_link' ) );

		}

		/**
		 * Get settings.
		 *
		 * @version 2.5.0
		 */
		public function get_settings() {
			global $current_section;
			$settings = apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() );

			if ( 'license' === $current_section ) {
				return $settings;
			} else {

				$reset_settings = array(
					array(
						'title' => __( 'Reset Settings', 'checkout-fees-for-woocommerce' ),
						'type'  => 'title',
						'id'    => 'alg_woocommerce_checkout_fees_' . $current_section . '_reset_options',
					),
					array(
						'title'   => __( 'Reset section settings', 'checkout-fees-for-woocommerce' ),
						'desc'    => '<strong>' . __( 'Reset', 'checkout-fees-for-woocommerce' ) . '</strong>',
						'id'      => 'alg_woocommerce_checkout_fees_' . $current_section . '_reset',
						'default' => 'no',
						'type'    => 'checkbox',
					),
				);

				if ( 'general' === $current_section || '' === $current_section ) {
					array_push(
						$reset_settings,
						array(
							'title'   => __( 'Reset Usage Tracking', 'checkout-fees-for-woocommerce' ),
							'desc'    => __( 'This will reset your usage tracking settings, causing it to show the opt-in banner again and not send any data.', 'checkout-fees-for-woocommerce' ),
							'id'      => $this->id . '_' . $current_section . '_reset_usage_tracking',
							'default' => 'no',
							'type'    => 'checkbox',
						)
					);
				}

				array_push(
					$reset_settings,
					array(
						'type' => 'sectionend',
						'id'   => 'alg_woocommerce_checkout_fees_' . $current_section . '_reset_options',
					)
				);

				return array_merge( $settings, $reset_settings );
			}
		}

		/**
		 * Reset settings.
		 *
		 * @version 2.5.0
		 * @since   2.5.0
		 */
		public function maybe_reset_settings() {
			global $current_section;
			if ( 'yes' === get_option( 'alg_woocommerce_checkout_fees_' . $current_section . '_reset', 'no' ) ) {
				foreach ( $this->get_settings() as $value ) {
					if ( isset( $value['id'] ) ) {
						if ( false !== strpos( $value['id'], '[' ) ) {
							$id = explode( '[', $value['id'] );
							$id = $id[0];
							delete_option( $id );
						} else {
							delete_option( $value['id'] );
						}
					}
				}
			}

			if ( 'yes' === get_option( $this->id . '_' . $current_section . '_reset_usage_tracking', 'no' ) ) {
				delete_option( 'cf_pro_allow_tracking' );
				delete_option( $this->id . '_' . $current_section . '_reset_usage_tracking' );
				if ( function_exists( 'as_next_scheduled_action' ) ) {
					if ( false !== as_next_scheduled_action( 'ts_send_data_tracking_usage' ) ) {
						as_unschedule_action( 'ts_send_data_tracking_usage' );
					}
				}
			}
		}

		/**
		 * Maybe unclean option.
		 *
		 * @param mixed $value Settings value.
		 * @param array $option Options array.
		 * @param mixed $raw_value Raw settings value.
		 * @version 2.5.0
		 * @since   2.5.0
		 */
		public function maybe_unclean_option( $value, $option, $raw_value ) {
			return ( isset( $option['alg_woocommerce_checkout_fees_raw'] ) && $option['alg_woocommerce_checkout_fees_raw'] ? $raw_value : $value );
		}

		/**
		 * Add a settings option to output custom link.
		 *
		 * @param array $value custom_link type values.
		 * @version 2.2.2
		 * @since   2.2.2
		 */
		public function output_custom_link( $value ) {
			$tooltip_html = ( isset( $value['desc_tip'] ) && '' !== $value['desc_tip'] ) ?
			'<span class="woocommerce-help-tip" data-tip="' . $value['desc_tip'] . '"></span>' : '';
			?><tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label><?php echo wp_kses_post( $tooltip_html ); ?>
			</th>
			<td class="forminp forminp-<?php echo wp_kses_post( sanitize_title( $value['type'] ) ); ?>">
				<?php echo wp_kses_post( $value['link'] ); ?>
			</td>
		</tr>
			<?php
		}

	}

endif;

return new Alg_WC_Settings_Checkout_Fees();
