<?php
/**
 * WC RMS Dependency Checker
 *
 * @package Woocommerce Product Disclaimer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WCPD_Dependencies' ) ) {
	/**
	 * Checks if WooCommerce is enabled.
	 */
	class WCPD_Dependencies {


		/**
		 * Active plugins
		 *
		 * @var static
		 */
		private static $active_plugins;

		/**
		 * Init the Dependencies.
		 */
		public static function init() {

			self::$active_plugins = (array) get_option( 'active_plugins', array() );

			if ( is_multisite() ) {
				self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
			}
		}

		/**
		 * Woocommerce active checker.
		 */
		public static function woocommerce_active_check() {

			if ( ! self::$active_plugins ) {
				self::init();
			}

			return in_array( 'woocommerce/woocommerce.php', self::$active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', self::$active_plugins );
		}
	}
}
