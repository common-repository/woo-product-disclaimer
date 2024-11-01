<?php
/**
 * WCPD Disclaimer.
 *
 * @package WCPD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCPD_Disclaimer' ) ) {
	/**
	 * Rms Menu
	 */
	class WCPD_Disclaimer {

		/**
		 * Instance
		 *
		 * @var $instance
		 */
		private static $instance = null;
		/**
		 * Global_disclaimers
		 *
		 * @var $global_disclaimers
		 */
		private static $global_disclaimers = null;
		/**
		 * Sitewide_disclaimers
		 *
		 * @var $sitewide_disclaimers
		 */
		private static $sitewide_disclaimers = null;
		/**
		 * Disclaimer_data
		 *
		 * @var $disclaimer_data
		 */
		protected static $disclaimer_data = null;

		/**
		 * Constructor
		 */
		private function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'wcpd_admin_scripts' ), 99 );
			add_action( 'wp_enqueue_scripts', array( $this, 'wcpd_front_scripts' ) );
			add_action( 'init', array( $this, 'wcpd_disclaimer_posttype' ) );
			add_action( 'admin_menu', array( $this, 'wcpd_create_sub_menu' ) );
			add_action( 'edit_form_after_editor', array( $this, 'wcpd_add_single_post_settings' ) );
			add_action( 'post_updated', array( $this, 'wcpd_update_single_post_settings' ), 99, 3 );
			add_filter( 'manage_wcpd_posts_columns', array( $this, 'wcpd_modify_column_names' ) );
			add_action( 'manage_wcpd_posts_custom_column', array( $this, 'wcpd_add_custom_column' ), 9, 2 );

			add_action( 'wp_footer', array( $this, 'add_wcpd_modal' ) );
		}
		/**
		 * Adds the WCPD modal to the frontend.
		 */
		public function add_wcpd_modal() {
			// WCPD modal.
			include WCPD_PATH . '/includes/views/frontend/modal/wcpd-modal.php';
		}

		/**
		 * Get All Global Disclaimers
		 */
		public static function get_all_global_disclaimers() {
			$meta_query = 'meta_query';
			$args       = array(
				'post_type'        => 'wcpd',
				'post_status'      => 'publish',
				'numberposts'      => -1,
				'orderby'          => 'menu_order',
				'order'            => 'ASC',
				'suppress_filters' => false,
				$meta_query        => array(
					array(
						'key'     => 'wcpd-disclaimer-type',
						'value'   => array( 'global' ),
						'compare' => 'IN',
					),
					array(
						'key'     => 'wcpd-disclaimer-toggle',
						'value'   => 'enabled',
						'compare' => '=',
					),
				),
			);

			self::$global_disclaimers = get_posts( $args );
			return self::$global_disclaimers;
		}

		/**
		 * Get All Sitewide Disclaimers
		 */
		public static function get_all_sitewide_disclaimers() {
			$meta_query                 = 'meta_query';
			$args                       = array(
				'post_type'        => 'wcpd',
				'post_status'      => 'publish',
				'numberposts'      => -1,
				'orderby'          => 'menu_order',
				'order'            => 'ASC',
				'suppress_filters' => false,
				$meta_query        => array(
					'relation' => 'AND',
					array(
						'key'     => 'wcpd-disclaimer-type',
						'value'   => array( 'sitewide' ),
						'compare' => 'IN',
					),
					array(
						'key'     => 'wcpd-disclaimer-toggle',
						'value'   => 'enabled',
						'compare' => '=',
					),
				),
			);
			self::$sitewide_disclaimers = get_posts( $args );
			return self::$sitewide_disclaimers;
		}
		/**
		 * Retrieve disclaimer information based on product ID and settings.
		 *
		 * @param int    $main_product_id The ID of the main product.
		 * @param int    $product_id      The ID of the product.
		 * @param int    $disclaimer_id   The ID of the disclaimer.
		 * @param string $type         The type of disclaimer (default is 'popup').
		 * @return mixed               Disclaimer data if conditions are met, false otherwise.
		 */
		public static function get_disclaimer_by_product_id( $main_product_id = '', $product_id = '', $disclaimer_id = '', $type = 'popup' ) {

			if ( empty( $main_product_id ) || empty( $disclaimer_id ) ) {
				return;
			}

			$flag        = false;
			$categories  = get_the_terms( $main_product_id, 'product_cat' );
			$product_cat = $categories[0]->slug;
			$data        = self::get_single_post_settings( $disclaimer_id );
			$prod        = wc_get_product( $main_product_id );

			if ( 'enabled' === $data->wcpd_disclaimer_toggle ) { // if is enabled.

				if ( 'specific' === $data->wcpd_disclaimer_type && $type === $data->wcpd_display_type ) {

					if ( 'enabled' === $data->wcpd_user_roles['toggle'] ) {

						if ( is_user_logged_in() ) {
							$user              = wp_get_current_user();
							$current_user_role = implode( ',', $user->roles );
						} else {
							$current_user_role = 'guest';
						}

						if ( ! isset( $data->wcpd_user_roles['values'] ) ) {
							$flag = true;
						} elseif ( in_array( $current_user_role, $data->wcpd_user_roles['values'], true ) ) {
							$flag = true;
						} else {
							return false;
						}
					}

					if ( 'variable' === $prod->get_type() ) {

						// for product exclude with variation id.
						if ( 'enabled' === $data->wcpd_products['toggle'] ) {
							if ( isset( $data->wcpd_products['exc_values'] ) && in_array( $product_id, $data->wcpd_products['exc_values'], true ) ) {
								return false;
							} else {
								$flag = true;
							}
						}

						// for product exclude with main product id.
						if ( 'enabled' === $data->wcpd_products['toggle'] ) {
							if ( isset( $data->wcpd_products['exc_values'] ) && in_array( $main_product_id, $data->wcpd_products['exc_values'], true ) ) {
								$flag = false;
							} else {
								$flag = true;
							}
						}
					} elseif ( 'enabled' === $data->wcpd_products['toggle'] ) {
						// for product exclude.
						if ( isset( $data->wcpd_products['exc_values'] ) && in_array( $product_id, $data->wcpd_products['exc_values'], true ) ) {
							return false;
						} else {
							$flag = true;
						}
					}

					if ( 'enabled' === $data->wcpd_categories['toggle'] ) {

						if ( empty( $product_cat ) ) {
							$flag = false;
						}

						if ( ! isset( $data->wcpd_categories['values'] ) ) {
							$flag = true;
						} elseif ( in_array( $product_cat, $data->wcpd_categories['values'], true ) ) {
							$flag = true;
						} else {
							foreach ( $categories as $category ) {
								if ( in_array( $category, $data->wcpd_categories['values'], true ) ) {
									$flag = true;
								} else {
									$flag = false;
								}
							}
						}
					}

					if ( 'variable' === $prod->get_type() ) {

						// for product includes with variation id.
						if ( 'enabled' === $data->wcpd_products['toggle'] ) {

							if ( ! isset( $data->wcpd_products['inc_values'] ) ) {
								$flag = true;
							} elseif ( in_array( $product_id, $data->wcpd_products['inc_values'], true ) ) {

								$flag = true;
							} else {
								$flag = false;
							}
						}

						// for product includes with main id.
						if ( 'enabled' === $data->wcpd_products['toggle'] ) {

							if ( ! isset( $data->wcpd_products['inc_values'] ) ) {
								$flag = true;
							} elseif ( in_array( $main_product_id, $data->wcpd_products['inc_values'], true ) ) {

								$flag = true;
							} else {
								$flag = false;
							}
						}
					} elseif ( 'enabled' === $data->wcpd_products['toggle'] ) {
						// for product includes.
						if ( ! isset( $data->wcpd_products['inc_values'] ) ) {
							$flag = true;
						} elseif ( in_array( $product_id, $data->wcpd_products['inc_values'], true ) ) {

							$flag = true;
						} else {
							$flag = false;
						}
					}

					if ( $flag ) {
						return $data;
					}
				}
			}

			return false;
		}

		/**
		 * Modify Columns for rms_menu post type
		 *
		 * @param array $columns The array of columns to be modified.
		 */
		public function wcpd_modify_column_names( $columns ) {

			unset( $columns['date'] );
			$columns['wcpd_type']   = __( 'Type', 'wcpd' );
			$columns['wcpd_status'] = __( 'Status', 'wcpd' );
			$columns['date']        = __( 'Date', 'wcpd' );
			return $columns;
		}

		/**
		 * Add Custom Column Values
		 *
		 * @param string $column The name of the custom column.
		 * @param int    $id The ID of the post.
		 */
		public function wcpd_add_custom_column( $column, $id ) {
			if ( 'wcpd_type' === $column ) {
				$wcpd_type = get_post_meta( $id, 'wcpd-disclaimer-type', true );
				if ( 'global' === $wcpd_type ) {
					echo 'Global';
				} else {
					echo 'sitewide';
				}
			}

			if ( 'wcpd_status' === $column ) {
				$wcpd_status = get_post_meta( $id, 'wcpd-disclaimer-toggle', true );
				if ( 'enabled' === $wcpd_status ) {
					echo '<span class="wcpd-active">Activated</span>';
				} else {
					echo '<span class="wcpd-deactive">Deactivated</span>';
				}
			}
		}

		/**
		 * Registring frontend scripts Only
		 */
		public function wcpd_front_scripts() {

			wp_enqueue_style( 'wcpd_frontend', WCPD_URL . 'assets/frontend/css/wcpd-frontend.css', array(), WCPD_VERSION . '?t=' . gmdate( 'His' ), 'all' );
			wp_register_script( 'wcpd_frontend', WCPD_URL . 'assets/frontend/js/wcpd-frontend.js', array( 'jquery' ), WCPD_VERSION . '?t=' . gmdate( 'His' ), true );

			wp_enqueue_script( 'wc-add-to-cart' );

			wp_localize_script(
				'wcpd_frontend',
				'wcpd',
				array(
					'ajaxurl'                    => admin_url( 'admin-ajax.php' ),
					'simple_product_disclaimer'  => 'wcpd_simple_product_disclaimer',
					'sitewide_disclaimer'        => 'wcpd_sitewide_disclaimer',
					'nonce'                      => sanitize_text_field( wp_create_nonce( '_wcpdnonce' ) ),
					'ajax_cart'                  => get_option( 'woocommerce_enable_ajax_add_to_cart' ),
					'cart_redirect'              => get_option( 'woocommerce_cart_redirect_after_add' ),
					'cart_url'                   => wc_get_cart_url(),
					'is_cart'                    => is_cart(),
					'i18n_view_cart'             => __( 'View cart', 'wcpd' ),
					'is_product'                 => is_product(),
					'add_cookies'                => 'wcpd_add_cookies',
					'sitewide_cookie_activation' => get_option( 'wcpd-sitewide-cookie-activation', 'disabled' ),
					'sitewide_cookie_duration'   => get_option( 'wcpd-sitewide-cookie-duration', 'session' ),
					'global_cookie_activation'   => get_option( 'wcpd-global-cookie-activation', 'disabled' ),
					'global_cookie_duration'     => get_option( 'wcpd-global-cookie-duration', 'session' ),
					'age_error_txt'              => esc_html__( '*Entered age is less than required age.', 'wcpd' ),
					'terms_error_txt'            => esc_html__( '*Required field.', 'wcpd' ),
				)
			);

			wp_enqueue_script( 'wcpd_frontend' );
		}

		/**
		 * Loading admin enqueue scripts
		 */
		public function wcpd_admin_scripts() {

			global $current_screen;

			if ( ( isset( $current_screen->id ) && 'wcpd_page_wcpd-settings' === $current_screen->id ) || 'wcpd' === $current_screen->post_type ) {
				/**
				 * All styles
				 */
				wp_enqueue_style( 'wcpd_admin', WCPD_URL . '/assets/admin/css/wcpd-admin.css', array(), WCPD_VERSION . '?t=' . gmdate( 'His' ), 'all' );

				/**
				 * All scripts
				 */
				wp_enqueue_script( 'wcpd_jscolor', WCPD_URL . '/assets/admin/js/jscolor.js', array( 'jquery' ), WCPD_VERSION . '?t=' . gmdate( 'His' ), true );
			}
		}

		/**
		 * Save/Update settings for each menu post
		 *
		 * @param int     $post_id      The ID of the post being updated.
		 * @param WP_Post $post_after The post object representing the state of the post after the update.
		 * @param WP_Post $post_before The post object representing the state of the post before the update.
		 */
		public function wcpd_update_single_post_settings( $post_id, $post_after, $post_before ) {

			if ( 'wcpd' === $post_before->post_type && 'trash' === $post_before->post_status ) {
				return;
			}

			if ( 'wcpd' === $post_after->post_type && 'publish' === $post_after->post_status ) {

				if ( ! isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), '_wpnonce' ) ) {
					exit( 'Not Authorized' );
				}

				/**
				* Hook.
				* 
				* @since 1.0.0
				*/
				do_action( 'wcpd_before_update_single_disclaimer_settings', $post_id, $post_after );

				$post = WCPD_Main::get_post_data( '' );

				if ( isset( $post['wcpd-disclaimer-toggle'] ) && ! empty( $post['wcpd-disclaimer-toggle'] ) ) {
					update_post_meta( $post_id, 'wcpd-disclaimer-toggle', $post['wcpd-disclaimer-toggle'] );
				} else {
					update_post_meta( $post_id, 'wcpd-disclaimer-toggle', 'disabled' );
				}

				if ( isset( $post['wcpd-disclaimer-type'] ) && 'sitewide' === $post['wcpd-disclaimer-type'] ) {
					update_post_meta( $post_id, 'wcpd-disclaimer-type', 'sitewide' );
				} else {
					update_post_meta( $post_id, 'wcpd-disclaimer-type', 'global' );
				}

				if ( isset( $post['wcpd-age-verification'] ) && count( $post['wcpd-age-verification'] ) > 0 ) {
					update_post_meta( $post_id, 'wcpd-age-verification', $post['wcpd-age-verification'] );
				} else {
					$data_age = array(
						'toggle' => 'disabled',
						'values' => '',
					);
					update_post_meta( $post_id, 'wcpd-age-verification', $data_age );
				}

				if ( isset( $post['wcpd-terms-condition'] ) && count( $post['wcpd-terms-condition'] ) > 0 ) {
					update_post_meta( $post_id, 'wcpd-terms-condition', $post['wcpd-terms-condition'] );
				} else {
					$data_terms = array(
						'toggle' => 'disabled',
						'values' => '',
					);
					update_post_meta( $post_id, 'wcpd-terms-condition', $data_terms );
				}

				update_post_meta( $post_id, 'wcpd-display-type', 'popup' );

				if ( isset( $post['wcpd-reject-txt'] ) && ! empty( $post['wcpd-reject-txt'] ) ) {
					update_post_meta( $post_id, 'wcpd-reject-txt', $post['wcpd-reject-txt'] );
				} else {
					update_post_meta( $post_id, 'wcpd-reject-txt', 'Reject' );
				}

				if ( isset( $post['wcpd-reject-url'] ) && ! empty( $post['wcpd-reject-url'] ) ) {
					update_post_meta( $post_id, 'wcpd-reject-url', $post['wcpd-reject-url'] );
				} else {
					update_post_meta( $post_id, 'wcpd-reject-url', '' );
				}

				if ( isset( $post['wcpd-accept-txt'] ) && ! empty( $post['wcpd-accept-txt'] ) ) {
					update_post_meta( $post_id, 'wcpd-accept-txt', $post['wcpd-accept-txt'] );
				} else {
					update_post_meta( $post_id, 'wcpd-accept-txt', 'Accept' );
				}

				update_post_meta( $post_id, 'wcpd-popup-style', 'mdoern' );

				if ( isset( $post['wcpd-popup-bg-color'] ) && ! empty( $post['wcpd-popup-bg-color'] ) ) {
					update_post_meta( $post_id, 'wcpd-popup-bg-color', $post['wcpd-popup-bg-color'] );
				} else {
					update_post_meta( $post_id, 'wcpd-popup-bg-color', 'FFFFFF' ); // White.
				}

				if ( isset( $post['wcpd-popup-txt-color'] ) && ! empty( $post['wcpd-popup-txt-color'] ) ) {
					update_post_meta( $post_id, 'wcpd-popup-txt-color', $post['wcpd-popup-txt-color'] );
				} else {
					update_post_meta( $post_id, 'wcpd-popup-txt-color', '000000' ); // Black.
				}

				if ( isset( $post['wcpd-btn-bg-color'] ) && ! empty( $post['wcpd-btn-bg-color'] ) ) {
					update_post_meta( $post_id, 'wcpd-btn-bg-color', $post['wcpd-btn-bg-color'] );
				} else {
					update_post_meta( $post_id, 'wcpd-btn-bg-color', '000000' ); // Black.
				}

				if ( isset( $post['wcpd-btn-txt-color'] ) && ! empty( $post['wcpd-btn-txt-color'] ) ) {
					update_post_meta( $post_id, 'wcpd-btn-txt-color', $post['wcpd-btn-txt-color'] );
				} else {
					update_post_meta( $post_id, 'wcpd-btn-txt-color', 'FFFFFF' ); // White.
				}

				/**
				* Hook.
				* 
				* @since 1.0.0
				*/
				do_action( 'wcpd_after_update_single_disclaimer_settings', $post_id, $post_after );

			}
		}
		/**
		 * Get settings for a single post.
		 *
		 * Retrieves and organizes settings for a specific disclaimer post identified by its ID.
		 *
		 * @param string $disclaimer_id The ID of the disclaimer post.
		 *
		 * @return object|null An object containing the disclaimer settings or null if the disclaimer ID is empty.
		 */
		public static function get_single_post_settings( $disclaimer_id = '' ) {

			if ( empty( $disclaimer_id ) ) {
				return;
			}

			self::$disclaimer_data = array(
				'wcpd_disclaimer_id'        => $disclaimer_id,
				'wcpd_disclaimer_toggle'    => ! empty( get_post_meta( $disclaimer_id, 'wcpd-disclaimer-toggle', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-disclaimer-toggle', true ) : 'disabled',
				'wcpd_disclaimer_type'      => ! empty( get_post_meta( $disclaimer_id, 'wcpd-disclaimer-type', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-disclaimer-type', true ) : 'global',
				'wcpd_user_roles'           => ! empty( get_post_meta( $disclaimer_id, 'wcpd-user-roles', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-user-roles', true ) : array(
					'toggle' => 'disabled',
					'values' => array(),
				),
				'wcpd_categories'           => ! empty( get_post_meta( $disclaimer_id, 'wcpd-categories', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-categories', true ) : array(
					'toggle' => 'disabled',
					'values' => array(),
				),
				'wcpd_products'             => ! empty( get_post_meta( $disclaimer_id, 'wcpd-products', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-products', true ) : array(
					'toggle'     => 'disabled',
					'inc_values' => array(),
					'exc_values' => array(),
				),
				'wcpd_age_verification'     => ! empty( get_post_meta( $disclaimer_id, 'wcpd-age-verification', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-age-verification', true ) : array(
					'toggle' => 'disabled',
					'values' => '',
				),
				'wcpd_terms_condition'      => ! empty( get_post_meta( $disclaimer_id, 'wcpd-terms-condition', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-terms-condition', true ) : array(
					'toggle' => 'disabled',
					'values' => '',
				),
				'wcpd_display_type'         => ! empty( get_post_meta( $disclaimer_id, 'wcpd-display-type', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-display-type', true ) : 'popup',
				'wcpd_reject_txt'           => ! empty( get_post_meta( $disclaimer_id, 'wcpd-reject-txt', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-reject-txt', true ) : 'Reject',
				'wcpd_reject_url'           => ! empty( get_post_meta( $disclaimer_id, 'wcpd-reject-url', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-reject-url', true ) : '',
				'wcpd_accept_txt'           => ! empty( get_post_meta( $disclaimer_id, 'wcpd-accept-txt', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-accept-txt', true ) : 'Accept',
				'wcpd_popup_style'          => ! empty( get_post_meta( $disclaimer_id, 'wcpd-popup-style', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-popup-style', true ) : 'modern',
				'wcpd_popup_bg_color'       => ! empty( get_post_meta( $disclaimer_id, 'wcpd-popup-bg-color', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-popup-bg-color', true ) : 'FFFFFF',
				'wcpd_popup_txt_color'      => ! empty( get_post_meta( $disclaimer_id, 'wcpd-popup-txt-color', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-popup-txt-color', true ) : '000000',
				'wcpd_btn_bg_color'         => ! empty( get_post_meta( $disclaimer_id, 'wcpd-btn-bg-color', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-btn-bg-color', true ) : '000000',
				'wcpd_btn_txt_color'        => ! empty( get_post_meta( $disclaimer_id, 'wcpd-btn-txt-color', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-btn-txt-color', true ) : 'FFFFFF',
				'wcpd_onpage_thumb_logo'    => ! empty( get_post_meta( $disclaimer_id, 'wcpd-onpage-thumb-logo', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-onpage-thumb-logo', true ) : '',
				'wcpd_disclaimer_bg_color'  => ! empty( get_post_meta( $disclaimer_id, 'wcpd-disclaimer-bg-color', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-disclaimer-bg-color', true ) : 'D7EEFF',
				'wcpd_disclaimer_txt_color' => ! empty( get_post_meta( $disclaimer_id, 'wcpd-disclaimer-txt-color', true ) ) ? get_post_meta( $disclaimer_id, 'wcpd-disclaimer-txt-color', true ) : '807B7B',

			);

			/**
			* Hook.
			* 
			* @since 1.0.0
			*/
			return (object) apply_filters( 'wcpd_disclaimer_object', self::$disclaimer_data, $disclaimer_id );
		}

		/**
		 * Add settings for each menu post
		 *
		 * @param WP_Post $post The post object for which settings are being added.
		 */
		public function wcpd_add_single_post_settings( $post ) {
			// need to add condition for particular post type.
			if ( 'wcpd' === $post->post_type ) {
				$disclaimer_id = $post->ID;
				$data          = self::get_single_post_settings( $disclaimer_id );

				$wcpd_disclaimer_toggle      = isset( $data->wcpd_disclaimer_toggle ) ? $data->wcpd_disclaimer_toggle : '';
				$wcpd_disclaimer_type        = isset( $data->wcpd_disclaimer_type ) ? $data->wcpd_disclaimer_type : 'global';
				$wcpd_user_roles_toggle      = isset( $data->wcpd_user_roles['toggle'] ) ? $data->wcpd_user_roles['toggle'] : '';
				$selected_roles              = isset( $data->wcpd_user_roles['values'] ) ? $data->wcpd_user_roles['values'] : '';
				$wcpd_categories_toggle      = isset( $data->wcpd_categories['toggle'] ) ? $data->wcpd_categories['toggle'] : '';
				$wcpd_categories             = isset( $data->wcpd_categories['values'] ) ? $data->wcpd_categories['values'] : '';
				$wcpd_products_toggle        = isset( $data->wcpd_products['toggle'] ) ? $data->wcpd_products['toggle'] : '';
				$include_product_ids         = isset( $data->wcpd_products['inc_values'] ) ? $data->wcpd_products['inc_values'] : '';
				$exclude_product_ids         = isset( $data->wcpd_products['exc_values'] ) ? $data->wcpd_products['exc_values'] : '';
				$wcpd_age_toggle             = isset( $data->wcpd_age_verification['toggle'] ) ? $data->wcpd_age_verification['toggle'] : '';
				$wcpd_age_verification_txt   = isset( $data->wcpd_age_verification['values'] ) ? $data->wcpd_age_verification['values'] : '';
				$wcpd_terms_condition_toggle = isset( $data->wcpd_terms_condition['toggle'] ) ? $data->wcpd_terms_condition['toggle'] : '';
				$wcpd_terms_condition_txt    = isset( $data->wcpd_terms_condition['values'] ) ? $data->wcpd_terms_condition['values'] : '';
				$wcpd_display_type           = isset( $data->wcpd_display_type ) ? $data->wcpd_display_type : '';
				$wcpd_onpage_thumb_logo      = isset( $data->wcpd_onpage_thumb_logo ) ? $data->wcpd_onpage_thumb_logo : '';
				$wcpd_disclaimer_bg_color    = isset( $data->wcpd_disclaimer_bg_color ) ? $data->wcpd_disclaimer_bg_color : '';
				$wcpd_disclaimer_txt_color   = isset( $data->wcpd_disclaimer_txt_color ) ? $data->wcpd_disclaimer_txt_color : '';
				$wcpd_reject_txt             = isset( $data->wcpd_reject_txt ) ? $data->wcpd_reject_txt : '';
				$wcpd_reject_url             = isset( $data->wcpd_reject_url ) ? $data->wcpd_reject_url : '';
				$wcpd_accept_txt             = isset( $data->wcpd_accept_txt ) ? $data->wcpd_accept_txt : '';
				$wcpd_popup_style            = isset( $data->wcpd_popup_style ) ? $data->wcpd_popup_style : 'modern';
				$wcpd_popup_bg_color         = isset( $data->wcpd_popup_bg_color ) ? $data->wcpd_popup_bg_color : '';
				$wcpd_popup_txt_color        = isset( $data->wcpd_popup_txt_color ) ? $data->wcpd_popup_txt_color : '';
				$wcpd_btn_bg_color           = isset( $data->wcpd_btn_bg_color ) ? $data->wcpd_btn_bg_color : '';
				$wcpd_btn_txt_color          = isset( $data->wcpd_btn_txt_color ) ? $data->wcpd_btn_txt_color : '';

				include WCPD_PATH . '/includes/views/admin/wcpd-single-disclaimer-settings.php';
			}
		}

		/**
		 * Registring rms_menu Post Type
		 */
		public function wcpd_disclaimer_posttype() {

			$labels = array(
				'name'                  => _x( 'WC Product Disclaimer', 'Post type general name', 'wcpd' ),
				'singular_name'         => _x( 'WC Product Disclaimer', 'Post type singular name', 'wcpd' ),
				'menu_name'             => _x( 'WCPD', 'Admin Menu text', 'wcpd' ),
				'name_admin_bar'        => _x( 'WC Product Disclaimer', 'Add New on Toolbar', 'wcpd' ),
				'add_new'               => __( 'Add New', 'wcpd' ),
				'add_new_item'          => __( 'Add New Disclaimer', 'wcpd' ),
				'new_item'              => __( 'New Disclaimer', 'wcpd' ),
				'edit_item'             => __( 'Edit Disclaimer', 'wcpd' ),
				'view_item'             => __( 'View Disclaimer', 'wcpd' ),
				'all_items'             => __( 'All Disclaimer', 'wcpd' ),
				'search_items'          => __( 'Search Disclaimer', 'wcpd' ),
				'parent_item_colon'     => __( 'Parent Disclaimer:', 'wcpd' ),
				'not_found'             => __( 'No Disclaimer found.', 'wcpd' ),
				'not_found_in_trash'    => __( 'No Disclaimer found in Trash.', 'wcpd' ),
				'featured_image'        => _x( 'Disclaimer Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'wcpd' ),
				'set_featured_image'    => _x( 'Set Disclaimer image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'wcpd' ),
				'remove_featured_image' => _x( 'Remove Disclaimer image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'wcpd' ),
				'use_featured_image'    => _x( 'Use as Disclaimer image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'wcpd' ),
				'archives'              => _x( 'Disclaimer archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'wcpd' ),
				'insert_into_item'      => _x( 'Insert into Disclaimer', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'wcpd' ),
				'uploaded_to_this_item' => _x( 'Uploaded to this Disclaimer', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'wcpd' ),
				'filter_items_list'     => _x( 'Filter Disclaimer list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'wcpd' ),
				'items_list_navigation' => _x( 'Disclaimer list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'wcpd' ),
				'items_list'            => _x( 'Disclaimer list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'wcpd' ),
				'attributes'            => esc_html__( 'Disclaimer Priority', 'wcpd' ),
			);

			$args = array(
				'labels'              => $labels,
				'public'              => true,
				'exclude_from_search' => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'query_var'           => true,
				'rewrite'             => array( 'slug' => 'wcpd' ),
				'capability_type'     => 'post',
				'has_archive'         => true,
				'hierarchical'        => false,
				'menu_icon'           => 'dashicons-yes-alt',
				'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			);

			register_post_type( 'wcpd', $args );
		}

		/**
		 * Craete plugin menu page
		 */
		public function wcpd_create_sub_menu() {

			add_submenu_page(
				__( 'edit.php?post_type=wcpd', 'wcpd' ),
				__( 'Settings', 'wcpd' ),
				__( 'Settings', 'wcpd' ),
				__( 'manage_options', 'wcpd' ),
				__( 'wcpd-settings', 'wcpd' ),
				array( $this, 'wcpd_general_setting_view' ),
				10
			);
		}




		/**
		 * General Setting View for Menu Post
		 */
		public function wcpd_general_setting_view() {
			include WCPD_PATH . '/includes/views/admin/wcpd-general-settings.php';
		}

		/**
		 * Singleton Class Instanace
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new WCPD_Disclaimer();
			}

			return self::$instance;
		}
	}

	$GLOBALS['WCPD_DISCLAIMER'] = WCPD_Disclaimer::get_instance();
}
