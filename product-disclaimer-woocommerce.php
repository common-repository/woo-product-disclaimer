<?php
/**
 * Plugin Name: Product Disclaimer For WooCommerce
 * Plugin URI: https://wpexperts.io/products/woocommerce-product-disclaimer-pro/
 * Description: This plugin creates disclaimers for woocommerce products just before adding them to cart
 * Version: 2.2.1
 * WC requires at least: 3.0
 * WC tested up to: 8.7.0
 * Author: wpexperts.io
 * Author URI: https://wpexperts.io/
 * License: GPL
 * Domain Path: /languages
 * Text Domain: wcpd
 *
 * @package WCPD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'WCPD_PATH' ) ) {
	define( 'WCPD_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WCPD_PATH_VIEW_FRONTEND' ) ) {
	define( 'WCPD_PATH_VIEW_FRONTEND', plugin_dir_path( __FILE__ ) . '/includes/views/frontend/' );
}

if ( ! defined( 'WCPD_URL' ) ) {
	define( 'WCPD_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'WCPD_VERSION' ) ) {
	define( 'WCPD_VERSION', '2.2.1' );
}


if ( ! class_exists( 'WCPD_Main' ) ) {

	/**
	 * Restaurant main class
	 */
	class WCPD_Main {

		/**
		 * Instance
		 *
		 * @var $instance
		 */
		private static $instance = null;
		/**
		 * Toggle
		 *
		 * @var $toggle
		 */
		public static $toggle = null;
		/**
		 * Duration
		 *
		 * @var $duration
		 */
		public static $duration = null;
		/**
		 * Disclaimer_type
		 *
		 * @var $disclaimer_type
		 */
		public static $disclaimer_type = null;
		/**
		 * Display_type
		 *
		 * @var $display_type
		 */
		public static $display_type = null;
		/**
		 * Popup_style
		 *
		 * @var $popup_style
		 */
		public static $popup_style = null;

		/**
		 * Class Constructor
		 */
		private function __construct() {

			/**
			* Add values to $toggle
			*/
			self::$toggle = array(
				'enabled'  => __( 'Enabled', 'wcpd' ),
				'disabled' => __( 'Disabled', 'wcpd' ),
			);

			/**
			* Add values to $duration
			*/
			self::$duration = array(
				'session' => __( 'Session', 'wcpd' ),
				'day'     => __( '1 Day', 'wcpd' ),
				'week'    => __( '1 Week', 'wcpd' ),
				'month'   => __( '1 Month', 'wcpd' ),
				'forever' => __( 'Forever', 'wcpd' ),
			);

			self::$disclaimer_type = array(
				'specific' => __( 'Specific (PRO)', 'wcpd' ),
				'global'   => __( 'Global', 'wcpd' ),
				'sitewide' => __( 'Sitewide', 'wcpd' ),
				'cart'     => __( 'Cart/checkout (PRO)', 'wcpd' ),
			);

			self::$display_type = array(
				'popup'  => __( 'Popup', 'wcpd' ),
				'onpage' => __( 'On Page', 'wcpd' ),
			);

			self::$popup_style = array(
				'modern'       => __( 'Modern', 'wcpd' ),
				'modern_round' => __( 'Modern (Rounded)', 'wcpd' ),
			);

			/**
			 * Check for plugin dependencies
			 */
			require_once WCPD_PATH . 'includes/classes/class-wcpd-dependencies.php';

			if ( WCPD_Dependencies::woocommerce_active_check() ) {

				// Compatibility hook for HPOS WooCommerce.
				add_action( 'before_woocommerce_init', array( $this, 'woo_hpos_incompatibility' ) );

				add_action( 'plugins_loaded', array( $this, 'wcpd_load_textdomain' ) );

				// Backward compatibility.
				add_action( 'wp_loaded', array( $this, 'wcpd_backward_compatibility' ) );

				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'wcpd_disclaimer_settings_link' ) );

				self::includes();

			} else {

				/**
				 * Notice for admin
				 */
				add_action( 'admin_notices', array( $this, 'inactive_plugin_notice' ) );
			}
		}

		/**
		 * Add setting link to plugin page
		 *
		 * @param array $links Array of links.
		 */
		public function wcpd_disclaimer_settings_link( $links ) {
			$settings_link = '<a href="' . esc_url( admin_url( 'edit.php?post_type=wcpd&page=wcpd-settings' ) ) . '" >' . esc_html__( 'Settings', 'wcpd' ) . '</a>';
			array_push( $links, $settings_link );
			return $links;
		}
		/**
		 * Handle High Point of Sale incompatibility.
		 *
		 * This function checks for the existence of the \Automattic\WooCommerce\Utilities\FeaturesUtil
		 * class and declares compatibility for certain features if the class is present.
		 */
		public function woo_hpos_incompatibility() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
			}
		}

		/**
		 * Backward Compatibility
		 */
		public function wcpd_backward_compatibility() {

			$i = 0;

			if ( get_option( 'wcpd_backward_comp', false ) === false ) {

				// Backward compatibility code will goes here.

				$general_discalimer_activation = get_option( 'disclaimer_general_activate' );

				$general_disclaimer_cookie_activation = get_option( 'disclaimer_cookie_activation' );

				$general_disclaimer_cookie_duration = get_option( 'disclaimer_cookie_duration' );

				$general_disclaimer_heading = get_option( 'disclaimer_popup_heading_text' );

				$general_disclaimer_message = get_option( 'disclaimer_general_message' );

				$general_disclaimer_reject_url = get_option( 'disclaimer_general_reject_url' );

				if ( 'yes' === $general_disclaimer_cookie_activation ) {
					update_option( 'wcpd-global-cookie-activation', 'enabled' );
				} else {
					update_option( 'wcpd-global-cookie-activation', 'disabled' );
				}

				if ( 'session' === $general_disclaimer_cookie_duration ) {
					update_option( 'wcpd-global-cookie-duration', 'session' );
				} elseif ( '1' === $general_disclaimer_cookie_duration ) {
					update_option( 'wcpd-global-cookie-duration', 'day' );
				} elseif ( '7' === $general_disclaimer_cookie_duration ) {
					update_option( 'wcpd-global-cookie-duration', 'week' );
				} elseif ( '30' === $general_disclaimer_cookie_duration ) {
					update_option( 'wcpd-global-cookie-duration', 'month' );
				} else {
					update_option( 'wcpd-global-cookie-duration', 'forever' );
				}

				// Creating general disclaimer.
				$general_disclaimer_id = wp_insert_post(
					array(
						'post_type'    => 'wcpd',
						'post_title'   => $general_disclaimer_heading,
						'post_status'  => 'publish',
						'post_content' => $general_disclaimer_message,
					)
				);

				if ( ! empty( $general_disclaimer_id ) ) {

					++$i;

					if ( 'yes' === $general_discalimer_activation ) {
						update_post_meta( $general_disclaimer_id, 'wcpd-disclaimer-toggle', 'enabled' );
					} else {
						update_post_meta( $general_disclaimer_id, 'wcpd-disclaimer-toggle', 'disabled' );
					}

					update_post_meta( $general_disclaimer_id, 'wcpd-disclaimer-type', 'global' );

					update_post_meta( $general_disclaimer_id, 'wcpd-display-type', 'popup' );

					$wcpd_terms_condition['toggle'] = 'disabled';
					$wcpd_terms_condition['values'] = '';
					update_post_meta( $general_disclaimer_id, 'wcpd-terms-condition', $wcpd_terms_condition );

					update_post_meta( $general_disclaimer_id, 'wcpd-reject-url', $general_disclaimer_reject_url );
					update_post_meta( $general_disclaimer_id, 'wcpd-reject-txt', 'Reject' );
					update_post_meta( $general_disclaimer_id, 'wcpd-accept-txt', 'Accept' );
					update_post_meta( $general_disclaimer_id, 'wcpd-popup-style', 'modern' );

					update_post_meta( $general_disclaimer_id, 'wcpd-popup-bg-color', '#FFF' );
					update_post_meta( $general_disclaimer_id, 'wcpd-popup-txt-color', '#000' );
					update_post_meta( $general_disclaimer_id, 'wcpd-btn-bg-color', '#000' );
					update_post_meta( $general_disclaimer_id, 'wcpd-btn-txt-color', '#FFF' );
				}

				// backward comp done.
				update_option( 'wcpd_backward_comp_total', $i );
				update_option( 'wcpd_backward_comp', 'true' );
			}
		}

		/**
		 * Plugin Domain loaded
		 */
		public function wcpd_load_textdomain() {
			load_plugin_textdomain( 'wcpd', false, basename( __DIR__ ) . '/languages/' );
		}

		/**
		 * Inactive plugin notice.
		 */
		public function inactive_plugin_notice() {
			$class    = 'notice notice-error';
			$headline = __( 'Woocommerce Product Disclaimer requires WooCommerce to be active.', 'wcpd' );
			$message  = __( 'Go to the plugins page to activate WooCommerce', 'wcpd' );
			printf( '<div class="%1$s"><h2>%2$s</h2><p>%3$s</p></div>', esc_attr( $class ), esc_html( $headline ), esc_html( $message ) );
		}

		/**
		 * Files to includes
		 */
		private static function includes() {
			require_once WCPD_PATH . '/includes/classes/class-wcpd-settings.php';
			require_once WCPD_PATH . '/includes/classes/class-wcpd-posttype.php';
			require_once WCPD_PATH . '/includes/classes/class-wcpd-ajax.php';
		}

		/**
		 * Get Post Data
		 *
		 * @param string $name Optional.
		 */
		public static function get_post_data( $name = '' ) {

			if ( isset( $_POST['rms_notrue_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rms_notrue_nonce'] ) ), 'rms_notrue_nonce' ) ) {
				exit;
			}
			$post = map_deep( $_POST, 'sanitize_text_field' );

			if ( isset( $post[ $name ] ) ) {
				return $post[ $name ];
			} else {
				return $post;
			}
		}
		/**
		 * Sanitize array recursively
		 *
		 * @param array $request The array to be sanitized.
		 * @return array The sanitized array.
		 */
		public static function wcpd_sanitize_array( $request ) {
			$sanitized_array = array();

			foreach ( $request as $index => $unsanitized ) {
				if ( is_array( $unsanitized ) ) {
					$sanitized_array[ $index ] = self::wcpd_sanitize_array( $unsanitized );
				} else {
					$sanitized_array[ $index ] = sanitize_text_field( $unsanitized );
				}
			}

			return $sanitized_array;
		}

		/**
		 * Singleton Class Instatnce.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new WCPD_Main();
			}

			return self::$instance;
		}
	}

	WCPD_Main::get_instance();

}
