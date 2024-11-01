<?php
/**
 * WCPD AJAX.
 *
 * @package WCPD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * All ajax request handled here
 */
if ( ! class_exists( 'WCPD_AJAX' ) ) {
	/**
	 * WCPD AJAX class
	 */
	class WCPD_AJAX {

		/**
		 * Hold the class instance..
		 *
		 * @var null
		 */
		private static $instance = null;

		/**
		 * Holds all disclaimers from the wcpd post type.
		 *
		 * @var null
		 */
		private static $global_disclaimers = null;
		/**
		 * Holds all disclaimers from the wcpd post type.
		 *
		 * @var null
		 */
		private static $sitewide_disclaimers = null;

		/**
		 * Class Constructor
		 */
		private function __construct() {

			self::$global_disclaimers   = WCPD_Disclaimer::get_all_global_disclaimers();
			self::$sitewide_disclaimers = WCPD_Disclaimer::get_all_sitewide_disclaimers();

			add_action( 'wp_ajax_wcpd_product_add_to_cart', array( $this, 'wcpd_product_add_to_cart' ) );
			add_action( 'wp_ajax_nopriv_wcpd_product_add_to_cart', array( $this, 'wcpd_product_add_to_cart' ) );

			add_action( 'wp_ajax_wcpd_simple_product_disclaimer', array( $this, 'wcpd_simple_product_disclaimer' ) );
			add_action( 'wp_ajax_nopriv_wcpd_simple_product_disclaimer', array( $this, 'wcpd_simple_product_disclaimer' ) );

			add_action( 'wp_ajax_wcpd_sitewide_disclaimer', array( $this, 'wcpd_sitewide_disclaimer' ) );
			add_action( 'wp_ajax_nopriv_wcpd_sitewide_disclaimer', array( $this, 'wcpd_sitewide_disclaimer' ) );

			add_action( 'wp_ajax_wcpd_add_cookies', array( $this, 'wcpd_add_cookies' ) );
			add_action( 'wp_ajax_nopriv_wcpd_add_cookies', array( $this, 'wcpd_add_cookies' ) );

			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'wcpd_add_to_cart_button' ), 10, 3  );
			
			add_action('wp_ajax_add_grouped_products_to_cart', array( $this, 'add_grouped_products_to_cart' ));
			add_action('wp_ajax_nopriv_add_grouped_products_to_cart', array( $this, 'add_grouped_products_to_cart' ));
		}

		public function add_grouped_products_to_cart() {

			if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), '_wcpdnonce' ) ) {
				exit( 'Unauthorized!' );
			}

			if (isset($_POST['product_data']) && is_array($_POST['product_data'])) {
				$sanitized_product_data = array_map('sanitize_text_field', $_POST['product_data']);
			
				foreach ($sanitized_product_data as $item) {
					$product_id = isset($item['productId']) ? absint($item['productId']) : 0;
					$quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
			
					if ($product_id > 0 && $quantity > 0) {
						WC()->cart->add_to_cart($product_id, $quantity);
					}
				}
			
				WC_AJAX::add_to_cart();
				wp_die();
			} else {
				wp_send_json_error('Product data not received');
			}
		}

		public function wcpd_add_to_cart_button( $cart_button, $product, $args ) {

			if (!is_shop()) {
				return $cart_button;
			}
	
			if ( $product ) {
				$defaults = array(
					'quantity' => 1,
					'class' => $args['class'],
				);
			}
			/**
			* Filter Woocommerce.
			* 
			* @since 9.7.0
			*/
			$class = apply_filters( 'woocommerce_loop_add_to_cart_args', $defaults )['class'];

			// WC versions before 3.3 did not have the product description aria-label
			if ( version_compare( get_option( 'woocommerce_version' ), '3.3.0', '<' ) ) {

				$link = sprintf( '<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" data-custom_attribute="%s"  class="%s">%s</a>',
					esc_url( $product->add_to_cart_url() ),
					esc_attr( isset( $quantity ) ? $quantity : 1 ),
					esc_attr( $product->get_id() ),
					esc_attr( $product->get_sku() ),
					esc_url( 'add-to-cart=' . $product->get_id() ),
					esc_attr( isset( $class ) ? $class : 'button' ),
					esc_html( $product->add_to_cart_text() )
				);

			} else {

				$link = sprintf( '<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s"  data-custom_attribute="%s" class="%s" aria-label="%s">%s</a>',
					esc_url( $product->add_to_cart_url() ),
					esc_attr( isset( $quantity ) ? $quantity : 1 ),
					esc_attr( $product->get_id() ),
					esc_attr( $product->get_sku() ),
					esc_url( 'add-to-cart=' . $product->get_id() ),
					esc_attr( isset( $class ) ? $class : 'button' ),
					esc_attr( $product->add_to_cart_description() ),
					esc_html( $product->add_to_cart_text() )
				);
			}

			return $link;
		}

		/**
		 * Handles the addition of cookies through AJAX.
		 *
		 * This function verifies the nonce, sets cookies based on the specified duration
		 * and type (sitewide or global), and echoes the cookie value.
		 */
		public function wcpd_add_cookies() {

			if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), '_wcpdnonce' ) ) {
				exit( 'Unauthorized!' );
			}

			$duration = ( isset( $_POST['cookie_duration'] ) && ! empty( sanitize_text_field( wp_unslash( $_POST['cookie_duration'] ) ) ) ) ? sanitize_text_field( wp_unslash( $_POST['cookie_duration'] ) ) : 'session';
			$type     = ( isset( $_POST['type'] ) && ! empty( sanitize_text_field( wp_unslash( $_POST['type'] ) ) ) ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

			if ( 'sitewide' === $type ) { // for sitewide.

				switch ( $duration ) {
					case 'session':
						setcookie( 'sitewide', 1, 0, '/' );
						break;

					case 'day':
						setcookie( 'sitewide', 1, time() + 86400, '/' );
						break;

					case 'week':
						setcookie( 'sitewide', 1, time() + ( 86400 * 7 ), '/' );
						break;

					case 'month':
						setcookie( 'sitewide', 1, time() + ( 86400 * 30 ), '/' );
						break;

					case 'forever':
						setcookie( 'sitewide', 1, time() + ( 86400 * 1000 ), '/' );
						break;

					default:
						setcookie( 'sitewide', 1, 0, '/' );

				}

				echo ( isset( $_COOKIE['sitewide'] ) && ! empty( sanitize_text_field( wp_unslash( $_COOKIE['sitewide'] ) ) ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_COOKIE['sitewide'] ) ) ) : 0;

			} elseif ( 'global' === $type ) {
				switch ( $duration ) {
					case 'session':
						setcookie( 'global', 1, 0, '/' );
						break;

					case 'day':
						setcookie( 'global', 1, time() + 86400, '/' );
						break;

					case 'week':
						setcookie( 'global', 1, time() + ( 86400 * 7 ), '/' );
						break;

					case 'month':
						setcookie( 'global', 1, time() + ( 86400 * 30 ), '/' );
						break;

					case 'forever':
						setcookie( 'global', 1, time() + ( 86400 * 1000 ), '/' );
						break;

					default:
						setcookie( 'global', 1, 0, '/' );
				}

				echo ( isset( $_COOKIE['global'] ) && ! empty( sanitize_text_field( wp_unslash( $_COOKIE['global'] ) ) ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_COOKIE['global'] ) ) ) : 0;
			}

			wp_die();
		}
		/**
		 * Handles the display of the sitewide disclaimer through AJAX.
		 *
		 * This function verifies the nonce, checks for sitewide disclaimers, and displays
		 * the disclaimer content if applicable. It also sets CSS variables for styling.
		 */
		public function wcpd_sitewide_disclaimer() {
			if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), '_wcpdnonce' ) ) {
				exit( 'Unauthorized!' );
			}
			$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '0';
			$quantity   = isset( $_POST['quantity'] ) ? sanitize_text_field( wp_unslash( $_POST['quantity'] ) ) : '0';
			if ( null !== self::$sitewide_disclaimers ) {
				ob_start();

				if ( isset( $_POST['cookie_activation'] ) && 'enabled' === sanitize_text_field( wp_unslash( $_POST['cookie_activation'] ) ) ) {

					if ( isset( $_POST['cookie_duration'] ) && 'session' === sanitize_text_field( wp_unslash( $_POST['cookie_duration'] ) ) ) {
						$flag = ( isset( $_COOKIE['sitewide'] ) && ! empty( sanitize_text_field( wp_unslash( $_COOKIE['sitewide'] ) ) ) ) ? sanitize_text_field( wp_unslash( $_COOKIE['sitewide'] ) ) : 0;
					} else {
						$flag = ( isset( $_COOKIE['sitewide'] ) && ! empty( sanitize_text_field( wp_unslash( $_COOKIE['sitewide'] ) ) ) ) ? sanitize_text_field( wp_unslash( $_COOKIE['sitewide'] ) ) : 0;
					}
				} else {
					$flag = 0;
				}

				if ( ! $flag ) {
					foreach ( self::$sitewide_disclaimers as $key => $disclaimer ) {
						$data = WCPD_Disclaimer::get_single_post_settings( $disclaimer->ID );
						if ( 'enabled' === $data->wcpd_disclaimer_toggle ) { // if is enabled.
							if ( 'sitewide' === $data->wcpd_disclaimer_type ) {
								break;
							} else {
								$data = '';
							}
						} else {
							$data = '';
						}
					}
				}
			}

			// WCPD modal content.
			if ( ! empty( $data ) ) {
				?>
				<style>	
					:root {
						--wcpdPopBgColor: #<?php echo esc_attr( $data->wcpd_popup_bg_color ); ?>;
						--wcpdPopTextColor: #<?php echo esc_attr( $data->wcpd_popup_txt_color ); ?>;
						--wcpdBtnBgColor: #<?php echo esc_attr( $data->wcpd_btn_bg_color ); ?>;
						--wcpdBtnTextColor: #<?php echo esc_attr( $data->wcpd_btn_txt_color ); ?>;							    
					}
				</style>	
				<?php
				include WCPD_PATH . '/includes/views/frontend/modal/wcpd-modal-content.php';
			}

			print_r( ob_get_clean() );

			wp_die();
		}
		/**
		 * Handles the addition of a product to the cart through AJAX.
		 *
		 * This function verifies the nonce, retrieves post data, processes the form
		 * serialized data, and then uses WooCommerce AJAX to add the product to the cart.
		 */
		public static function wcpd_product_add_to_cart() {

			if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), '_wcpdnonce' ) ) {
				exit( 'Unauthorized!' );
			}

			$post = WCPD_Main::get_post_data();

			if ( isset( $post['form_serialized'] ) ) {
				$params = array();

				parse_str( $post['form_serialized'], $params );
				$_post = $params;
				$_post = array_merge( $_post, $params );
				// Recomended by Name your price plugin.
				$_request = WCPD_Main::wcpd_sanitize_array( $_REQUEST );
				$_request = array_merge( $_request, $_post );

				if ( isset( $_post['variation_id'] ) && sanitize_text_field( $_post['variation_id'] ) ) {
					$_post['product_id'] = sanitize_text_field( $_post['variation_id'] );
					$_post['variation']  = wc_get_product_variation_attributes( $post['variation_id'] );
				}
			}

			WC_AJAX::add_to_cart();
			wp_die();
		}
		/**
		 * Handles the display of a disclaimer for a simple product.
		 *
		 * This function checks for the presence of a disclaimer based on the provided
		 * product information. If a disclaimer is found and conditions are met, it
		 * includes the modal content for the disclaimer on the frontend.
		 */
		public function wcpd_simple_product_disclaimer() {

			if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), '_wcpdnonce' ) ) {
				exit( 'Unauthorized!' );
			}

			if ( isset( $_POST['product_id'] ) && isset( $_POST['quantity'] ) ) {

				$main_product_id = sanitize_text_field( wp_unslash( $_POST['product_id'] ) );
				$product_id      = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '0';
				$quantity        = isset( $_POST['quantity'] ) ? sanitize_text_field( wp_unslash( $_POST['quantity'] ) ) : '0';

				$post = WCPD_Main::wcpd_sanitize_array( $_POST );
				$data = '';
				if ( isset( $_POST['variation_id'] ) && ! empty( $_POST['variation_id'] ) ) {

					$post['product_id'] = $post['variation_id'];
				}
				if ( isset( $post['cookie_activation'] ) && 'enabled' === $post['cookie_activation'] ) {

					if ( isset( $post['cookie_duration'] ) && 'session' === $post['cookie_duration'] ) {
						$flag = ( isset( $_COOKIE['global'] ) && ! empty( sanitize_text_field( wp_unslash( $_COOKIE['global'] ) ) ) ) ? sanitize_text_field( wp_unslash( $_COOKIE['global'] ) ) : 0;
					} else {
						$flag = ( isset( $_COOKIE['global'] ) && ! empty( sanitize_text_field( wp_unslash( $_COOKIE['global'] ) ) ) ) ? sanitize_text_field( wp_unslash( $_COOKIE['global'] ) ) : 0;
					}
				} else {
					$flag = 0;
				}

				if ( ! $flag ) {

					if ( null !== self::$global_disclaimers ) {

						if ( empty( $data ) ) { // for global disclaimer check.
							foreach ( self::$global_disclaimers as $key => $disclaimer ) {
								$data = WCPD_Disclaimer::get_single_post_settings( $disclaimer->ID );
								if ( 'enabled' === $data->wcpd_disclaimer_toggle ) { // if is enabled.
									if ( 'global' === $data->wcpd_disclaimer_type ) {
										break;
									} else {
										$data = '';
									}
								} else {
									$data = '';
								}
							}
						}
					}
				}

				// WCPD modal content.
				if ( ! empty( $data ) ) {
					?>
					<style>	
						:root {
							--wcpdPopBgColor: #<?php echo esc_attr( $data->wcpd_popup_bg_color ); ?>;
							--wcpdPopTextColor: #<?php echo esc_attr( $data->wcpd_popup_txt_color ); ?>;
							--wcpdBtnBgColor: #<?php echo esc_attr( $data->wcpd_btn_bg_color ); ?>;
							--wcpdBtnTextColor: #<?php echo esc_attr( $data->wcpd_btn_txt_color ); ?>;							    
						}
					</style>	
					<?php
					include WCPD_PATH . '/includes/views/frontend/modal/wcpd-modal-content.php';
				}

				print_r( ob_get_clean() );
			}

			wp_die();
		}
		/**
		 * Get an instance of the WCPD_AJAX class.
		 *
		 * @return WCPD_AJAX The instance of the WCPD_AJAX class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new WCPD_AJAX();
			}

			return self::$instance;
		}
	}

	WCPD_AJAX::get_instance();
}
