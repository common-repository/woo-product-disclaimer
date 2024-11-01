<?php
/**
 * WCPD Settings.
 *
 * @package WCPD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPD_Settings' ) ) {
	/**
	 * Rms Menu
	 */
	class WCPD_Settings {

		/**
		 * Instance
		 *
		 * @var $instance
		 */
		private static $instance = null;
		/**
		 * Settings
		 *
		 * @var $settings
		 */
		private static $settings = null;

		/**
		 * Constructor
		 */
		private function __construct() {
			add_action( 'admin_init', array( $this, 'wcpd_register_general_settings' ) );
		}

		/**
		 * Register general settings
		 */
		public function wcpd_register_general_settings() {

			if ( isset( $_POST['option_page'] ) ) {
				if ( ! empty( $_POST['option_page'] && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ), '_wpnonce' ) ) ) ) {

					$option_page = sanitize_text_field( wp_unslash( $_POST['option_page'] ) );

					switch ( $option_page ) {
						case 'wcpd-settings-general':
							register_setting( 'wcpd-settings-general', 'wcpd-global-cookie-activation' );
							register_setting( 'wcpd-settings-general', 'wcpd-global-cookie-duration' );
							break;
						case 'wcpd-settings-sitewide':
							register_setting( 'wcpd-settings-sitewide', 'wcpd-sitewide-cookie-activation' );
							register_setting( 'wcpd-settings-sitewide', 'wcpd-sitewide-cookie-duration' );
							break;
					}
				}
			}
		}
		/**
		 *  Get_settings.
		 */
		public static function get_settings() {

			self::$settings = array(
				'general'  => array(
					'cookie_activation' => ! empty( get_option( 'wcpd-global-cookie-activation' ) ) ? get_option( 'wcpd-global-cookie-activation' ) : 'disabled',
					'cookie_duration'   => ! empty( get_option( 'wcpd-global-cookie-duration' ) ) ? get_option( 'wcpd-global-cookie-duration' ) : 'session',
				),
				'sitewide' => array(
					'cookie_activation' => ! empty( get_option( 'wcpd-sitewide-cookie-activation' ) ) ? get_option( 'wcpd-sitewide-cookie-activation' ) : 'disabled',
					'cookie_duration'   => ! empty( get_option( 'wcpd-sitewide-cookie-duration' ) ) ? get_option( 'wcpd-sitewide-cookie-duration' ) : 'session',
				),
			);

			/**
			* Filter.
			* 
			* @since 1.0.0
			*/
			return (object) apply_filters( 'wcpd_settings_object', self::$settings );
		}


		/**
		 * Singleton Class Instanace
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new WCPD_Settings();
			}

			return self::$instance;
		}
	}

	$GLOBALS['WCPD_SETTINGS'] = WCPD_Settings::get_instance();
}
